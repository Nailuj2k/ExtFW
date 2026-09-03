<?php

class Inflect{

    /** Pluraliza en español con soporte multibyte. */
    public static function pluralize(string $s, int $n = 1): string {
        if ($n === 1 || $s === '') return $s;

        $last   = mb_substr($s, -1, null, 'UTF-8');
        $prev   = mb_substr($s, -2, 1, 'UTF-8');
        $upper  = (mb_strtoupper($s, 'UTF-8') === $s);
        $isV    = self::isVowel($last);
        $prevT  = self::isTildeVowel($prev);

        // Palabras que acaban en s/x: invariables salvo agudas (acento en última) o monosílabos
        if (self::endsWith($s, ['s', 'S', 'x', 'X'])) {
            $accentPos   = self::lastAccentPos($s);
            $len         = mb_strlen($s, 'UTF-8');
            $accentLast  = $accentPos !== null && $accentPos >= $len - 2; // acento en última sílaba (aprox)
            $monosil     = self::isMonosyllable($s);

            if ($accentLast || $monosil) {
                $base = $accentLast ? self::stripLastAccent($s) : $s;
                return $base . ($upper ? 'ES' : 'es');
            }
            return $s;
        }

        // z -> ces
        if ($last === 'z' || $last === 'Z') {
            return mb_substr($s, 0, -1, 'UTF-8') . ($upper ? 'CES' : 'ces');
        }

        // Vocal final: í -> es, ú -> s (preferente), ü -> es, resto s
        if ($isV) {
            if ($last === 'ú' || $last === 'Ú') {
                return $s . ($upper ? 'S' : 's');
            }
            if ($last === 'í' || $last === 'Í' || $last === 'ü' || $last === 'Ü') {
                return $s . ($upper ? 'ES' : 'es');
            }
            return $s . ($upper ? 'S' : 's');
        }

        // Consonante: si es aguda, quita acento antes de añadir "es"
        $accentPos = self::lastAccentPos($s);
        if ($accentPos !== null) {
            $len = mb_strlen($s, 'UTF-8');
            if ($accentPos >= $len - 2) {
                $s = self::stripLastAccent($s);
            }
        }
        return $s . ($upper ? 'ES' : 'es');
    }

    /** Singulariza formas simples generadas por pluralize. */
    public static function singularize(string $s): string {
        if ($s === '') return $s;

        // Invariables en -sis / -is (crisis, análisis, tesis, etc.)
        if (self::endsWithInsensitive($s, 'sis') || self::endsWithInsensitive($s, 'is')) {
            return $s;
        }

        // -íes -> -í (jabalíes, rubíes)
        if (self::endsWithInsensitive($s, 'íes')) {
            return mb_substr($s, 0, -2, 'UTF-8');
        }

        // ces -> z
        if (self::endsWithInsensitive($s, 'ces')) {
            $stem  = mb_substr($s, 0, -3, 'UTF-8');
            $upper = (mb_strtoupper($s, 'UTF-8') === $s);
            return $stem . ($upper ? 'Z' : 'z');
        }

        // consonante + es -> consonante
        if (self::endsWithInsensitive($s, 'es')) {
            $stem = mb_substr($s, 0, -2, 'UTF-8');
            $last = mb_substr($stem, -1, null, 'UTF-8');
            if (!self::isVowel($last)) {
                // Recupera acento en agudas tipo camión/avión (plural camiones/aviones)
                if (self::endsWithInsensitive($stem, 'on')) {
                    $stem = self::accentLastPlainVowel($stem);
                }
                return $stem;
            }
        }

        // vocal + s -> vocal
        if (self::endsWithInsensitive($s, 's')) {
            $stem = mb_substr($s, 0, -1, 'UTF-8');
            $last = mb_substr($stem, -1, null, 'UTF-8');
            if (self::isVowel($last)) return $stem;
        }

        return $s;
    }

    public static function pluralize_if(int $count, string $string, ?string $zero_string = null): string {
        return $count > 0
            ? $count . ' ' . self::pluralize($string, $count)
            : ($zero_string ?? $string);
    }

    private static function isVowel(string $c): bool {
        return mb_stripos('aeiouáéíóúü', $c, 0, 'UTF-8') !== false;
    }

    private static function isIUWithAccent(string $c): bool {
        return mb_stripos('íúü', $c, 0, 'UTF-8') !== false || mb_stripos('ÍÚÜ', $c, 0, 'UTF-8') !== false;
    }

    private static function isTildeVowel(string $c): bool {
        return mb_stripos('áéíóú', $c, 0, 'UTF-8') !== false;
    }

    private static function endsWith(string $s, array $suffixes): bool {
        foreach ($suffixes as $suf) {
            if (mb_substr($s, -mb_strlen($suf, 'UTF-8'), null, 'UTF-8') === $suf) return true;
        }
        return false;
    }

    private static function endsWithInsensitive(string $s, string $suffix): bool {
        $len = mb_strlen($suffix, 'UTF-8');
        return mb_strtolower(mb_substr($s, -$len, null, 'UTF-8'), 'UTF-8') === mb_strtolower($suffix, 'UTF-8');
    }

    /** Heurística simple: 1 grupo vocálico -> monosílabo. */
    private static function isMonosyllable(string $s): bool {
        if ($s === '') return true;
        preg_match_all('/[aeiouáéíóúü]+/iu', $s, $m);
        return count($m[0]) <= 1;
    }

    /** Índice (0-based) del último acento agudo; null si no hay. */
    private static function lastAccentPos(string $s): ?int {
        $len = mb_strlen($s, 'UTF-8');
        $pos = null;
        for ($i = 0; $i < $len; $i++) {
            $ch = mb_substr($s, $i, 1, 'UTF-8');
            if (mb_stripos('áéíóúÁÉÍÓÚ', $ch, 0, 'UTF-8') !== false) {
                $pos = $i;
            }
        }
        return $pos;
    }

    /** Sustituye el último acento agudo por su vocal sin acento. */
    private static function stripLastAccent(string $s): string {
        $map = [
            'á' => 'a','é' => 'e','í' => 'i','ó' => 'o','ú' => 'u',
            'Á' => 'A','É' => 'E','Í' => 'I','Ó' => 'O','Ú' => 'U',
        ];
        $len = mb_strlen($s, 'UTF-8');
        for ($i = $len - 1; $i >= 0; $i--) {
            $ch = mb_substr($s, $i, 1, 'UTF-8');
            if (isset($map[$ch])) {
                return mb_substr($s, 0, $i, 'UTF-8') . $map[$ch] . mb_substr($s, $i + 1, null, 'UTF-8');
            }
        }
        return $s;
    }

    /** Acentúa la última vocal no acentuada (a/e/i/o/u -> á/é/í/ó/ú). */
    private static function accentLastPlainVowel(string $s): string {
        $map = ['a'=>'á','e'=>'é','i'=>'í','o'=>'ó','u'=>'ú',
                'A'=>'Á','E'=>'É','I'=>'Í','O'=>'Ó','U'=>'Ú'];
        $len = mb_strlen($s, 'UTF-8');
        for ($i = $len - 1; $i >= 0; $i--) {
            $ch = mb_substr($s, $i, 1, 'UTF-8');
            if (isset($map[$ch])) {
                return mb_substr($s, 0, $i, 'UTF-8') . $map[$ch] . mb_substr($s, $i + 1, null, 'UTF-8');
            }
        }
        return $s;
    }
}