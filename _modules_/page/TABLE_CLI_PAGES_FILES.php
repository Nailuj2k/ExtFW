<?php

    $tabla = new TableMysql('CLI_PAGES_FILES');
    $tabla->uploaddir = 'media/page/files';
    $tabla->fk = 'id_item';
    //$tabla->show_empty_rows=false;
    //$tabla->table_tags = false; //'RRHH_TAGS';
    $tabla->table_tags = 'CLI_TAGS';
    $tabla->order = true;
    $tabla->epub=true;
    $tabla->download_count = 'DOWNLOAD_COUNT';
    //$tabla->download_count_fieldname = 'DOWNLOAD_COUNT';
    $tabla->link_gallery_mode=true;
    $tabla->page_num_items=5;
    $tabla->group=true;                             //FIX checkbox 'usar categorias'  if not true then $tabla->group false
                                                    //FIX fields id? & item_id &  miniatura & principal & descargas? & order? ->hide

    $tabla->hash_filenames = true; // cfg setting

    $tabla->id_parent = isset($_SESSION['PAGE_FILES_ID_PARENT'])&&$_SESSION['PAGE_FILES_ID_PARENT']>0;

    function editable($id=false){
        global $_ACL;
        if (!$_SESSION['userid']) return false;

        $item = TableMySql::getFieldsValues("SELECT item_id,item_parent FROM ".TB_ITEM." WHERE item_url = (SELECT item_name FROM ".TB_PAGES." WHERE item_id='".$id."')");

        $parents = array();
        $module_id = $item['item_id'];
        $parent_id = $item['item_parent'];
        $ir = $_ACL->getItemRoles($module_id);
        $ur = $_ACL->getUserRoles();
        if(is_array($ir)&&is_array($ur)){
            //Vars::debug_var($module_id,'$module_id');     
            //Vars::debug_var($parent_id,'$parent_id');     
            $laps = 0;
            if($parent_id>0) {
                $parents[] = $parent_id;
                while ($parent_id>0){
                    if(++$laps>10) break;  // prevent recursion
                    //Vars::debug_var("SELECT item_id,item_parent FROM ".TB_ITEM." WHERE item_id = '".$parent_id."'",'SQL');     
                    $item = TableMySql::getFieldsValues("SELECT item_id,item_parent FROM ".TB_ITEM." WHERE item_id = '".$parent_id."'");
                    $module_id = $item['item_id']; 
                    $parent_id = $item['item_parent'];
                    if($parent_id>0) { 
                        $parents[] = $parent_id;
                        $ir = array_merge($ir,$_ACL->getItemRoles($module_id));
                    }
                }
            }
            //Vars::debug_var($parents,'$parents');     
            //Vars::debug_var(implode(',',array_values($ur)),'USERROLES');     
            //Vars::debug_var(implode(',',array_values($ir)),'ITEMROLES');     
            $uir = array_intersect(array_values($ir),array_values($ur));       
            //Vars::debug_var(implode(',',$uir),'ITEMUSERROLES');        
            $ed = count($uir)>0;
        }
        return $ed; 
    }

    //include_once(SCRIPT_DIR_LIB.'/phpqrcode/qrlib.php');

    include(SCRIPT_DIR_MODULES.'/control_panel/TPL_TABLE_FILES.php');

    $tabla->title = '<br />';

    if(isset($_SESSION['PAGE_FILES_ID_PARENT'])&&$_SESSION['PAGE_FILES_ID_PARENT']>0){
       // echo '<div class="info"><p>PAGE_FILES_ID_PARENT: '.$tabla->fk.'='.$_SESSION['PAGE_FILES_ID_PARENT'].'</p></div>';
        $tabla->setParent($tabla->fk,$_SESSION['PAGE_FILES_ID_PARENT']);
       // die(__LINE__);
    }else {
        echo '<div class="warning"><p>Missing parent !!<br>Try reload page</p></div>';
      //  Vars::debug_var($_ARGS,'$_ARGS');
      //  Vars::debug_var($tabla,'$tabla');
        die(__LINE__);
    }
    $tabla->link_cfg = editable($_SESSION['PAGE_FILES_ID_PARENT']) || Administrador();

    $tabla->perms['filter'] = false;
    $tabla->perms['show']   = false;
    $tabla->perms['reload'] = Administrador();  ;
    $tabla->perms['add']    = editable($_SESSION['PAGE_FILES_ID_PARENT']) || Administrador();
    $tabla->perms['delete'] = editable($_SESSION['PAGE_FILES_ID_PARENT']) || Administrador();
    $tabla->perms['edit']   = editable($_SESSION['PAGE_FILES_ID_PARENT']) || Administrador();
    $tabla->perms['setup']  = Administrador();  
    $tabla->perms['filter'] = Administrador();  
    $tabla->show_inputsearch = true;