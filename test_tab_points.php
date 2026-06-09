<?php
/**
 * Script de test pour vérifier le calcul des points avec Tirs Au But
 * 
 * Usage: php test_tab_points.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\MatchGame;
use App\Models\Prediction;
use App\Models\User;
use App\Jobs\ProcessMatchPoints;

// Test 1: Simuler un match avec TAB
echo "=== TEST TIRS AU BUT ===\n\n";

// Créer un match test (équipes réelles de la base pour respecter la FK)
$teams = \App\Models\Team::take(2)->pluck('id');
if ($teams->count() < 2) {
    echo "❌ Moins de 2 équipes en base\n";
    exit(1);
}

$match = MatchGame::create([
    'home_team_id' => $teams[0],
    'away_team_id' => $teams[1],
    'team_a' => 'Test A',
    'team_b' => 'Test B',
    'match_date' => now(),
    'phase' => 'group_stage',
    'status' => 'finished',
    'score_a' => 2,
    'score_b' => 2,
    'winner' => 'away', // Team B gagne aux TAB
]);

echo "✅ Match créé: {$match->team_a} {$match->score_a} - {$match->score_b} {$match->team_b}\n";
echo "   Vainqueur TAB: " . ($match->winner === 'away' ? $match->team_b : $match->team_a) . "\n\n";

// Créer un utilisateur test
$user = User::first();
if (!$user) {
    echo "❌ Aucun utilisateur trouvé\n";
    exit;
}

// Créer une prédiction correcte avec TAB
$prediction = Prediction::create([
    'user_id' => $user->id,
    'match_id' => $match->id,
    'predicted_winner' => 'away',
    'score_a' => 2,
    'score_b' => 2,
    'predict_draw' => true,
    'penalty_winner' => 'away',
]);

echo "✅ Prédiction créée pour l'utilisateur {$user->name}\n";
echo "   Score prédit: {$prediction->score_a} - {$prediction->score_b}\n";
echo "   TAB prédit: " . ($prediction->penalty_winner === 'away' ? 'Team B' : 'Team A') . "\n\n";

// Calculer les points
$pointsBefore = $user->points_total;
echo "Points avant calcul: {$pointsBefore}\n";

// Simuler le job de calcul des points
ProcessMatchPoints::dispatchSync($match->id);

// Recharger l'utilisateur pour voir les nouveaux points
$user->refresh();
$pointsAfter = $user->points_total;
$pointsEarned = $pointsAfter - $pointsBefore;

echo "Points après calcul: {$pointsAfter}\n";
echo "Points gagnés: {$pointsEarned}\n\n";

// Vérifier les détails des points
$pointLogs = \App\Models\PointLog::where('user_id', $user->id)
    ->where('match_id', $match->id)
    ->get();

echo "=== DÉTAIL DES POINTS ===\n";
foreach ($pointLogs as $log) {
    echo "• {$log->source}: +{$log->points} pts\n";
}

// Vérifier que le score exact n'a PAS été attribué
$hasExactScore = $pointLogs->where('source', 'prediction_exact')->count() > 0;
if ($hasExactScore) {
    echo "\n❌ ERREUR: Des points de score exact ont été attribués pour un match TAB!\n";
} else {
    echo "\n✅ CORRECT: Aucun point de score exact pour le match TAB\n";
}

// Nettoyer les données de test
echo "\n=== NETTOYAGE ===\n";
\App\Models\PointLog::where('match_id', $match->id)->delete();
$prediction->delete();
$match->delete();
echo "✅ Données de test supprimées\n";

echo "\n=== TEST TERMINÉ ===\n";
