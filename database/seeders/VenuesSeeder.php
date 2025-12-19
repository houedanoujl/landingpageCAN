<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\MatchGame;
use App\Models\Team;
use App\Models\Bar;
use App\Models\Animation;
use Carbon\Carbon;

class VenuesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * DANGER: This seeder performs destructive operations!
     * It will DELETE ALL DATA from: animations, matches, teams, bars
     */
    public function run(): void
    {
        $this->command->warn('⚠️  WARNING: This will DELETE ALL existing data!');
        $this->command->warn('Tables affected: animations, matches, teams, bars');

        if (!$this->command->confirm('Do you want to continue?', false)) {
            $this->command->info('Operation cancelled.');
            return;
        }

        $this->command->info('🔄 Starting fresh planning import from CSV...');

        // Step 1: Cleanup
        $this->cleanupDatabase();

        // Step 2: Parse CSV
        $csvData = $this->parseCsvFile();

        // Step 3: Import data
        $this->importTeams($csvData);
        $this->importVenues($csvData);
        $this->importMatches($csvData);
        $this->importAnimations($csvData);

        $this->command->info('✅ Fresh planning import completed successfully!');
        $this->printSummary();
    }

    /**
     * Cleanup database - DESTRUCTIVE!
     */
    protected function cleanupDatabase(): void
    {
        $this->command->info('🗑️  Cleaning up database...');

        // Disable foreign key checks
        Schema::disableForeignKeyConstraints();

        // Truncate tables in correct order (respect foreign keys)
        DB::table('animations')->truncate();
        $this->command->info('   - Truncated: animations');

        DB::table('predictions')->truncate();
        $this->command->info('   - Truncated: predictions');

        DB::table('matches')->truncate();
        $this->command->info('   - Truncated: matches');

        DB::table('teams')->truncate();
        $this->command->info('   - Truncated: teams');

        DB::table('bars')->truncate();
        $this->command->info('   - Truncated: bars (venues)');

        // Re-enable foreign key checks
        Schema::enableForeignKeyConstraints();

        $this->command->info('✓ Database cleaned');
    }

    /**
     * Parse CSV file
     */
    protected function parseCsvFile(): array
    {
        $this->command->info('📄 Parsing CSV file...');

        $csvPath = base_path('venues.csv');

        if (!file_exists($csvPath)) {
            $this->command->error("CSV file not found at: {$csvPath}");
            return [];
        }

        $rows = [];
        $handle = fopen($csvPath, 'r');

        // Skip header row
        fgetcsv($handle);

        while (($data = fgetcsv($handle)) !== false) {
            // Parse the match name to extract teams
            $matchName = trim($data[4] ?? '');
            $isPlayoff = !str_contains($matchName, ' VS ');

            if ($isPlayoff) {
                // Playoff match
                $team1 = $matchName;
                $team2 = '';
            } else {
                // Regular match
                $teams = explode(' VS ', $matchName);
                $team1 = trim($teams[0] ?? '');
                $team2 = trim($teams[1] ?? '');
            }

            $rows[] = [
                'venue_name' => trim($data[0] ?? ''),
                'zone' => trim($data[1] ?? ''),
                'date' => trim($data[2] ?? ''),
                'time' => trim($data[3] ?? ''),
                'match_name' => $matchName,
                'team_1' => $team1,
                'team_2' => $team2,
                'latitude' => trim($data[5] ?? ''),
                'longitude' => trim($data[6] ?? ''),
                'type_pdv' => trim($data[7] ?? 'dakar'), // Default to dakar if empty
            ];
        }

        fclose($handle);

        $count = count($rows);
        $this->command->info("   - Parsed {$count} rows");
        return $rows;
    }

    /**
     * Import teams
     */
    protected function importTeams(array $csvData): void
    {
        $this->command->info('👥 Importing teams...');

        $teamNames = [];

        foreach ($csvData as $row) {
            // Team 1 (always exists)
            if (!empty($row['team_1']) && !in_array($row['team_1'], $teamNames)) {
                // Skip playoff match names
                if (!$this->isPlayoffName($row['team_1'])) {
                    $teamNames[] = $row['team_1'];
                }
            }

            // Team 2 (may be empty for playoff matches)
            if (!empty($row['team_2']) && !in_array($row['team_2'], $teamNames)) {
                $teamNames[] = $row['team_2'];
            }
        }

        $created = 0;
        foreach ($teamNames as $teamName) {
            Team::firstOrCreate(
                ['name' => $teamName],
                [
                    'name' => $teamName,
                    'iso_code' => null, // Can be updated manually later
                    'group' => null, // Can be updated manually later
                ]
            );
            $created++;
        }

        $this->command->info("   ✓ Created/verified {$created} teams");
    }

    /**
     * Import venues
     */
    protected function importVenues(array $csvData): void
    {
        $this->command->info('🏢 Importing venues...');

        $venuesByKey = [];

        foreach ($csvData as $row) {
            $key = $row['venue_name'] . '|' . $row['zone'];

            if (!isset($venuesByKey[$key])) {
                $venuesByKey[$key] = [
                    'name' => $row['venue_name'],
                    'zone' => $row['zone'],
                    'latitude' => $row['latitude'],
                    'longitude' => $row['longitude'],
                    'type_pdv' => empty($row['type_pdv']) ? 'dakar' : $row['type_pdv'],
                ];
            }
        }

        $created = 0;
        foreach ($venuesByKey as $venueData) {
            Bar::firstOrCreate(
                [
                    'name' => $venueData['name'],
                    'zone' => $venueData['zone'],
                ],
                [
                    'address' => $venueData['zone'], // Zone as address
                    'latitude' => $venueData['latitude'],
                    'longitude' => $venueData['longitude'],
                    'type_pdv' => $venueData['type_pdv'],
                    'is_active' => true,
                ]
            );
            $created++;
        }

        $this->command->info("   ✓ Created/verified {$created} venues");
    }

    /**
     * Import matches
     */
    protected function importMatches(array $csvData): void
    {
        $this->command->info('⚽ Importing matches...');

        $matchesByKey = [];

        foreach ($csvData as $row) {
            $matchDate = $this->parseDateTime($row['date'], $row['time']);
            $isPlayoff = empty($row['team_2']);

            if ($isPlayoff) {
                // Playoff match: match_name is in team_1
                $matchName = $row['team_1'];
                $key = $matchDate->format('Y-m-d H:i') . '|' . $matchName;

                if (!isset($matchesByKey[$key])) {
                    $matchesByKey[$key] = [
                        'match_date' => $matchDate,
                        'match_name' => $matchName,
                        'is_playoff' => true,
                        'phase' => $this->detectPhase($matchName),
                    ];
                }
            } else {
                // Regular match with two teams
                $team1 = Team::where('name', $row['team_1'])->first();
                $team2 = Team::where('name', $row['team_2'])->first();

                $key = $matchDate->format('Y-m-d H:i') . '|' . $row['team_1'] . '|' . $row['team_2'];

                if (!isset($matchesByKey[$key])) {
                    $matchesByKey[$key] = [
                        'match_date' => $matchDate,
                        'team_a' => $row['team_1'],
                        'team_b' => $row['team_2'],
                        'home_team_id' => $team1?->id,
                        'away_team_id' => $team2?->id,
                        'is_playoff' => false,
                        'phase' => 'group_stage',
                    ];
                }
            }
        }

        $created = 0;
        foreach ($matchesByKey as $matchData) {
            if ($matchData['is_playoff']) {
                MatchGame::firstOrCreate(
                    [
                        'match_date' => $matchData['match_date'],
                        'match_name' => $matchData['match_name'],
                    ],
                    [
                        'team_a' => 'À déterminer',
                        'team_b' => 'À déterminer',
                        'home_team_id' => null,
                        'away_team_id' => null,
                        'status' => 'scheduled',
                        'phase' => $matchData['phase'],
                        'stadium' => 'À déterminer',
                    ]
                );
            } else {
                MatchGame::firstOrCreate(
                    [
                        'match_date' => $matchData['match_date'],
                        'team_a' => $matchData['team_a'],
                        'team_b' => $matchData['team_b'],
                    ],
                    [
                        'home_team_id' => $matchData['home_team_id'],
                        'away_team_id' => $matchData['away_team_id'],
                        'status' => 'scheduled',
                        'phase' => $matchData['phase'],
                        'stadium' => 'À déterminer',
                    ]
                );
            }
            $created++;
        }

        $this->command->info("   ✓ Created/verified {$created} matches");
    }

    /**
     * Import animations (match-venue links)
     */
    protected function importAnimations(array $csvData): void
    {
        $this->command->info('🔗 Importing animations (match-venue links)...');

        $created = 0;
        $errors = 0;

        foreach ($csvData as $row) {
            $matchDate = $this->parseDateTime($row['date'], $row['time']);
            $isPlayoff = empty($row['team_2']);

            // Find match
            if ($isPlayoff) {
                $match = MatchGame::where('match_date', $matchDate)
                    ->where('match_name', $row['team_1'])
                    ->first();
            } else {
                $match = MatchGame::where('match_date', $matchDate)
                    ->where('team_a', $row['team_1'])
                    ->where('team_b', $row['team_2'])
                    ->first();
            }

            // Find venue
            $venue = Bar::where('name', $row['venue_name'])
                ->where('zone', $row['zone'])
                ->first();

            if ($match && $venue) {
                Animation::firstOrCreate(
                    [
                        'match_id' => $match->id,
                        'bar_id' => $venue->id,
                    ],
                    [
                        'animation_date' => $matchDate->format('Y-m-d'),
                        'animation_time' => $matchDate->format('H:i'),
                        'is_active' => true,
                    ]
                );
                $created++;
            } else {
                $errors++;
                $this->command->warn("   ⚠ Could not link: {$row['venue_name']} - {$row['match_name']}");
            }
        }

        $this->command->info("   ✓ Created {$created} animations");
        if ($errors > 0) {
            $this->command->warn("   ⚠ {$errors} errors during animation import");
        }
    }

    /**
     * Parse date and time from CSV format
     */
    protected function parseDateTime(string $date, string $time): Carbon
    {
        // Parse date: DD/MM/YYYY
        [$day, $month, $year] = explode('/', $date);

        // Parse time: "15 H" -> "15:00"
        $hour = (int) str_replace([' H', ' h', 'H', 'h'], '', $time);

        return Carbon::create($year, $month, $day, $hour, 0, 0);
    }

    /**
     * Detect phase from match name
     */
    protected function detectPhase(string $matchName): string
    {
        $matchName = strtoupper($matchName);

        if (str_contains($matchName, 'HUITIEME')) {
            return 'round_of_16';
        } elseif (str_contains($matchName, 'QUART')) {
            return 'quarter_final';
        } elseif (str_contains($matchName, 'DEMI')) {
            return 'semi_final';
        } elseif (str_contains($matchName, 'TROISIEME') || str_contains($matchName, '3EME')) {
            return 'third_place';
        } elseif (str_contains($matchName, 'FINALE')) {
            return 'final';
        }

        return 'group_stage';
    }

    /**
     * Check if a name is a playoff stage name
     */
    protected function isPlayoffName(string $name): bool
    {
        $playoffNames = [
            'HUITIEME DE FINALE',
            'QUART DE FINALE',
            'DEMI FINALE',
            'TROISIEME PLACE',
            'FINALE',
        ];

        return in_array(strtoupper($name), $playoffNames);
    }

    /**
     * Print summary
     */
    protected function printSummary(): void
    {
        $this->command->info('');
        $this->command->info('📊 Summary:');
        $this->command->info('   - Teams: ' . Team::count());
        $this->command->info('   - Venues: ' . Bar::count());
        $this->command->info('   - Matches: ' . MatchGame::count());
        $this->command->info('   - Animations: ' . Animation::count());
        $this->command->info('');
    }
}
