<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;
use App\Models\Team;
use App\Models\Bar;
use App\Models\MatchGame;
use App\Models\Animation;
use App\Models\Prediction;
use App\Models\PointLog;

class SyncDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:sync 
                            {action : Action à effectuer: backup|restore|sync|compare}
                            {--env=local : Environnement (local|production)}
                            {--file= : Fichier de backup/restore}
                            {--safe : Mode sécurisé (préserve les utilisateurs)}
                            {--force : Force l\'exécution sans confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchroniser la base de données entre local et production';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');
        $env = $this->option('env');
        $safe = $this->option('safe');
        $force = $this->option('force');
        
        $this->info('╔════════════════════════════════════════════════╗');
        $this->info('║   GESTIONNAIRE DE SYNCHRONISATION DB          ║');
        $this->info('╚════════════════════════════════════════════════╝');
        $this->newLine();
        
        switch ($action) {
            case 'backup':
                return $this->backup($env);
                
            case 'restore':
                return $this->restore($env, $force);
                
            case 'sync':
                return $this->sync($safe, $force);
                
            case 'compare':
                return $this->compare();
                
            default:
                $this->error("Action inconnue: $action");
                return 1;
        }
    }
    
    /**
     * Créer un backup de la base de données
     */
    protected function backup($env = 'local')
    {
        $this->info("📦 Création d'un backup ($env)...");
        
        $timestamp = now()->format('Y-m-d_H-i-s');
        $backupPath = storage_path('backups');
        
        // Créer le dossier si nécessaire
        if (!file_exists($backupPath)) {
            mkdir($backupPath, 0755, true);
        }
        
        $filename = "{$env}_backup_{$timestamp}.sql";
        $filepath = "$backupPath/$filename";
        
        // Déterminer les credentials
        if ($env === 'production') {
            $config = $this->getProductionConfig();
        } else {
            $config = config('database.connections.mysql');
        }
        
        // Construire la commande mysqldump
        $command = sprintf(
            'mysqldump -h %s -P %s -u %s -p%s %s > %s 2>&1',
            $config['host'],
            $config['port'] ?? 3306,
            $config['username'],
            $config['password'],
            $config['database'],
            $filepath
        );
        
        // Exécuter le backup
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0 && file_exists($filepath)) {
            $filesize = $this->formatBytes(filesize($filepath));
            
            $this->info("✅ Backup créé avec succès!");
            $this->info("📁 Fichier: $filename");
            $this->info("💾 Taille: $filesize");
            $this->info("📍 Chemin: $filepath");
            
            // Garder uniquement les 10 derniers backups
            $this->cleanOldBackups($backupPath, $env);
            
            return 0;
        } else {
            $this->error("❌ Erreur lors de la création du backup");
            $this->error(implode("\n", $output));
            return 1;
        }
    }
    
    /**
     * Restaurer un backup
     */
    protected function restore($env = 'production', $force = false)
    {
        $file = $this->option('file');
        
        if (!$file) {
            // Lister les backups disponibles
            $backups = $this->listBackups();
            
            if (empty($backups)) {
                $this->error("Aucun backup disponible");
                return 1;
            }
            
            $file = $this->choice('Quel backup restaurer?', $backups);
        }
        
        $filepath = storage_path("backups/$file");
        
        if (!file_exists($filepath)) {
            $this->error("Fichier non trouvé: $filepath");
            return 1;
        }
        
        // Confirmation si production
        if ($env === 'production' && !$force) {
            $this->warn("⚠️  ATTENTION: Vous êtes sur le point d'écraser la base $env!");
            
            if (!$this->confirm('Êtes-vous sûr de vouloir continuer?')) {
                $this->info("Restauration annulée.");
                return 0;
            }
        }
        
        $this->info("🔄 Restauration du backup: $file");
        
        // Déterminer les credentials
        if ($env === 'production') {
            $config = $this->getProductionConfig();
        } else {
            $config = config('database.connections.mysql');
        }
        
        // Construire la commande mysql
        $command = sprintf(
            'mysql -h %s -P %s -u %s -p%s %s < %s 2>&1',
            $config['host'],
            $config['port'] ?? 3306,
            $config['username'],
            $config['password'],
            $config['database'],
            $filepath
        );
        
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0) {
            $this->info("✅ Base de données restaurée avec succès!");
            
            // Clear cache après restauration
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            
            return 0;
        } else {
            $this->error("❌ Erreur lors de la restauration");
            $this->error(implode("\n", $output));
            return 1;
        }
    }
    
    /**
     * Synchroniser local → production
     */
    protected function sync($safe = false, $force = false)
    {
        $this->info("🔄 Synchronisation Local → Production");
        $this->newLine();
        
        // Vérifier qu'on est en local
        if (app()->environment('production')) {
            $this->error("⚠️  Impossible de synchroniser depuis la production!");
            return 1;
        }
        
        // Afficher les statistiques actuelles
        $this->showStats('LOCAL', $this->getStats());
        
        if (!$force) {
            if (!$this->confirm('Voulez-vous synchroniser ces données vers la production?')) {
                $this->info("Synchronisation annulée.");
                return 0;
            }
        }
        
        // 1. Backup de production
        $this->info("📦 Création d'un backup de sécurité de la production...");
        $this->backup('production');
        
        // 2. Export des données locales
        $timestamp = now()->format('Y-m-d_H-i-s');
        $exportFile = storage_path("backups/sync_export_{$timestamp}.sql");
        
        if ($safe) {
            $this->info("📤 Export sécurisé (sans utilisateurs)...");
            $this->exportSafeData($exportFile);
        } else {
            $this->info("📤 Export complet...");
            $this->exportFullData($exportFile);
        }
        
        // 3. Import en production
        if (file_exists($exportFile)) {
            $this->info("📥 Import en production...");
            
            // Ici, vous devez adapter selon votre méthode d'accès
            $this->warn("⚠️  Exécutez cette commande sur le serveur de production:");
            $this->line("mysql -u DB_USER -p DB_NAME < $exportFile");
            
            // Si SSH est configuré
            if (env('PRODUCTION_SSH_HOST')) {
                if ($this->confirm('Exécuter automatiquement via SSH?')) {
                    $this->executeRemoteImport($exportFile);
                }
            }
        }
        
        return 0;
    }
    
    /**
     * Comparer les bases de données
     */
    protected function compare()
    {
        $this->info("🔍 Comparaison Local vs Production");
        $this->newLine();
        
        // Stats locales
        $localStats = $this->getStats();
        $this->showStats('LOCAL', $localStats);
        
        // Stats production (si accessible)
        try {
            $prodConfig = $this->getProductionConfig();
            if ($prodConfig) {
                $prodStats = $this->getStats('production');
                $this->showStats('PRODUCTION', $prodStats);
                
                // Comparaison
                $this->newLine();
                $this->info("📊 DIFFÉRENCES:");
                $this->table(
                    ['Table', 'Local', 'Production', 'Différence'],
                    $this->compareStats($localStats, $prodStats)
                );
            }
        } catch (\Exception $e) {
            $this->warn("Impossible de se connecter à la production");
        }
        
        return 0;
    }
    
    /**
     * Export sécurisé (sans utilisateurs)
     */
    protected function exportSafeData($filepath)
    {
        $config = config('database.connections.mysql');
        
        $ignoreTables = [
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
        
        $ignoreOptions = '';
        foreach ($ignoreTables as $table) {
            $ignoreOptions .= " --ignore-table={$config['database']}.{$table}";
        }
        
        $command = sprintf(
            'mysqldump -h %s -P %s -u %s -p%s %s %s > %s 2>&1',
            $config['host'],
            $config['port'] ?? 3306,
            $config['username'],
            $config['password'],
            $config['database'],
            $ignoreOptions,
            $filepath
        );
        
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0) {
            $this->info("✅ Export sécurisé créé: $filepath");
        } else {
            $this->error("❌ Erreur lors de l'export");
            $this->error(implode("\n", $output));
        }
    }
    
    /**
     * Export complet
     */
    protected function exportFullData($filepath)
    {
        $config = config('database.connections.mysql');
        
        $command = sprintf(
            'mysqldump -h %s -P %s -u %s -p%s %s > %s 2>&1',
            $config['host'],
            $config['port'] ?? 3306,
            $config['username'],
            $config['password'],
            $config['database'],
            $filepath
        );
        
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0) {
            $this->info("✅ Export complet créé: $filepath");
        } else {
            $this->error("❌ Erreur lors de l'export");
            $this->error(implode("\n", $output));
        }
    }
    
    /**
     * Obtenir les statistiques
     */
    protected function getStats($env = 'local')
    {
        if ($env === 'production') {
            // Connexion à la base de production
            $config = $this->getProductionConfig();
            
            // Créer une connexion temporaire
            config(['database.connections.production_temp' => $config]);
            DB::purge('production_temp');
            DB::reconnect('production_temp');
            
            $stats = [
                'users' => DB::connection('production_temp')->table('users')->count(),
                'teams' => DB::connection('production_temp')->table('teams')->count(),
                'matches' => DB::connection('production_temp')->table('matches')->count(),
                'venues' => DB::connection('production_temp')->table('bars')->count(),
                'animations' => DB::connection('production_temp')->table('animations')->count(),
                'predictions' => DB::connection('production_temp')->table('predictions')->count(),
                'point_logs' => DB::connection('production_temp')->table('point_logs')->count(),
            ];
            
            DB::disconnect('production_temp');
            
            return $stats;
        }
        
        return [
            'users' => User::count(),
            'teams' => Team::count(),
            'matches' => MatchGame::count(),
            'venues' => Bar::count(),
            'animations' => Animation::count(),
            'predictions' => Prediction::count(),
            'point_logs' => PointLog::count(),
        ];
    }
    
    /**
     * Afficher les statistiques
     */
    protected function showStats($title, $stats)
    {
        $this->info("📊 $title:");
        foreach ($stats as $table => $count) {
            $this->line("   " . ucfirst($table) . ": $count");
        }
        $this->newLine();
    }
    
    /**
     * Comparer les statistiques
     */
    protected function compareStats($local, $prod)
    {
        $comparison = [];
        
        foreach ($local as $table => $localCount) {
            $prodCount = $prod[$table] ?? 0;
            $diff = $localCount - $prodCount;
            $sign = $diff >= 0 ? '+' : '';
            
            $comparison[] = [
                ucfirst($table),
                $localCount,
                $prodCount,
                $sign . $diff
            ];
        }
        
        return $comparison;
    }
    
    /**
     * Obtenir la configuration de production
     */
    protected function getProductionConfig()
    {
        // Essayer de lire .env.production
        $envFile = base_path('.env.production');
        
        if (!file_exists($envFile)) {
            return null;
        }
        
        $env = parse_ini_file($envFile);
        
        return [
            'driver' => 'mysql',
            'host' => $env['DB_HOST'] ?? 'localhost',
            'port' => $env['DB_PORT'] ?? '3306',
            'database' => $env['DB_DATABASE'] ?? 'soboa_foot_time',
            'username' => $env['DB_USERNAME'] ?? 'root',
            'password' => $env['DB_PASSWORD'] ?? '',
        ];
    }
    
    /**
     * Lister les backups disponibles
     */
    protected function listBackups()
    {
        $backupPath = storage_path('backups');
        
        if (!is_dir($backupPath)) {
            return [];
        }
        
        $files = scandir($backupPath);
        $backups = [];
        
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
                $backups[] = $file;
            }
        }
        
        // Trier par date (plus récent en premier)
        rsort($backups);
        
        return $backups;
    }
    
    /**
     * Nettoyer les anciens backups
     */
    protected function cleanOldBackups($path, $prefix, $keep = 10)
    {
        $files = glob("$path/{$prefix}_backup_*.sql");
        
        if (count($files) > $keep) {
            // Trier par date de modification
            usort($files, function($a, $b) {
                return filemtime($a) - filemtime($b);
            });
            
            // Supprimer les plus anciens
            $toDelete = array_slice($files, 0, count($files) - $keep);
            
            foreach ($toDelete as $file) {
                unlink($file);
                $this->line("   Ancien backup supprimé: " . basename($file));
            }
        }
    }
    
    /**
     * Formater la taille en bytes
     */
    protected function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
