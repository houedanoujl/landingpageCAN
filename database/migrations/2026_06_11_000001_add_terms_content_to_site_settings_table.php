<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Contenu éditable des Conditions Générales d'Utilisation.
     * NULL = la version statique de resources/views/terms.blade.php est affichée.
     */
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->longText('terms_content')->nullable()->after('hero_promo_text');
            $table->timestamp('terms_updated_at')->nullable()->after('terms_content');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['terms_content', 'terms_updated_at']);
        });
    }
};
