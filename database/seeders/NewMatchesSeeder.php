<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Bar;
use App\Models\Match as MatchModel;
use App\Models\Prediction;
use App\Models\PointsLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class NewMatchesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Démarrage de l\'importation des nouveaux matchs...');
        
        // 1. NETTOYAGE DES DONNÉES EXISTANTES
        $this->command->info('🧹 Nettoyage des données existantes...');
        
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Supprimer les points logs liés aux matchs et bars
        PointsLog::whereNotNull('match_id')->delete();
        PointsLog::whereNotNull('bar_id')->delete();
        $this->command->info('✅ Points logs supprimés');
        
        // Supprimer toutes les predictions
        Prediction::truncate();
        $this->command->info('✅ Prédictions supprimées');
        
        // Supprimer tous les matchs
        MatchModel::truncate();
        $this->command->info('✅ Matchs supprimés');
        
        // Supprimer tous les bars
        Bar::truncate();
        $this->command->info('✅ Bars supprimés');
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        // 2. LECTURE DU CSV
        $this->command->info('📖 Lecture du fichier CSV...');
        $csvPath = database_path('seeders/new_matches.csv');
        
        if (!file_exists($csvPath)) {
            $this->command->error('❌ Le fichier CSV n\'existe pas: ' . $csvPath);
            return;
        }
        
        $file = fopen($csvPath, 'r');
        $header = fgetcsv($file); // Skip header
        
        $barsData = [];
        $matchesData = [];
        
        while (($row = fgetcsv($file)) !== false) {
            if (count($row) < 8) continue;
            
            $venueName = trim($row[0]);
            $zone = trim($row[1]);
            $date = trim($row[2]);
            $time = trim($row[3]);
            $team1 = trim($row[4]);
            $team2 = trim($row[5]);
            $latitude = floatval($row[6]);
            $longitude = floatval($row[7]);
            
            // Enregistrer les bars uniques
            $barKey = $venueName . '|' . $latitude . '|' . $longitude;
            if (!isset($barsData[$barKey])) {
                $barsData[$barKey] = [
                    'name' => $venueName,
                    'zone' => $zone,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                ];
            }
            
            // Enregistrer les matchs
            $matchesData[] = [
                'venue_key' => $barKey,
                'date' => $date,
                'time' => $time,
                'team_1' => $team1,
                'team_2' => $team2,
            ];
        }
        
        fclose($file);
        
        $this->command->info('📊 ' . count($barsData) . ' bars uniques trouvés');
        $this->command->info('📊 ' . count($matchesData) . ' matchs trouvés');
        
        // 3. CRÉATION DES BARS
        $this->command->info('🏪 Création des bars...');
        $createdBars = [];
        
        foreach ($barsData as $barKey => $barInfo) {
            $bar = Bar::create([
                'name' => $barInfo['name'],
                'address' => $barInfo['zone'],
                'zone' => $barInfo['zone'],
                'latitude' => $barInfo['latitude'],
                'longitude' => $barInfo['longitude'],
                'qr_code' => strtoupper(str_replace(' ', '_', $barInfo['name'])) . '_' . time(),
                'is_active' => true,
            ]);
            
            $createdBars[$barKey] = $bar;
        }
        
        $this->command->info('✅ ' . count($createdBars) . ' bars créés');
        
        // 4. CRÉATION DES MATCHS
        $this->command->info('⚽ Création des matchs...');
        $matchCount = 0;
        
        foreach ($matchesData as $matchInfo) {
            $bar = $createdBars[$matchInfo['venue_key']];
            
            // Parser la date et l'heure
            $dateString = $this->parseDateString($matchInfo['date'], $matchInfo['time']);
            
            // Déterminer si c'est un match TBD (phase de knockout)
            $isTbd = empty($matchInfo['team_2']);
            $phaseName = $isTbd ? $matchInfo['team_1'] : null;
            
            MatchModel::create([
                'team_a' => $isTbd ? 'À déterminer' : $matchInfo['team_1'],
                'team_b' => $isTbd ? 'À déterminer' : $matchInfo['team_2'],
                'match_date' => $dateString,
                'status' => 'scheduled',
                'phase_name' => $phaseName,
                'is_tbd' => $isTbd,
                'bar_id' => $bar->id,
                'stadium' => $bar->name,
            ]);
            
            $matchCount++;
        }
        
        $this->command->info('✅ ' . $matchCount . ' matchs créés');
        
        $this->command->info('🎉 Importation terminée avec succès!');
        $this->command->info('📍 Bars: ' . count($createdBars));
        $this->command->info('⚽ Matchs: ' . $matchCount);
    }
    
    /**
     * Parse une date et heure du format DD/MM/YYYY et HH H
     */
    private function parseDateString(string $date, string $time): string
    {
        // Parser la date (format: DD/MM/YYYY)
        $dateParts = explode('/', $date);
        $day = $dateParts[0];
        $month = $dateParts[1];
        $year = $dateParts[2];
        
        // Parser l'heure (format: "15 H" ou "20 H")
        $hour = (int) str_replace(' H', '', $time);
        
        // Créer la date complète
        return sprintf('%s-%s-%s %02d:00:00', $year, $month, $day, $hour);
    }
}
