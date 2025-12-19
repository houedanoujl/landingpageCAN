<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Team;
use App\Models\Stadium;
use App\Models\MatchGame;
use App\Models\Bar;
use App\Models\Animation;
use App\Models\User;
use App\Models\Prediction;

class ProductionSafeSeeder extends Seeder
{
    /**
     * Orchestrateur de seeders SAFE pour production
     *
     * ✅ Préserve : users, predictions, user_points
     * ✅ Met à jour : teams, matches, venues, animations
     * ✅ Idempotent : peut être exécuté plusieurs fois
     *
     * CRITICAL: Ce seeder utilise updateOrCreate() au lieu de truncate()
     * pour préserver toutes les données utilisateurs existantes.
     */
    public function run()
    {
        $this->command->info('');
        $this->command->info('╔════════════════════════════════════════╗');
        $this->command->info('║   PRODUCTION-SAFE SEEDING - CAN 2025  ║');
        $this->command->info('╚════════════════════════════════════════╝');
        $this->command->info('');

        // Sauvegarde des counts initiaux pour vérification
        $initialUsers = User::count();
        $initialPredictions = Prediction::count();

        $this->command->info("📊 Initial State:");
        $this->command->info("   Users: {$initialUsers}");
        $this->command->info("   Predictions: {$initialPredictions}");
        $this->command->info('');

        // Ordre important : dépendances
        $this->command->info('📋 Step 1/4: Seeding Teams...');
        $this->call(TeamSeeder::class);

        $this->command->info('');
        $this->command->info('🏟️  Step 2/4: Seeding Stadiums...');
        $this->call(StadiumSeeder::class);

        $this->command->info('');
        $this->command->info('⚽ Step 3/4: Seeding Matches...');
        $this->call(MatchSeeder::class);

        $this->command->info('');
        $this->command->info('📍 Step 4/4: Seeding Venues & Animations...');
        $this->call(FixAnimationsSeeder::class);

        $this->command->info('');
        $this->command->info('✅ PRODUCTION-SAFE seeding completed!');
        $this->command->info('');

        // Vérification finale
        $finalUsers = User::count();
        $finalPredictions = Prediction::count();

        // Statistiques détaillées
        $this->command->info('╔════════════════════════════════════════╗');
        $this->command->info('║       FINAL STATISTICS                ║');
        $this->command->info('╚════════════════════════════════════════╝');
        $this->command->info('');

        $this->command->table(
            ['Resource', 'Count', 'Status'],
            [
                ['Teams', Team::count(), '✅'],
                ['Stadiums', Stadium::count(), '✅'],
                ['Matches', MatchGame::count(), '✅'],
                ['Venues', Bar::count(), '✅'],
                ['Venues with coordinates', Bar::whereNotNull('latitude')->count(), '✅'],
                ['Venues with zones', Bar::whereNotNull('zone')->count(), '✅'],
                ['Animations', Animation::count(), '✅'],
                ['---', '---', '---'],
                ['Users (PRESERVED)', $finalUsers, $finalUsers === $initialUsers ? '✅ SAFE' : '❌ CHANGED'],
                ['Predictions (PRESERVED)', $finalPredictions, $finalPredictions === $initialPredictions ? '✅ SAFE' : '❌ CHANGED'],
            ]
        );

        // Alerte si données utilisateurs modifiées
        if ($finalUsers !== $initialUsers || $finalPredictions !== $initialPredictions) {
            $this->command->error('');
            $this->command->error('⚠️  WARNING: User data was modified!');
            $this->command->error("   Users: {$initialUsers} → {$finalUsers}");
            $this->command->error("   Predictions: {$initialPredictions} → {$finalPredictions}");
            $this->command->error('   This should NOT happen with production-safe seeders!');
            $this->command->error('');
        } else {
            $this->command->info('');
            $this->command->info('✅ User data integrity verified: All users and predictions preserved!');
            $this->command->info('');
        }

        // Exemples de données
        $this->command->info('📋 Sample Data Check:');
        $sampleVenue = Bar::whereNotNull('latitude')->whereNotNull('zone')->first();
        if ($sampleVenue) {
            $this->command->info("   Sample Venue: {$sampleVenue->name}");
            $this->command->info("   Zone: {$sampleVenue->zone}");
            $this->command->info("   Coordinates: {$sampleVenue->latitude}, {$sampleVenue->longitude}");
        }

        $sampleAnimation = Animation::with(['bar', 'match'])->whereHas('match')->first();
        if ($sampleAnimation && $sampleAnimation->bar && $sampleAnimation->match) {
            $this->command->info("   Sample Animation: {$sampleAnimation->bar->name}");
            $this->command->info("   → Match: {$sampleAnimation->match->team_a} vs {$sampleAnimation->match->team_b}");
            $this->command->info("   → Date: {$sampleAnimation->animation_date} {$sampleAnimation->animation_time}");
        }

        $this->command->info('');
        $this->command->info('🎉 Production deployment ready!');
        $this->command->info('');
    }
}
