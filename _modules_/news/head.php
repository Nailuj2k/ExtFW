<?php

    if(OUTPUT === 'pdf' || OUTPUT === 'txt'  || OUTPUT === 'html' || !OUTPUT){

        //$rrss_url = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        //$rrss_img = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].'/'.'media/images/logo.png'; 
        //$rrss_tit = CFG::$vars['site']['title'] ;
        //$rrss_key = CFG::$vars['site']['keywords'] ;
        //$rrss_des = CFG::$vars['site']['description'] ;


        if( $_ARGS[1]=='view') $_SESSION[TB_PREFIX.'_'.TB_NAME]['view'] = $_ARGS[2]?$_ARGS[2]:false;

        if(in_array($_ARGS[1],array_merge(['view','theme','lang','output','debug'],CFG::$vars['outputs'],CFG::$vars['langs'])) || !$_ARGS[1]){

            if (Administrador()){
                $_SHOW_LINK_ALL = true;
            }

           // echo __LINE__;

            $_SHOW_ALL = true;

        }else if($_ARGS[1]){

            $fields = array();
            $fields[]=TB_PREFIX.'_ID';
            $fields[]=TB_PREFIX.'_DATE';
            $fields[]='VIDEO';
            $fields[]='TOP_IMAGE';
            $fields[]='GALLERY';
            $fields[]='FILES';
            $fields[]='KEYWORDS';
            $fields[]='DESCRIPTION';
            $fields[]='ALLOW_COMMENTS';
            $fields[]='ALLOW_RATING';
            $fields[]='USER_ID';
            $fields[]='SIGN';
            $fields[]='VIEWS';
            if ( CFG::$vars['site']['langs']['enabled']!==true ){  //default lang
                    $fields[] = TB_PREFIX.'_TITLE';   
                    $fields[] = TB_PREFIX.'_SUBTITLE';   
                    $fields[] = TB_PREFIX.'_TEXT'; 
            }else if (  $_SESSION['lang']==CFG::$vars['default_lang']){ //CFG::$vars['default_lang']){
                    $fields[] = "COALESCE(NULLIF(".TB_PREFIX."_TITLE,''), ".TB_PREFIX."_TITLE_en) AS ".TB_PREFIX."_TITLE";
                    $fields[] = TB_PREFIX.'_SUBTITLE';   
                    $fields[] = TB_PREFIX.'_TEXT'; 
            }else{
                    $fields[] = "COALESCE(NULLIF(".TB_PREFIX."_TITLE_".$_SESSION['lang'].",''), ".TB_PREFIX."_TITLE) AS ".TB_PREFIX."_TITLE";
                    $fields[] = "COALESCE(NULLIF(".TB_PREFIX."_SUBTITLE_".$_SESSION['lang'].",''), ".TB_PREFIX."_SUBTITLE) AS ".TB_PREFIX."_SUBTITLE";
                    $fields[] = "COALESCE(NULLIF(".TB_PREFIX."_TEXT_".$_SESSION['lang'].",''), ".TB_PREFIX."_TEXT) AS ".TB_PREFIX."_TEXT";
            }
            $sql  = "SELECT ".implode(',',$fields)." FROM ".TB_PREFIX."_".TB_NAME." WHERE ";

            if(CFG::$vars['site']['langs']['enabled']!==true || $_SESSION['lang']==CFG::$vars['default_lang']){
                $sql .=  " ".TB_PREFIX."_NAME = '".Str::sanitizeName($_ARGS[1])."'";
            }else{
                $sql .=  " (".TB_PREFIX."_NAME_".$_SESSION['lang']." = '".Str::sanitizeName($_ARGS[1])."' OR ".TB_PREFIX."_NAME = '".Str::sanitizeName($_ARGS[1])."') ";
            }

            if (Administrador() && $_SESSION[TB_PREFIX.'_'.TB_NAME]['view']=='all') 
                $_and .= '';
            else
                $_and .=" AND ACTIVE='1' "; //.(CFG::$vars['modules'][MODULE]['selected_langs']?" AND ".TB_PREFIX."_TITLE".($_SESSION['lang']!=CFG::$vars['default_lang'] ? '_'.$_SESSION['lang']:'')." <> ''":'');

            $sql .= $_and." LIMIT 1 ";

            $rows = Table::sqlQuery($sql);
            if($rows){
                $row=$rows[0];
                $_ID_ = $row[TB_PREFIX.'_ID']; 
                $author = Login::getUserData($row['USER_ID']);
                if($row['SIGN']) $author['user_fullname'] = $row['SIGN'];

                $prev_next =  Table::getPrevNext(TB_PREFIX.'_'.TB_NAME,$_ID_,TB_PREFIX.'_ID',TB_PREFIX.'_NAME',TB_PREFIX.'_TITLE',TB_PREFIX.'_ID',$_and);

                if($row[TB_PREFIX.'_TITLE'])   $rrss_tit = CFG::$vars['site']['title'].' - '.$row[TB_PREFIX.'_TITLE'];
                if($row['KEYWORDS'])    $rrss_key = $row['KEYWORDS'];
                $rrss_des = $row[TB_PREFIX.'_SUBTITLE'] ? $row[TB_PREFIX.'_SUBTITLE'] : ( $row['DESCRIPTION'] ? $row['DESCRIPTION'] : $rrss_des ) ;

                $sql_tags = 'SELECT NAME FROM '.TB_PREFIX.'_TAGS WHERE TAG_ID IN (SELECT TAG_ID FROM '.TB_PREFIX.'_'.TB_NAME.'_TAGS WHERE '.TB_NAME.'_ID = '.$row[TB_PREFIX.'_ID'].')';

                //Vars::debug_var($sql_tags);

                $rows_tags = Table::sqlQuery($sql_tags);
                $tags = array();
                foreach ($rows_tags as $k=>$v){
                    $tags[Str::sanitizeName($v['NAME'])]=t($v['NAME']);
                }
                 
                $ver = hash('crc32b',$row['LAST_UPDATE_DATE']??'');
                $tags = implode(', ',array_values($tags));

                //Vars::debug_var($tags,'$tags');

                if ( CFG::$vars['site']['langs']['enabled']!==true ||  $_SESSION['lang']=='es'){ //CFG::$vars['default_lang']){
                        $field_name = 'NAME'; 
                        $field_desc = 'DESCRIPTION'; 
                }else{
                        $field_name = "COALESCE(NULLIF(NAME_".$_SESSION['lang'].",''), NAME) AS NAME";
                        $field_desc = "COALESCE(NULLIF(DESCRIPTION_".$_SESSION['lang'].",''), DESCRIPTION) AS DESCRIPTION";
                }
                
                if     ($row['VIDEO']=='1')     $sql_img = 'SELECT '.$field_name.',ID_PROVIDER,FILE_NAME,'.$field_desc.',LINK FROM '.TB_PREFIX.'_'.TB_NAME.'_FILES WHERE '.TB_NAME.'_ID='.$row[TB_PREFIX.'_ID'].' AND ID_PROVIDER=\'2\' ORDER BY ID DESC';   
                else if($row['TOP_IMAGE']=='1') $sql_img = 'SELECT '.$field_name.',ID_PROVIDER,FILE_NAME,'.$field_desc.',LINK FROM '.TB_PREFIX.'_'.TB_NAME.'_FILES WHERE '.TB_NAME.'_ID='.$row[TB_PREFIX.'_ID'].' AND MAIN=\'1\' ORDER BY ID DESC';   
                if($sql_img){
                    $images = Table::sqlQuery($sql_img);
                    $image =  false;
                    $image_desc = false;
                    if($images){
                        if(count($images)>0){
                            $image_big  = SCRIPT_DIR_MEDIA.'/'.TB_NAME.'/files/'.$row[TB_PREFIX.'_ID'].'/'.BIG_PREFIX.$images[0]['FILE_NAME'];
                            $image      = file_exists($image_big) ? $image_big : SCRIPT_DIR_MEDIA.'/'.TB_NAME.'/files/'.$row[TB_PREFIX.'_ID'].'/'.$images[0]['FILE_NAME'];
                            //$image    = './media/'.TB_NAME.'/files/'.$row[TB_PREFIX.'_ID'].'/'.$images[0]['FILE_NAME'];
                            $image_desc = $images[0]['DESCRIPTION'];
                            $rrss_img   = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].'/media/'.TB_NAME.'/files/'.$row[TB_PREFIX.'_ID'].'/'.$images[0]['FILE_NAME'];
                        }
                    }
                }
                $parent = $row[TB_PREFIX.'_ID'];
                //$provider->values     = array('1'=>'Image', '2'=>'Youtube', '3'=>'Vimeo', '4'=>'Local', '5'=>'PDF', '6'=>'Remote');
                //Vars::debug_var($images[0]);

                $row[TB_PREFIX.'_DATE'] = DateTime::createFromFormat('Y-m-d', $row[TB_PREFIX.'_DATE'])->format('d / m / Y'); // => 2013-12-24

                $_SHOW_ONE = true;

                $editable = $_ACL->hasPermission(MODULE.'_admin');
                include(SCRIPT_DIR_MODULES.'/page/inline_head.php');

            } else{  //  if($rows)
          
                $_SHOW_404 = true;
                http_response_code(404);

            }


        }else{   //  else if($_ARGS[1])

            $_SHOW_ALL = true;

        }

        Breadcrumb::$replace[MODULE]     = [ t(TB_NAME),MODULE.'/tag'];
        //Breadcrumb::$replace['noticias'] = [ t(TB_NAME),'noticias/tag'];

        HTML::css(SCRIPT_DIR_MODULES.'/news/style.css?ver=1.9.1');
        HTML::css(SCRIPT_DIR_THEME.'/style.'.MODULE.'.css?ver=1.9.2');

    }