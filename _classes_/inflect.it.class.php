<?php

class Inflect{
    public static function pluralize(string $s, int $n = 1): string {
        if ($n === 1 || $s === '') return $s;
        $last = mb_substr($s, -1, null, 'UTF-8');
        $last2= mb_substr($s, -2, null, 'UTF-8');

        // Invariables: termina en consonante o vocal acentuada
        if (preg_match('/[bcdfghjklmnpqrstvwxyz]$/iu', $s) || preg_match('/[àèéìíòóùú]$/u', $s)) return $s;

        // -cia / -gia (vocal antes -> cie/gie; consonante antes -> ce/ge)
        if (preg_match('/[aeiou]cia$/iu', $s)) return mb_substr($s, 0, -3, 'UTF-8') . 'e';
        if (preg_match('/[aeiou]gia$/iu', $s)) return mb_substr($s, 0, -3, 'UTF-8') . 'e';
        if (preg_match('/cia$/iu', $s)) return mb_substr($s, 0, -2, 'UTF-8'); // ce
        if (preg_match('/gia$/iu', $s)) return mb_substr($s, 0, -2, 'UTF-8'); // ge

        // -ca / -ga -> che / ghe
        if (preg_match('/ca$/iu', $s)) return mb_substr($s, 0, -2, 'UTF-8') . 'che';
        if (preg_match('/ga$/iu', $s)) return mb_substr($s, 0, -2, 'UTF-8') . 'ghe';

        // -co / -go (simplificado): -> chi / ghi
        if (preg_match('/co$/iu', $s)) return mb_substr($s, 0, -1, 'UTF-8') . 'hi';
        if (preg_match('/go$/iu', $s)) return mb_substr($s, 0, -1, 'UTF-8') . 'hi';

        // -o -> -i
        if ($last === 'o' || $last === 'O') return mb_substr($s, 0, -1, 'UTF-8') . 'i';

        // -a -> -e
        if ($last === 'a' || $last === 'A') return mb_substr($s, 0, -1, 'UTF-8') . 'e';

        // -e -> -i
        if ($last === 'e' || $last === 'E') return mb_substr($s, 0, -1, 'UTF-8') . 'i';

        return $s;
    }

    public static function singularize(string $s): string {
        if ($s === '') return $s;
        $last = mb_substr($s, -1, null, 'UTF-8');

        // Invariables: consonante o vocal acentuada
        if (preg_match('/[bcdfghjklmnpqrstvwxyz]$/iu', $s) || preg_match('/[àèéìíòóùú]$/u', $s)) return $s;

        // he/hi -> ho (co/go)
        if (preg_match('/chi$/iu', $s)) return mb_substr($s, 0, -2, 'UTF-8') . 'o';
        if (preg_match('/ghi$/iu', $s)) return mb_substr($s, 0, -2, 'UTF-8') . 'o';

        // che/ghe -> ca/ga
        if (preg_match('/che$/iu', $s)) return mb_substr($s, 0, -3, 'UTF-8') . 'ca';
        if (preg_match('/ghe$/iu', $s)) return mb_substr($s, 0, -3, 'UTF-8') . 'ga';

        // cie/gie -> cia/gia
        if (preg_match('/cie$/iu', $s)) return mb_substr($s, 0, -2, 'UTF-8') . 'a';
        if (preg_match('/gie$/iu', $s)) return mb_substr($s, 0, -2, 'UTF-8') . 'a';
        // ce/ge (from cia/gia consonant)
        if (preg_match('/ce$/iu', $s)) return mb_substr($s, 0, -1, 'UTF-8') . 'a';
        if (preg_match('/ge$/iu', $s)) return mb_substr($s, 0, -1, 'UTF-8') . 'a';

        // -i -> -o (masc) fallback
        if ($last === 'i' || $last === 'I') return mb_substr($s, 0, -1, 'UTF-8') . 'o';

        // -e -> -a (fem) fallback
        if ($last === 'e' || $last === 'E') return mb_substr($s, 0, -1, 'UTF-8') . 'a';

        return $s;
    }

    public static function pluralize_if(int $count, string $string, ?string $zero_string = null): string {
        return $count > 0
            ? $count . ' ' . self::pluralize($string, $count)
            : ($zero_string ?? $string);
    }
}