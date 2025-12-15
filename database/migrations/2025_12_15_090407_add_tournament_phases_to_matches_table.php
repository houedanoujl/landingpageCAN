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
            // Phase du tournoi (poules, 1/8, 1/4, 1/2, finale, 3e place)
            if (!Schema::hasColumn('matches', 'phase')) {
                $table->enum('phase', [
                    'group_stage',      // Phase de poules
                    'round_of_16',      // 1/8e de finale
                    'quarter_final',    // 1/4 de finale
                    'semi_final',       // 1/2 finale (demi-finale)
                    'third_place',      // Match pour la 3e place
                    'final'             // Finale
                ])->default('group_stage')->after('group_name');
            }

            // Numéro du match dans la phase (ex: Match 1, Match 2, etc.)
            if (!Schema::hasColumn('matches', 'match_number')) {
                $table->integer('match_number')->nullable()->after('phase');
            }

            // Position dans le bracket (pour générer l'arbre du tournoi)
            if (!Schema::hasColumn('matches', 'bracket_position')) {
                $table->integer('bracket_position')->nullable()->after('match_number');
            }

            // Ordre d'affichage
            if (!Schema::hasColumn('matches', 'display_order')) {
                $table->integer('display_order')->default(0)->after('bracket_position');
            }

            // Match parent 1 (le gagnant vient de ce match)
            if (!Schema::hasColumn('matches', 'parent_match_1_id')) {
                $table->foreignId('parent_match_1_id')->nullable()->after('display_order')
                    ->constrained('matches')->nullOnDelete();
            }

            // Match parent 2 (le gagnant vient de ce match)
            if (!Schema::hasColumn('matches', 'parent_match_2_id')) {
                $table->foreignId('parent_match_2_id')->nullable()->after('parent_match_1_id')
                    ->constrained('matches')->nullOnDelete();
            }

            // Position du gagnant dans le parent (home ou away)
            if (!Schema::hasColumn('matches', 'winner_goes_to')) {
                $table->enum('winner_goes_to', ['home', 'away'])->nullable()->after('parent_match_2_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->dropForeign(['parent_match_1_id']);
            $table->dropForeign(['parent_match_2_id']);
            $table->dropColumn([
                'phase',
                'match_number',
                'bracket_position',
                'display_order',
                'parent_match_1_id',
                'parent_match_2_id',
                'winner_goes_to'
            ]);
        });
    }
};
