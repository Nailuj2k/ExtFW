<?php

$tabla = new TableMysql( 'CFG_AREAS_USERS' ); // (str_replace('TABLE_', '', get_file_name(__FILE__)) );

$id            = new Field();
$id->type      = 'int';
$id->len       = 5;
$id->width     = 40;
$id->fieldname = 'ID';
$id->label     = 'Id';   
//$id->hide      = true;

$area            = new Field();
$area->type      = 'int';
$area->len       = 5;
$area->width     = 15;
$area->fieldname = 'ID_AREA';
$area->label     = 'Área';   
$area->hide      = true;

$user            = new Field();
$user->type      = 'select';
$user->len       = 5;
$user->fieldname = 'ID_USER';
$user->label     = 'Usuario';   
$user->source    = 'users';   
//$user->source_all= 'users_all';   

$user->values    = $tabla->toarray('usuarios', TB_USER ,'user_id','CONCAT(username,\' -  \',user_fullname)','',false);
$user->values_all= $tabla->toarray('usuarios', TB_USER ,'user_id','CONCAT(username,\' -  \',user_fullname)','',false);


//$user->values    = $tabla->toarray('doku_admins', TB_USER ,'user_id','username'," WHERE user_id IN (SELECT id_user FROM ".TB_ACL_USER_ROLES." WHERE id_role IN (SELECT role_id FROM ".TB_ACL_ROLES." WHERE role_name IN ('HulammWare')))",false);
//OR
//SELECT from area  ACL //add default area in install

$user->multiselect = true;
$user->editable  = true;
$user->searchable      = true;

$avatar = new Field();
$avatar->fieldname  = 'AVATAR';
$avatar->label      = 'Avatar';   
$avatar->len        = 7;
$avatar->width      = 85;
$avatar->type       = 'varchar';
$avatar->editable = false;
$avatar->calculated = true;
$avatar->sortable = false;

$admin = new Field();
$admin->type      = 'bool';
$admin->fieldname = 'ADMIN';
$admin->label     = 'Administrador';   
$admin->editable = $_ACL->hasPermission('area_admin');
$admin->default_value = '0';
$admin->editable  = true;
$admin->filtrable = true;  
$admin->width = 25;

$tabla->title =  'Usuarios';       // str_replace('CFG_', '', $tabla->tablename);
$tabla->showtitle = true;
$tabla->show_inputsearch = false;
$tabla->page = $page;
$tabla->page_num_items = 15;
$tabla->show_empty_rows =true;
$tabla->addCol($id);
$tabla->addCol($area);
$tabla->addCol($user);
$tabla->addCol($avatar);
$tabla->addCol($admin);
$tabla->addActiveCol();
$tabla->addWhoColumns();
$tabla->orderby = 'ACTIVE DESC';

$tabla->perms['view']   = $_ACL->userHasRoleName('Area_Admin') || $_ACL->userHasRoleName('Area_User');
$tabla->perms['reload'] = $_ACL->userHasRoleName('Area_Admin') || $_ACL->userHasRoleName('Area_User');
$tabla->perms['edit']   = $_ACL->userHasRoleName('Area_Admin') || $_ACL->userHasRoleName('Area_User');
$tabla->perms['add']    = $_ACL->userHasRoleName('Area_Admin') || $_ACL->userHasRoleName('Area_User');
$tabla->perms['delete'] = $_ACL->userHasRoleName('Area_Admin') || $_ACL->userHasRoleName('Area_User');
$tabla->perms['setup']  = $_ACL->userHasRoleName('Administradores');  

$tabla->setParent('ID_AREA',$parent);   // Set as detail

$tabla->classname = 'column right';

class AREAS_USERS_Events extends defaultTableEvents implements iEvents{ 
    function OnCalculate($owner,&$row) { 
        $user = $owner->query2array('SELECT * FROM '.TB_USER.' WHERE user_id='.$row['ID_USER']);
        //  $_avatar_default_image = 'https://'.$_SERVER['HTTP_HOST'].SCRIPT_DIR."/_images_/avatars/avatar.gif";
        $_avatar_default_image = "/_images_/avatars/avatar.gif";
        $_avatar_size  = 40;
        $_dirphotos = './media/avatars/';
        $_img = $user[0]['user_url_avatar'];
        $_email = $user[0]['user_email'];                   
        if (!$_img || !file_exists($_dirphotos.$_img)){ 
            $_url = $_avatar_default_image;  //"https://www.gravatar.com/avatar.php?gravatar_id=".md5($_email)."&default=".urlencode($_avatar_default_image)."&size=".$_avatar_size; 
            $row['AVATAR']='<img class="avatar"   src="'.$_url.'"  title="'.$row['user_email'].'" border="0">';  //
        }else{
            $_url = $_dirphotos.$_img;
            $row['AVATAR']='<img class="avatar"   src="'.$_url.'?hash='.time().'" title="'.$row['user_email'].'"  border="0">';  
        }       
    }

    function OnDrawRow($owner,&$row,&$class){
    }

 
    function OnBeforeShow($owner){
    }
    
    function IsAreaAdmin($owner,$id) {          //Get areas with this user is admin
        $sql = 'SELECT count(ID_AREA) AS RESULT FROM CFG_AREAS_USERS WHERE ID_USER = '.$id.' AND ADMIN = 1 ';
        return $owner->getFieldValue($sql);
    }

    function IsAreaUser($owner,$id) {          //Get areas with this user
        $sql = 'SELECT count(ID_AREA) FROM CFG_AREAS_USERS WHERE ID_USER = '.$id;
        return $owner->getFieldValue($sql);
    }

    function OnInsert($owner,&$result,&$post) { 
        global $_ACL;
        $_ACL->updateUserRole( $post['ID_USER'] , 'Area_User'  , true );
        $_ACL->updateUserRole( $post['ID_USER'] , 'Area_Admin' , ($post['ADMIN']==1) );
    }

    function OnUpdate($owner,&$result,&$post) { 
        global $_ACL;
        $_ACL->updateUserRole( $post['ID_USER'] , 'Area_User'  , true );
        $_ACL->updateUserRole( $post['ID_USER'] , 'Area_Admin' , ( ($this->IsAreaAdmin($owner,$row['ID_USER'])>1) || $post['ADMIN']==1) );  
        // Check if is admin in other areas
    }

    function OnDelete($owner,&$result,$id){
        global $_ACL;
        $row = $owner->getRow($id);
        $_ACL->updateUserRole( $row['ID_USER'] , 'Area_User'  , ($this->IsAreaUser($owner,$row['ID_USER'])>1) );
        $_ACL->updateUserRole( $row['ID_USER'] , 'Area_Admin' , ($this->IsAreaAdmin($owner,$row['ID_USER'])>1));    // Check if is admin in other areas
        //FIX delete from groups and perms if noAdmin
    }

}

$tabla->events = New AREAS_USERS_Events();
