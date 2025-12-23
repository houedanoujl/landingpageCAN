<?php

namespace App\Console\Commands;

use App\Models\PointLog;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AuditPointsConsistency extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'points:audit {--fix : Automatically fix inconsistencies}';

    /**
     * The console command description.
     */
    protected $description = 'Audit and optionally fix points inconsistencies between users.points_total and sum of point_logs';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Audit du système d\'attribution des points...');
        $this->newLine();

        $shouldFix = $this->option('fix');
        $inconsistencies = [];
        $duplicates = [];

        // 1. Vérifier la cohérence entre points_total et sum(point_logs)
        $this->info('1️⃣ Vérification de la cohérence points_total vs sum(point_logs)...');
        
        $users = User::all();
        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        foreach ($users as $user) {
            $sumPointLogs = PointLog::where('user_id', $user->id)->sum('points');
            
            if ($user->points_total != $sumPointLogs) {
                $inconsistencies[] = [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'points_total' => $user->points_total,
                    'sum_point_logs' => $sumPointLogs,
                    'difference' => $user->points_total - $sumPointLogs,
                ];
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        if (count($inconsistencies) > 0) {
            $this->warn("⚠️  " . count($inconsistencies) . " utilisateurs avec incohérences détectées:");
            $this->table(
                ['User ID', 'Nom', 'points_total', 'sum(point_logs)', 'Différence'],
                array_map(fn($i) => [$i['user_id'], $i['name'], $i['points_total'], $i['sum_point_logs'], $i['difference']], $inconsistencies)
            );

            if ($shouldFix) {
                $this->info('🔧 Correction des incohérences...');
                foreach ($inconsistencies as $inc) {
                    User::where('id', $inc['user_id'])->update(['points_total' => $inc['sum_point_logs']]);
                    $this->line("  ✅ User #{$inc['user_id']}: {$inc['points_total']} → {$inc['sum_point_logs']}");
                }
            }
        } else {
            $this->info('✅ Tous les utilisateurs ont un points_total cohérent avec leurs point_logs.');
        }

        $this->newLine();

        // 2. Vérifier les doublons de points par match
        $this->info('2️⃣ Vérification des doublons de points par match...');

        $duplicateParticipation = DB::table('point_logs')
            ->select('user_id', 'match_id', DB::raw('COUNT(*) as count'))
            ->where('source', 'prediction_participation')
            ->whereNotNull('match_id')
            ->groupBy('user_id', 'match_id')
            ->having('count', '>', 1)
            ->get();

        $duplicateWinner = DB::table('point_logs')
            ->select('user_id', 'match_id', DB::raw('COUNT(*) as count'))
            ->where('source', 'prediction_winner')
            ->whereNotNull('match_id')
            ->groupBy('user_id', 'match_id')
            ->having('count', '>', 1)
            ->get();

        $duplicateExact = DB::table('point_logs')
            ->select('user_id', 'match_id', DB::raw('COUNT(*) as count'))
            ->where('source', 'prediction_exact')
            ->whereNotNull('match_id')
            ->groupBy('user_id', 'match_id')
            ->having('count', '>', 1)
            ->get();

        $totalDuplicates = $duplicateParticipation->count() + $duplicateWinner->count() + $duplicateExact->count();

        if ($totalDuplicates > 0) {
            $this->warn("⚠️  {$totalDuplicates} cas de doublons détectés:");
            
            if ($duplicateParticipation->count() > 0) {
                $this->error("  - {$duplicateParticipation->count()} doublons de participation");
                foreach ($duplicateParticipation as $dup) {
                    $this->line("    User #{$dup->user_id}, Match #{$dup->match_id}: {$dup->count}x");
                }
            }
            
            if ($duplicateWinner->count() > 0) {
                $this->error("  - {$duplicateWinner->count()} doublons de vainqueur correct");
            }
            
            if ($duplicateExact->count() > 0) {
                $this->error("  - {$duplicateExact->count()} doublons de score exact");
            }

            if ($shouldFix) {
                $this->info('🔧 Suppression des doublons...');
                $this->removeDuplicatePointLogs();
                $this->info('✅ Doublons supprimés. Re-exécutez l\'audit pour vérifier.');
            }
        } else {
            $this->info('✅ Aucun doublon de points par match détecté.');
        }

        $this->newLine();

        // 3. Vérifier les point_logs sans match_id pour les sources liées aux matchs
        $this->info('3️⃣ Vérification des point_logs sans match_id...');

        $orphanedLogs = PointLog::whereIn('source', ['prediction_participation', 'prediction_winner', 'prediction_exact'])
            ->whereNull('match_id')
            ->count();

        if ($orphanedLogs > 0) {
            $this->warn("⚠️  {$orphanedLogs} point_logs de prédiction sans match_id.");
            $this->line("   Ces entrées peuvent causer des doublons si le job est relancé.");
        } else {
            $this->info('✅ Tous les point_logs de prédiction ont un match_id.');
        }

        $this->newLine();

        // 4. Statistiques générales
        $this->info('4️⃣ Statistiques du système de points:');
        
        $stats = [
            ['Métrique', 'Valeur'],
            ['Total utilisateurs', User::count()],
            ['Total point_logs', PointLog::count()],
            ['Points participation', PointLog::where('source', 'prediction_participation')->sum('points')],
            ['Points vainqueur correct', PointLog::where('source', 'prediction_winner')->sum('points')],
            ['Points score exact', PointLog::where('source', 'prediction_exact')->sum('points')],
            ['Points venue bonus', PointLog::where('source', 'venue_visit')->sum('points')],
            ['Points bar visit', PointLog::where('source', 'bar_visit')->sum('points')],
            ['Points login', PointLog::where('source', 'login')->sum('points')],
            ['Total points distribués', PointLog::sum('points')],
            ['Total points_total users', User::sum('points_total')],
        ];

        $this->table(['Métrique', 'Valeur'], array_slice($stats, 1));

        $this->newLine();
        $this->info('✅ Audit terminé.');

        return count($inconsistencies) > 0 || $totalDuplicates > 0 ? 1 : 0;
    }

    /**
     * Remove duplicate point_logs, keeping only the first one
     */
    private function removeDuplicatePointLogs(): void
    {
        $sources = ['prediction_participation', 'prediction_winner', 'prediction_exact'];

        foreach ($sources as $source) {
            $duplicates = DB::table('point_logs')
                ->select('user_id', 'match_id', DB::raw('MIN(id) as keep_id'))
                ->where('source', $source)
                ->whereNotNull('match_id')
                ->groupBy('user_id', 'match_id')
                ->having(DB::raw('COUNT(*)'), '>', 1)
                ->get();

            foreach ($duplicates as $dup) {
                $deleted = PointLog::where('user_id', $dup->user_id)
                    ->where('match_id', $dup->match_id)
                    ->where('source', $source)
                    ->where('id', '!=', $dup->keep_id)
                    ->delete();

                if ($deleted > 0) {
                    $this->line("  Supprimé {$deleted} doublon(s) pour {$source}, User #{$dup->user_id}, Match #{$dup->match_id}");
                }
            }
        }
    }
}
