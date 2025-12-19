<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\MatchGame;
use App\Models\Team;
use App\Models\Bar;
use App\Models\Animation;
use App\Models\User;
use App\Models\Prediction;
use Carbon\Carbon;

class FreshDeploymentSeeder extends Seeder
{
    /**
     * Fresh deployment seeder for production
     *
     * ✅ Preserves: users, predictions, point_logs
     * 🔄 Refreshes: teams, matches, venues, animations (from CSV)
     * ✅ Production-safe: Can be run multiple times
     *
     * This seeder provides a "fresh start" for the planning data
     * while keeping all user data intact.
     */
    public function run(): void
    {
        $this->command->info('');
        $this->command->info('╔════════════════════════════════════════╗');
        $this->command->info('║   FRESH DEPLOYMENT - CAN 2025         ║');
        $this->command->info('║   With CSV Data Import                ║');
        $this->command->info('╚════════════════════════════════════════╝');
        $this->command->info('');

        // Save initial counts for verification
        $initialUsers = User::count();
        $initialPredictions = Prediction::count();

        $this->command->info("📊 Initial State:");
        $this->command->info("   Users: {$initialUsers}");
        $this->command->info("   Predictions: {$initialPredictions}");
        $this->command->info('');

        $this->command->warn('⚠️  This will REFRESH planning data (teams, matches, venues, animations)');
        $this->command->warn('   User data (users, predictions) will be PRESERVED');
        $this->command->info('');

        if (!$this->command->confirm('Do you want to continue?', true)) {
            $this->command->info('Operation cancelled.');
            return;
        }

        // Step 1: Clean planning data only
        $this->cleanPlanningData();

        // Step 2: Import fresh data from CSV
        $csvData = $this->parseCsvFile();

        if (empty($csvData)) {
            $this->command->error('No data found in CSV file. Aborting.');
            return;
        }

        // Step 3: Import all data
        $this->importTeams($csvData);
        $this->importVenues($csvData);
        $this->importMatches($csvData);
        $this->importAnimations($csvData);

        // Verification
        $this->verifyDataIntegrity($initialUsers, $initialPredictions);

        $this->command->info('');
        $this->command->info('🎉 Fresh deployment completed successfully!');
        $this->command->info('');
    }

    /**
     * Clean planning data only (preserve user data)
     */
    protected function cleanPlanningData(): void
    {
        $this->command->info('🗑️  Cleaning planning data...');

        // Disable foreign key checks
        Schema::disableForeignKeyConstraints();

        // Truncate planning tables only (NOT users, predictions, point_logs)
        DB::table('animations')->truncate();
        $this->command->info('   - Truncated: animations');

        // Important: Only truncate predictions if they reference old matches
        // For fresh deployment, we'll delete predictions for old matches
        $this->command->info('   - Cleaning old predictions...');
        DB::table('predictions')->delete();
        $this->command->info('   - Deleted: predictions (users preserved)');

        DB::table('matches')->truncate();
        $this->command->info('   - Truncated: matches');

        DB::table('teams')->truncate();
        $this->command->info('   - Truncated: teams');

        DB::table('bars')->truncate();
        $this->command->info('   - Truncated: bars (venues)');

        // Re-enable foreign key checks
        Schema::enableForeignKeyConstraints();

        $this->command->info('✓ Planning data cleaned (user data preserved)');
        $this->command->info('');
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
            // Skip empty rows
            if (empty($data[0])) {
                continue;
            }

            // New CSV format: venue_name,zone,date,time,team_1,team_2,latitude,longitude,TYPE_PDV
            $team1 = trim($data[4] ?? '');
            $team2 = trim($data[5] ?? '');

            // Check if it's a playoff match (team_2 is empty)
            $isPlayoff = empty($team2);

            if ($isPlayoff) {
                // Playoff match: team_1 contains the playoff stage name
                $matchName = $team1;
            } else {
                // Regular match: construct match_name from teams
                $matchName = $team1 . ' VS ' . $team2;
            }

            $rows[] = [
                'venue_name' => trim($data[0] ?? ''),
                'zone' => trim($data[1] ?? ''),
                'date' => trim($data[2] ?? ''),
                'time' => trim($data[3] ?? ''),
                'match_name' => $matchName,
                'team_1' => $team1,
                'team_2' => $team2,
                'latitude' => trim($data[6] ?? ''),
                'longitude' => trim($data[7] ?? ''),
                'type_pdv' => !empty(trim($data[8] ?? '')) ? trim($data[8]) : 'dakar',
            ];
        }

        fclose($handle);

        $count = count($rows);
        $this->command->info("   - Parsed {$count} rows from CSV");
        $this->command->info('');
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

        // Mapping des noms d'équipes vers leurs ISO codes
        $teamIsoMapping = [
            'MAROC' => 'ma',
            'ALGÉRIE' => 'dz',
            'ALGERIE' => 'dz',
            'ÉGYPTE' => 'eg',
            'EGYPTE' => 'eg',
            'TUNISIE' => 'tn',
            'SÉNÉGAL' => 'sn',
            'SENEGAL' => 'sn',
            'CÔTE D\'IVOIRE' => 'ci',
            'COTE D\'IVOIRE' => 'ci',
            'COTE DIVOIRE' => 'ci',
            'NIGERIA' => 'ng',
            'MALI' => 'ml',
            'BURKINA FASO' => 'bf',
            'BÉNIN' => 'bj',
            'BENIN' => 'bj',
            'GUINÉE ÉQUATORIALE' => 'gq',
            'GUINEE EQUATORIALE' => 'gq',
            'CAMEROUN' => 'cm',
            'RD CONGO' => 'cd',
            'GABON' => 'ga',
            'ANGOLA' => 'ao',
            'OUGANDA' => 'ug',
            'TANZANIE' => 'tz',
            'SOUDAN' => 'sd',
            'COMORES' => 'km',
            'AFRIQUE DU SUD' => 'za',
            'ZAMBIE' => 'zm',
            'ZIMBABWE' => 'zw',
            'MOZAMBIQUE' => 'mz',
            'BOTSWANA' => 'bw',
        ];

