<?php

namespace App\Console\Commands;

use App\Models\Bar;
use Illuminate\Console\Command;

class DedupeBars extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bars:dedupe {--dry-run : Affiche les fusions sans modifier la base}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fusionne les points de vente en double (même nom + adresse normalisés)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Mode simulation : aucune modification ne sera effectuée.');
        }

        $stats = Bar::mergeDuplicates($dryRun);

        if ($stats['duplicate_groups'] === 0) {
            $this->info('Aucun doublon détecté.');
            return self::SUCCESS;
        }

        foreach ($stats['details'] as $line) {
            $this->line('  - ' . $line);
        }

        $this->newLine();
        $this->info("Groupes de doublons : {$stats['duplicate_groups']}");
        $this->info("PDV fusionnés : {$stats['bars_merged']}");

        if (!$dryRun) {
            $this->info("Animations déplacées : {$stats['animations_moved']}");
            $this->info("Animations en double supprimées : {$stats['animations_dropped']}");
        }

        return self::SUCCESS;
    }
}
