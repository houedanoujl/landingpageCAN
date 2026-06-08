<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 🔄 Remplacement total : on vide les données de la compétition précédente
        // (CAN) avant d'importer la Coupe du Monde 2026 (Football Fest 2026).
        $this->wipeCompetitionData();

        // Seeders de données essentielles (toujours exécutés)
        $this->call([
            TeamSeeder::class,           // ✅ Équipes Football Fest 2026 (Coupe du Monde 2026)
            StadiumSeeder::class,        // ✅ Stades (USA / Canada / Mexique)
            MatchSeeder::class,          // ✅ Matchs (104 rencontres)
            TestMatchSeeder::class,      // 🧪 Match test (commence dès demain) pour tester les pronostics
            BarSeeder::class,            // ✅ Points de vente (réseau SOBOA)
            // ⚠️ AnimationSeeder désactivé : ses données (match-bar) étaient propres
            //    à la CAN et ne correspondent plus aux matchs de la Coupe du Monde.
            //    Les animations sont à (re)créer via l'espace admin.
            AdminUserSeeder::class,      // ✅ Admin
        ]);

        // Seeders de test (uniquement en développement)
        if (app()->environment('local', 'development')) {
            $this->call([
                UserSeeder::class,       // 🧪 Utilisateurs de test
                PredictionSeeder::class, // 🧪 Prédictions de test
            ]);
            $this->command->info('🧪 Données de test ajoutées (environnement local)');
        }
    }

    /**
     * Vide les données liées à la compétition (équipes, matchs, stades, animations)
     * ainsi que les pronostics associés, avant le ré-import.
     * Les contraintes de clés étrangères sont temporairement désactivées.
     */
    private function wipeCompetitionData(): void
    {
        Schema::disableForeignKeyConstraints();

        // Détacher l'équipe favorite référencée dans les réglages du site
        if (Schema::hasTable('site_settings') && Schema::hasColumn('site_settings', 'favorite_team_id')) {
            DB::table('site_settings')->update(['favorite_team_id' => null]);
        }

        foreach (['predictions', 'animations', 'matches', 'teams', 'stadiums'] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }

        Schema::enableForeignKeyConstraints();

        $this->command->info('🧹 Données de l\'ancienne compétition supprimées (équipes, matchs, stades, animations, pronostics).');
    }
}
