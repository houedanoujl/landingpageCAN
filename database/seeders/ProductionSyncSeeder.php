<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Team;
use App\Models\Bar;
use App\Models\MatchGame;
use App\Models\Animation;
use App\Models\Stadium;
use App\Models\User;
use App\Models\Prediction;
use App\Models\PointLog;

class ProductionSyncSeeder extends Seeder
{
    /**
     * SEEDER DE SYNCHRONISATION LOCAL → PRODUCTION
     *
     * Ce seeder exporte les données locales et les importe en production
     * en préservant les données utilisateurs existantes.
     *
     * Usage:
     * - Local: php artisan db:seed --class=ProductionSyncSeeder --export
     * - Production: php artisan db:seed --class=ProductionSyncSeeder --import
     */
    
    protected $exportFile = 'storage/app/production_sync.json';
    protected $preserveTables = [
        'users',
        'predictions',
        'point_logs',
        'password_reset_tokens',
        'personal_access_tokens',
        'sessions',
        'cache',
        'cache_locks',
        'jobs',
        'failed_jobs',
    ];
    
    public function run(): void
    {
        $action = $this->command->option('export') ? 'export' : 
                  ($this->command->option('import') ? 'import' : 'sync');
        
        $this->command->newLine();
        $this->command->info('╔════════════════════════════════════════════════╗');
        $this->command->info('║   SYNCHRONISATION LOCAL → PRODUCTION          ║');
        $this->command->info('║   Mode: ' . strtoupper($action) . str_repeat(' ', 40 - strlen($action)) . '║');
        $this->command->info('╚════════════════════════════════════════════════╝');
        $this->command->newLine();
        
        switch ($action) {
            case 'export':
                $this->exportData();
                break;
            case 'import':
                $this->importData();
                break;
            default:
                $this->syncData();
                break;
        }
    }
    
    /**
     * Export les données locales vers un fichier
     */
    protected function exportData(): void
    {
        $this->command->info('📦 Export des données locales...');
        
        $data = [];
        
        // Export des équipes
        $data['teams'] = Team::all()->toArray();
        $this->command->line('   ✓ Teams: ' . count($data['teams']));
        
        // Export des stades
        $data['stadiums'] = Stadium::all()->toArray();
        $this->command->line('   ✓ Stadiums: ' . count($data['stadiums']));
        
        // Export des PDV
        $data['bars'] = Bar::all()->toArray();
        $this->command->line('   ✓ Venues: ' . count($data['bars']));
        
        // Export des matchs
        $data['matches'] = MatchGame::all()->toArray();
        $this->command->line('   ✓ Matches: ' . count($data['matches']));
        
        // Export des animations
        $data['animations'] = Animation::all()->toArray();
        $this->command->line('   ✓ Animations: ' . count($data['animations']));
        
        // Métadonnées
        $data['metadata'] = [
            'exported_at' => now()->toIso8601String(),
            'environment' => app()->environment(),
            'version' => '1.0',
            'user_count' => User::count(),
            'prediction_count' => Prediction::count(),
        ];
        
        // Sauvegarder dans un fichier
        $json = json_encode($data, JSON_PRETTY_PRINT);
        file_put_contents(base_path($this->exportFile), $json);
        
        $this->command->newLine();
        $this->command->info('✅ Export terminé!');
        $this->command->info('📁 Fichier: ' . $this->exportFile);
        $this->command->info('📊 Taille: ' . $this->formatBytes(strlen($json)));
        $this->command->newLine();
        
        // Instructions
        $this->command->info('📋 Prochaines étapes:');
        $this->command->line('1. Copier le fichier sur le serveur de production');
        $this->command->line('2. Exécuter sur production: php artisan db:seed --class=ProductionSyncSeeder --import');
    }
    
