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
        Schema::table('matches', function (Blueprint $table) {
            $table->string('group_name')->nullable()->after('stadium');
            $table->string('phase')->default('group_stage')->after('group_name'); // group_stage, round_of_16, quarter_final, semi_final, third_place, final
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->dropColumn(['group_name', 'phase']);
        });
    }
};
