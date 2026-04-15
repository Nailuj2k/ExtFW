<?php

$id            = new Field();
$id->type      = 'int';
$id->len       = 5;
$id->width     = 15;
$id->fieldname = 'ID';
$id->label     = 'Id';   
$id->hide      = true;

$name = new Field();
$name->fieldname = 'NAME';
$name->label     = 'Nombre';   
$name->len       = 100;
$name->type      = 'varchar';
$name->editable  = true;

$key = new Field();
$key->fieldname = 'APPKEY';
$key->label     = 'Identificador';   
$key->len       = 30;
$key->type      = 'varchar';
$key->editable  = true;
$key->hide  = true;

$tabla = new myTableMysqlApps( 'CFG_APPS' );
$tabla->title =  'Aplicaciones';      
$tabla->showtitle = true;
//$tabla->verbose = true;
$tabla->show_inputsearch = false;
$tabla->page = $page;
$tabla->page_num_items = 15;
$tabla->show_empty_rows =true;
$tabla->addCol($id);

//$tabla->addCol($area);
$tabla->addCol($name);
$tabla->addCol($key);
$tabla->addActiveCol();
$tabla->addWhoColumns();
$tabla->orderby = 'ACTIVE DESC';

$tabla->classname = 'column left';

$tabla->perms['view']   = Administrador(); //$_ACL->userHasRoleName('Area_Admin');
$tabla->perms['reload'] = Administrador(); //$_ACL->userHasRoleName('Area_Admin');
$tabla->perms['edit']   = Administrador(); //$_ACL->userHasRoleName('Area_Admin');
$tabla->perms['add']    = Administrador(); //$_ACL->userHasRoleName('Area_Admin');
$tabla->perms['delete'] = Root(); //$_ACL->userHasRoleName('Area_Admin');
$tabla->perms['setup']  = Root(); //$_ACL->userHasRoleName('Administradores');  

$tabla->detail_tables = ['CFG_APPS_PERMS'];  // Set as master

//if ($_ACL->userHasRoleName('Area_Admin')===false)
if (!Administrador())
  $tabla->where = 'ID IN (SELECT ID_APP 
                          FROM CFG_AREAS_APPS 
                          WHERE ACTIVE = 1 
                          AND ID_AREA IN ( SELECT ID_AREA 
                                           FROM CFG_AREAS_USERS 
                                           WHERE ID_USER = '.$_SESSION['userid'].' 
                                           AND ADMIN = 1 ) )';

class myTableMysqlApps extends TableMysql{

    public function isAdmin(){

        //Get areas with this app
        $sql = 'SELECT ID_AREA FROM CFG_AREAS_APPS WHERE ID_APP = '.$owner->parent_value.' AND ID_AREA IN ('
              .'SELECT ID_AREA FROM CFG_AREAS_USERS WHERE ID_USER = '.$_SESSION['userid'].' AND ADMIN = 1 )';  // areas with this user is admin
        
        // echo $sql; 
        //return $owner->sql_query($sql) ? true : false;
    }

}

class APPS_Events extends defaultTableEvents implements iEvents{ 

    function OnShow($owner){
        //$owner->perms['view'] = true; //( $owner->perms['view'] || $owner->IsAdmin() );
        if ($owner->isAdmin()) $owner->title='Jau';
    }

    function OnInsert($owner,&$result,&$post) { 
        $post['APPKEY'] = $post['APPKEY'] ? Str::sanitizeName($post['APPKEY']) : Str::sanitizeName($post['NAME']);
    }

    function OnUpdate($owner,&$result,&$post) { 
        $post['APPKEY'] = $post['APPKEY'] ? Str::sanitizeName($post['APPKEY']) : Str::sanitizeName($post['NAME']);
    }

    function OnAfterCreate($owner){ 
        if($owner->recordCount()<1){
            $owner->sql_query("INSERT INTO {$owner->tablename} (NAME,APPKEY) VALUES('Notificaciones','notifications')");
            $owner->sql_query("INSERT INTO {$owner->tablename} (NAME,APPKEY) VALUES('Tareas','tasks')");
            $owner->sql_query("INSERT INTO {$owner->tablename} (NAME,APPKEY) VALUES('Documentos','docs')");
        }
    } 


}

$tabla->events = New APPS_Events();
