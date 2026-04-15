<?php

$id            = new Field();
$id->type      = 'int';
$id->len       = 5;
$id->width     = 15;
$id->fieldname = 'ID';
$id->label     = 'Id';   
$id->hide      = true;

$app            = new Field();
$app->type      = 'int';
$app->len       = 5;
$app->width     = 15;
$app->fieldname = 'ID_APP';
$app->label     = 'Aplicación';   
$app->hide      = true;

$name = new Field();
$name->fieldname = 'NAME';
$name->label     = 'Nombre';   
$name->len       = 100;
$name->type      = 'varchar';
$name->editable  = true;

$key = new Field();
$key->fieldname = 'PERMKEY';
$key->label     = 'Identificador';   
$key->len       = 30;
$key->type      = 'varchar';
$key->editable  = true;
$key->hide  = true;

$default = new Field();
$default->type      = 'bool';
$default->fieldname = 'BYDEFAULT';
$default->label     = 'Por omisión';   
$default->editable = true;
$default->default_value = '0';
$default->editable  = true;
$default->filtrable = true;  
$default->width = 85;

$description = new Field();
$description->fieldname = 'DESCRIPTION';
$description->label     = 'Descripción';   
$description->type      = 'textarea';
$description->editable  = true;
$description->wysiwyg  = false;

$tabla = new TableMysql( 'CFG_APPS_PERMS' ); 
$tabla->title =  'Permisos';      
$tabla->showtitle = true;
$tabla->show_inputsearch = false;
$tabla->page = $page;
$tabla->page_num_items = 15;
$tabla->show_empty_rows =true;
$tabla->addCol($id);
$tabla->addCol($app);
$tabla->addCol($name);
$tabla->addCol($key);
$tabla->addCol($description);
$tabla->addCol($default);
$tabla->addActiveCol();
$tabla->addWhoColumns();
$tabla->orderby = 'ACTIVE DESC';

$tabla->classname = 'column right';

$tabla->perms['view']   = Administrador(); //$_ACL->userHasRoleName('Area_Admin');
$tabla->perms['reload'] = Administrador(); //$_ACL->userHasRoleName('Area_Admin');
$tabla->perms['edit']   = Administrador(); //$_ACL->userHasRoleName('Area_Admin');
$tabla->perms['add']    = Administrador(); //$_ACL->userHasRoleName('Area_Admin');
$tabla->perms['delete'] = Root(); //$_ACL->userHasRoleName('Area_Admin');
$tabla->perms['setup']  = Root(); //$_ACL->userHasRoleName('Administradores');  

$tabla->setParent('ID_APP',$parent);  

class APPS_PERMS_Events extends defaultTableEvents implements iEvents{ 

    function OnInsert($owner,&$result,&$post) { 
        $post['PERMKEY'] = $post['PERMKEY'] ? Str::sanitizeName($post['PERMKEY']) : Str::sanitizeName($post['NAME']);
    }
  
    function OnUpdate($owner,&$result,&$post) { 
        $post['PERMKEY'] = $post['PERMKEY'] ? Str::sanitizeName($post['PERMKEY']) : Str::sanitizeName($post['NAME']);
    }

    function OnAfterCreate($owner){ 
        if($owner->recordCount()<1){
            $owner->sql_query("INSERT INTO {$owner->tablename} (ID_APP,NAME,PERMKEY,BYDEFAULT) VALUES(1,'Enviar cualquier destino','send',0)");
            $owner->sql_query("INSERT INTO {$owner->tablename} (ID_APP,NAME,PERMKEY,BYDEFAULT) VALUES(1,'Enviar al área','send_area',0)");
            $owner->sql_query("INSERT INTO {$owner->tablename} (ID_APP,NAME,PERMKEY,BYDEFAULT) VALUES(1,'responsable área','receive_area',0)");
            $owner->sql_query("INSERT INTO {$owner->tablename} (ID_APP,NAME,PERMKEY,BYDEFAULT) VALUES(1,'Recibir','receive',1)");
            $owner->sql_query("INSERT INTO {$owner->tablename} (ID_APP,NAME,PERMKEY,BYDEFAULT) VALUES(2,'Crear','create',0)");
            $owner->sql_query("INSERT INTO {$owner->tablename} (ID_APP,NAME,PERMKEY,BYDEFAULT) VALUES(2,'Asignar','assign',0)");
            $owner->sql_query("INSERT INTO {$owner->tablename} (ID_APP,NAME,PERMKEY,BYDEFAULT) VALUES(2,'Cerrar','close',0)");
            $owner->sql_query("INSERT INTO {$owner->tablename} (ID_APP,NAME,PERMKEY,BYDEFAULT) VALUES(3,'Ver','view',1)");
            $owner->sql_query("INSERT INTO {$owner->tablename} (ID_APP,NAME,PERMKEY,BYDEFAULT) VALUES(3,'Publicar','publish',0)");
        }
    } 

}

$tabla->events = New APPS_PERMS_Events();
