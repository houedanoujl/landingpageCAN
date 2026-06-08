<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            // football-data.org match ID (or any other external provider). Nullable
            // so existing matches keep working; admin fills it per match to enable
            // auto-sync. Indexed for fast lookup by sync command.
            $table->string('external_id')->nullable()->unique()->after('id');
            $table->timestamp('last_synced_at')->nullable()->after('external_id');
        });
    }

    public function down(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->dropUnique(['external_id']);
            $table->dropColumn(['external_id', 'last_synced_at']);
        });
    }
};
