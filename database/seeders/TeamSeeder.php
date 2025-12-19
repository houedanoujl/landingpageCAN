<?php

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Production-safe: updateOrCreate instead of truncate
        // Preserves existing teams and their relationships (matches, predictions, etc.)

        $teams = [
            // Group A
            ['name' => 'Maroc', 'iso_code' => 'ma', 'group' => 'A'],
            ['name' => 'Comores', 'iso_code' => 'km', 'group' => 'A'],
            ['name' => 'Mali', 'iso_code' => 'ml', 'group' => 'A'],
            ['name' => 'Zambie', 'iso_code' => 'zm', 'group' => 'A'],
            
            // Group B
            ['name' => 'Égypte', 'iso_code' => 'eg', 'group' => 'B'],
            ['name' => 'Afrique du Sud', 'iso_code' => 'za', 'group' => 'B'],
            ['name' => 'Angola', 'iso_code' => 'ao', 'group' => 'B'],
            ['name' => 'Zimbabwe', 'iso_code' => 'zw', 'group' => 'B'],
            
            // Group C
            ['name' => 'Nigeria', 'iso_code' => 'ng', 'group' => 'C'],
            ['name' => 'Tunisie', 'iso_code' => 'tn', 'group' => 'C'],
            ['name' => 'Ouganda', 'iso_code' => 'ug', 'group' => 'C'],
            ['name' => 'Tanzanie', 'iso_code' => 'tz', 'group' => 'C'],
            
            // Group D
            ['name' => 'Sénégal', 'iso_code' => 'sn', 'group' => 'D'],
            ['name' => 'RD Congo', 'iso_code' => 'cd', 'group' => 'D'],
            ['name' => 'Bénin', 'iso_code' => 'bj', 'group' => 'D'],
            ['name' => 'Botswana', 'iso_code' => 'bw', 'group' => 'D'],
            
            // Group E
            ['name' => 'Algérie', 'iso_code' => 'dz', 'group' => 'E'],
            ['name' => 'Burkina Faso', 'iso_code' => 'bf', 'group' => 'E'],
            ['name' => 'Guinée Équatoriale', 'iso_code' => 'gq', 'group' => 'E'],
            ['name' => 'Soudan', 'iso_code' => 'sd', 'group' => 'E'],
            
            // Group F
            ['name' => 'Cameroun', 'iso_code' => 'cm', 'group' => 'F'],
            ['name' => 'Côte d\'Ivoire', 'iso_code' => 'ci', 'group' => 'F'],
            ['name' => 'Gabon', 'iso_code' => 'ga', 'group' => 'F'],
            ['name' => 'Mozambique', 'iso_code' => 'mz', 'group' => 'F'],
        ];

        $created = 0;
        $updated = 0;

        foreach ($teams as $team) {
            $teamModel = Team::updateOrCreate(
                ['name' => $team['name']], // Unique key
                $team // All data to update/create
            );

            if ($teamModel->wasRecentlyCreated) {
                $created++;
            } else {
                $updated++;
            }
        }

        $this->command->info("✅ Teams: {$created} created, {$updated} updated (Total: " . count($teams) . ")");
    }
}
