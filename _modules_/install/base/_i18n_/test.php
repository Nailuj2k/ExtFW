<?php

die('hello');

$done = false;

function NNNt($s){
    global $done,$strings;
    /*
    if(!$done){
        $done =true;
        foreach ($strings as $k => $v){
           if ( ctype_upper($k) || strpos($k, '_') || strpos($k, '%s') ){
                $oki = Table::getFieldsValues("SELECT str_id,str_string FROM ".TB_STR." WHERE str_string = '$k'");
                if(!$oki) Table::sqlExec("INSERT INTO  ".TB_STR." (str_string) VALUES ('".$k."')");
            
                $r = $strings[$k];
                if($r){
                    $oki2 = Table::getFieldsValues("SELECT cc_id,id_str,id_lang,cc_string FROM ".TB_CC." WHERE id_lang = (SELECT lang_id FROM ".TB_LANG." WHERE lang_cc='".$_SESSION['lang']."') AND id_str = (SELECT str_id FROM  ".TB_STR." WHERE str_string = '$k')");
                    if(!$oki2) Table::sqlExec("INSERT INTO  ".TB_CC." (id_str,id_lang,cc_string) VALUES ((SELECT str_id FROM  ".TB_STR." WHERE str_string = '$k'),(SELECT lang_id FROM ".TB_LANG." WHERE lang_cc='".$_SESSION['lang']."'),'".$strings[$k]."')");
                
                }
           }
        }
    }
    */
    if ( ctype_upper($s) || strpos($s, '_') || strpos($s, '%s') ){
    
        $oki = Table::getFieldsValues("SELECT str_id,str_string FROM ".TB_STR." WHERE str_string = '$s'");
        if(!$oki) Table::sqlExec("INSERT INTO  ".TB_STR." (str_string) VALUES ('".$s."')");
    
        $r = $strings[$s];
        if($r){
            $oki2 = Table::getFieldsValues("SELECT cc_id,id_str,id_lang,cc_string FROM ".TB_CC." WHERE id_lang = (SELECT lang_id FROM ".TB_LANG." WHERE lang_cc='".$_SESSION['lang']."') AND id_str = (SELECT str_id FROM  ".TB_STR." WHERE str_string = '$s')");
            if(!$oki2) Table::sqlExec("INSERT INTO  ".TB_CC." (id_str,id_lang,cc_string) VALUES ((SELECT str_id FROM  ".TB_STR." WHERE str_string = '$s'),(SELECT lang_id FROM ".TB_LANG." WHERE lang_cc='".$_SESSION['lang']."'),'".$strings[$s]."')");
        
        }
    }

    return $r ? $r : $s;


}
