<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Team;
use App\Models\Bar;
use App\Models\MatchGame;
use App\Models\Animation;
use App\Models\User;
use App\Models\Prediction;

class ProductionSeeder extends Seeder
{
    /**
     * SEEDER DÉFINITIF DE PRODUCTION
     *
     * Ce seeder synchronise les données de production avec le développement local
     * SANS toucher aux données utilisateurs (users, predictions).
     *
     * Utilisation:
     * php artisan db:seed --class=ProductionSeeder
     */
    public function run(): void
    {
        $this->command->newLine();
        $this->command->info('╔════════════════════════════════════════╗');
        $this->command->info('║   PRODUCTION SEEDER - CAN 2025         ║');
        $this->command->info('║   Synchronisation Dev → Production     ║');
        $this->command->info('╚════════════════════════════════════════╝');
        $this->command->newLine();

        // 📊 État initial
        $this->showInitialState();

        // ⚠️ Confirmation
        if ($this->command->confirm('⚠️  Voulez-vous continuer? Les données de planning seront RÉINITIALISÉES (utilisateurs préservés)', true)) {

            DB::beginTransaction();

            try {
                // 🗑️ Nettoyage des données de planning
                $this->cleanPlanningData();

                // 📄 Import du CSV
                $csvData = $this->parseCSV();

                // 👥 Import des équipes
                $this->importTeams($csvData);

                // 🏢 Import des PDV
                $this->importVenues($csvData);

                // ⚽ Import des matchs
                $this->importMatches($csvData);

                // 🔗 Import des animations
                $this->importAnimations($csvData);

                DB::commit();

                // ✅ Vérifications finales
                $this->verifyData();

                $this->command->newLine();
                $this->command->info('🎉 Synchronisation terminée avec succès!');

            } catch (\Exception $e) {
                DB::rollBack();
                $this->command->error('❌ Erreur: ' . $e->getMessage());
                $this->command->error($e->getTraceAsString());
                throw $e;
            }
        } else {
            $this->command->warn('⚠️  Opération annulée');
        }
    }

    /**
     * Affiche l'état initial de la base de données
     */
    protected function showInitialState(): void
    {
        $this->command->info('📊 État initial:');
        $this->command->line('   Users: ' . User::count());
        $this->command->line('   Predictions: ' . Prediction::count());
        $this->command->line('   Teams: ' . Team::count());
        $this->command->line('   Venues: ' . Bar::count());
        $this->command->line('   Matches: ' . MatchGame::count());
        $this->command->line('   Animations: ' . Animation::count());
        $this->command->newLine();
    }

    /**
     * Nettoie les données de planning (préserve users et predictions)
     */
    protected function cleanPlanningData(): void
    {
        $this->command->info('🗑️  Nettoyage des données de planning...');

        // Désactiver les vérifications de clés étrangères
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Supprimer les animations
        Animation::truncate();
        $this->command->line('   - Truncated: animations');

        // Supprimer la table match_notifications si elle existe
        if (DB::getSchemaBuilder()->hasTable('match_notifications')) {
            DB::table('match_notifications')->truncate();
            $this->command->line('   - Truncated: match_notifications');
        }

        // Supprimer les matchs
        MatchGame::truncate();
        $this->command->line('   - Truncated: matches');

        // Supprimer les équipes
        Team::truncate();
        $this->command->line('   - Truncated: teams');

        // Supprimer les PDV
        Bar::truncate();
        $this->command->line('   - Truncated: bars');

        // Réactiver les vérifications de clés étrangères
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('✓ Nettoyage terminé (données utilisateurs préservées)');
        $this->command->newLine();
    }

    /**
     * Parse le fichier CSV
     */
    protected function parseCSV(): array
    {
        $this->command->info('📄 Lecture du fichier CSV...');

        $csvPath = base_path('venues.csv');

        if (!file_exists($csvPath)) {
            throw new \Exception("Fichier CSV non trouvé: {$csvPath}");
        }

        $rows = [];
        $handle = fopen($csvPath, 'r');

        // Skip header
        fgetcsv($handle);

        while (($data = fgetcsv($handle)) !== false) {
            if (count($data) < 9) continue;

            $rows[] = [
                'venue_name' => trim($data[0] ?? ''),
                'zone' => trim($data[1] ?? ''),
                'date' => trim($data[2] ?? ''),
                'time' => trim($data[3] ?? ''),
                'team_1' => trim($data[4] ?? ''),
                'team_2' => trim($data[5] ?? ''),
                'latitude' => trim($data[6] ?? ''),
                'longitude' => trim($data[7] ?? ''),
                'type_pdv' => !empty(trim($data[8] ?? '')) ? trim($data[8]) : 'dakar',
            ];
        }

        fclose($handle);

        $this->command->line("   - {count($rows)} lignes parsées");
        $this->command->newLine();

        return $rows;
    }

