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
        // First, modify the column to accept both old and new values temporarily
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE predictions MODIFY COLUMN predicted_winner VARCHAR(20)");
        }

        // Update existing values
        DB::statement("UPDATE predictions SET predicted_winner = 'home' WHERE predicted_winner = 'team_a'");
        DB::statement("UPDATE predictions SET predicted_winner = 'away' WHERE predicted_winner = 'team_b'");

        // Now set the final enum constraint
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE predictions MODIFY COLUMN predicted_winner ENUM('home', 'away', 'draw') NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First, change to VARCHAR to allow updates
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE predictions MODIFY COLUMN predicted_winner VARCHAR(20)");
        }

        // Update values back
        DB::statement("UPDATE predictions SET predicted_winner = 'team_a' WHERE predicted_winner = 'home'");
        DB::statement("UPDATE predictions SET predicted_winner = 'team_b' WHERE predicted_winner = 'away'");

        // Revert the column enum
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE predictions MODIFY COLUMN predicted_winner ENUM('team_a', 'team_b', 'draw') NOT NULL");
        }
    }
};
