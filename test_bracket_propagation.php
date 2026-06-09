<?php
/**
 * Script de test : propagation du bracket Coupe du Monde 2026.
 *
 * Vérifie :
 *  - vainqueur (score décisif) propagé vers le slot "W{n}" du match suivant
 *  - vainqueur aux tirs au but (score égal + winner) propagé correctement
 *  - perdants des demi-finales propagés vers la 3e place (slots "L{n}")
 *  - createKnockoutBracket refuse de dupliquer un bracket existant
 *
 * Utilise des numéros de matchs fictifs (99xx) pour ne jamais toucher
 * au vrai bracket (n°73-104).
 *
 * Usage: php test_bracket_propagation.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\MatchGame;
use App\Models\Team;
use App\Services\TournamentService;

$passed = 0;
$failed = 0;

function check(string $label, bool $ok): void
{
    global $passed, $failed;
    if ($ok) { $passed++; echo "✅ {$label}\n"; }
    else { $failed++; echo "❌ {$label}\n"; }
}

$teams = Team::take(4)->get();
if ($teams->count() < 4) {
    echo "❌ Moins de 4 équipes en base\n";
    exit(1);
}
[$t1, $t2, $t3, $t4] = [$teams[0], $teams[1], $teams[2], $teams[3]];

echo "=== TEST PROPAGATION BRACKET ===\n\n";

$make = fn (array $attrs) => MatchGame::create(array_merge([
    'match_date' => now()->addDay(),
    'stadium' => 'Test',
    'status' => 'scheduled',
], $attrs));

// Demi-finales fictives n°9901 / n°9902
$semi1 = $make(['phase' => 'semi_final', 'match_number' => 9901,
    'home_team_id' => $t1->id, 'away_team_id' => $t2->id, 'team_a' => $t1->name, 'team_b' => $t2->name]);
$semi2 = $make(['phase' => 'semi_final', 'match_number' => 9902,
    'home_team_id' => $t3->id, 'away_team_id' => $t4->id, 'team_a' => $t3->name, 'team_b' => $t4->name]);

// Finale + 3e place fictives consommant W/L
$final = $make(['phase' => 'final', 'match_number' => 9904, 'team_a' => 'W9901', 'team_b' => 'W9902']);
$third = $make(['phase' => 'third_place', 'match_number' => 9903, 'team_a' => 'L9901', 'team_b' => 'L9902']);

// 0. Mode manuel (défaut) : AUCUNE propagation automatique
config(['game.auto_bracket_propagation' => false]);
$semi1->update(['status' => 'finished', 'score_a' => 2, 'score_b' => 1]);
$final->refresh();
check('Mode manuel (défaut) : slot W9901 intact, pas de propagation', $final->team_a === 'W9901' && $final->home_team_id === null);

// Réinitialiser pour tester le mode automatique
$semi1->update(['status' => 'scheduled', 'score_a' => null, 'score_b' => null]);
config(['game.auto_bracket_propagation' => true]);

// 1. Score décisif : t1 bat t2 (2-1) -> W9901 = t1, L9901 = t2
$semi1->update(['status' => 'finished', 'score_a' => 2, 'score_b' => 1]);
$final->refresh(); $third->refresh();
check('Score décisif : W9901 -> finale home = ' . $t1->name, $final->home_team_id === $t1->id && $final->team_a === $t1->name);
check('Score décisif : L9901 -> 3e place home = ' . $t2->name, $third->home_team_id === $t2->id && $third->team_a === $t2->name);

// 2. Tirs au but : 1-1, t4 gagne aux TAB -> W9902 = t4, L9902 = t3
$semi2->update(['status' => 'finished', 'score_a' => 1, 'score_b' => 1, 'winner' => 'away']);
$final->refresh(); $third->refresh();
check('TAB : W9902 -> finale away = ' . $t4->name, $final->away_team_id === $t4->id && $final->team_b === $t4->name);
check('TAB : L9902 -> 3e place away = ' . $t3->name, $third->away_team_id === $t3->id && $third->team_b === $t3->name);

// 3. Pas d'écrasement : re-sauvegarder le parent ne change rien (slots déjà résolus)
$semi1->update(['score_a' => 3]);
$final->refresh();
check('Pas d\'écrasement de slot déjà résolu', $final->home_team_id === $t1->id);

// 4. Garde anti-duplication du bracket
try {
    app(TournamentService::class)->createKnockoutBracket();
    check('createKnockoutBracket refuse si bracket existant', false);
} catch (\Exception $e) {
    check('createKnockoutBracket refuse si bracket existant', str_contains($e->getMessage(), 'existe déjà'));
}

// 5. resolveWinnerLoser : égalité sans vainqueur TAB -> aucun vainqueur
$semi2->winner = null;
[$w, $l] = app(TournamentService::class)->resolveWinnerLoser($semi2);
check('Égalité sans winner TAB -> pas de propagation', $w === null && $l === null);

// --- Nettoyage ---
echo "\n=== NETTOYAGE ===\n";
MatchGame::whereIn('match_number', [9901, 9902, 9903, 9904])->delete();
echo "✅ Matchs de test supprimés\n";

echo "\n=== RÉSULTAT : {$passed} OK, {$failed} KO ===\n";
exit($failed > 0 ? 1 : 0);
