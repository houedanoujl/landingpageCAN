<?php

use App\Models\Bar;
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
        Schema::table('bars', function (Blueprint $table) {
            $table->string('dedup_key', 40)->nullable()->after('address');
        });

        // Fusionner les doublons existants avant de poser la contrainte unique
        Bar::mergeDuplicates();

        // Remplir la clé pour tous les PDV restants
        Bar::query()->orderBy('id')->each(function (Bar $bar) {
            $bar->timestamps = false;
            $bar->dedup_key = Bar::makeDedupKey($bar->name, $bar->address);
            $bar->saveQuietly();
        });

        Schema::table('bars', function (Blueprint $table) {
            $table->unique('dedup_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bars', function (Blueprint $table) {
            $table->dropUnique(['dedup_key']);
            $table->dropColumn('dedup_key');
        });
    }
};
