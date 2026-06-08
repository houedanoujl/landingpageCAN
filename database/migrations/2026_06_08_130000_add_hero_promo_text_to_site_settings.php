<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Paragraphe promotionnel affiché sous le titre du hero (page d'accueil).
     * Modifiable depuis l'admin. Null = paragraphe masqué.
     */
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->text('hero_promo_text')->nullable()->after('hero_image_path');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn('hero_promo_text');
        });
    }
};
