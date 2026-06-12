<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Bar extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'zone',
        'latitude',
        'longitude',
        'is_active',
        'type_pdv',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        // La clé de déduplication est toujours dérivée du nom + adresse,
        // jamais fournie de l'extérieur.
        static::saving(function (Bar $bar) {
            $bar->dedup_key = self::makeDedupKey($bar->name, $bar->address);
        });
    }

    /**
     * Clé de déduplication : nom + adresse normalisés (minuscules, sans
     * accents, ponctuation et espaces multiples réduits), puis hashés.
     * "Bar  Le Phare, Dakar" et "bar le phare Dakar" donnent la même clé.
     */
    public static function makeDedupKey(?string $name, ?string $address): string
    {
        $normalize = function (?string $value): string {
            $value = Str::ascii(mb_strtolower(trim((string) $value)));
            return trim(preg_replace('/[^a-z0-9]+/', ' ', $value));
        };

        return sha1($normalize($name) . '|' . $normalize($address));
    }

    /**
     * Fusionne les points de vente en double (même clé de déduplication).
     * Conserve le plus ancien, lui rattache animations / point_logs /
     * animation_media des copies, complète ses champs vides, puis supprime
     * les copies.
     *
     * @return array{duplicate_groups:int,bars_merged:int,animations_moved:int,animations_dropped:int,details:string[]}
     */
    public static function mergeDuplicates(bool $dryRun = false): array
    {
        $stats = [
            'duplicate_groups' => 0,
            'bars_merged' => 0,
            'animations_moved' => 0,
            'animations_dropped' => 0,
            'details' => [],
        ];

        $groups = self::orderBy('id')->get()
            ->groupBy(fn (Bar $bar) => self::makeDedupKey($bar->name, $bar->address))
            ->filter(fn ($group) => $group->count() > 1);

        $stats['duplicate_groups'] = $groups->count();

        foreach ($groups as $group) {
            /** @var Bar $keep */
            $keep = $group->shift();

            foreach ($group as $dupe) {
                $stats['details'][] = "#{$dupe->id} \"{$dupe->name}\" ({$dupe->address}) → fusionné dans #{$keep->id}";
                $stats['bars_merged']++;

                if ($dryRun) {
                    continue;
                }

                DB::transaction(function () use ($keep, $dupe, &$stats) {
                    // Animations : déplacer, sauf si le PDV conservé couvre
                    // déjà le même match (contrainte unique bar_id + match_id).
                    foreach (Animation::where('bar_id', $dupe->id)->get() as $animation) {
                        $conflict = Animation::where('bar_id', $keep->id)
                            ->where('match_id', $animation->match_id)
                            ->exists();

                        if ($conflict) {
                            $animation->delete();
                            $stats['animations_dropped']++;
                        } else {
                            $animation->update(['bar_id' => $keep->id]);
                            $stats['animations_moved']++;
                        }
                    }

                    DB::table('point_logs')->where('bar_id', $dupe->id)->update(['bar_id' => $keep->id]);
                    DB::table('animation_media')->where('bar_id', $dupe->id)->update(['bar_id' => $keep->id]);

                    // Compléter les champs vides du PDV conservé avec ceux de la copie
                    $fill = [];
                    foreach (['latitude', 'longitude', 'type_pdv', 'zone'] as $field) {
                        if ($keep->{$field} === null && $dupe->{$field} !== null) {
                            $fill[$field] = $dupe->{$field};
                        }
                    }
                    if ($fill) {
                        $keep->update($fill);
                    }

                    $dupe->delete();
                });
            }
        }

        return $stats;
    }

    /**
     * Types de PDV disponibles
     */
    public static function getTypePdvOptions()
    {
        return [
            'dakar' => 'Points de vente Dakar',
            'regions' => 'Points de vente Régions',
            'chr' => 'Cafés-Hôtel-Restaurants (CHR)',
            'fanzone' => 'Fanzones',
            'fanzone_public' => 'Fanzone tout public',
            'fanzone_hotel' => 'Fanzone hôtel',
        ];
    }

    /**
     * Obtenir le nom lisible du type PDV
     */
    public function getTypePdvNameAttribute()
    {
        $types = self::getTypePdvOptions();
        return $types[$this->type_pdv] ?? $this->type_pdv;
    }

    /**
     * Get the animations for this bar.
     */
    public function animations()
    {
        return $this->hasMany(Animation::class);
    }
}
