<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\PointsService;
use Illuminate\Console\Command;

class TestLoginBonus extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'test:login-bonus {phone?}';

    /**
     * The console command description.
     */
    protected $description = 'Test the daily login bonus points attribution';

    /**
     * Execute the console command.
     */
    public function handle(PointsService $pointsService)
    {
        $phone = $this->argument('phone') ?? '+22500000001';
        
        $this->info("🧪 Test du bonus de connexion quotidienne");
        $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        
        // Trouver ou créer l'utilisateur test
        $user = User::firstOrCreate(
            ['phone' => $phone],
            ['name' => 'Test User ' . substr($phone, -4)]
        );
        
        $this->info("📱 Utilisateur: {$user->name} ({$user->phone})");
        $this->info("💰 Points avant: {$user->points_total}");
        
        // Vérifier si déjà connecté aujourd'hui
        $alreadyConnected = \App\Models\PointLog::where('user_id', $user->id)
            ->where('source', 'login')
            ->whereDate('created_at', today())
            ->exists();
        
        if ($alreadyConnected) {
            $this->warn("⚠️  Cet utilisateur a déjà reçu son bonus aujourd'hui");
        }
        
        // Attribuer les points
        $pointsService->awardDailyLoginPoints($user);
        
        // Recharger l'utilisateur
        $user->refresh();
        
        $this->info("💰 Points après: {$user->points_total}");
        
        // Vérifier le log
        $lastLog = \App\Models\PointLog::where('user_id', $user->id)
            ->where('source', 'login')
            ->latest()
            ->first();
        
        if ($lastLog && $lastLog->created_at->isToday()) {
            $this->info("✅ Log créé: +{$lastLog->points} point(s) - {$lastLog->created_at->format('H:i:s')}");
        } else {
            $this->warn("❌ Aucun nouveau log créé (déjà attribué aujourd'hui)");
        }
        
        $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        
        // Afficher tous les logs de connexion de l'utilisateur
        $this->info("📜 Historique des bonus de connexion:");
        $logs = \App\Models\PointLog::where('user_id', $user->id)
            ->where('source', 'login')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();
        
        if ($logs->isEmpty()) {
            $this->line("   Aucun historique");
        } else {
            foreach ($logs as $log) {
                $this->line("   - {$log->created_at->format('d/m/Y H:i')} : +{$log->points} pt");
            }
        }
        
        return Command::SUCCESS;
    }
}