    /**
     * Import les données depuis un fichier
     */
    protected function importData(): void
    {
        if (!file_exists(base_path($this->exportFile))) {
            $this->command->error('❌ Fichier d\'export non trouvé: ' . $this->exportFile);
            return;
        }
        
        $this->command->info('📥 Import des données...');
        
        $json = file_get_contents(base_path($this->exportFile));
        $data = json_decode($json, true);
        
        if (!$data) {
            $this->command->error('❌ Erreur lors de la lecture du fichier JSON');
            return;
        }
        
        // Afficher les métadonnées
        $this->command->info('📊 Métadonnées:');
        $this->command->line('   Exporté le: ' . $data['metadata']['exported_at']);
        $this->command->line('   Environnement source: ' . $data['metadata']['environment']);
        $this->command->line('   Users dans export: ' . $data['metadata']['user_count']);
        $this->command->line('   Predictions dans export: ' . $data['metadata']['prediction_count']);
        $this->command->newLine();
        
        // Sauvegarder l'état actuel
        $currentStats = $this->getCurrentStats();
        $this->command->info('📊 État actuel:');
        foreach ($currentStats as $table => $count) {
            $this->command->line("   $table: $count");
        }
        $this->command->newLine();
        
        // Confirmation
        if (!$this->command->confirm('⚠️  Voulez-vous continuer? Les données de planning seront REMPLACÉES')) {
            $this->command->warn('Import annulé.');
            return;
        }
        
        DB::beginTransaction();
        
        try {
            // Désactiver les contraintes de clé étrangère
            Schema::disableForeignKeyConstraints();
            
            // 1. Nettoyer les tables (sauf utilisateurs)
            $this->command->info('🗑️ Nettoyage des tables...');
            DB::table('animations')->truncate();
            DB::table('matches')->truncate();
            DB::table('bars')->truncate();
            DB::table('stadiums')->truncate();
            DB::table('teams')->truncate();
            
            // 2. Importer les équipes
            $this->command->info('📥 Import des teams...');
            foreach ($data['teams'] as $team) {
                Team::create($team);
            }
            $this->command->line('   ✓ ' . count($data['teams']) . ' teams importées');
            
            // 3. Importer les stades
            $this->command->info('📥 Import des stadiums...');
            foreach ($data['stadiums'] as $stadium) {
                Stadium::create($stadium);
            }
            $this->command->line('   ✓ ' . count($data['stadiums']) . ' stadiums importés');
            
            // 4. Importer les PDV
            $this->command->info('📥 Import des venues...');
            foreach ($data['bars'] as $bar) {
                Bar::create($bar);
            }
            $this->command->line('   ✓ ' . count($data['bars']) . ' venues importés');
            
            // 5. Importer les matchs
            $this->command->info('📥 Import des matches...');
            foreach ($data['matches'] as $match) {
                MatchGame::create($match);
            }
            $this->command->line('   ✓ ' . count($data['matches']) . ' matches importés');
            
            // 6. Importer les animations
            $this->command->info('📥 Import des animations...');
            foreach ($data['animations'] as $animation) {
                Animation::create($animation);
            }
            $this->command->line('   ✓ ' . count($data['animations']) . ' animations importées');
            
            // Réactiver les contraintes
            Schema::enableForeignKeyConstraints();
            
            DB::commit();
            
            // Statistiques finales
            $finalStats = $this->getCurrentStats();
            $this->command->newLine();
            $this->command->info('✅ Import terminé avec succès!');
            $this->command->newLine();
            $this->command->info('📊 État final:');
            foreach ($finalStats as $table => $count) {
                $before = $currentStats[$table] ?? 0;
                $diff = $count - $before;
                $sign = $diff >= 0 ? '+' : '';
                $this->command->line("   $table: $count ($sign$diff)");
            }
            
            // Vérifier l'intégrité des données utilisateurs
            $this->verifyUserDataIntegrity($currentStats, $finalStats);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Schema::enableForeignKeyConstraints();
            $this->command->error('❌ Erreur lors de l\'import: ' . $e->getMessage());
            $this->command->error($e->getTraceAsString());
        }
    }
    
