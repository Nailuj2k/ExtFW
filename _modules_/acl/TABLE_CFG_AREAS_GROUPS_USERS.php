<?php

$tabla = new TableMysql( 'CFG_AREAS_GROUPS_USERS' ); // (str_replace('TABLE_', '', get_file_name(__FILE__)) );

$id            = new Field();
$id->type      = 'int';
$id->len       = 5;
$id->width     = 15;
$id->fieldname = 'ID';
$id->label     = 'Id';   
$id->width     = 15;
$id->hide      = true;

$area_group            = new Field();
$area_group->type      = 'hidden';
$area_group->len       = 5;
$area_group->fieldname = 'ID_AREA_GROUP';
$area_group->label     = 'Área Grupo';   
$area_group->value     = $parent;
$area_group->width     = 85;
$area_group->hide      = true;

$user            = new Field();
$user->type      = 'select';
$user->len       = 5;
$user->width     = 350;
$user->fieldname = 'ID_USER';
$user->label     = 'Usuario';   
$user->editable  = true;
$user->values    = $tabla->toarray('users', ' SELECT user_id AS ID, CONCAT(username,\' -  \',user_fullname) AS NAME '
                                         . ' FROM '.TB_USER 
                                         . ' WHERE user_id IN ( '
                                         . '   SELECT ID_USER FROM CFG_AREAS_USERS WHERE ID_AREA = ( '
                                         . '     SELECT ID_AREA FROM CFG_AREAS_GROUPS WHERE ID = '.$parent
                                         . '   ) '
                                         . ' ) '   
                                );      

$avatar = new Field();
$avatar->fieldname  = 'AVATAR';
$avatar->label      = 'Avatar';   
$avatar->len        = 7;
$avatar->width      = 85;
$avatar->type       = 'varchar';
$avatar->editable = false;
$avatar->calculated = true;
$avatar->sortable = false;

$tabla->title =  'Usuarios '.$parent;    
$tabla->showtitle = true;
$tabla->show_inputsearch = false;
$tabla->page = $page;
$tabla->page_num_items = 15;
$tabla->show_empty_rows =true;
$tabla->addCol($id);
$tabla->addCol($area_group);
$tabla->addCol($user);
$tabla->addCol($avatar);
$tabla->addActiveCol();
$tabla->addWhoColumns();
$tabla->orderby = 'ACTIVE DESC';

$tabla->perms['view']   = $_ACL->userHasRoleName('Area_Admin') || $_ACL->userHasRoleName('Area_User');
$tabla->perms['reload'] = $_ACL->userHasRoleName('Area_Admin') || $_ACL->userHasRoleName('Area_User');
$tabla->perms['edit']   = $_ACL->userHasRoleName('Area_Admin') || $_ACL->userHasRoleName('Area_User');
$tabla->perms['add']    = $_ACL->userHasRoleName('Area_Admin') || $_ACL->userHasRoleName('Area_User');
$tabla->perms['delete'] = $_ACL->userHasRoleName('Area_Admin') || $_ACL->userHasRoleName('Area_User');
$tabla->perms['setup']  = $_ACL->userHasRoleName('Administradores');  

$tabla->setParent('ID_AREA_GROUP',$parent);   // Set as detail
//$tabla->detail_tables=array('CFG_APPS_PERMS');  // Set as master
$tabla->classname = 'column right';

class AREAS_GROUPS_USERS_Events extends defaultTableEvents implements iEvents{ 
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

    function OnDrawCell($owner,&$row,&$col,&$cell){
        global $prefix;
        if(in_array($owner->state,array('update','insert'))){
            if($col->fieldname=='ID_USER') {
                /**
                    $col->values = $owner->toarray('users', ' SELECT user_id AS ID, CONCAT(user_name,\' -  \',user_fullname) AS NAME '
                                                        . ' FROM '.TB_USER 
                                                        . ' WHERE user_id IN ( '
                                                        . '   SELECT ID_USER FROM CFG_AREAS_USERS WHERE ID_AREA = ( '
                                                        . '     SELECT ID_AREA FROM CFG_AREAS_GROUPS WHERE ID = '.$owner->parent_value
                                                        . '   ) '
                                                        . ' ) ' );      
                **/
            }
        }
    }

}

$tabla->events = New AREAS_GROUPS_USERS_Events();