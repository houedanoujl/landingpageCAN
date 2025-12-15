<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Index pour les matchs (requêtes fréquentes par statut et date)
        Schema::table('matches', function (Blueprint $table) {
            $table->index(['status', 'match_date'], 'idx_matches_status_date');
            $table->index('match_date', 'idx_matches_date');
        });

        // Index pour les pronostics (jointures user/match fréquentes)
        Schema::table('predictions', function (Blueprint $table) {
            $table->index(['user_id', 'match_id'], 'idx_predictions_user_match');
            $table->index(['match_id', 'points_earned'], 'idx_predictions_match_points');
        });

        // Index pour le classement (tri par points très fréquent)
        Schema::table('users', function (Blueprint $table) {
            $table->index('points_total', 'idx_users_points');
            $table->index('phone', 'idx_users_phone');
        });

        // Index pour géolocalisation des bars
        Schema::table('bars', function (Blueprint $table) {
            $table->index('is_active', 'idx_bars_active');
            // Note: Pour une vraie optimisation géospatiale, utilisez MySQL SPATIAL INDEX
            // $table->spatialIndex(['latitude', 'longitude']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->dropIndex('idx_matches_status_date');
            $table->dropIndex('idx_matches_date');
        });

        Schema::table('predictions', function (Blueprint $table) {
            $table->dropIndex('idx_predictions_user_match');
            $table->dropIndex('idx_predictions_match_points');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_points');
            $table->dropIndex('idx_users_phone');
        });

        Schema::table('bars', function (Blueprint $table) {
            $table->dropIndex('idx_bars_active');
        });
    }
};
