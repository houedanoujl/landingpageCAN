<?php

namespace App\Services;

class ContentModerationService
{
    /**
     * Le texte contient-il un terme de la liste noire ?
     * Insensible à la casse, aux accents, aux espaces et au leetspeak.
     */
    public function isClean(string $text): bool
    {
        return $this->firstBanned($text) === null;
    }

    /**
     * Retourne le premier terme banni détecté, ou null.
     */
    public function firstBanned(string $text): ?string
    {
        $haystack = $this->normalize($text);

        foreach ((array) config('moderation.banned', []) as $word) {
            $needle = $this->normalize($word);
            if ($needle !== '' && str_contains($haystack, $needle)) {
                return $word;
            }
        }

        return null;
    }

    /**
     * Normalise une chaîne pour la comparaison :
     * minuscules, sans accents, leetspeak décodé, uniquement [a-z0-9].
     */
    public function normalize(string $value): string
    {
        $value = mb_strtolower($value, 'UTF-8');

        // Supprimer les accents (é -> e, à -> a ...)
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if ($ascii !== false) {
            $value = $ascii;
        }

        // Décoder les substitutions courantes (leetspeak)
        $value = strtr($value, [
            '@' => 'a', '4' => 'a',
            '8' => 'b',
            '3' => 'e',
            '1' => 'i', '!' => 'i', '|' => 'i',
            '0' => 'o',
            '5' => 's', '$' => 's',
            '7' => 't',
        ]);

        // Ne garder que les lettres et chiffres (neutralise espaces, points, tirets)
        return preg_replace('/[^a-z0-9]/', '', $value) ?? '';
    }
}
