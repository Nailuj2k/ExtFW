<?php

class Inflect{

    public static function pluralize($s,$n=1) {
        if($n==1) return $s;
        else      return $s.'h';
    }

    public static function singularize( $string ){
        return $string;
    }

    public static function pluralize_if($count, $string, $zero_string=null) {
        if ($count == 1)
            return "1 $string";
        else if ($count > 1)
            return $count . " " . self::pluralize($string);
        else 
            return $zero_string??$string;
    }
}