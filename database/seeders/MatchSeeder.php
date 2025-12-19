<?php

namespace Database\Seeders;

use App\Models\MatchGame;
use App\Models\Team;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class MatchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Production-safe: updateOrCreate instead of truncate
        // CRITICAL: Preserves matches and their predictions (cascade delete!)
        // Uses composite key: home_team_id + away_team_id + match_date for uniqueness

        $matches = [
            // Group Stage Matches from Animation Data
            ['home' => 'Sénégal', 'away' => 'Botswana', 'date' => '2025-12-23 15:00:00', 'phase' => 'group_stage', 'grp' => 'A', 'stadium' => 'Stade de Rabat'],
            ['home' => 'Afrique du Sud', 'away' => 'Égypte', 'date' => '2025-12-26 15:00:00', 'phase' => 'group_stage', 'grp' => 'B', 'stadium' => 'Stade de Tanger'],
            ['home' => 'Sénégal', 'away' => 'RD Congo', 'date' => '2025-12-27 15:00:00', 'phase' => 'group_stage', 'grp' => 'A', 'stadium' => 'Stade de Fès'],
            ['home' => 'Côte d\'Ivoire', 'away' => 'Cameroun', 'date' => '2025-12-28 20:00:00', 'phase' => 'group_stage', 'grp' => 'C', 'stadium' => 'Stade Mohammed V, Casablanca'],
            ['home' => 'Sénégal', 'away' => 'Bénin', 'date' => '2025-12-30 19:00:00', 'phase' => 'group_stage', 'grp' => 'A', 'stadium' => 'Stade de Fès'],

            // Additional Group Stage Matches
            ['home' => 'Maroc', 'away' => 'Comores', 'date' => '2025-12-21 20:00:00', 'phase' => 'group_stage', 'grp' => 'D', 'stadium' => 'Stade Mohammed V, Casablanca'],
            ['home' => 'Mali', 'away' => 'Zambie', 'date' => '2025-12-22 15:00:00', 'phase' => 'group_stage', 'grp' => 'D', 'stadium' => 'Stade de Marrakech'],
            ['home' => 'Afrique du Sud', 'away' => 'Angola', 'date' => '2025-12-22 18:00:00', 'phase' => 'group_stage', 'grp' => 'B', 'stadium' => 'Stade de Tanger'],
            ['home' => 'Nigeria', 'away' => 'Tanzanie', 'date' => '2025-12-23 18:30:00', 'phase' => 'group_stage', 'grp' => 'C', 'stadium' => 'Stade de Fès'],
            ['home' => 'Côte d\'Ivoire', 'away' => 'Mozambique', 'date' => '2025-12-24 18:30:00', 'phase' => 'group_stage', 'grp' => 'C', 'stadium' => 'Stade Mohammed V, Casablanca'],
            ['home' => 'Cameroun', 'away' => 'Gabon', 'date' => '2025-12-24 21:00:00', 'phase' => 'group_stage', 'grp' => 'C', 'stadium' => 'Stade de Marrakech'],
            ['home' => 'Maroc', 'away' => 'Mali', 'date' => '2025-12-26 21:00:00', 'phase' => 'group_stage', 'grp' => 'D', 'stadium' => 'Stade Mohammed V, Casablanca'],
            ['home' => 'Nigeria', 'away' => 'Tunisie', 'date' => '2025-12-27 21:00:00', 'phase' => 'group_stage', 'grp' => 'C', 'stadium' => 'Stade de Tanger'],
            ['home' => 'Maroc', 'away' => 'Zambie', 'date' => '2025-12-29 20:00:00', 'phase' => 'group_stage', 'grp' => 'D', 'stadium' => 'Stade de Rabat'],
            ['home' => 'Côte d\'Ivoire', 'away' => 'Gabon', 'date' => '2025-12-31 20:00:00', 'phase' => 'group_stage', 'grp' => 'C', 'stadium' => 'Stade de Marrakech'],

            // Knockout Stage Matches (TBD teams)
            ['home' => null, 'away' => null, 'date' => '2026-01-03 16:00:00', 'phase' => 'round_of_16', 'grp' => null, 'stadium' => 'Stade de Rabat'],
            ['home' => null, 'away' => null, 'date' => '2026-01-04 16:00:00', 'phase' => 'round_of_16', 'grp' => null, 'stadium' => 'Stade de Tanger'],
            ['home' => null, 'away' => null, 'date' => '2026-01-05 16:00:00', 'phase' => 'round_of_16', 'grp' => null, 'stadium' => 'Stade de Fès'],
            ['home' => null, 'away' => null, 'date' => '2026-01-06 16:00:00', 'phase' => 'round_of_16', 'grp' => null, 'stadium' => 'Stade de Marrakech'],
            ['home' => null, 'away' => null, 'date' => '2026-01-09 16:00:00', 'phase' => 'quarter_final', 'grp' => null, 'stadium' => 'Stade de Rabat'],
            ['home' => null, 'away' => null, 'date' => '2026-01-10 16:00:00', 'phase' => 'quarter_final', 'grp' => null, 'stadium' => 'Stade de Tanger'],
            ['home' => null, 'away' => null, 'date' => '2026-01-14 16:00:00', 'phase' => 'semi_final', 'grp' => null, 'stadium' => 'Stade Mohammed V, Casablanca'],
            ['home' => null, 'away' => null, 'date' => '2026-01-15 16:00:00', 'phase' => 'semi_final', 'grp' => null, 'stadium' => 'Stade de Marrakech'],
            ['home' => null, 'away' => null, 'date' => '2026-01-17 16:00:00', 'phase' => 'third_place', 'grp' => null, 'stadium' => 'Stade de Rabat'],
            ['home' => null, 'away' => null, 'date' => '2026-01-18 16:00:00', 'phase' => 'final', 'grp' => null, 'stadium' => 'Stade Mohammed V, Casablanca'],
        ];

        $created = 0;
        $updated = 0;

        foreach ($matches as $matchData) {
            $homeTeamId = null;
            $awayTeamId = null;
            $teamA = 'À déterminer';
            $teamB = 'À déterminer';

            // Find teams if specified (null for TBD knockout matches)
            if ($matchData['home']) {
                $homeTeam = Team::where('name', $matchData['home'])->first();
                if ($homeTeam) {
                    $homeTeamId = $homeTeam->id;
                    $teamA = $homeTeam->name;
                } else {
                    $this->command->warn("⚠️ Équipe domicile non trouvée: {$matchData['home']}");
                    continue;
                }
            }

            if ($matchData['away']) {
                $awayTeam = Team::where('name', $matchData['away'])->first();
                if ($awayTeam) {
                    $awayTeamId = $awayTeam->id;
                    $teamB = $awayTeam->name;
                } else {
                    $this->command->warn("⚠️ Équipe extérieur non trouvée: {$matchData['away']}");
                    continue;
                }
            }

            $matchDate = Carbon::parse($matchData['date']);

            // Composite unique key to prevent duplicates
            // For TBD matches (null teams): use date + phase
            // For known matches: use home_team_id + away_team_id + match_date
            $uniqueKey = [];
            if ($homeTeamId && $awayTeamId) {
                $uniqueKey = [
                    'home_team_id' => $homeTeamId,
                    'away_team_id' => $awayTeamId,
                    'match_date' => $matchDate,
                ];
            } else {
                // TBD knockout matches: unique by date + phase
                $uniqueKey = [
                    'match_date' => $matchDate,
                    'phase' => $matchData['phase'] ?? 'group_stage',
                ];
            }

            $matchModel = MatchGame::updateOrCreate(
                $uniqueKey, // Composite unique key
                [
                    'home_team_id' => $homeTeamId,
                    'away_team_id' => $awayTeamId,
                    'team_a' => $teamA,
                    'team_b' => $teamB,
                    'match_date' => $matchDate,
                    'phase' => $matchData['phase'] ?? 'group_stage',
                    'group_name' => $matchData['grp'],
                    'stadium' => $matchData['stadium'],
                    'status' => 'scheduled',
                    // Preserve existing scores if match already exists
                ]
            );

            if ($matchModel->wasRecentlyCreated) {
                $created++;
            } else {
                $updated++;
            }
        }

        $this->command->info("✅ Matches: {$created} created, {$updated} updated (Total: " . ($created + $updated) . ")");
    }
}