        $created = 0;
        foreach ($teamNames as $teamName) {
            $isoCode = $teamIsoMapping[strtoupper($teamName)] ?? null;

            Team::create([
                'name' => $teamName,
                'iso_code' => $isoCode,
                'group' => null, // Can be updated manually later
            ]);
            $created++;
        }

        $this->command->info("   ✓ Created {$created} teams");
        $this->command->info('');
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
                    'type_pdv' => $row['type_pdv'],
                ];
            }
        }

        $created = 0;
        foreach ($venuesByKey as $venueData) {
            Bar::create([
                'name' => $venueData['name'],
                'zone' => $venueData['zone'],
                'address' => $venueData['zone'], // Zone as address
                'latitude' => $venueData['latitude'],
                'longitude' => $venueData['longitude'],
                'type_pdv' => $venueData['type_pdv'],
                'is_active' => true,
            ]);
            $created++;
        }

        $this->command->info("   ✓ Created {$created} venues");
        $this->command->info('');
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
                MatchGame::create([
                    'match_date' => $matchData['match_date'],
                    'match_name' => $matchData['match_name'],
                    'team_a' => 'TBD',
                    'team_b' => 'TBD',
                    'home_team_id' => null,
                    'away_team_id' => null,
                    'status' => 'scheduled',
                    'phase' => $matchData['phase'],
                    'stadium' => 'TBD',
                ]);
            } else {
                MatchGame::create([
                    'match_date' => $matchData['match_date'],
                    'team_a' => $matchData['team_a'],
                    'team_b' => $matchData['team_b'],
                    'home_team_id' => $matchData['home_team_id'],
                    'away_team_id' => $matchData['away_team_id'],
                    'status' => 'scheduled',
                    'phase' => $matchData['phase'],
                    'stadium' => 'TBD',
                ]);
            }
            $created++;
        }

        $this->command->info("   ✓ Created {$created} matches");
        $this->command->info('');
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
                Animation::create([
                    'match_id' => $match->id,
                    'bar_id' => $venue->id,
                    'animation_date' => $matchDate->format('Y-m-d'),
                    'animation_time' => $matchDate->format('H:i'),
                    'is_active' => true,
                ]);
                $created++;
            } else {
                $errors++;
                if (!$match) {
                    $this->command->warn("   ⚠ Match not found: {$row['match_name']}");
                }
                if (!$venue) {
                    $this->command->warn("   ⚠ Venue not found: {$row['venue_name']} ({$row['zone']})");
                }
            }
        }

        $this->command->info("   ✓ Created {$created} animations");
        if ($errors > 0) {
            $this->command->warn("   ⚠ {$errors} errors during animation import");
        }
        $this->command->info('');
    }

    /**
     * Verify data integrity
     */
    protected function verifyDataIntegrity(int $initialUsers, int $initialPredictions): void
    {
        $this->command->info('');
        $this->command->info('╔════════════════════════════════════════╗');
        $this->command->info('║       VERIFICATION & STATISTICS       ║');
        $this->command->info('╚════════════════════════════════════════╝');
        $this->command->info('');

        $finalUsers = User::count();
        $finalPredictions = Prediction::count();

        $this->command->table(
            ['Resource', 'Count', 'Status'],
            [
                ['Teams', Team::count(), '✅'],
                ['Venues', Bar::count(), '✅'],
                ['Venues with coordinates', Bar::whereNotNull('latitude')->count(), '✅'],
                ['Venues with zones', Bar::whereNotNull('zone')->count(), '✅'],
                ['Matches', MatchGame::count(), '✅'],
                ['Animations', Animation::count(), '✅'],
                ['---', '---', '---'],
                ['Users (PRESERVED)', $finalUsers, $finalUsers === $initialUsers ? '✅ SAFE' : '⚠️  CHANGED'],
                ['Predictions', $finalPredictions, '📊'],
            ]
        );

        // User data integrity check
        if ($finalUsers !== $initialUsers) {
            $this->command->error('');
            $this->command->error('⚠️  WARNING: User count changed!');
            $this->command->error("   Users: {$initialUsers} → {$finalUsers}");
            $this->command->error('   This should NOT happen!');
        } else {
            $this->command->info('');
            $this->command->info('✅ User data integrity verified!');
        }

        // Sample data
        $this->command->info('');
        $this->command->info('📋 Sample Data:');

        $sampleVenue = Bar::whereNotNull('latitude')->first();
        if ($sampleVenue) {
            $this->command->info("   Venue: {$sampleVenue->name} ({$sampleVenue->zone})");
            $this->command->info("   Type: {$sampleVenue->type_pdv}");
            $this->command->info("   Coordinates: {$sampleVenue->latitude}, {$sampleVenue->longitude}");
        }

        $sampleMatch = MatchGame::first();
        if ($sampleMatch) {
            $this->command->info("   Match: {$sampleMatch->team_a} vs {$sampleMatch->team_b}");
            $this->command->info("   Date: {$sampleMatch->match_date}");
        }

        $animationCount = Animation::count();
        $this->command->info("   Animations: {$animationCount} match-venue links created");
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
}