    /**
     * Synchronisation directe (export + import en une fois)
     */
    protected function syncData(): void
    {
        $this->command->info('🔄 Mode synchronisation directe...');
        
        if (app()->environment('production')) {
            $this->command->error('⚠️  Impossible d\'exécuter en mode sync sur la production!');
            $this->command->info('Utilisez --import pour importer un fichier existant.');
            return;
        }
        
        if (!$this->command->confirm('Cette action va exporter les données locales. Continuer?')) {
            $this->command->warn('Synchronisation annulée.');
            return;
        }
        
        // Export
        $this->exportData();
        
        $this->command->newLine();
        $this->command->info('📋 Pour terminer la synchronisation:');
        $this->command->line('1. Transférer le fichier ' . $this->exportFile . ' vers le serveur de production');
        $this->command->line('2. Sur le serveur de production, exécuter:');
        $this->command->line('   php artisan db:seed --class=ProductionSyncSeeder --import');
        $this->command->newLine();
        
        // Optionnel: Upload automatique si configuré
        if (env('PRODUCTION_SSH_HOST')) {
            if ($this->command->confirm('Upload automatique vers production?')) {
                $this->uploadToProduction();
            }
        }
    }
    
    /**
     * Upload automatique vers production (si configuré)
     */
    protected function uploadToProduction(): void
    {
        $host = env('PRODUCTION_SSH_HOST');
        $user = env('PRODUCTION_SSH_USER', 'forge');
        $path = env('PRODUCTION_PATH', '/home/forge/soboa-foot-time');
        
        $this->command->info('📤 Upload vers production...');
        $this->command->line("   Host: $user@$host");
        $this->command->line("   Path: $path");
        
        $localFile = base_path($this->exportFile);
        $remoteFile = "$path/$this->exportFile";
        
        $command = "scp $localFile $user@$host:$remoteFile";
        
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0) {
            $this->command->info('✅ Upload réussi!');
            
            // Optionnel: Exécuter l'import à distance
            if ($this->command->confirm('Exécuter l\'import sur production maintenant?')) {
                $sshCommand = "ssh $user@$host 'cd $path && php artisan db:seed --class=ProductionSyncSeeder --import --force'";
                exec($sshCommand, $output, $returnCode);
                
                if ($returnCode === 0) {
                    $this->command->info('✅ Import exécuté avec succès sur production!');
                } else {
                    $this->command->error('❌ Erreur lors de l\'import distant');
                }
            }
        } else {
            $this->command->error('❌ Erreur lors de l\'upload');
        }
    }
    
    /**
     * Obtenir les statistiques actuelles
     */
    protected function getCurrentStats(): array
    {
        return [
            'Users' => User::count(),
            'Teams' => Team::count(),
            'Stadiums' => Stadium::count(),
            'Venues' => Bar::count(),
            'Matches' => MatchGame::count(),
            'Animations' => Animation::count(),
            'Predictions' => Prediction::count(),
            'PointLogs' => PointLog::count(),
        ];
    }
    
    /**
     * Vérifier l'intégrité des données utilisateurs
     */
    protected function verifyUserDataIntegrity(array $before, array $after): void
    {
        $this->command->newLine();
        $this->command->info('🔍 Vérification de l\'intégrité...');
        
        $userDataChanged = false;
        
        if ($before['Users'] !== $after['Users']) {
            $this->command->error('⚠️  Le nombre d\'utilisateurs a changé!');
            $userDataChanged = true;
        }
        
        if ($before['Predictions'] !== $after['Predictions']) {
            $this->command->error('⚠️  Le nombre de prédictions a changé!');
            $userDataChanged = true;
        }
        
        if ($before['PointLogs'] !== $after['PointLogs']) {
            $this->command->error('⚠️  Le nombre de logs de points a changé!');
            $userDataChanged = true;
        }
        
        if (!$userDataChanged) {
            $this->command->info('✅ Intégrité des données utilisateurs vérifiée!');
        }
    }
    
    /**
     * Formater la taille en bytes
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
