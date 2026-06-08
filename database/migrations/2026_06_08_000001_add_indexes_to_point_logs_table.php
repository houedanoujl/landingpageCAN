<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Index pour accélérer le calcul des points en fin de match.
     * Les requêtes filtrent sur (match_id, source) et (match_id, user_id).
     */
    public function up(): void
    {
        Schema::table('point_logs', function (Blueprint $table) {
            $table->index(['match_id', 'source'], 'point_logs_match_source_idx');
            $table->index(['match_id', 'user_id'], 'point_logs_match_user_idx');
        });
    }

    public function down(): void
    {
        Schema::table('point_logs', function (Blueprint $table) {
            $table->dropIndex('point_logs_match_source_idx');
            $table->dropIndex('point_logs_match_user_idx');
        });
    }
};