    /**
     * Import des équipes avec ISO codes
     */
    protected function importTeams(array $csvData): void
    {
        $this->command->info('👥 Import des équipes...');

        // Mapping noms → ISO codes
        $teamIsoMapping = [
            'MAROC' => 'ma', 'ALGÉRIE' => 'dz', 'ALGERIE' => 'dz',
            'ÉGYPTE' => 'eg', 'EGYPTE' => 'eg', 'TUNISIE' => 'tn',
            'SÉNÉGAL' => 'sn', 'SENEGAL' => 'sn',
            'CÔTE D\'IVOIRE' => 'ci', 'COTE D\'IVOIRE' => 'ci', 'COTE DIVOIRE' => 'ci',
            'NIGERIA' => 'ng', 'MALI' => 'ml', 'BURKINA FASO' => 'bf',
            'BÉNIN' => 'bj', 'BENIN' => 'bj',
            'GUINÉE ÉQUATORIALE' => 'gq', 'GUINEE EQUATORIALE' => 'gq',
            'CAMEROUN' => 'cm', 'RD CONGO' => 'cd', 'GABON' => 'ga', 'ANGOLA' => 'ao',
            'OUGANDA' => 'ug', 'TANZANIE' => 'tz', 'SOUDAN' => 'sd', 'COMORES' => 'km',
            'AFRIQUE DU SUD' => 'za', 'ZAMBIE' => 'zm', 'ZIMBABWE' => 'zw',
            'MOZAMBIQUE' => 'mz', 'BOTSWANA' => 'bw',
        ];

        $teamNames = [];
        foreach ($csvData as $row) {
            if (!empty($row['team_1'])) {
                $teamNames[] = $row['team_1'];
            }
            if (!empty($row['team_2'])) {
                $teamNames[] = $row['team_2'];
            }
        }
        $teamNames = array_unique($teamNames);

        $created = 0;
        foreach ($teamNames as $teamName) {
            $isoCode = $teamIsoMapping[strtoupper($teamName)] ?? null;

            Team::create([
                'name' => $teamName,
                'iso_code' => $isoCode,
                'group' => null,
            ]);
            $created++;
        }

        $this->command->line("   ✓ {$created} équipes créées");
        $this->command->newLine();
    }

