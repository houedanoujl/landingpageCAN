<?php

namespace App\Console\Commands;

use App\Models\ArchivedRanking;
use App\Models\Prediction;
use App\Models\PointLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ArchiveRankings extends Command
{
    /**
     * Archive le classement courant puis remet les compteurs à zéro.
     *
     * Exemple : à la fin de la CAN 2025, avant la Coupe du Monde 2026 :
     *   php artisan rankings:archive "CAN 2025" --force
     */
    protected $signature = 'rankings:archive {label=CAN 2025 : libellé de la compétition à archiver} {--force : ne pas demander de confirmation}';

    protected $description = 'Archive le classement actuel (snapshot) puis réinitialise les points pour repartir à zéro';

    public function handle(): int
    {
        $label = $this->argument('label');
        $now = Carbon::now();

        $users = User::orderBy('points_total', 'desc')
            ->orderBy('name', 'asc')
            ->get();

        $this->info("📊 Classement à archiver sous le libellé : « {$label} »");
        $this->line("   Utilisateurs : {$users->count()}");
        $this->line("   Logs de points : " . PointLog::count());
        $this->line("   Pronostics : " . Prediction::count());
        $this->newLine();

        if ($users->isEmpty()) {
            $this->warn('Aucun utilisateur à archiver. Abandon.');
            return self::SUCCESS;
        }

        if (!$this->option('force') && !$this->confirm("⚠️  Archiver puis RÉINITIALISER tout le classement à zéro ?", false)) {
            $this->warn('❌ Opération annulée.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($users, $label, $now) {
            // 1) Snapshot du classement courant
            $rank = 0;
            foreach ($users as $user) {
                $rank++;
                ArchivedRanking::create([
                    'batch'             => $label,
                    'archived_at'       => $now,
                    'user_id'           => $user->id,
                    'name'              => $user->name,
                    'phone'             => $user->phone,
                    'email'             => $user->email,
                    'points_total'      => $user->points_total ?? 0,
                    'rank'              => $rank,
                    'predictions_count' => Prediction::where('user_id', $user->id)->count(),
                ]);
            }

            // 2) Réinitialisation des compteurs (on repart à zéro)
            User::query()->update(['points_total' => 0]);
            Prediction::query()->update(['points_earned' => 0]);

            // delete() et non truncate() : TRUNCATE fait un commit implicite en
            // MySQL et casserait la transaction ("There is no active transaction").
            Schema::disableForeignKeyConstraints();
            DB::table('point_logs')->delete();
            if (Schema::hasTable('weekly_rankings')) {
                DB::table('weekly_rankings')->delete();
            }
            Schema::enableForeignKeyConstraints();
        });

        $this->newLine();
        $this->info("✅ Classement « {$label} » archivé ({$users->count()} entrées) et compteurs remis à zéro.");
        $this->line('   Consultable dans la table archived_rankings.');

        return self::SUCCESS;
    }
}
