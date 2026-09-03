<?php 

$tabla = new TableMysql(TB_LOG);

$id            = new Field();
$id->type      = 'int';
$id->width       = 15;
$id->len        = 10;
$id->fieldname = 'ID';
$id->label     = 'Id';   
//$id->hide      = $production;
//$id->readonly  = true;
//$id->attribute = 'unsigned';

$type = new Field();
$type->fieldname = 'TYPE';
$type->label     = 'Tipo';   
$type->len       = 20;
$type->type      = 'select';
//$type->values    = array('0'=>t('UNKNOWN'),'1'=>t('MSG_TO_USER'),'2'=>t('MSG_FROM_USER'),'3'=>t('ORDER_SENT') ,'4'=>'Url' ,'5'=>t('CONTACT_FORM_MSG') );
$type->editable  = Root();
//$type->readonly = true;
$type->filtrable = true;
$type->width     = 20;
//$type->allowNull = false;

$user_id = new Field();
$user_id->fieldname = 'ID_USER';
$user_id->label     = 'User';   
$user_id->len       = 50;
$user_id->width     = 35;
$user_id->type      = 'varchar';
$user_id->editable  = Root();
//$user_id->allowNull = true;
//$user_id->readonly  = true;

$email = new Field();
$email->fieldname = 'EMAIL';
$email->label     = 'Email';   
$email->len       = 100;
$email->width     = 150;
$email->type      = 'varchar';
$email->editable  = Root(); //$_ACL->hasPermission('log_view');
$email->searchable= true;
$email->filtrable = true;
//$email->readonly  = true;

$subject = new Field();
$subject->fieldname = 'SUBJECT';
$subject->label     = 'Asunto';   
$subject->len       = 100;
$subject->width     = 200;
$subject->type      = 'varchar';
$subject->editable  = $_ACL->hasPermission('log_view');
$subject->searchable= true;
$subject->filtrable = true;

$message = new Field();
$message->type       = 'textarea';
$message->fieldname  = 'MESSAGE';
$message->label      = 'Mensaje';   
$message->editable   = $_ACL->hasPermission('log_view');
$message->hide       = true;
$message->searchable = true;
$message->height  = 50;  
$message->filtrable  = false;  
$message->wysiwyg  = false;  

$event_date = new Field();
$event_date->type = 'datetime';
$event_date->fieldname = 'EVENT_DATE';
$event_date->label = 'Fecha';  
$event_date->editable  = false;  
$event_date->default_value    = 'current_timestamp()'; //$tabla->sql_currentdate();


// New column: ALTER TABLE LOG_EVENTS ADD COLUMN EVENT_DATE datetime DEFAULT current_timestamp();
// New column: ALTER TABLE LOG_EVENTS ADD COLUMN ID_USER INT(9);


$tabla->showtitle = true;
$tabla->verbose = false;

$tabla->page = $page;


$tabla->orderby = 'ID DESC'; 

//    $id->hide  = true;
$tabla->addCol($id);

if(isset($LOG_EVENT_WHERE)) {
    $tabla->title = 'Mensajes';
    $type->values    = array('0'=>t('UNKNOWN'),'1'=>t('MSG_RECEIVED','Mensaje recibido'),'2'=>t('MESSAGE_SENT','Mensaje enviado'),'3'=>t('ORDER_SENT','Pedido enviado') ,'4'=>'Url' ,'5'=>t('CONTACT_FORM_MSG','Envío formulario de contacto') );
    $tabla->where=$LOG_EVENT_WHERE;
    $tabla->perms['view']   = true;
    $tabla->perms['edit']  = false; /////////////////////true;
    $tabla->perms['add']   = false; /////////////////////true;
    $tabla->perms['reload']   = true;
    $tabla->page_num_items   = 5;
    $tabla->show_empty_rows  = true;
    $user_id->hide  = true;
    $email->hide  = true;
    $type->default_value    = '2';
    $event_date->editable  = false;
    $type->editable  = false;
    $event_date->editable  = false;
    $user_id->editable  = false;
    $email->editable  = false;
    $subject->editable  = true;
    $message->editable  = true;

}else{
    $subject->readonly  = true;
    $message->readonly  = true;
    $type->default_value    = '1';
    $tabla->title = 'Log';
    $type->values    = array('0'=>t('UNKNOWN'),'1'=>t('MSG_TO_USER'),'2'=>t('MSG_FROM_USER'),'3'=>t('ORDER_SENT') ,'4'=>'Url' ,'5'=>t('CONTACT_FORM_MSG') );
    $tabla->page_num_items = Vars::getArrayVar(CFG::$vars['log_events']['options'],'num_rows',20);
    $tabla->perms['view']   = Administrador() || $_ACL->hasPermission('log_view');
    $tabla->perms['filter'] = Administrador() || $_ACL->hasPermission('log_view');
    $tabla->perms['reload'] = Administrador() || $_ACL->hasPermission('log_view');
    $tabla->perms['delete'] = Administrador();
    $tabla->perms['add']    = Root();
    $tabla->perms['edit']   = Root();
}

$type->values['6'] = 'LDAP_ERROR';
$type->values['7'] = 'AUTH';
$type->values['8'] = 'SQL';

$tabla->perms['setup']  = Root();  

$tabla->addCol($event_date);
$tabla->addCol($type);
$tabla->addCol($user_id);
$tabla->addCol($email);
$tabla->addCol($subject);
$tabla->addCol($message);
//$tabla->addWhoColumns();


class LogEventsEvents extends defaultTableEvents implements iEvents{ 
  function OnShow($owner){
    /*
    $owner->paginator->begin_end_links = false;
    $owner->paginator->prev_next_links = false;
    $owner->paginator->page_links = true;
    $owner->paginator->aux_links  = false; //(!$this->paginator_simple);
    $owner->paginator->label_page = false; //(!$this->paginator_simple);
    $owner->paginator->label_item = false; //(!$this->paginator_simple);
    */
    $owner->paginator->labels['add'] = '<i class="fa fa-envelope"></i> &nbsp; Nuevo mensaje &nbsp;  &nbsp; ';
  }

  
  function OnDrawRow($owner,&$row,&$class){
/*
    if ( in_array( $row['TYPE'] , array_keys( $owner->colByName('TYPE')->list_values) ) )
            $row['TYPE'] = $owner->colByName('TYPE')->list_values[$row['TYPE']];
*/
    //if ( Is_integer$row['ID_USER'] ,  )
    //        $row['TYPE'] = $owner->colByName('TYPE')->list_values[$row['TYPE']];

  }

  function OnInsert($owner,&$result,&$post) { 
     // $post['user_email']=trim($post['user_email']);
     // if(!$post['username']) $post['username']=trim($post['user_email']);
  }

  function OnAfterInsert($owner,&$result,&$post) { 
    // $query = "UPDATE ".$owner->tablename." SET EVENT_DATE = ".$owner->sql_currentdate().", ID_USER=".$_SESSION['userid'].", EMAIL='".$_SESSION['user_email']."',TYPE='2' WHERE ID = {$result['last_insert_id']}";
    // $owner->sql_exec($query);
  }


}

$tabla->events = New LogEventsEvents();
