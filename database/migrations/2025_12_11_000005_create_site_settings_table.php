<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('site_name')->default('SOBOA CAN 2025');
            $table->string('primary_color')->default('#003399');
            $table->string('secondary_color')->default('#FF6600');
            $table->string('logo_path')->nullable();
            $table->string('hero_image_path')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        DB::table('site_settings')->insert([
            'site_name' => 'SOBOA CAN 2025',
            'primary_color' => '#003399',
            'secondary_color' => '#FF6600',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
