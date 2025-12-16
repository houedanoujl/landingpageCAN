<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BackupDatabase extends Command
{
    protected $signature = 'db:backup';
    protected $description = 'Create a backup of the database';

    public function handle()
    {
        $backupPath = storage_path('backups');
        
        // Create backups directory if it doesn't exist
        if (!file_exists($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        // Generate backup filename with timestamp
        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = "backup_{$timestamp}.sql";
        $filepath = $backupPath . '/' . $filename;

        // Get database credentials
        $host = env('DB_HOST');
        $port = env('DB_PORT', 3306);
        $database = env('DB_DATABASE');
        $user = env('DB_USERNAME');
        $password = env('DB_PASSWORD');

        // Build mysqldump command
        $command = "mysqldump -h {$host} -P {$port} -u {$user} -p{$password} {$database} > {$filepath}";

        try {
            $this->info("📦 Création du backup...");
            
            // Execute mysqldump
            exec($command, $output, $returnCode);

            if ($returnCode === 0 && file_exists($filepath)) {
                $filesize = filesize($filepath);
                $filesizeKb = round($filesize / 1024, 2);
                
                $this->info("✅ Backup créé avec succès!");
                $this->info("📁 Fichier: {$filename}");
                $this->info("💾 Taille: {$filesizeKb} KB");
                $this->info("📍 Chemin: {$filepath}");
                
                return 0;
            } else {
                $this->error("❌ Erreur lors de la création du backup");
                $this->error("Assurez-vous que mysqldump est installé et accessible");
                return 1;
            }
        } catch (\Exception $e) {
            $this->error("❌ Erreur: " . $e->getMessage());
            return 1;
        }
    }
}
