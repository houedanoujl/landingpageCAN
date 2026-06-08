<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Mode test : quand activé, autorise l'inscription/connexion avec des
     * numéros de Côte d'Ivoire (+225) en plus du Sénégal (+221).
     * Permet de tester l'envoi de SMS sans numéro sénégalais.
     */
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->boolean('test_mode')->default(false)->after('geofencing_radius');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn('test_mode');
        });
    }
};
