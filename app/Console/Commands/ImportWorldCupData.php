<?php

namespace App\Console\Commands;

use App\Models\MatchGame;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ImportWorldCupData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'matches:import-worldcup
        {--fresh : Vider les tables matches et teams avant import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importe les équipes et les matchs de la Coupe du Monde depuis public/db/*.json';

    /**
     * Traduction des noms d'équipes (anglais → français).
     *
     * @var array<string, string>
     */
    private array $frenchNames = [
        'Mexico' => 'Mexique',
        'South Africa' => 'Afrique du Sud',
        'South Korea' => 'Corée du Sud',
        'Czech Republic' => 'République tchèque',
        'Canada' => 'Canada',
        'Bosnia & Herzegovina' => 'Bosnie-Herzégovine',
        'Qatar' => 'Qatar',
        'Switzerland' => 'Suisse',
        'Brazil' => 'Brésil',
        'Morocco' => 'Maroc',
        'Haiti' => 'Haïti',
        'Scotland' => 'Écosse',
        'USA' => 'États-Unis',
        'Paraguay' => 'Paraguay',
        'Australia' => 'Australie',
        'Turkey' => 'Turquie',
        'Germany' => 'Allemagne',
        'Curaçao' => 'Curaçao',
        'Ivory Coast' => 'Côte d\'Ivoire',
        'Ecuador' => 'Équateur',
        'Netherlands' => 'Pays-Bas',
        'Japan' => 'Japon',
        'Sweden' => 'Suède',
        'Tunisia' => 'Tunisie',
        'Belgium' => 'Belgique',
        'Egypt' => 'Égypte',
        'Iran' => 'Iran',
        'New Zealand' => 'Nouvelle-Zélande',
        'Spain' => 'Espagne',
        'Cape Verde' => 'Cap-Vert',
        'Saudi Arabia' => 'Arabie saoudite',
        'Uruguay' => 'Uruguay',
        'France' => 'France',
        'Senegal' => 'Sénégal',
        'Iraq' => 'Irak',
        'Norway' => 'Norvège',
        'Argentina' => 'Argentine',
        'Algeria' => 'Algérie',
        'Austria' => 'Autriche',
        'Jordan' => 'Jordanie',
        'Portugal' => 'Portugal',
        'DR Congo' => 'RD Congo',
        'Uzbekistan' => 'Ouzbékistan',
        'Colombia' => 'Colombie',
        'England' => 'Angleterre',
        'Croatia' => 'Croatie',
        'Ghana' => 'Ghana',
        'Panama' => 'Panama',
    ];

    public function handle(): int
    {
        $teamsFile = public_path('db/worldcup.teams.json');
        $matchesFile = public_path('db/worldcup.json');

        if (!is_file($teamsFile) || !is_file($matchesFile)) {
            $this->error('Fichiers JSON introuvables dans public/db/.');
            return self::FAILURE;
        }

        if ($this->option('fresh')) {
            $this->warn('Suppression des matchs et équipes existants...');
            MatchGame::query()->delete();
            Team::query()->delete();
        }

        $teamsImported = $this->importTeams($teamsFile);
        $this->info("Équipes importées/mises à jour : {$teamsImported}");

        [$created, $updated, $skipped] = $this->importMatches($matchesFile);
        $this->info("Matchs créés : {$created}, mis à jour : {$updated}, ignorés (équipes inconnues) : {$skipped}");

        return self::SUCCESS;
    }

    /**
     * Importe les équipes en dérivant le code ISO à partir de l'emoji drapeau.
     */
    private function importTeams(string $file): int
    {
        $teams = json_decode((string) file_get_contents($file), true) ?? [];
        $count = 0;

        foreach ($teams as $team) {
            $name = trim($team['name'] ?? '');
            if ($name === '') {
                continue;
            }

            Team::updateOrCreate(
                ['name' => $this->toFrench($name)],
                [
                    'iso_code' => $this->isoFromFlagEmoji($team['flag_icon'] ?? null),
                    'group' => $this->normaliseGroup($team['group'] ?? null),
                ]
            );
            $count++;
        }

        return $count;
    }

    /**
     * Importe les matchs en liant les équipes par leur nom.
     *
     * @return array{0:int,1:int,2:int} [created, updated, skipped]
     */
    private function importMatches(string $file): array
    {
        $data = json_decode((string) file_get_contents($file), true) ?? [];
        $matches = $data['matches'] ?? [];

        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($matches as $match) {
            $team1Name = trim($match['team1'] ?? '');
            $team2Name = trim($match['team2'] ?? '');

            $team1 = $this->findTeam($this->toFrench($team1Name));
            $team2 = $this->findTeam($this->toFrench($team2Name));

            // On n'importe que les matchs dont les deux équipes existent
            // (les matchs à élimination directe utilisent des libellés génériques).
            if (!$team1 || !$team2) {
                $skipped++;
                continue;
            }

            $matchDate = $this->parseMatchDate($match['date'] ?? null, $match['time'] ?? null);

            $existing = MatchGame::where('home_team_id', $team1->id)
                ->where('away_team_id', $team2->id)
                ->whereDate('match_date', $matchDate->toDateString())
                ->first();

            $attributes = [
                'home_team_id' => $team1->id,
                'away_team_id' => $team2->id,
                'team_a' => $team1->name,
                'team_b' => $team2->name,
                'match_date' => $matchDate,
                'stadium' => trim($match['ground'] ?? 'À définir') ?: 'À définir',
                'group_name' => $this->normaliseGroupName($match['group'] ?? null),
                'status' => 'scheduled',
            ];

            if ($existing) {
                $existing->update($attributes);
                $updated++;
            } else {
                MatchGame::create($attributes);
                $created++;
            }
        }

        return [$created, $updated, $skipped];
    }

    private function findTeam(string $name): ?Team
    {
        if ($name === '') {
            return null;
        }

        return Team::where('name', $name)->first()
            ?? Team::where('name', 'like', $name)->first();
    }

    /**
     * Traduit un nom d'équipe anglais en français (sinon renvoie l'original).
     */
    private function toFrench(string $english): string
    {
        return $this->frenchNames[$english] ?? $english;
    }

    /**
     * Combine la date et l'heure (en ignorant l'offset textuel) en datetime.
     */
    private function parseMatchDate(?string $date, ?string $time): Carbon
    {
        $date = $date ?: now()->toDateString();

        // "13:00 UTC-6" -> "13:00"
        $clock = '00:00';
        if ($time && preg_match('/(\d{1,2}:\d{2})/', $time, $m)) {
            $clock = $m[1];
        }

        return Carbon::parse("{$date} {$clock}");
    }

    /**
     * Dérive un code ISO alpha-2 (minuscule) depuis un emoji drapeau régional.
     */
    private function isoFromFlagEmoji(?string $flag): ?string
    {
        if (!$flag) {
            return null;
        }

        $codepoints = [];
        foreach (preg_split('//u', $flag, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $char) {
            $cp = mb_ord($char, 'UTF-8');
            if ($cp !== false && $cp >= 0x1F1E6 && $cp <= 0x1F1FF) {
                $codepoints[] = chr(ord('a') + ($cp - 0x1F1E6));
            }
        }

        if (count($codepoints) === 2) {
            return implode('', $codepoints);
        }

        return null;
    }

    private function normaliseGroup(?string $group): ?string
    {
        if (!$group) {
            return null;
        }

        // "A" ou "Group A" -> "A"
        $group = strtoupper(trim(str_ireplace('group', '', $group)));

        return preg_match('/^[A-L]$/', $group) ? $group : null;
    }

    private function normaliseGroupName(?string $group): ?string
    {
        if (!$group) {
            return null;
        }

        // Les vues affichent déjà "Groupe X" : on ne stocke que la lettre.
        // "Group A" -> "A"
        $group = strtoupper(trim(str_ireplace('group', '', $group)));

        return preg_match('/^[A-L]$/', $group) ? $group : null;
    }
}
