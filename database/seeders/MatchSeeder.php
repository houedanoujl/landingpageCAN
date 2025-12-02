<?php

namespace Database\Seeders;

use App\Models\MatchGame;
use Illuminate\Database\Seeder;

class MatchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Supprimer tous les pronostics et matchs existants
        \App\Models\Prediction::query()->delete();
        MatchGame::query()->delete();

        $matches = [
            // GROUPE A - Journée 1
            [
                'team_a' => 'Maroc',
                'team_b' => 'Comores',
                'match_date' => '2025-12-21 20:00:00',
                'stadium' => 'Stade Prince Moulay Abdellah, Rabat',
                'group_name' => 'A',
                'phase' => 'group_stage',
                'status' => 'scheduled',
            ],
            [
                'team_a' => 'Mali',
                'team_b' => 'Zambie',
                'match_date' => '2025-12-22 15:30:00',
                'stadium' => 'Stade Moulay Hassan, Rabat',
                'group_name' => 'A',
                'phase' => 'group_stage',
                'status' => 'scheduled',
            ],

            // GROUPE B - Journée 1
            [
                'team_a' => 'Égypte',
                'team_b' => 'Zimbabwe',
                'match_date' => '2025-12-22 18:00:00',
                'stadium' => 'Grand Stade d\'Agadir',
                'group_name' => 'B',
                'phase' => 'group_stage',
                'status' => 'scheduled',
            ],
            [
                'team_a' => 'Afrique du Sud',
                'team_b' => 'Angola',
                'match_date' => '2025-12-22 20:30:00',
                'stadium' => 'Grand Stade de Marrakech',
                'group_name' => 'B',
                'phase' => 'group_stage',
                'status' => 'scheduled',
            ],

            // GROUPE C - Journée 1
            [
                'team_a' => 'Nigeria',
                'team_b' => 'Tanzanie',
                'match_date' => '2025-12-23 13:00:00',
                'stadium' => 'Complexe Sportif de Fès',
                'group_name' => 'C',
                'phase' => 'group_stage',
                'status' => 'scheduled',
            ],
            [
                'team_a' => 'Tunisie',
                'team_b' => 'Ouganda',
                'match_date' => '2025-12-23 15:30:00',
                'stadium' => 'Stade Annexe Moulay Abdellah, Rabat',
                'group_name' => 'C',
                'phase' => 'group_stage',
                'status' => 'scheduled',
            ],

            // GROUPE D - Journée 1
            [
                'team_a' => 'Sénégal',
                'team_b' => 'Botswana',
                'match_date' => '2025-12-23 18:00:00',
                'stadium' => 'Stade Ibn Batouta, Tanger',
                'group_name' => 'D',
                'phase' => 'group_stage',
                'status' => 'scheduled',
            ],
            [
                'team_a' => 'RD Congo',
                'team_b' => 'Bénin',
                'match_date' => '2025-12-23 20:30:00',
                'stadium' => 'Stade Al Barid, Rabat',
                'group_name' => 'D',
                'phase' => 'group_stage',
                'status' => 'scheduled',
            ],

            // GROUPE E - Journée 1
            [
                'team_a' => 'Algérie',
                'team_b' => 'Soudan',
                'match_date' => '2025-12-24 13:00:00',
                'stadium' => 'Stade Moulay Hassan, Rabat',
                'group_name' => 'E',
                'phase' => 'group_stage',
                'status' => 'scheduled',
            ],
            [
                'team_a' => 'Burkina Faso',
                'team_b' => 'Guinée Équatoriale',
                'match_date' => '2025-12-24 15:30:00',
                'stadium' => 'Stade Mohammed V, Casablanca',
                'group_name' => 'E',
                'phase' => 'group_stage',
                'status' => 'scheduled',
            ],

            // GROUPE F - Journée 1
            [
                'team_a' => 'Côte d\'Ivoire',
                'team_b' => 'Mozambique',
                'match_date' => '2025-12-24 18:00:00',
                'stadium' => 'Grand Stade de Marrakech',
                'group_name' => 'F',
                'phase' => 'group_stage',
                'status' => 'scheduled',
            ],
            [
                'team_a' => 'Cameroun',
                'team_b' => 'Gabon',
                'match_date' => '2025-12-24 20:30:00',
                'stadium' => 'Grand Stade d\'Agadir',
                'group_name' => 'F',
                'phase' => 'group_stage',
                'status' => 'scheduled',
            ],

            // GROUPE A - Journée 2
            [
                'team_a' => 'Maroc',
                'team_b' => 'Mali',
                'match_date' => '2025-12-26 13:00:00',
                'stadium' => 'Stade Prince Moulay Abdellah, Rabat',
                'group_name' => 'A',
                'phase' => 'group_stage',
                'status' => 'scheduled',
            ],
            [
                'team_a' => 'Zambie',
                'team_b' => 'Comores',
                'match_date' => '2025-12-26 15:30:00',
                'stadium' => 'Stade Mohammed V, Casablanca',
                'group_name' => 'A',
                'phase' => 'group_stage',
                'status' => 'scheduled',
            ],

            // GROUPE B - Journée 2
            [
                'team_a' => 'Égypte',
                'team_b' => 'Afrique du Sud',
                'match_date' => '2025-12-26 18:00:00',
                'stadium' => 'Grand Stade d\'Agadir',
                'group_name' => 'B',
                'phase' => 'group_stage',
                'status' => 'scheduled',
            ],
            [
                'team_a' => 'Angola',
                'team_b' => 'Zimbabwe',
                'match_date' => '2025-12-26 20:30:00',
                'stadium' => 'Grand Stade de Marrakech',
                'group_name' => 'B',
                'phase' => 'group_stage',
                'status' => 'scheduled',
            ],

            // GROUPE A - Journée 3
            [
                'team_a' => 'Zambie',
                'team_b' => 'Maroc',
                'match_date' => '2025-12-29 18:30:00',
                'stadium' => 'Stade Prince Moulay Abdellah, Rabat',
                'group_name' => 'A',
                'phase' => 'group_stage',
                'status' => 'scheduled',
            ],
            [
                'team_a' => 'Comores',
                'team_b' => 'Mali',
                'match_date' => '2025-12-29 18:30:00',
                'stadium' => 'Stade Mohammed V, Casablanca',
                'group_name' => 'A',
                'phase' => 'group_stage',
                'status' => 'scheduled',
            ],
        ];

        foreach ($matches as $match) {
            MatchGame::create($match);
        }
    }
}
