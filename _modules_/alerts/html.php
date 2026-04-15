<?php

$TYPE = $_ARGS['type']?$_ARGS['type']:'1';

//echo __LINE__.':'.$TYPE;

// $type->values    = array('1'=>'Aviso normal','2'=>'Popup','3'=>'Imagen','4'=>'Redirect');

if($TYPE=='1'){

                    //BEGIN newsticker
                    $sql_newsticker = "SELECT ID,TYPE,DESCRIPTION,TEXT FROM CFG_ALERTS WHERE ACTIVE='1' AND D4TE<=CURDATE() AND (D4TE_END>=CURDATE() OR D4TE_END IS NULL)  AND TYPE='{$TYPE}' ORDER BY LAST_UPDATE_DATE DESC";  
                    $rows_newsticker = Table::sqlQuery($sql_newsticker); 
                    if($rows_newsticker){

                       // include_once(SCRIPT_DIR_LIB.'/file_viewer/pdf_viewer.php'); 
                       // include_once(SCRIPT_DIR_LIB.'/file_viewer/epub_viewer.php'); 

                    ?>
                    <style>
                        #pdf_viewer {    min-height: 300px; border:2px solid #444;}

@-webkit-keyframes ticker {
  0% {
    -webkit-transform: translate3d(0, 0, 0);
    transform: translate3d(0, 0, 0);
    visibility: visible;
  }
  100% {
    -webkit-transform: translate3d(-100%, 0, 0);
    transform: translate3d(-100%, 0, 0);
  }
}
@keyframes ticker {
  0% {
    -webkit-transform: translate3d(0, 0, 0);
    transform: translate3d(0, 0, 0);
    visibility: visible;
  }
  100% {
    -webkit-transform: translate3d(-100%, 0, 0);
    transform: translate3d(-100%, 0, 0);
  }
}
.ticker-wrap {
    /*
  position: fixed;
  bottom: 0;
  width: 100%;
  overflow: hidden;
  height: 4rem;
  padding-left: 100%;
  */
/*  background-color: rgba(0, 0, 0, 0.9);*/
  box-sizing: content-box;
}
.ticker-wrap .ticker {
  display: inline-block;
  /*height: 4rem;*/
  /*line-height: 4rem;*/
  white-space: nowrap;
  /*padding-right: 100%;*/
  box-sizing: content-box;
  -webkit-animation-iteration-count: infinite;
  animation-iteration-count: infinite;
  -webkit-animation-timing-function: linear;
  animation-timing-function: linear;
  -webkit-animation-name: ticker;
  animation-name: ticker;
  -webkit-animation-duration: 30s;
  animation-duration: 30s;
}

.ticker-wrap:hover .ticker {
 /* -webkit-animation-name: unset;
  animation-name: unset;
  */
  animation-play-state: paused;
}
 

.ticker-wrap .ticker__item {
  display: inline-block;
  padding: 0 4rem;
/*  font-size: 2rem;
  color: white;*/
}


                    </style>
                    <div class="inner" style="margin-top:20px;"><!----><div class="items-alerts announcements-container"><!--
                 --><span class="alerts-label container-title">Avisos<div class="arrow-right"></div></span><!--
                 --><div class="newsticker ticker-wrap"><!--
                 --><ul class="js-frame announcements ticker"><?php
                    foreach ($rows_newsticker as $row_newsticker){
                        ?>
                        <li class="js-item ticker__item"><span class="alert-type" title="<?=$row_newsticker['TEXT']?$row_newsticker['TEXT']:$row_newsticker['DESCRIPTION']?>"><?=Str::truncate($row_newsticker['DESCRIPTION'], 65)?></span> 
                        <?php
                           $files_newsticker = Table::sqlQuery("SELECT ID,NAME,FILE_NAME,ID_PROVIDER,LINK FROM CFG_ALERTS_FILES WHERE ITEM_ID ={$row_newsticker['ID']} AND ACTIVE='1'"); 
                           if($files_newsticker)
                            foreach($files_newsticker as $file_newsticker){                                //$ext = Str::get_file_extension($file_newsticker['FILE_NAME']);
                                $file = SCRIPT_DIR_MEDIA.'/CFG_ALERTS_FILES/files/'.$row_newsticker['ID'].'/'.$file_newsticker['FILE_NAME'];
                                echo ' <a class="pdf open_file_pdf" data-title="'.$row_newsticker['DESCRIPTION'].'" data-href="'.$file.'" title="'.$file_newsticker['NAME'].'"><img src="_images_/filetypes/icon_pdf.png"></a> ';
                            }
                        ?>
                        </li>
                        <?php
                    }
                    ?>
                    </ul><!--
                 --></div><!--
                 --></div><!--
                 --><a href="alerts" id="view-alerts" title="Ver todos los avisos" class="fa fa-arrow-right"></a></div>
                   <!--<script type="text/javascript" src="<?=SCRIPT_DIR_THEME?>/newsticker.script.js?ver=1.0.0"></script>-->
                    <script>
                        var pdf_viewer_initialized = false;     
                        var txt_viewer_initialized = false;     
                        var epub_initialized = false;     
                        //OKI $('.newsticker').newsticker();
                    </script>
                    <?php 
                    }
                    //END newsticker

}else if($TYPE=='2'){

                $sql_popups = "SELECT ID,TYPE,DESCRIPTION,TEXT,LAST_UPDATE_DATE FROM CFG_ALERTS WHERE ACTIVE='1' AND D4TE<=CURDATE() AND (D4TE_END>=CURDATE() OR D4TE_END IS NULL) AND TYPE='{$TYPE}' ORDER BY LAST_UPDATE_DATE";  
                $rows_popups = Table::sqlQuery($sql_popups); 
                if($rows_popups){
                    foreach ($rows_popups as $row_popup){
                        $popup_id = 'popup_'.$row_popup['ID'].'_'.Str::sanitizeName($row_popup['LAST_UPDATE_DATE']);
                        $is_url = strpos($row_popup['DESCRIPTION'],'/html')>0;

                        $user_logged = strpos($row_popup['DESCRIPTION'],'login/html')!==false && $_SESSION['userid'];
                        if($user_logged) continue; 
                        //else echo '<link  href="_classes_/crud/css/style.float.css?ver=1.0.0" rel="stylesheet" type="text/css" />';

                        if($is_url){     
      
                            if (!$_SESSION[$popup_id]) {
                                $_SESSION[$popup_id]=true;
                                ?>
                                <script type="text/javascript"> 
                                    $(function() {
                                        setTimeout(function(){

                                            //$.modalform({ 'classes':'popup', 'url': '<?=$row_popup['DESCRIPTION']?>' /* , 'buttons':'close'*/ }); 
                                            /** */
                                            $("body").dialog({
                                                title: '<img class="icon-favicon" src="<?=SCRIPT_DIR_MEDIA?>/images/.tn_favicon.png" style="height:22px">&nbsp;',
                                                class:'blank',
                                                content:  '<?=$row_popup['DESCRIPTION']?>'
                                             });
                                             /**/

                                        },1000);
                                    });
                                      
                                </script>
                                <?php
                            }
                        }else{

                            //$_SESSION[$popup_id]=false;
                            if (!$_SESSION[$popup_id]) {
                                //Vars::debug_var($row_popup);
                                $_SESSION[$popup_id]=true;
                                ?><script type="text/javascript"> $(function() { 
                                    setTimeout(function(){ 
                                           
                                            //  $.modalform({  /*'classes':'popup',*/ 'title' : '<?=$row_popup['DESCRIPTION']?>', 'html': '<p><?=str_replace("\n",'',$row_popup['TEXT'])?></p>' , 'buttons':'close' }); 


                                            $("body").dialog({
                                                title: '<img class="icon-favicon" src="<?=SCRIPT_DIR_MEDIA?>/images/.tn_favicon.png" style="height:22px">&nbsp; &nbsp; &nbsp; &nbsp; <?=$row_popup['DESCRIPTION']?>',
                                                type:'html',
                                                width:'550px',
                                                content:  '<p><?=str_replace("\n",'',$row_popup['TEXT'])?></p>',
                                                buttons: [ $.dialog.okButton ]
                                             });

                                                



                                    //$.modalform({   'title' : '<h3><?=$row_popup['DESCRIPTION']?></h3>', 'html': '<p><?=str_replace(["\n"],['<br>'],$row_popup['TEXT'])?></p>' , 'buttons':'close' }); 
                                },1000); });</script><?php
                            }
                        }
                    }
                }
}else if($TYPE=='3'){

   //if ($_SESSION['valid_user']){
              //$sql_popups = "SELECT ID,TYPE,DESCRIPTION,TEXT,LAST_UPDATE_DATE FROM CFG_ALERTS WHERE ACTIVE='1' AND TYPE='{$TYPE}' ORDER BY LAST_UPDATE_DATE";  
                $sql_popups = "SELECT ID,TYPE,DESCRIPTION,TEXT,LAST_UPDATE_DATE FROM CFG_ALERTS WHERE ACTIVE='1' AND TYPE='{$TYPE}' AND D4TE<=CURDATE() AND (D4TE_END>=CURDATE() OR D4TE_END IS NULL) ORDER BY LAST_UPDATE_DATE";  
                $rows_popups = Table::sqlQuery($sql_popups); 
                if($rows_popups){
                    foreach ($rows_popups as $row_popup){
                        $popup_id = 'popup_'.$row_popup['ID'].'_'.Str::sanitizeName($row_popup['LAST_UPDATE_DATE']);
                      //$_SESSION[$popup_id]=false;
                        if (!$_SESSION[$popup_id]) {
                          //print_r($popup_id);
                            $_SESSION[$popup_id]=true;
                            $images_popup = Table::sqlQuery("SELECT ID,NAME,FILE_NAME,ID_PROVIDER,LINK FROM CFG_ALERTS_FILES WHERE ITEM_ID ={$row_popup['ID']} AND ACTIVE='1' AND MAIN='1' LIMIT 1"); 
                            if($images_popup)
                            foreach($images_popup as $image_popup){                                //$ext = Str::get_file_extension($image_popup['FILE_NAME']);
                              //print_r($image_popup);
                                ?>
                                <script type="text/javascript"> 
                                    $(function() {
                                      //console.log('<?=SCRIPT_DIR_MEDIA.'/CFG_ALERTS_FILES/files/'.$row_popup['ID'].'/'.$image_popup['FILE_NAME']?>');
                                        setTimeout(function(){ 
                                      //OLD image('<?='media/CFG_ALERTS_FILES/files/'.$row_popup['ID'].'/'.$image_popup['FILE_NAME']?>','<?=$row_popup['NAME']?>',20000);
                                            image('<?=SCRIPT_DIR_MEDIA.'/CFG_ALERTS_FILES/files/'.$row_popup['ID'].'/'.$image_popup['FILE_NAME']?>');
                                        },2000); 
                                    });
                                </script>
                                <?php
                            }
                        }
                    }
                }
    //}

}else if($TYPE=='4'){
                $sql_popups = "SELECT ID,TYPE,DESCRIPTION,TEXT,LAST_UPDATE_DATE FROM CFG_ALERTS WHERE ACTIVE='1' AND D4TE<=CURDATE() AND (D4TE_END>=CURDATE() OR D4TE_END IS NULL) AND TYPE='{$TYPE}' ORDER BY LAST_UPDATE_DATE";  
                $rows_popups = Table::sqlQuery($sql_popups); 
                if($rows_popups){
                    foreach ($rows_popups as $row_popup){
                        $popup_id = 'popup_'.$row_popup['ID'].'_'.Str::sanitizeName($row_popup['LAST_UPDATE_DATE']);

                            //$_SESSION[$popup_id]=false; ///FIX
                            if (!$_SESSION[$popup_id]) {
                                //Vars::debug_var($row_popup);
                                $_SESSION[$popup_id]=true;
                                Jeader($row_popup['DESCRIPTION']);
                            }

                    }
                }

}
