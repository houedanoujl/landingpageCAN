<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Table d'archive des classements.
     *
     * À chaque changement de compétition (ex: fin de la CAN 2025 avant la
     * Coupe du Monde 2026), on fige le classement courant ici puis on
     * remet les compteurs à zéro pour repartir à neuf.
     * On stocke une copie figée (name/email/points) afin que l'archive
     * survive même si l'utilisateur est supprimé plus tard.
     */
    public function up(): void
    {
        Schema::create('archived_rankings', function (Blueprint $table) {
            $table->id();
            $table->string('batch');            // Libellé de la compétition archivée (ex: "CAN 2025")
            $table->timestamp('archived_at');   // Date de l'archivage
            $table->unsignedBigInteger('user_id')->nullable(); // Référence souple (pas de FK)
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->integer('points_total')->default(0);
            $table->integer('rank')->nullable();
            $table->integer('predictions_count')->default(0);
            $table->timestamps();

            $table->index(['batch', 'rank']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('archived_rankings');
    }
};
