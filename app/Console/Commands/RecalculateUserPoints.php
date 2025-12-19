<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\PointLog;
use App\Models\Prediction;
use App\Models\MatchGame;
use App\Jobs\ProcessMatchPoints;
use Illuminate\Console\Command;

class RecalculateUserPoints extends Command
{
    protected $signature = 'user:recalculate-points {phone}';
    protected $description = 'Recalcule proprement les points d\'un utilisateur pour les matchs terminés';

    public function handle()
    {
        $phone = $this->argument('phone');
        
        $user = User::where('phone', $phone)->first();
        
        if (!$user) {
            $this->error("❌ Utilisateur avec le numéro {$phone} non trouvé.");
            return 1;
        }
        
        $this->info("👤 Utilisateur: {$user->name}");
        $this->line("📊 Points actuels: {$user->points_total} pts");
        $this->newLine();
        
        // 1. Supprimer TOUS les logs de points existants
        $this->info('🗑️  Suppression des anciens logs...');
        $deleted = PointLog::where('user_id', $user->id)->delete();
        $this->line("   {$deleted} logs supprimés");
        
        // 2. Réinitialiser le total
        $this->info('🔄 Réinitialisation du compteur...');
        $user->points_total = 0;
        $user->save();
        
        // 3. Récupérer uniquement les matchs TERMINÉS où l'utilisateur a pronostiqué
        $this->info('📋 Recherche des matchs terminés avec pronostics...');
        
        $predictions = Prediction::where('user_id', $user->id)
            ->whereHas('match', function($q) {
                $q->where('status', 'finished')
                  ->whereNotNull('score_a')
                  ->whereNotNull('score_b');
            })
            ->get();
        
        $this->line("   {$predictions->count()} matchs terminés trouvés");
        $this->newLine();
        
        // 4. Recalculer les points pour chaque match terminé
        if ($predictions->count() > 0) {
            $this->info('⚙️  Recalcul des points pour les matchs terminés...');
            
            foreach ($predictions as $prediction) {
                $match = MatchGame::find($prediction->match_id);
                $this->line("   • Match {$match->id}: {$match->team_a} vs {$match->team_b}");
                
                // Utiliser le job ProcessMatchPoints pour recalculer
                ProcessMatchPoints::dispatchSync($match->id);
            }
            
            $this->newLine();
            $this->info('✅ Recalcul terminé !');
        } else {
            $this->warn('ℹ️  Aucun match terminé à recalculer');
        }
        
        // 5. Afficher le résultat
        $user->refresh();
        $newLogs = PointLog::where('user_id', $user->id)->count();
        
        $this->newLine();
        $this->info('📊 RÉSULTAT FINAL');
        $this->line("👤 {$user->name}");
        $this->line("🎯 Points recalculés: {$user->points_total} pts");
        $this->line("📝 Logs de points: {$newLogs}");
        
        return 0;
    }
}
