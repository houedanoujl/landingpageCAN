<?php
/**
 * Script de test : règle "pas de match nul en phase à élimination directe".
 *
 * Vérifie côté serveur (Web + API PredictionController) :
 *  - knockout + score égal sans penalty_winner  => rejet 422
 *  - knockout + score égal + penalty_winner     => sauvegardé, predicted_winner = vainqueur TAB
 *  - group_stage + score égal                   => sauvegardé, predicted_winner = draw, penalty_winner = null
 *  - group_stage + score décisif                => predicted_winner correct (non-régression)
 *  - phase_name de round_of_32                  => "1/16e de finale"
 *
 * Usage: php test_knockout_draw_rule.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\MatchGame;
use App\Models\PointLog;
use App\Models\Prediction;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

$passed = 0;
$failed = 0;

function check(string $label, bool $ok): void
{
    global $passed, $failed;
    if ($ok) {
        $passed++;
        echo "✅ {$label}\n";
    } else {
        $failed++;
        echo "❌ {$label}\n";
    }
}

// --- Préparation de l'environnement de test ---

// Session en mémoire + géofencing désactivé pour des appels déterministes
config(['session.driver' => 'array', 'game.require_venue_geofencing' => false]);

$user = User::first();
if (!$user) {
    echo "❌ Aucun utilisateur trouvé\n";
    exit(1);
}
$pointsBefore = $user->points_total;

// Tournoi non terminé pendant le test (restauré à la fin)
$settings = SiteSetting::first();
$originalTournamentEnded = $settings ? $settings->tournament_ended : null;
if ($settings && $settings->tournament_ended) {
    $settings->update(['tournament_ended' => false]);
}

// Utiliser deux équipes réelles de la base
$teams = \App\Models\Team::take(2)->pluck('id');
if ($teams->count() < 2) {
    echo "❌ Moins de 2 équipes en base\n";
    exit(1);
}

$matchKO = MatchGame::create([
    'home_team_id' => $teams[0],
    'away_team_id' => $teams[1],
    'team_a' => 'Test KO A',
    'team_b' => 'Test KO B',
    'match_date' => now()->addDay(),
    'phase' => 'round_of_32',
    'status' => 'scheduled',
]);

$matchGS = MatchGame::create([
    'home_team_id' => $teams[0],
    'away_team_id' => $teams[1],
    'team_a' => 'Test GS A',
    'team_b' => 'Test GS B',
    'match_date' => now()->addDay(),
    'phase' => 'group_stage',
    'status' => 'scheduled',
]);

session(['user_id' => $user->id]); // auth web (session applicative)
Auth::setUser($user);              // auth API

function callWebStore(array $data)
{
    $request = Request::create('/predictions', 'POST', $data);
    $request->headers->set('X-Requested-With', 'XMLHttpRequest');
    $request->headers->set('Accept', 'application/json');
    $request->setLaravelSession(app('session.store'));
    app()->instance('request', $request);

    return app(\App\Http\Controllers\Web\PredictionController::class)->store($request);
}

function callApiStore(array $data)
{
    $request = Request::create('/api/predictions', 'POST', $data);
    $request->headers->set('Accept', 'application/json');
    app()->instance('request', $request);

    return app(\App\Http\Controllers\Api\PredictionController::class)->store($request);
}

echo "=== TEST RÈGLE KNOCKOUT (pas de match nul) ===\n\n";

// 1a. WEB : knockout + 1-1 sans penalty_winner => 422
$res = callWebStore(['match_id' => $matchKO->id, 'score_a' => 1, 'score_b' => 1]);
check('WEB knockout 1-1 sans penalty_winner => 422', $res->getStatusCode() === 422);

// 1b. API : knockout + 1-1 sans penalty_winner => 422
$res = callApiStore(['match_id' => $matchKO->id, 'score_a' => 1, 'score_b' => 1]);
check('API knockout 1-1 sans penalty_winner => 422', $res->getStatusCode() === 422);

// 2. WEB : knockout + 1-1 + penalty_winner=home => sauvegardé, predicted_winner=home
$res = callWebStore([
    'match_id' => $matchKO->id, 'score_a' => 1, 'score_b' => 1,
    'predict_draw' => '1', 'penalty_winner' => 'home',
]);
$pred = Prediction::where('user_id', $user->id)->where('match_id', $matchKO->id)->first();
check('WEB knockout 1-1 + TAB home => 200', $res->getStatusCode() === 200);
check('WEB knockout 1-1 + TAB home => predicted_winner=home', $pred && $pred->predicted_winner === 'home');

// 2b. API : knockout + 1-1 + penalty_winner=away => predicted_winner=away
$res = callApiStore([
    'match_id' => $matchKO->id, 'score_a' => 1, 'score_b' => 1,
    'penalty_winner' => 'away',
]);
$pred = Prediction::where('user_id', $user->id)->where('match_id', $matchKO->id)->first();
check('API knockout 1-1 + TAB away => 200', $res->getStatusCode() === 200);
check('API knockout 1-1 + TAB away => predicted_winner=away', $pred && $pred->predicted_winner === 'away');

// 3. WEB : group_stage + 1-1 (penalty_winner envoyé par un client malicieux) => draw, TAB ignoré
$res = callWebStore([
    'match_id' => $matchGS->id, 'score_a' => 1, 'score_b' => 1,
    'predict_draw' => '1', 'penalty_winner' => 'home',
]);
$pred = Prediction::where('user_id', $user->id)->where('match_id', $matchGS->id)->first();
check('WEB group_stage 1-1 => 200', $res->getStatusCode() === 200);
check('WEB group_stage 1-1 => predicted_winner=draw', $pred && $pred->predicted_winner === 'draw');
check('WEB group_stage 1-1 => penalty_winner=null', $pred && $pred->penalty_winner === null);

// 4. WEB : group_stage + 2-0 => predicted_winner=home (non-régression)
$res = callWebStore(['match_id' => $matchGS->id, 'score_a' => 2, 'score_b' => 0]);
$pred = Prediction::where('user_id', $user->id)->where('match_id', $matchGS->id)->first();
check('WEB group_stage 2-0 => predicted_winner=home', $pred && $pred->predicted_winner === 'home');

// 5. Libellé de phase
check("phase_name round_of_32 => '1/16e de finale'", $matchKO->phase_name === '1/16e de finale');

// 6. Accessor allows_draw : nul autorisé seulement en poules
check('allows_draw group_stage => true', $matchGS->allows_draw === true);
check('allows_draw round_of_32 => false', $matchKO->allows_draw === false);
foreach (['round_of_16', 'quarter_final', 'semi_final', 'third_place', 'final'] as $phase) {
    $matchKO->phase = $phase;
    check("allows_draw {$phase} => false", $matchKO->allows_draw === false);
}
$matchKO->phase = 'round_of_32'; // restaurer (non sauvegardé)

// 7. Vainqueur accepté dans toutes les phases (score décisif, knockout)
$res = callWebStore(['match_id' => $matchKO->id, 'score_a' => 2, 'score_b' => 1]);
$pred = Prediction::where('user_id', $user->id)->where('match_id', $matchKO->id)->first();
check('WEB knockout 2-1 => 200', $res->getStatusCode() === 200);
check('WEB knockout 2-1 => predicted_winner=home', $pred && $pred->predicted_winner === 'home');

// --- Nettoyage ---
echo "\n=== NETTOYAGE ===\n";
Prediction::whereIn('match_id', [$matchKO->id, $matchGS->id])->delete();
PointLog::whereIn('match_id', [$matchKO->id, $matchGS->id])->delete();
$matchKO->delete();
$matchGS->delete();
$user->update(['points_total' => $pointsBefore]);
if ($settings && $originalTournamentEnded) {
    $settings->update(['tournament_ended' => $originalTournamentEnded]);
}
echo "✅ Données de test supprimées, points restaurés\n";

echo "\n=== RÉSULTAT : {$passed} OK, {$failed} KO ===\n";
exit($failed > 0 ? 1 : 0);
