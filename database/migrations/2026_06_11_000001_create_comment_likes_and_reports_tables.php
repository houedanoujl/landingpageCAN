<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Likes sur les commentaires (mur des matchs ET commentaires de pronostics)
        Schema::create('comment_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->morphs('comment'); // comment_type + comment_id
            $table->timestamps();

            $table->unique(['user_id', 'comment_type', 'comment_id'], 'comment_likes_unique');
        });

        // Signalements : source principale pour découvrir les termes qui
        // passent entre les mailles de la liste noire.
        Schema::create('comment_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->morphs('comment');
            $table->string('reason', 255)->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'comment_type', 'comment_id'], 'comment_reports_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comment_reports');
        Schema::dropIfExists('comment_likes');
    }
};
