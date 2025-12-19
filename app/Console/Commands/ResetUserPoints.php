<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\PointLog;
use Illuminate\Console\Command;

class ResetUserPoints extends Command
{
    protected $signature = 'user:reset-points {phone} {--keep-predictions}';
    protected $description = 'Réinitialise les points d\'un utilisateur à zéro';

    public function handle()
    {
        $phone = $this->argument('phone');
        $keepPredictions = $this->option('keep-predictions');
        
        // Trouver l'utilisateur
        $user = User::where('phone', $phone)->first();
        
        if (!$user) {
            $this->error("❌ Utilisateur avec le numéro {$phone} non trouvé.");
            return 1;
        }
        
        $this->info("👤 Utilisateur trouvé: {$user->name}");
        $this->line("📊 Points actuels: {$user->points_total} pts");
        $this->newLine();
        
        // Compter les logs de points
        $logsCount = PointLog::where('user_id', $user->id)->count();
        $this->line("🗂️  Logs de points: {$logsCount}");
        $this->newLine();
        
        // Demander confirmation
        if (!$this->confirm('⚠️  Voulez-vous RÉELLEMENT réinitialiser les points à zéro ?', false)) {
            $this->warn('❌ Opération annulée.');
            return 0;
        }
        
        // Supprimer tous les logs de points
        $this->info('🗑️  Suppression des logs de points...');
        $deleted = PointLog::where('user_id', $user->id)->delete();
        $this->line("   Logs supprimés: {$deleted}");
        
        // Réinitialiser les points
        $this->info('🔄 Réinitialisation du compteur de points...');
        $user->points_total = 0;
        $user->save();
        
        // IMPORTANT: Marquer aussi les pronostics comme "points_earned = 0"
        // pour éviter qu'ils soient recalculés automatiquement
        $this->info('📝 Réinitialisation des points sur les pronostics...');
        $predictions = \App\Models\Prediction::where('user_id', $user->id)->update(['points_earned' => 0]);
        $this->line("   {$predictions} pronostics réinitialisés");
        
        // Optionnel : supprimer aussi les pronostics
        if (!$keepPredictions) {
            if ($this->confirm('🎯 Voulez-vous aussi supprimer tous les pronostics de cet utilisateur ?', false)) {
                $predCount = \App\Models\Prediction::where('user_id', $user->id)->count();
                \App\Models\Prediction::where('user_id', $user->id)->delete();
                $this->line("   Pronostics supprimés: {$predCount}");
            }
        }
        
        $this->newLine();
        $this->info('✅ Réinitialisation terminée!');
        $this->newLine();
        
        // Afficher le résultat
        $user->refresh();
        $this->line("👤 {$user->name}");
        $this->line("📊 Points: {$user->points_total} pts");
        $this->line("🗂️  Logs: " . PointLog::where('user_id', $user->id)->count());
        $this->line("🎯 Pronostics: " . \App\Models\Prediction::where('user_id', $user->id)->count());
        
        return 0;
    }
}
