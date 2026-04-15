<?php

   // include(SCRIPT_DIR_MODULE.'/edit_ware.class.php');
//    $root_dir = $_SERVER['DOCUMENT_ROOT'];/*SCRIPT_DIR_MODULES*/

    $module  = $_ARGS[1]?'/'.$_ARGS[1]:'';
    $module .= $_ARGS[2]?'/'.$_ARGS[2]:'';

    $root_dir = $_SERVER['DOCUMENT_ROOT'].$module;
    $parseDir = EDIT_ware::parseDir($root_dir,true);
    $files = $parseDir['files'];
    if(trim($module)=='') $module = '/'; // else $module=str_replace('/','<span style="color:#666;">::</span>',$module);
    //Vars::debug_var($_SERVER['DOCUMENT_ROOT']);
    //Vars::debug_var($files);

?>

<!-- The Modal -->
<div id="modal-overlay">
    <!-- Modal content -->
    <div id="modal-window">
        <div id="modal-header">
            <span id="modal-close">&times;</span>
            <span id="modal-title">Nuevo Archivo</span>
        </div>
        <div id="modal-body">
            <input type="text" id="new-filename" placeholder="Nombre del archivo">
            <select id="new-filetype">
                <option value="html">HTML</option>
                <option value="js">JavaScript</option>
                <option value="css">CSS</option>
                <option value="php">PHP</option>
                <option value="json">JSON</option>
                <option value="sql">SQL</option>
                <option value="txt">TXT</option>
            </select>
        </div>
        <div id="modal-footer">
            <button id="modal-btn-create">Crear</button>
            <button id="modal-btn-cancel">Cancelar</button>
        </div>
    </div>
</div>



<div class="inner" id="editor">
    <div id="editor-sidebar">
        <img class="btn-sidebar" id="btn-files" src="/_images_/icons/files.svg">
        <img class="btn-sidebar" id="btn-search" src="/_images_/icons/search.svg">
        <img class="btn-sidebar" id="btn-liveshare" src="/_images_/icons/disconnect.svg">
        <img class="btn-sidebar" id="btn-ai" src="/_images_/icons/icon-ai-chatgpt.svg">
    </div>

    <div id="editor-top">
        <span id="editor-title">AutoEdit <i class="fa fa-bullseye btn-help editor-logo"></i>
            <span id="editor-user" title="<?=$_SESSION['username'].' - '.$_SESSION['user_email']?>"><img class="avatar" src="<?=Login::getUrlAvatar()?>"> &nbsp; <?=$_SESSION['user_fullname']?></span>
        </span>
        <span id="top-filename"></span>

        <i title="Split" id="btn-split" class="fa fa-columns"></i>
        <i title="Guardar" id="btn-editor-save" class="fa fa-floppy-o"></i>
        <i title="Maximizar/restaurar ventana" id="btn-fullscreen" class="fa fa-window-maximize"></i>
    </div>
    <div id="file-tree-header" class="root-folder folder-open">

        <a      class = "link-rootdir" 
                   id = "li-<?=md5($root_dir)?>" 
              data-id = "<?=md5($root_dir)?>" 
            data-size = "? b." 
        data-basename = "<?=dirname($root_dir)?>" 
            data-path = "<?=$root_dir?>" 
          data-nfiles = "n" 
             data-ext = "DIR" 
             data-dir = "<?=$root_dir?>" 
            data-type = "folder"><?=$module?></a>
   
        <div id="file-tree-buttons">
            <img id="btn-new-file"   src="_images_/icons/new-file.svg"   title="Nuevo archivo">
            <img id="btn-new-folder" src="_images_/icons/new-folder.svg" title="Nueva carpeta">     
            <img id="btn-refresh"    src="_images_/icons/refresh.svg"    title="Actualizar">     
        </div>        
    </div>

    <div id="file-tree">
        <ul id="child-of-<?=md5($root_dir)?>" class="ul-child" data-parent="<?=md5($root_dir)?>"></ul>
    </div>
    
    <div id="file-search" style="display:none;">
        <div class="search-header">
            <input type="search" id="search-input" placeholder="Search in files...">
            <button id="btn-do-search" title="Search"><i class="fa fa-search"></i></button>
        </div>
        <div class="search-info"></div>
        <div class="search-results"></div>
    </div>

    <div id="file-editor-wrapper">   
        <div id="file-editor" class="file-editor checkerboard"><div id="btn-tabs" class="btn-tabs"></div></div>
        <div id="file-editor-right" class="file-editor checkerboard" style="display:none;"><div id="btn-tabs-right" class="btn-tabs"></div></div>
    </div>
    
    <div id="editor-sidebar-right">
        <div id="suggestion-box" class="suggestion-box monaco">
            <div id="ai-chat-header" style="padding:5px;border-bottom:1px solid #333;font-size:11px;color:#999;display:flex;justify-content:space-between;align-items:center;">
                <span>AI Assistant</span>
                <button id="ai-chat-clear" title="Clear chat history" style="background:transparent;color:#999;border:1px solid #555;padding:2px 8px;cursor:pointer;font-size:9px;border-radius:3px;">Clear</button>
            </div>
            <div id="ai-chat-history" style=""></div>
            <div id="suggestion-editor"></div><!--MONACO-->
            <div id="ai-chat-input-wrapper" style="padding:5px;border-top:1px solid #333;display:flex;gap:5px;">
                <input type="text" id="ai-chat-input" placeholder="Ask AI or provide context..." style="flex:1;background:#1d1d1d;border:1px solid #666;color:#fff;padding:4px;font-size:11px;outline:none;">
                <button id="ai-chat-send" style="background:#007acc;color:#fff;border:none;padding:4px 10px;cursor:pointer;font-size:11px;">Send</button>
            </div>
        </div>
    </div>

    <div id="editor-status-bar">
        <span id="active_id">unknown</span>
        <span id="connection-controls"><span id="connection-status">Offline</span>  Users online: <span id="users-count">0</span></span>
        <span id="position">Line <span>0</span>, Col <span>0</span></span>
        <span id="status-theme">Theme: <select></select></span>
        <span id="status-wordwrap" class="off"><img src="_images_/icons/word-wrap.svg"></span>
        <span id="status-ai">AI: <select id="ai-service-selector">
            <option value="ollama" selected>Ollama</option>
            <option value="claude">Claude</option>
            <option value="deepseek">DeepSeek</option>
            <option value="openai">OpenAI</option>
            <option value="gemini">Gemini</option>
            <option value="grok">Grok</option>
            <option value="kimi">Kimi</option>
            <option value="dummy">Dummy</option>
        </select></span>
        <span id="ai-status"></span>
        <span id="status-capslock">CAP</span>
    </div>
    
</div>
<!--
https://codepen.io/kazzkiq/pen/xGXaKR
https://codepen.io/gdube/pen/oXEBZR
https://jsfiddle.net/xlaptop2001/y70gL3kv/  :)
https://codepen.io/defims/pen/rnzjh
-->


