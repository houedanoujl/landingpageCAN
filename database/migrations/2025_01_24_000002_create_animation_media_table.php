<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table pour stocker les médias des animations CAN
     * - Highlights (photos)
     * - Vidéos
     */
    public function up(): void
    {
        Schema::create('animation_media', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['photo', 'video'])->default('photo');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path'); // Chemin du fichier (image ou vidéo)
            $table->string('thumbnail_path')->nullable(); // Miniature pour les vidéos
            $table->string('video_url')->nullable(); // URL externe (YouTube, etc.)
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('bar_id')->nullable()->constrained()->onDelete('set null'); // Lieu associé (optionnel)
            $table->timestamps();

            $table->index(['type', 'is_active', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('animation_media');
    }
};
