<?php 
	$btn_help  = '<i style="cursor:pointer;float:right;margin:2px 5px;" class="btn-help fa fa-question-circle-o fa-inverse"></i>';
	$btn_close = '<i style="cursor:pointer;float:right;margin:2px 5px;" class="fa fa-close close-box fa-inverse"></i>';
	$btn_users_reload = '<i style="cursor:pointer;float:right;margin:2px 5px;" id="users-reload"  class="fa fa-refresh fa-inverse"></i>';

    if($_ARGS['test']){

        echo '<pre>';       
        echo  AreasACL::hasPermission(APP_NAME,'doku_add') ? 'y' : 'n'; 
        echo  AreasACL::hasPermission(APP_NAME,'doku_admin') ? 'y' : 'n'; 
        echo  AreasACL::hasPermission(APP_NAME,'doku_view') ? 'y' : 'n'; 
        echo  AreasACL::hasPermission(APP_NAME,'doku_add') ? 'y' : 'n'; 
        echo  AreasACL::hasPermission(APP_NAME,'doku_no') ? 'y' : 'n'; 
        echo '</pre>'; 
         
        //print_r(AreasACL::getUsersWithPermission('fac',APP_NAME,'read'));

        $users = AreasACL::getUsersWithPermissionInAnyArea('dokux', 'doku_view');

        echo '<pre>';
        print_r($users);
        echo '</pre>';

        die('test');


    }

	$myareas =AreasACL::getAreas($_SESSION['userid'],true);

	if(is_array($myareas)){
	    if(count($myareas)==1){
	        $_SESSION['DOKU_AREA'] = $myareas[0];
	    }else if(count($myareas)>0){
	        Vars::setDefaultSessionVar('DOKU_AREA'   ,$myareas[0]);
	    }          
	    $doku_allow_oper=count($myareas)>0;
	}

	?>

    <div class="inner doku-page">

        <div id="doku-bar" class="doku-help"><?=APP_NAME?><i class="fa fa-bullseye btn-help" style="font-size:13px;vertical-align:top;color:#f30c68;"></i>

           <div id="cfg-links">
                <span style="font-size:16px;line-height:0.1em;font-weight:100;"> </span>
                <?php 

                    if(count($myareas)==1){
                       ?><a data-box=""  class="btn-cfg">Área: <b><?=$_SESSION['DOKU_AREA']['NAME']?></b></a><?php
                    }elseif(count($myareas)>1){
                       ?>Área: <select id="select_area"><?php foreach ($myareas as $k => $v){ ?><option <?=$_SESSION['DOKU_AREA']['ID']==$v['ID']?'selected ':''?>value="<?=$v['ID']?>"><?=$v['NAME']?></option><?php } ?></select><?php
                    //}else{
                       //$_SESSION['DOKU_TABLE_FILES_ROWS']=DOKU_FILES_MAX_ROWS;
                    }

                ?>
                <?php if( $doku_admin) { ?>
                <a id="btn-cfg-permissions"    href="acl"    data-box="permissions"     class="btn-cfg"><i class="fa fa-user"></i> Permisos</a>
                <!--
                <a id="btn-cfg-servicios"    data-box="servicios" class="btn-cfg"><i class="fa fa-th-list"></i> Servicios</a>
                <a id="btn-cfg-users"        data-box="users"     class="btn-cfg"><i class="fa fa-user"></i> Usuarios</a>
                -->
                <?php } ?>
                <?php if($doku_admin) { ?>
                <a id="btn-cfg-filetypes"    data-box="filetypes" class="btn-cfg"><i class="fa fa-files-o"></i> Documentos</a>
                <?php } ?>
                <?php if($doku_oper) { ?>
                IA: <select id="ai_service" title="Servicio de IA usado al pulsar el icono de IA">
                    <!--
                    <optgroup label="Leen documentos">
                    -->
                        <option value="gemini">Gemini</option>
                        <option value="claude">Claude</option>
                        <option value="openai">OpenAI</option>
                        <option value="ollama" selected>Ollama (local/vision)</option>
                    <!--
                    </optgroup>
                    <optgroup label="No leen documentos">
                        <option value="deepseek">DeepSeek</option>
                        <option value="grok">Grok</option>
                        <option value="kimi">Kimi</option>
                        <option value="cerebras">Cerebras</option>
                        <option value="dummy">Dummy</option>
                    </optgroup>
                    -->
                </select>
                <?php } ?>
                <a id="btn-cfg-pdf_text"     data-box="pdf_text"  class="btn-cfg" style="float:right;display:none;visibility:hidden;">PDF text</a>
                <span id="btn-cfg-pdf_text"     data-box=""  class="btn-cfg">Nivel de acceso: <b><?=($doku_admin?'Administrador':($doku_oper?'Operador':($doku_user?'Usuario':'')))?></b></span>      
                <span id="btn-cfg-user-profile"  title="<?=$_SESSION['username'].' - '.$_SESSION['user_email']?>" data-box="" class="btn-cfg"><img src="<?=Login::getUrlAvatar()?>" style="display:inline;height:20px;margin-bottom:-6px;"> &nbsp; <?=$_SESSION['user_fullname']?></span>
           </div>
        </div>

        <div id="dokux_files" class="dokux_box doku-help" style="position:relative;width:100%;height:auto;margin-bottom:8px;">
            <div class="title-bar">Documentos procesados <i title="Maximizar" class="fa fa-window-maximize fa-inverse" id="full-view"></i> <?=$btn_help?></div>
            <?php
            Table::init();
          //Table::sqlExec('DROP TABLE DOKU_FILETYPES');
          //Table::sqlExec('TRUNCATE TABLE DOKU_FILES');
          //Table::sqlExec('UPDATE DOKU_FILETYPES SET SERVICIO=1');
          //Table::sqlExec('DROP TABLE DOKU_FILES');
          //Table::sqlExec('DROP TABLE DOKU_AREAS');
            Table::show_table('DOKU_FILES');
            $maximized = Vars::getArrayVar($_SESSION,'DOKU_TABLE_FILES_ROWS')>DOKU_FILES_ROWS;
            ?>
        </div>
        <?php if($doku_allow_oper){?>
        <div id="dokux_inbox" <?=$maximized?'style="display:none;"':'style="/*cursor:none;*/"'?> class="dokux_box doku-help">
                     <div class="title-bar">Entrada <span id="sys"></span>
                     <i id="files-reload"  style="cursor:pointer;float:right;margin:2px 5px;" title="Actualizar" class="fa fa-refresh fa-inverse"></i> 
                     <i id="files-upload"  style="cursor:pointer;float:right;margin:2px 5px;" title="Subir documentos" class="fa fa-upload fa-inverse"></i> 
                     <?=$btn_help?></div>
            <?php if($doku_oper) { ?>
                <table class="dokux-table"><tr><th></th><th></th><th></th><th></th><th></th></tr></table>
                <?php include(SCRIPT_DIR_MODULE.'/upload.php');   ?>
            <?php }else{?>
                <p style="text-align:center;margin-top:80px;">No dispone de permisos para añadir documentos.</p>
            <?php }?>
            <div id="cursor" style="/**display:none;**/width:20px;height:20px;background-color:red;position:absolute;color:white;text-align:center;"></div>
        </div>

        <div id="dokux_pdf_viewer" <?=$maximized?'style="display:none;"':''?> class="dokux_box doku-help"><div class="title-bar">Visor <?=$btn_help?></div>
            <div id="img_preview">
            </div>
            <div id="canvas_container">
                <canvas id="the-canvas"></canvas>
            </div>
            <div class="button-bar">
              <button class="btn btn-small" id="prev"><?=t('PREV')?></button>
              <button class="btn btn-small" id="next"><?=t('NEXT')?></button>
              &nbsp; &nbsp;
              <span><?=t('PAGE')?>: <span id="page_num"></span> / <span id="page_count"></span></span>
            </div>
        </div>
        <?php }?>
        
        <?php if($doku_admin) { ?>
        
           <div id="dokux_cfg-filetypes" class="dokux_cfg dokux_box doku-help shadow"><div class="title-bar">Documentos <?=$btn_close?> <?=$btn_help?></div>
               <?php Table::show_table('DOKU_FILETYPES');  ?>
           </div>
        <?php } ?>

        <?php if($doku_admin) { ?>

           <div id="dokux_cfg-servicios" class="dokux_cfg dokux_box doku-help shadow"><div class="title-bar">Servicios <?=$btn_close?> <?=$btn_help?></div>
               <?php /*Table::show_table('DOKU_AREAS'); */ ?>
           </div>

           <div id="dokux_cfg-users" class="dokux_cfg dokux_box doku-help shadow"><div class="title-bar">Usuarios<?=$btn_close?> <?=$btn_users_reload?> <?=$btn_help?></div>
               <table id="hw_tb_users"  class="dokux-table datatable-rows"><thead><tr><th style="width:375px">Usuarios</th></tr></thead><tbody></tbody></table>
               <table id="hw_tb_opers"  class="dokux-table datatable-rows"><thead><tr><th style="width:375px">Operadores</th></thead><tbody></tbody></table>
               <table id="hw_tb_admins" class="dokux-table datatable-rows"><thead><tr><th style="width:375px">Administradores</th></tr></thead><tbody></tbody></table>
           </div>

        <?php } ?>

        <div id="dokux_cfg-pdf_text" class="dokux_cfg dokux_box shadow"><div class="title-bar"><span>Texto PDF</span><?=$btn_close?></div>
            <div id="pdf-text">
            </div>
        </div>

        <div id="dokux_upload" style="display:none;max-width:400px;" class="dokux_box shadow"><div class="title-bar">Añadir documentos<?=$btn_close?></div>
           <p>
           Seleccione uno o mas documentos y se subirán automáticamente. Puede cerrar esta ventana y arrastrar documentos desde el explorador de archivos. Para volverla a abrir pulse <i class="fa fa-upload"></i><br /><br />
           <input type="file" name="FILE_NAME" id="acho" multiple>
           </p>
       </div>

    </div>            
