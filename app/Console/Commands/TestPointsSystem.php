<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\MatchGame;
use App\Models\Prediction;
use App\Models\PointLog;
use App\Jobs\ProcessMatchPoints;
use Illuminate\Console\Command;

class TestPointsSystem extends Command
{
    protected $signature = 'test:points-system {--phone=+2250748348221}';
    protected $description = 'Test complet du système de points';

    public function handle()
    {
        $phone = $this->option('phone');
        $user = User::where('phone', $phone)->first();
        
        if (!$user) {
            $this->error("❌ Utilisateur non trouvé: {$phone}");
            return 1;
        }
        
        $this->info("╔══════════════════════════════════════════╗");
        $this->info("║    TEST DU SYSTÈME DE POINTS             ║");
        $this->info("╚══════════════════════════════════════════╝");
        $this->newLine();
        
        // Étape 1 : État initial
        $this->info("📊 ÉTAT INITIAL");
        $this->line("👤 Utilisateur: {$user->name}");
        $this->line("📱 Téléphone: {$user->phone}");
        $this->line("🎯 Points actuels: {$user->points_total} pts");
        $this->line("📝 Logs de points: " . PointLog::where('user_id', $user->id)->count());
        $this->line("🎲 Pronostics: " . Prediction::where('user_id', $user->id)->count());
        $this->newLine();
        
        // Étape 2 : Réinitialisation
        $this->info("🔄 RÉINITIALISATION DES POINTS");
        
        // Supprimer les logs
        $deleted = PointLog::where('user_id', $user->id)->delete();
        $this->line("   ✓ {$deleted} logs supprimés");
        
        // Réinitialiser le total
        $user->points_total = 0;
        $user->save();
        $this->line("   ✓ Points remis à 0");
        
        // Réinitialiser points_earned sur les pronostics
        $predictions = Prediction::where('user_id', $user->id)->update(['points_earned' => 0]);
        $this->line("   ✓ {$predictions} pronostics réinitialisés");
        $this->newLine();
        
        // Étape 3 : Créer un pronostic test
        $this->info("🎲 CRÉATION D'UN PRONOSTIC TEST");
        
        // Trouver ou créer un match test
        $match = MatchGame::where('status', 'finished')
            ->whereNotNull('score_a')
            ->whereNotNull('score_b')
            ->first();
            
        if (!$match) {
            $this->warn("   ⚠️ Aucun match terminé trouvé");
            return 1;
        }
        
        $this->line("   Match: {$match->team_a} vs {$match->team_b}");
        $this->line("   Score réel: {$match->score_a} - {$match->score_b}");
        
        // Supprimer l'ancien pronostic si existe
        Prediction::where('user_id', $user->id)
            ->where('match_id', $match->id)
            ->delete();
        
        // Créer un nouveau pronostic (score exact pour test)
        $prediction = Prediction::create([
            'user_id' => $user->id,
            'match_id' => $match->id,
            'score_a' => $match->score_a,
            'score_b' => $match->score_b,
            'predicted_winner' => $match->score_a > $match->score_b ? 'home' : 
                                 ($match->score_a < $match->score_b ? 'away' : 'draw'),
            'predict_draw' => $match->score_a == $match->score_b,
            'points_earned' => 0, // Important: commencer à 0
        ]);
        
        $this->line("   ✓ Pronostic créé: {$match->score_a} - {$match->score_b}");
        $this->newLine();
        
        // Étape 4 : Calculer les points
        $this->info("⚙️ CALCUL DES POINTS");
        
        // Utiliser dispatchSync pour exécution immédiate
        ProcessMatchPoints::dispatchSync($match->id);
        
        $this->line("   ✓ Job ProcessMatchPoints exécuté");
        $this->newLine();
        
        // Étape 5 : Vérifier le résultat
        $this->info("✅ RÉSULTAT APRÈS CALCUL");
        
        $user->refresh();
        $newLogs = PointLog::where('user_id', $user->id)->where('match_id', $match->id)->get();
        
        $this->line("👤 {$user->name}");
        $this->line("🎯 Points totaux: {$user->points_total} pts");
        $this->line("📝 Détail des points attribués:");
        
        foreach ($newLogs as $log) {
            $this->line("   • {$log->source}: +{$log->points} pts");
        }
        
        if ($user->points_total === 0) {
            $this->error("⚠️ PROBLÈME: Aucun point n'a été attribué!");
        } else {
            $this->info("✅ Système de points fonctionnel!");
        }
        
        $this->newLine();
        
        // Test supplémentaire : Vérifier que les points ne se dupliquent pas
        $this->info("🔁 TEST DE NON-DUPLICATION");
        
        $pointsBefore = $user->points_total;
        ProcessMatchPoints::dispatchSync($match->id);
        $user->refresh();
        $pointsAfter = $user->points_total;
        
        if ($pointsBefore === $pointsAfter) {
            $this->info("   ✅ Pas de duplication (points identiques: {$pointsAfter} pts)");
        } else {
            $this->error("   ❌ DUPLICATION DÉTECTÉE! Avant: {$pointsBefore} pts, Après: {$pointsAfter} pts");
        }
        
        return 0;
    }
}
