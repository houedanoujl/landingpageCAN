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

class FreshPlanningSeeder extends Seeder
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

        $this->command->info('🔄 Starting fresh planning import...');

        // Step 1: Cleanup
        $this->cleanupDatabase();

        // Step 2: Parse CSV
        $csvData = $this->parseCsvData();

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
     * Parse CSV data
     */
    protected function parseCsvData(): array
    {
        $this->command->info('📄 Parsing CSV data...');

        $csvContent = $this->getCsvContent();
        $rows = [];

        foreach ($csvContent as $row) {
            // Trim all fields
            $rows[] = [
                'venue_name' => trim($row[0]),
                'zone' => trim($row[1]),
                'date' => trim($row[2]),
                'time' => trim($row[3]),
                'team_1' => trim($row[4]),
                'team_2' => trim($row[5] ?? ''),
                'latitude' => trim($row[6]),
                'longitude' => trim($row[7]),
                'type_pdv' => trim($row[8] ?? 'dakar'), // Default to dakar if empty
            ];
        }

        $this->command->info("   - Parsed " . count($rows) . " rows");
        return $rows;
    }

    /**
     * Get CSV content
     */
    protected function getCsvContent(): array
    {
        return [
            ['CHEZ JEAN', 'THIAROYE', '23/12/2025', '15 H', 'SENEGAL', 'BOTSWANA', '14.7517342', '-17.381228', ''],
            ['BAR BONGRE', 'TIVAOUNE PEUL', '26/12/2025', '15 H', 'AFRIQUE DU SUD', 'EGYPTE', '14.7880784', '-17.2884962', ''],
            ['BAR CHEZ HENRI', 'SEBIKOTANE', '27/12/2025', '15 H', 'SENEGAL', 'RD CONGO', '14.75083', '-17.4558011', ''],
            ['BAR CHEZ PREIRA', 'KEUR MBAYE FALL', '28/12/2025', '20 H', 'COTE D\'IVOIRE', 'CAMEROUN', '14.7498845', '-17.3440214', ''],
            ['BAR KAMIEUM', 'THIAROYE', '30/12/2025', '19 H', 'SENEGAL', 'BENIN', '14.7642474', '-17.3732367', ''],
            ['BAR ALLIANCE', 'KEUR MBAYE FALL', '03/01/2026', '16 H', 'HUITIEME DE FINALE', '', '14.7407892', '-17.3234235', ''],
            ['BAR CHEZ TANTI', 'THIAROYE', '09/01/2026', '16 H', 'QUART DE FINALE', '', '14.7669105', '-17.3801388', ''],
            ['BAR BLEUKEUSSS', 'DIAMEGEUNE', '14/01/2026', '16 H', 'DEMI FINALE', '', '14.7652458', '-17.4457674', ''],
            ['CHEZ JEAN', 'THIAROYE', '17/01/2026', '16 H', 'TROISIEME PLACE', '', '14.7517342', '-17.381228', ''],
            ['BAR CHEZ PREIRA', 'KEUR MBAYE FALL', '18/01/2026', '16 H', 'FINALE', '', '14.7498845', '-17.3440214', ''],
            ['BAR FOUGON 2', 'MALIKA', '23/12/2025', '15 H', 'SENEGAL', 'BOTSWANA', '14.7922816', '-17.3289989', ''],
            ['BAR JOE BASS', 'KEUR MASSAR', '26/12/2025', '15 H', 'AFRIQUE DU SUD', 'EGYPTE', '14.7778322', '-17.33062', ''],
            ['BAR CHEZ MILI', 'MALIKA', '27/12/2025', '15 H', 'SENEGAL', 'RD CONGO', '14.7508245', '-17.4557677', ''],
            ['BAR TERANGA', 'KEUR MASSAR', '28/12/2025', '20 H', 'COTE D\'IVOIRE', 'CAMEROUN', '14.7508636', '-17.3102724', ''],
            ['BAR BAKASAO', 'MALIKA', '30/12/2025', '19 H', 'SENEGAL', 'BENIN', '14.7508286', '-17.4557744', ''],
            ['BAR KAWARAFAN', 'KEUR MASSAR', '03/01/2026', '16 H', 'HUITIEME DE FINALE', '', '14.7644153', '-17.3052866', ''],
            ['BAR CHEZ ALICE', 'KEUR MASSAR', '09/01/2026', '16 H', 'QUART DE FINALE', '', '14.7612882', '-17.2841361', ''],
            ['BAR TITANIUM', 'KOUNOUNE', '14/01/2026', '16 H', 'DEMI FINALE', '', '14.7562447', '-17.2612446', ''],
            ['BAR CONCENSUS', 'KEUR MASSAR', '17/01/2026', '16 H', 'TROISIEME PLACE', '', '14.7738608', '-17.3208291', ''],
            ['BAR FOUGON 2', 'MALIKA', '18/01/2026', '16 H', 'FINALE', '', '14.7922816', '-17.3289989', ''],
            ['BAR POPEGUINE', 'KEUR MASSAR', '23/12/2025', '15 H', 'SENEGAL', 'BOTSWANA', '14.7722491', '-17.3154377', ''],
            ['BAR YAKAR', 'KEURMASSAR', '26/12/2025', '15 H', 'AFRIQUE DU SUD', 'EGYPTE', '14.771011', '-17.3150093', ''],
            ['BAR BAZILE', 'GUEDIAWAYE', '27/12/2025', '15 H', 'SENEGAL', 'RD CONGO', '14.7813471', '-17.3755211', ''],
            ['BAR POPEGUINE', 'KEURMASSAR', '28/12/2025', '20 H', 'COTE D\'IVOIRE', 'CAMEROUN', '14.772204', '-17.315406', ''],
            ['BAR CHEZ PASCAL', 'GUEDIAWAYE', '30/12/2025', '19 H', 'SENEGAL', 'BENIN', '14.785374', '-17.378309', ''],
            ['BAR KAPOL', 'GUEDIAWAYE', '03/01/2026', '16 H', 'HUITIEME DE FINALE', '', '14.776948', '-17.377118', ''],
            ['CHEZ MARCEL', 'GUEDIAWAYE', '09/01/2026', '16 H', 'QUART DE FINALE', '', '14.76825', '-17.3895', ''],
            ['BAR ELTON', 'GUEDIAWAYE', '14/01/2026', '16 H', 'DEMI FINALE', '', '14.785426', '-17.3783207', ''],
            ['BAR BOUELO', 'GUEDIAWAYE', '17/01/2026', '16 H', 'TROISIEME PLACE', '', '14.6761585', '-17.4477634', ''],
            ['BAR POPEGUINE', 'KEUR MASSAR', '18/01/2026', '16 H', 'FINALE', '', '14.6815672', '-17.4544187', ''],
            ['BAR OUTHEKOR', 'GRAND-YOFF', '23/12/2025', '15 H', 'SENEGAL', 'BOTSWANA', '14.736913', '-17.4467729', ''],
            ['CHEZ HENRIETTE', 'GRAND-YOFF', '26/12/2025', '15 H', 'AFRIQUE DU SUD', 'EGYPTE', '14.7382659', '-17.4518328', ''],
            ['CASA BAR', 'GRAND-YOFF', '27/12/2025', '15 H', 'SENEGAL', 'RD CONGO', '14.7375747', '-17.444779', ''],
            ['BAR KAMEME', 'GRAND-YOFF', '28/12/2025', '20 H', 'COTE D\'IVOIRE', 'CAMEROUN', '14.7343559', '-17.4462383', ''],
            ['CHEZ MANOU', 'GRAND-YOFF', '30/12/2025', '19 H', 'SENEGAL', 'BENIN', '14.7344494', '-17.4539584', ''],
            ['COUCOU LE JOIE', 'GRAND-YOFF', '03/01/2026', '16 H', 'HUITIEME DE FINALE', '', '14.7328', '-17.4562', ''],
            ['BAR EDIOUNGOU', 'GRAND-YOFF', '09/01/2026', '16 H', 'QUART DE FINALE', '', '14.7375483', '-17.4481482', ''],
            ['BAR AWARA', 'GRAND-YOFF', '14/01/2026', '16 H', 'DEMI FINALE', '', '14.7416678', '-17.4444997', ''],
            ['BAR ROYAUME DU PORC', 'GRAND-YOFF', '17/01/2026', '16 H', 'TROISIEME PLACE', '', '14.7378194', '-17.4435484', ''],
            ['BAR SANTHIABA', 'GRAND-YOFF', '18/01/2026', '16 H', 'FINALE', '', '14.7372804', '-17.4447347', ''],
            ['BAR ETALON', 'GRAND-DAKAR', '23/12/2025', '15 H', 'SENEGAL', 'BOTSWANA', '14.6917911', '-17.4337784', ''],
            ['BAR CHEZ JEAN', 'GRAND-DAKAR', '26/12/2025', '15 H', 'AFRIQUE DU SUD', 'EGYPTE', '14.7382449', '-17.4518402', ''],
            ['BAR BANDIAL', 'REUBEUSS', '27/12/2025', '15 H', 'SENEGAL', 'RD CONGO', '14.6704586', '-17.4414847', ''],
            ['BAR BISTRO', 'SICAP LIBERTE 5', '28/12/2025', '20 H', 'COTE D\'IVOIRE', 'CAMEROUN', '14.7090235', '-17.4582593', ''],
            ['BAR CHEZ CATHO', 'LIBERTE 5', '30/12/2025', '19 H', 'SENEGAL', 'BENIN', '14.7215509', '-17.4628454', ''],
            ['BAR CHEZ GUILLAINE', 'HLM', '03/01/2026', '16 H', 'HUITIEME DE FINALE', '', '14.70865', '-17.446952', ''],
            ['BAR ETALON', 'GRAND-DAKAR', '09/01/2026', '16 H', 'QUART DE FINALE', '', '14.700343', '-17.4554238', ''],
            ['BAR SAMARITIN', 'LIBERT 3', '14/01/2026', '16 H', 'DEMI FINALE', '', '14.80691', '-17.33091', ''],
            ['BAR CHEZ JEAN', 'GRAND-DAKAR', '17/01/2026', '16 H', 'TROISIEME PLACE', '', '14.7517342', '-17.381228', ''],
            ['BAR ETALON', 'GRAND-DAKAR', '18/01/2026', '16 H', 'FINALE', '', '14.6917911', '-17.4337784', ''],
            ['BAR UMIRAN', 'PARCELLES ASSAINIES U 17', '23/12/2025', '15 H', 'SENEGAL', 'BOTSWANA', '14.7567058', '-17.4406723', ''],
            ['BAR LA GOREENNE', 'PATTE D\'OIE', '26/12/2025', '15 H', 'AFRIQUE DU SUD', 'EGYPTE', '14.7476696', '-17.4432123', ''],
            ['BAR DAKHARGUI', 'PARCELLES ASSAINIES U 17', '27/12/2025', '15 H', 'SENEGAL', 'RD CONGO', '14.757696', '-17.439845', ''],
            ['BAR ETHIOUNG', 'PARCELLES ASSAINIES U 7', '28/12/2025', '20 H', 'COTE D\'IVOIRE', 'CAMEROUN', '14.72545', '-17.442953', ''],
            ['BAR MONTAGNE', 'PARCELLES ASSAINIES U 26', '30/12/2025', '19 H', 'SENEGAL', 'BENIN', '14.756638', '-17.441177', ''],
            ['BAR KANDJIDIASSA', 'PARCELLES ASSAINIES U 19', '03/01/2026', '16 H', 'HUITIEME DE FINALE', '', '14.75513', '-17.451919', ''],
            ['BAR DAKHARGUI', 'PARCELLES ASSAINIES U 17', '09/01/2026', '16 H', 'QUART DE FINALE', '', '14.7575839', '-17.4399306', ''],
            ['BAR KADETH', 'PARCELLES ASSAINIES U 12', '14/01/2026', '16 H', 'DEMI FINALE', '', '14.7573599', '-17.4417203', ''],
            ['BAR CHEZ VINCENT', 'PARCELLES ASSAINIES U 24', '17/01/2026', '16 H', 'TROISIEME PLACE', '', '14.7536497', '-17.4467705', ''],
            ['BAR UMIRAN', 'PARCELLES ASSAINIES U 17', '18/01/2026', '16 H', 'FINALE', '', '14.7567058', '-17.4406723', ''],
            ['BAR SET SET', 'PARCELLES ASSAINIES U 21', '23/12/2025', '15 H', 'SENEGAL', 'BOTSWANA', '14.7557735', '-17.4448494', ''],
            ['BAR CASA ESTANCIA', 'PARCELLES ASSAINIES U 10', '26/12/2025', '15 H', 'AFRIQUE DU SUD', 'EGYPTE', '14.76136', '-17.4337118', ''],
            ['BAR CHEZ FRANCOIS', 'CITE FADIA', '27/12/2025', '15 H', 'SENEGAL', 'RD CONGO', '14.7095886', '-17.4523725', ''],
            ['BAR CASA ESTANCIA', 'PARCELLES ASSAINIES U 10', '28/12/2025', '20 H', 'COTE D\'IVOIRE', 'CAMEROUN', '14.76136', '-17.4337118', ''],
            ['BAR SET SET', 'PARCELLES ASSAINIES U 21', '30/12/2025', '19 H', 'SENEGAL', 'BENIN', '14.7557735', '-17.4448494', ''],
            ['BAR CHEZ VALERIE', 'ROND POINT CASE', '03/01/2026', '16 H', 'HUITIEME DE FINALE', '', '14.7577258', '-17.4285123', ''],
            ['BAR CHEZ FRANCOIS', 'CITE FADIA', '09/01/2026', '16 H', 'QUART DE FINALE', '', '14.7095886', '-17.4523725', ''],
            ['BAR MAISON BLANCHE', 'PARCELLES U 10', '14/01/2026', '16 H', 'DEMI FINALE', '', '14.7617111', '-17.4365972', ''],
            ['BAR CHEZ VALERIE', 'ROND POINT CASE', '17/01/2026', '16 H', 'TROISIEME PLACE', '', '14.7577274', '-17.4285475', ''],
            ['BAR SET SET', 'PARECELLES U 21', '18/01/2026', '16 H', 'FINALE', '', '14.7557735', '-17.4448494', ''],
            ['BAR JOYCE', 'OUAKAM', '23/12/2025', '15 H', 'SENEGAL', 'BOTSWANA', '14.6928039', '-17.4603993', ''],
            ['BAR JEROME', 'OUAKAM', '26/12/2025', '15 H', 'AFRIQUE DU SUD', 'EGYPTE', '14.7269138', '-17.4828138', ''],
            ['BAR JEROME', 'OUAKAM', '27/12/2025', '15 H', 'SENEGAL', 'RD CONGO', '14.7269138', '-17.4828138', ''],
            ['BAR LE BOURBEOIS', 'OUAKAM', '28/12/2025', '20 H', 'COTE D\'IVOIRE', 'CAMEROUN', '14.7274408', '-17.4838794', ''],
            ['BAR JOYCE', 'OUAKAM', '30/12/2025', '19 H', 'SENEGAL', 'BENIN', '14.6928039', '-17.4603993', ''],
            ['BAR JEROME', 'OUAKAM', '03/01/2026', '16 H', 'HUITIEME DE FINALE', '', '14.7269138', '-17.4828138', ''],
            ['BAR CHEZ LOPY', 'OUAKAM', '09/01/2026', '16 H', 'QUART DE FINALE', '', '14.72', '-17.48', ''],
            ['BAR JOYCE', 'OUAKAM', '14/01/2026', '16 H', 'DEMI FINALE', '', '14.6928039', '-17.4603993', ''],
            ['BAR AWALE', 'OUAKAM', '17/01/2026', '16 H', 'TROISIEME PLACE', '', '14.725', '-17.481', ''],
            ['BAR JEROME', 'OUAKAM', '18/01/2026', '16 H', 'FINALE', '', '14.7269138', '-17.4828138', ''],
        ];
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
                $teamNames[] = $row['team_1'];
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
                $this->command->warn("   ⚠ Could not link: {$row['venue_name']} - {$row['team_1']}");
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
