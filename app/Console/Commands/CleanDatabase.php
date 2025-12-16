<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\MatchGame;
use App\Models\Bar;
use App\Models\Prediction;
use App\Models\PointLog;
use Illuminate\Console\Command;

class CleanDatabase extends Command
{
    protected $signature = 'db:clean {--force : Skip confirmation}';
    protected $description = 'Clean database: remove users (except admin), matches, PDV, and related data. Keep teams.';

    public function handle()
    {
        // Check if --force flag is provided
        if (!$this->option('force')) {
            $this->warn('⚠️  ATTENTION: Cette opération va supprimer:');
            $this->warn('  - Tous les utilisateurs SAUF l\'admin');
            $this->warn('  - Tous les matchs');
            $this->warn('  - Tous les points de vente (PDV)');
            $this->warn('  - Tous les pronostics');
            $this->warn('  - Tous les logs de points');
            $this->info('');
            $this->info('✅ Sera CONSERVÉ:');
            $this->info('  - Les équipes (Teams)');
            $this->info('  - L\'utilisateur admin');
            $this->info('');

            if (!$this->confirm('Êtes-vous sûr de vouloir continuer?')) {
                $this->info('Opération annulée.');
                return 0;
            }
        }

        // Create backup before cleaning
        $this->info('');
        $this->info('📦 Création d\'un backup de sécurité avant nettoyage...');
        $backupResult = $this->call('db:backup');

        if ($backupResult !== 0) {
            $this->error('❌ Impossible de créer le backup. Nettoyage annulé.');
            return 1;
        }

        $this->info('');

        try {
            // Start transaction
            \DB::beginTransaction();

            // 1. Delete all predictions (they reference matches and users)
            $predictionCount = Prediction::count();
            Prediction::truncate();
            $this->info("✅ Suppression de $predictionCount pronostics");

            // 2. Delete all point logs (they reference users and matches)
            $pointLogCount = PointLog::count();
            PointLog::truncate();
            $this->info("✅ Suppression de $pointLogCount logs de points");

            // 3. Delete all matches
            $matchCount = MatchGame::count();
            MatchGame::truncate();
            $this->info("✅ Suppression de $matchCount matchs");

            // 4. Delete all bars/PDV
            $barCount = Bar::count();
            Bar::truncate();
            $this->info("✅ Suppression de $barCount points de vente (PDV)");

            // 5. Delete all users except admin
            $adminUser = User::where('is_admin', true)->first();
            $adminId = $adminUser?->id;

            if ($adminId) {
                $usersDeleted = User::where('id', '!=', $adminId)->delete();
                $this->info("✅ Suppression de $usersDeleted utilisateurs (admin conservé: {$adminUser->name})");
            } else {
                $usersDeleted = User::delete();
                $this->warn("⚠️  Aucun admin trouvé. Tous les utilisateurs ont été supprimés.");
            }

            // Reset auto-increment for better organization
            $this->resetAutoIncrement();

            // Commit transaction
            \DB::commit();

            $this->info('');
            $this->info('✅ Nettoyage de la base de données terminé avec succès!');
            $this->info('');
            $this->info('État final:');
            $this->info('  - Équipes: ' . \App\Models\Team::count() . ' équipes');
            $this->info('  - Utilisateurs: ' . User::count() . ' utilisateur(s)');
            $this->info('  - Matchs: ' . MatchGame::count() . ' match(s)');
            $this->info('  - PDV: ' . Bar::count() . ' point(s) de vente');
            $this->info('  - Pronostics: ' . Prediction::count() . ' pronostic(s)');
            $this->info('  - Logs de points: ' . PointLog::count() . ' log(s)');

            return 0;
        } catch (\Exception $e) {
            \DB::rollBack();
            $this->error('❌ Erreur lors du nettoyage: ' . $e->getMessage());
            return 1;
        }
    }

    private function resetAutoIncrement()
    {
        try {
            // Reset auto-increment for tables
            \DB::statement('ALTER TABLE predictions AUTO_INCREMENT = 1');
            \DB::statement('ALTER TABLE point_logs AUTO_INCREMENT = 1');
            \DB::statement('ALTER TABLE matches AUTO_INCREMENT = 1');
            \DB::statement('ALTER TABLE bars AUTO_INCREMENT = 1');
            
            // Reset users auto-increment but keep admin's ID
            $adminId = User::where('is_admin', true)->value('id') ?? 1;
            $nextId = $adminId + 1;
            \DB::statement("ALTER TABLE users AUTO_INCREMENT = $nextId");
        } catch (\Exception $e) {
            // Silently fail if statement doesn't work (different DB engines)
        }
    }
}
