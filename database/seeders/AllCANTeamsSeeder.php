<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Team;
use Illuminate\Support\Facades\DB;

class AllCANTeamsSeeder extends Seeder
{
    /**
     * Run the database seeder to add all 24 CAN 2025 qualified teams.
     */
    public function run(): void
    {
        $this->command->info('🌍 Adding all 24 qualified teams for CAN 2025...');

        // All 24 qualified teams with their ISO codes
        $teams = [
            // North Africa
            ['name' => 'MAROC', 'iso_code' => 'ma'],
            ['name' => 'ALGÉRIE', 'iso_code' => 'dz'],
            ['name' => 'ÉGYPTE', 'iso_code' => 'eg'],
            ['name' => 'TUNISIE', 'iso_code' => 'tn'],

            // West Africa
            ['name' => 'SÉNÉGAL', 'iso_code' => 'sn'],
            ['name' => 'CÔTE D\'IVOIRE', 'iso_code' => 'ci'],
            ['name' => 'NIGERIA', 'iso_code' => 'ng'],
            ['name' => 'MALI', 'iso_code' => 'ml'],
            ['name' => 'BURKINA FASO', 'iso_code' => 'bf'],
            ['name' => 'BÉNIN', 'iso_code' => 'bj'],
            ['name' => 'GUINÉE ÉQUATORIALE', 'iso_code' => 'gq'],

            // Central Africa
            ['name' => 'CAMEROUN', 'iso_code' => 'cm'],
            ['name' => 'RD CONGO', 'iso_code' => 'cd'],
            ['name' => 'GABON', 'iso_code' => 'ga'],
            ['name' => 'ANGOLA', 'iso_code' => 'ao'],

            // East Africa
            ['name' => 'OUGANDA', 'iso_code' => 'ug'],
            ['name' => 'TANZANIE', 'iso_code' => 'tz'],
            ['name' => 'SOUDAN', 'iso_code' => 'sd'],
            ['name' => 'COMORES', 'iso_code' => 'km'],

            // Southern Africa
            ['name' => 'AFRIQUE DU SUD', 'iso_code' => 'za'],
            ['name' => 'ZAMBIE', 'iso_code' => 'zm'],
            ['name' => 'ZIMBABWE', 'iso_code' => 'zw'],
            ['name' => 'MOZAMBIQUE', 'iso_code' => 'mz'],
            ['name' => 'BOTSWANA', 'iso_code' => 'bw'],
        ];

        DB::beginTransaction();

        try {
            $created = 0;
            $updated = 0;

            foreach ($teams as $teamData) {
                $team = Team::updateOrCreate(
                    ['iso_code' => $teamData['iso_code']],
                    [
                        'name' => $teamData['name'],
                        'iso_code' => $teamData['iso_code'],
                    ]
                );

                if ($team->wasRecentlyCreated) {
                    $created++;
                    $this->command->line("  ✓ Créé: {$teamData['name']} ({$teamData['iso_code']})");
                } else {
                    $updated++;
                    $this->command->line("  ↻ Mis à jour: {$teamData['name']} ({$teamData['iso_code']})");
                }
            }

            DB::commit();

            // Verification
            $totalTeams = Team::count();

            $this->command->newLine();
            $this->command->info('📊 Résumé:');
            $this->command->line("  - Équipes créées: {$created}");
            $this->command->line("  - Équipes mises à jour: {$updated}");
            $this->command->line("  - Total dans la base: {$totalTeams}");
            $this->command->newLine();

            if ($totalTeams === 24) {
                $this->command->info('✅ Toutes les 24 équipes qualifiées sont dans la base !');
            } else {
                $this->command->warn("⚠️  Nombre d'équipes inattendu: {$totalTeams} (attendu: 24)");
            }

            // Display teams by region
            $this->command->newLine();
            $this->command->info('🌍 Équipes par région:');
            $this->command->line('  Afrique du Nord: ' . implode(', ', array_column(array_slice($teams, 0, 4), 'name')));
            $this->command->line('  Afrique de l\'Ouest: ' . implode(', ', array_column(array_slice($teams, 4, 7), 'name')));
            $this->command->line('  Afrique Centrale: ' . implode(', ', array_column(array_slice($teams, 11, 4), 'name')));
            $this->command->line('  Afrique de l\'Est: ' . implode(', ', array_column(array_slice($teams, 15, 4), 'name')));
            $this->command->line('  Afrique Australe: ' . implode(', ', array_column(array_slice($teams, 19, 5), 'name')));

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Erreur lors de l\'import des équipes:');
            $this->command->error($e->getMessage());
            throw $e;
        }
    }
}
