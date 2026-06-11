<?php

namespace App\Services;

class ContentModerationService
{
    public const LEVEL_CLEAN = 'clean';
    public const LEVEL_REVIEW = 'review';
    public const LEVEL_BLOCK = 'block';

    /**
     * Niveau de modération d'un texte :
     *  - 'block'  : terme interdit détecté -> publication refusée.
     *  - 'review' : terme ambigu détecté   -> mise en attente de modération.
     *  - 'clean'  : rien détecté.
     */
    public function check(string $text): string
    {
        if ($this->matchesList($text, (array) config('moderation.banned', []))
            || $this->matchesPatterns($text)) {
            return self::LEVEL_BLOCK;
        }

        if ($this->matchesList($text, (array) config('moderation.review', []))) {
            return self::LEVEL_REVIEW;
        }

        // Deuxième couche : Tisane.ai (si TISANE_API_KEY configurée).
        // Attrape ce que la liste noire locale ne connaît pas encore.
        // Fail-open : API absente/en panne => on s'en tient au résultat local.
        $tisane = app(TisaneModerationService::class)->check($text);
        if ($tisane === self::LEVEL_BLOCK || $tisane === self::LEVEL_REVIEW) {
            return $tisane;
        }

        return self::LEVEL_CLEAN;
    }

    /**
     * Le texte contient-il un terme de la liste noire (niveau block) ?
     * Conservé pour compatibilité avec les appels existants.
     */
    public function isClean(string $text): bool
    {
        return $this->check($text) !== self::LEVEL_BLOCK;
    }

    /**
     * Le texte doit-il passer en file de modération humaine ?
     */
    public function needsReview(string $text): bool
    {
        return $this->check($text) === self::LEVEL_REVIEW;
    }

    /**
     * Compare le texte à une liste de termes.
     *
     * - Terme en écriture arabe : recherche de sous-chaîne sur le texte Unicode
     *   débarrassé de tout ce qui n'est pas lettre/chiffre.
     * - Terme multi-mots ("fils de pute") : recherche de sous-chaîne sur le
     *   texte normalisé SANS espaces (attrape "filsdepute", "f.i.l.s de pute").
     * - Terme simple ("pute") : correspondance de mot entier sur le texte
     *   normalisé AVEC espaces, pour ne pas bloquer "disputé", "député",
     *   "montre" (contient "ntr"), etc.
     */
    private function matchesList(string $text, array $terms): bool
    {
        $stripped = null;       // normalisé, sans espaces
        $words = null;          // normalisé, mots séparés par espaces
        $unicodeStripped = null; // unicode, sans séparateurs (pour l'arabe)

        foreach ($terms as $term) {
            $term = (string) $term;
            if ($term === '') {
                continue;
            }

            if (preg_match('/\p{Arabic}/u', $term)) {
                $unicodeStripped ??= $this->normalizeUnicode($text);
                $needle = $this->normalizeUnicode($term);
                if ($needle !== '' && mb_strpos($unicodeStripped, $needle) !== false) {
                    return true;
                }
                continue;
            }

            if (preg_match('/\s/', trim($term))) {
                $stripped ??= $this->normalize($text);
                $needle = $this->normalize($term);
                if ($needle !== '' && str_contains($stripped, $needle)) {
                    return true;
                }
                continue;
            }

            $words ??= $this->normalizeWords($text);
            $needle = $this->normalize($term);
            if ($needle !== '' && preg_match('/\b' . preg_quote($needle, '/') . '\b/', $words)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Applique les regex de config('moderation.patterns') sur le texte en
     * minuscules, accents supprimés (espaces conservés).
     */
    private function matchesPatterns(string $text): bool
    {
        $soft = $this->softNormalize($text);

        foreach ((array) config('moderation.patterns', []) as $pattern) {
            if (@preg_match($pattern, $soft) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retourne le premier terme banni (niveau block) détecté, ou null.
     * Conservé pour compatibilité.
     */
    public function firstBanned(string $text): ?string
    {
        foreach ((array) config('moderation.banned', []) as $word) {
            if ($this->matchesList($text, [$word])) {
                return $word;
            }
        }

        return null;
    }

    /**
     * Normalise une chaîne pour la comparaison :
     * minuscules, sans accents, leetspeak décodé, répétitions écrasées
     * (puuute -> pute), uniquement [a-z0-9].
     */
    public function normalize(string $value): string
    {
        return preg_replace('/[^a-z0-9]/', '', $this->normalizeWords($value)) ?? '';
    }

    /**
     * Comme normalize() mais conserve les séparations de mots :
     * tout caractère non alphanumérique devient une espace simple.
     */
    public function normalizeWords(string $value): string
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
            '9' => 'g',
        ]);

        // Écraser les répétitions excessives (puuute -> pute)
        $value = preg_replace('/([a-z0-9])\1{2,}/', '$1', $value) ?? $value;

        // Séparateurs -> espace simple
        $value = preg_replace('/[^a-z0-9]+/', ' ', $value) ?? '';
        $value = trim($value);

        // Recoller les lettres isolées (évasion "p.u.t.e" -> "p u t e" -> "pute")
        $value = preg_replace_callback(
            '/\b\w( \w)+\b/',
            fn ($m) => str_replace(' ', '', $m[0]),
            $value
        ) ?? $value;

        return $value;
    }

    /**
     * Normalisation légère pour les regex : minuscules + accents supprimés,
     * le reste (espaces, chiffres leetspeak, ponctuation) est conservé.
     */
    private function softNormalize(string $value): string
    {
        $value = mb_strtolower($value, 'UTF-8');

        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);

        return $ascii !== false ? $ascii : $value;
    }

    /**
     * Normalisation Unicode (écriture arabe) : minuscules, suppression de tout
     * ce qui n'est ni lettre ni chiffre (espaces, ponctuation, tatweel...).
     */
    private function normalizeUnicode(string $value): string
    {
        $value = mb_strtolower($value, 'UTF-8');

        return preg_replace('/[^\p{L}\p{N}]+/u', '', $value) ?? '';
    }
}
