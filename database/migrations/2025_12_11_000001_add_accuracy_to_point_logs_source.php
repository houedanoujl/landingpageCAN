<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update enum to include all point sources
        DB::statement("ALTER TABLE point_logs MODIFY COLUMN source ENUM('login', 'prediction', 'accuracy', 'venue_visit', 'bar_visit') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE point_logs MODIFY COLUMN source ENUM('login', 'prediction', 'bar_visit') NOT NULL");
    }
};