    /**
     * Import des venues (PDV)
     */
    protected function importVenues(array $csvData): void
    {
        $this->command->info('🏢 Import des PDV...');

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
                'address' => $venueData['zone'],
                'latitude' => $venueData['latitude'],
                'longitude' => $venueData['longitude'],
                'type_pdv' => $venueData['type_pdv'],
                'is_active' => true,
            ]);
            $created++;
        }

        $this->command->line("   ✓ {$created} PDV créés");
        $this->command->newLine();
    }

    /**
     * Import des matchs
     */
    protected function importMatches(array $csvData): void
    {
        $this->command->info('⚽ Import des matchs...');

        $matchesByKey = [];

        foreach ($csvData as $row) {
            $team1 = $row['team_1'];
            $team2 = $row['team_2'];
            $isPlayoff = empty($team2);

            $matchName = $isPlayoff ? $team1 : $team1 . ' VS ' . $team2;

            $dateTime = \Carbon\Carbon::createFromFormat('d/m/Y H\Hi',
                $row['date'] . ' ' . str_replace(' ', '', $row['time']));

            $matchKey = $matchName . '|' . $dateTime->toDateTimeString();

            if (!isset($matchesByKey[$matchKey])) {
                $homeTeam = !$isPlayoff ? Team::where('name', $team1)->first() : null;
                $awayTeam = !$isPlayoff ? Team::where('name', $team2)->first() : null;

                $phase = $this->determinePhase($matchName, $isPlayoff);

                $matchesByKey[$matchKey] = [
                    'match_date' => $dateTime,
                    'match_name' => $matchName,
                    'team_a' => !$isPlayoff ? $team1 : 'À déterminer',
                    'team_b' => !$isPlayoff ? $team2 : 'À déterminer',
                    'home_team_id' => $homeTeam?->id,
                    'away_team_id' => $awayTeam?->id,
                    'phase' => $phase,
                    'is_playoff' => $isPlayoff,
                ];
            }
        }

        $created = 0;
        foreach ($matchesByKey as $matchData) {
            MatchGame::create([
                'match_date' => $matchData['match_date'],
                'match_name' => $matchData['match_name'],
                'team_a' => $matchData['team_a'],
                'team_b' => $matchData['team_b'],
                'home_team_id' => $matchData['home_team_id'],
                'away_team_id' => $matchData['away_team_id'],
                'status' => 'scheduled',
                'phase' => $matchData['phase'],
                'stadium' => 'À déterminer',
            ]);
            $created++;
        }

        $this->command->line("   ✓ {$created} matchs créés");
        $this->command->newLine();
    }

    /**
     * Import des animations (liens match-PDV)
     */
    protected function importAnimations(array $csvData): void
    {
        $this->command->info('🔗 Import des animations...');

        $created = 0;

        foreach ($csvData as $row) {
            $bar = Bar::where('name', $row['venue_name'])
                     ->where('zone', $row['zone'])
                     ->first();

            if (!$bar) continue;

            $team1 = $row['team_1'];
            $team2 = $row['team_2'];
            $matchName = empty($team2) ? $team1 : $team1 . ' VS ' . $team2;

            $dateTime = \Carbon\Carbon::createFromFormat('d/m/Y H\Hi',
                $row['date'] . ' ' . str_replace(' ', '', $row['time']));

            $match = MatchGame::where('match_name', $matchName)
                             ->where('match_date', $dateTime)
                             ->first();

            if (!$match) continue;

            Animation::create([
                'match_id' => $match->id,
                'bar_id' => $bar->id,
                'animation_date' => $dateTime->toDateString(),
                'animation_time' => $dateTime->format('H:i'),
            ]);

            $created++;
        }

        $this->command->line("   ✓ {$created} animations créées");
        $this->command->newLine();
    }

    /**
     * Détermine la phase d'un match
     */
    protected function determinePhase(string $matchName, bool $isPlayoff): string
    {
        if (!$isPlayoff) {
            return 'group_stage';
        }

        $phaseMap = [
            'HUITIEME DE FINALE' => 'round_of_16',
            'QUART DE FINALE' => 'quarter_final',
            'DEMI-FINALE' => 'semi_final',
            'FINALE' => 'final',
            '3E PLACE' => 'third_place',
        ];

        foreach ($phaseMap as $keyword => $phase) {
            if (stripos($matchName, $keyword) !== false) {
                return $phase;
            }
        }

        return 'group_stage';
    }

    /**
     * Vérifications finales
     */
    protected function verifyData(): void
    {
        $this->command->newLine();
        $this->command->info('╔════════════════════════════════════════╗');
        $this->command->info('║       VÉRIFICATION FINALE              ║');
        $this->command->info('╚════════════════════════════════════════╝');
        $this->command->newLine();

        $teams = Team::count();
        $venues = Bar::count();
        $matches = MatchGame::count();
        $animations = Animation::count();
        $users = User::count();
        $predictions = Prediction::count();

        $venuesWithCoords = Bar::whereNotNull('latitude')->whereNotNull('longitude')->count();
        $venuesWithZones = Bar::whereNotNull('zone')->count();
        $teamsWithIso = Team::whereNotNull('iso_code')->count();

        $this->command->table(
            ['Ressource', 'Nombre', 'Statut'],
            [
                ['Équipes', $teams, $teams > 0 ? '✅' : '❌'],
                ['Équipes avec ISO', $teamsWithIso, $teamsWithIso > 0 ? '✅' : '⚠️'],
                ['PDV', $venues, $venues > 0 ? '✅' : '❌'],
                ['PDV avec coordonnées', $venuesWithCoords, $venuesWithCoords == $venues ? '✅' : '⚠️'],
                ['PDV avec zones', $venuesWithZones, $venuesWithZones == $venues ? '✅' : '⚠️'],
                ['Matchs', $matches, $matches > 0 ? '✅' : '❌'],
                ['Animations', $animations, $animations > 0 ? '✅' : '❌'],
                ['---', '---', '---'],
                ['Users (PRÉSERVÉS)', $users, '✅ SAFE'],
                ['Prédictions (PRÉSERVÉES)', $predictions, '✅ SAFE'],
            ]
        );

        $this->command->newLine();
        $this->command->info('✅ Données utilisateurs intactes!');
        $this->command->info('✅ Production synchronisée avec développement!');
    }
}
