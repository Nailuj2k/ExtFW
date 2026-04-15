<?php

$_footer = '<div class="inner" style="display:block;text-align:center;">'
         . '  <img class="logo_footer editable-image-png" alt="Logo" src="/media/images/logo_footer.png?ver='.CFG::$vars['site']['lastupdate'].'" style="margin:10px auto 0 auto;max-height:50px;"><br />'
      //   . '   <p style="display:block;font-size:0.9em;font-weight: 300;"> '.CFG::$vars['site']['title'].' © Todos los berberechos reservados</p>'
         . '</div><style>#footer .logo_footer{max-width:300px;}</style>';

                if(MODULE!='install'){

                    if ( CFG::$vars['site']['langs']['enabled']!==true || $_SESSION['lang']==CFG::$vars['default_lang'])
                        $_text_field_name = 'item_text';
                    else
                        $_text_field_name = "COALESCE(NULLIF(item_text_".$_SESSION['lang'].",''), item_text) AS item_text";                 

                    $rfooter = Table::sqlQuery("SELECT $_text_field_name,item_code_css FROM ".TB_PAGES." WHERE item_name= 'footer-".THEME."'");
               
                    if($rfooter){
                        if(count($rfooter)==1){
                            $k =  Vars::rekey(CFG::$vars['site']);                           
                            $_footer .= str_replace(array_keys($k),array_values($k), $rfooter[0]['item_text']).'<style>'.$rfooter[0]['item_code_css'].'</style>';
                            if($_ACL->HasPermission('edit_items')&&$_ARGS[1]!='footer-'.THEME)
                                $_footer .=  '<a style="position:absolute;left:90px;bottom:20px;color:white;" href="'.Vars::mkUrl('footer-'.THEME).'" class="btn btn-small btn-light"><i class="fa fa-edit"></i> Editar footer</a>';                       
                        }else{
                            //echo "SELECT $_text_field_name FROM ".TB_PAGES." WHERE item_name= 'footer-".THEME."'";
                        }
                    }
                }


echo $_footer;