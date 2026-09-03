<?php

    if(!OUTPUT){

        HTML::css(SCRIPT_DIR_MODULE.'/style.css?ver=2.3.3');

        if (in_array($_ARGS[1],array_merge(['view','theme','lang','output','debug','control_panel','login'],CFG::$vars['outputs'],CFG::$vars['langs'])) )  $_ARGS[1]=false; 

        if($_ARGS[1]===false&&CFG::$vars['default_module']=='page') $_ARGS[1]=CFG::$vars['default_page']?CFG::$vars['default_page']:'home';

        if($_ARGS[1]){
              
            $sanitize_url = Str::sanitizeName($_ARGS[1]);
            
            $sql_rows = "SELECT * FROM ".TB_PAGES." WHERE "; //item_name= '".$sanitize_url."'"; 
            if(CFG::$vars['site']['langs']['enabled']!==true ||  $_SESSION['lang']==CFG::$vars['default_lang']){
                $sql_rows .=  " item_name = '".$sanitize_url."'";
            }else{
                $sql_rows .=  " (item_name_".$_SESSION['lang']." = '".$sanitize_url."' OR item_name = '".$sanitize_url."') ";
            }
            /*
            include(SCRIPT_DIR_MODULE.'/functions.php');
            $editable = editable();
            if(!$editable) {       // restricted access by luserlevel 
                $sql_rows .=  " AND item_level <= ".($_SESSION['userlevel']??'100')." LIMIT 1 ";
            }
            */
	        $sql_rows .=  " LIMIT 1 ";
            $items_rows = Table::sqlQuery( $sql_rows);

            //CREATE TABLE CLI_PAGES SELECT * FROM `TB_ITEM` WHERE id_menu='0'
            $field_text_name  = CFG::$vars['site']['langs']['enabled']!==true || $_SESSION['lang']==CFG::$vars['default_lang'] ? 'item_text'  : 'item_text_'.$_SESSION['lang'];
            $field_title_name = CFG::$vars['site']['langs']['enabled']!==true || $_SESSION['lang']==CFG::$vars['default_lang'] ? 'item_title' : 'item_title_'.$_SESSION['lang'];
             /*
            if(!$items_rows){
	    
                $_NOT_FOUND =true;
		
            }else */if(count($items_rows)>1){ //FIX if not countable show message to update tables with translatables cols

                $ip = get_ip();
                $m2 = new Mailer();
                $m2->Subject = CFG::$vars['site']['title'].' - Debug information - '.$ip;
                $m2->body = '<p>Página duplicada.</p><pre>'
                            . Vars::debug_var($_ARGS,'_ARGS',true)
                            . Vars::debug_var($items_rows,'rows',true)  
                            .'</pre>'; 
                $m2->SetFrom('soporte@extralab.net','soporte@extralab.net') ;
                $m2->AddAddress(CFG::$vars['site']['debug']['email'],CFG::$vars['site']['debug']['email']);
                $m2->Send();
                
                $_HTML_duplicate_entry = '<h3>Error: Url duplicada</h3><p>Hay un error en la página que busca. Se ha enviado una notificación al admin para su correción.</p>';

            }else if( count($items_rows)==1){

                $row= $items_rows[0];

                $_SHOW_page = true;

                if(isset($row['NOT_TITLE']))   CFG::$vars['site']['title']       = CFG::$vars['site']['title'].' - '.$row['NOT_TITLE'];
                if(isset($row['KEYWORDS']))    CFG::$vars['site']['keywords']    = $row['KEYWORDS'];
                if(isset($row['DESCRIPTION'])) CFG::$vars['site']['description'] = $row['DESCRIPTION'];
     
                $name_default = $row['item_name'];

                $_ID_          = $row['item_id'];
                $_NAME_        = $row['item_name']; 
                $_TEXT_        = Table::unescape($row[$field_text_name]);
                $item_code     = str_replace('\"','',Table::unescape($row['item_code']));
                $item_code_css = str_replace('\"','',Table::unescape($row['item_code_css']));
                $item_code_js  = str_replace('\"','',Table::unescape($row['item_code_js']));

                //if ( CFG::$vars['site']['langs']['enabled']!==true || $_SESSION['lang']=='es')

                $_DEFAULT_LANG_TEXT_ = $row['item_text'];
                if($_SESSION['lang']!='es' && !$_TEXT_) $_TEXT_ = $_DEFAULT_LANG_TEXT_;

                $_SESSION['PAGE_FILES_ID_PARENT'] = $_ID_;

                //if($_ACL->HasPermission('edit_items')){
                    include(SCRIPT_DIR_MODULE.'/functions.php');
                    $editable = editable();
                //}else{
                //    $editable = false;
                //}

                
                if($row['item_level']>($_SESSION['userlevel']??100)/* && !$_ACL->HasPermission('edit_items')*/ ){
                    
                    
                    if($editable) {       // restricted access by userlevel 
                        
                        // $sql_rows .=  " AND item_level <= ".($_SESSION['userlevel']??'100')." LIMIT 1 ";
                        
                    }else{
                        //include(SCRIPT_DIR_MODULES.'/page/inline_head.php');
                        $_SHOW_page = false;
                        $_NOT_FOUND = true;            
                        http_response_code(404);
                        
                    }
                }  

                if($_SHOW_page)
                    include(SCRIPT_DIR_MODULES.'/page/inline_head.php');

               // if(CFG::$vars['plugins']['rating']) {
               //     HTML::css(SCRIPT_DIR_JS.'/jquery.bar-rating-master/dist/themes/css-stars.css');
               //     HTML::js(SCRIPT_DIR_JS.'/jquery.bar-rating-master/dist/jquery.barrating.min.js','defer');
                //    HTML::js(SCRIPT_DIR_MODULE.'/rating.js','defer');                  
               // }

            
            } elseif( (!$items_rows) || count($items_rows)<1 && $_ACL->HasPermission('edit_items') ){

                if(isset($_ARGS[2])&&$_ARGS[2]=='create' ){

                    $_CREATE_page =true;

                }else{

                    $_NOT_FOUND =true;
                    http_response_code(404);
                
                }

            }else{

                $_NOT_FOUND =true;            
                http_response_code(404);

            }

            // if($_NOT_FOUND) Header('Location: /404'); 

        }else{

            $_PAGE_LIST =true;

        }

        ///////////////////////////////////HTML::js(SCRIPT_DIR_LIB.'/bitcoin/noble-secp256k1-1.2.14.js');

        HTML::js(SCRIPT_DIR_MODULE.'/script.js?ver=1.0.2','defer');  

    }