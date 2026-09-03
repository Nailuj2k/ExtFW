<?php 

class i18n{
  
    public static $langs_dir = SCRIPT_DIR_I18N;

    function __construct(){
    }

    public static function write_lang_file($lang) {
        $locales = array();
        
        $locales['es'] = 'setlocale(LC_TIME, \'Spanish\');'."\n"
                       . 'setlocale(LC_ALL, "es_ES",  "es_ES.utf-8", "Spanish");'."\n";
        $locales['en'] = 'setlocale(LC_TIME, \'English\');'."\n"
                       . 'setlocale(LC_ALL, "en_EN",  "en_EN.utf-8", "English");'."\n";
   
        $locales['it'] = 'setlocale(LC_TIME, \'Italian\');'."\n"
                       . 'setlocale(LC_ALL, "it_IT",  "it_IT.utf-8", "Italian");'."\n";
   
        $filename = self::$langs_dir.'/'.$lang.'.php';
        $rows = Table::sqlQuery("SELECT ss.str_string, cc.cc_string FROM ".TB_CC." cc, ".TB_STR." ss WHERE ss.str_id=cc.id_str AND cc.id_lang=(SELECT lang_id FROM ".TB_LANG." WHERE lang_cc='".$lang."') ");
        $text = '<?php '."\n"
              . $locales[$lang]
              . '$strings=array();'."\n";
        foreach ($rows as $row){
           $text .= '$strings[\''.$row['str_string'].'\']=\''.   str_replace(array("'","\n"), array("\'",'<br />'), $row['cc_string']).'\';'."\n";
        } 

        if($f = fopen($filename,'w+')) {
            if(@fwrite($f,$text)) { //serialize($data))) {
                @fclose($f);
            } else die("Error::i18n::No se puede escribir en el archivo ".$filename.'<br />');
        } else die("Error::i18n::No se puede abrir el archivo ".$filename.'<br />');

    }

}

