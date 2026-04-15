<?php
<?php

class Inflect{
    public static function pluralize(string $s, int $n = 1): string {
        if ($n === 1 || $s === '') return $s;
        $last = mb_substr($s, -1, null, 'UTF-8');
        $last2= mb_substr($s, -2, null, 'UTF-8');

        // Invariables
        if (preg_match('/[sxz]$/iu', $s)) return $s;

        // -al -> -aux (except bal, carnaval, festival, récital, régal, chacal)
        if (preg_match('/al$/iu', $s) && !preg_match('/(bal|carnaval|festival|récital|régal|chacal)$/iu', $s)) {
            return mb_substr($s, 0, -2, 'UTF-8') . 'aux';
        }

        // -au, -eau, -eu -> +x (feu/beau exception already x)
        if (preg_match('/(au|eau|eu)$/iu', $s)) {
            return $s . 'x';
        }

        // -ail -> -ails (exceptions ail -> aulx; travail -> travaux)
        if (preg_match('/travail$/iu', $s)) return mb_substr($s, 0, -5, 'UTF-8') . 'travaux';
        if (preg_match('/ail$/iu', $s)) return $s . 's';

        // default +s
        return $s . 's';
    }

    public static function singularize(string $s): string {
        if ($s === '') return $s;

        // Invariables s/x/z
        if (preg_match('/[sxz]$/iu', $s) && !preg_match('/aux$/iu', $s)) return $s;

        // -aux -> -al (common case)
        if (preg_match('/aux$/iu', $s)) {
            // exceptions: travaux -> travail
            if (preg_match('/travaux$/iu', $s)) return mb_substr($s, 0, -6, 'UTF-8') . 'travail';
            return mb_substr($s, 0, -3, 'UTF-8') . 'al';
        }

        // -eaux/-eaus/-eus -> drop s/x
        if (preg_match('/(eaux|eaus|eus)$/iu', $s)) return rtrim($s, 'sx');

        // default: drop trailing s
        if (preg_match('/s$/u', $s)) return mb_substr($s, 0, -1, 'UTF-8');
        return $s;
    }

    public static function pluralize_if(int $count, string $string, ?string $zero_string = null): string {
        return $count > 0
            ? $count . ' ' . self::pluralize($string, $count)
            : ($zero_string ?? $string);
    }
}