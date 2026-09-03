<?php

$tabla = new myTableClicks( 'CFG_CLICKS' ); 

$id            = new Field();
$id->type      = 'int';
$id->len       = 5;
$id->fieldname = 'ID';
$id->label     = 'Id';   
$id->hide      = true;

$user_id = new Field();
$user_id->fieldname = 'ID_USER';
$user_id->label     = 'Id user';   
$user_id->len       = 9;
$user_id->type      = 'int';

$tb_name=new Field();
$tb_name->fieldname = 'T4BLE_NAME';
$tb_name->label     = 'Tabla';   
$tb_name->type      = 'select';
$tb_name->values    =  array('CFG_LINKS'=>'Links','CLI_PAGES'=>'Páginas','NOT_NEWS'=>'Noticias');
$tb_name->values_all=  $tb_name->values;
$tb_name->editable  =  true; 
$tb_name->len       =  20; 
$tb_name->default_value ='CFG_LINKS';
$tb_name->allowNull = false;
$tb_name->readonly  =  true; 

$item_id = new Field();
$item_id->fieldname = 'ID_ITEM';
$item_id->label     = 'Id Item';   
$item_id->len       = 9;
$item_id->type      = 'int';
$item_id->editable  =  Administrador(); 
$item_id->readonly  =  true; 

$clicks = new Field();
$clicks->fieldname = 'CLICKS';
$clicks->label     = 'Clicks';   
$clicks->len       = 9;
$clicks->type      = 'int';
$clicks->editable  =  Administrador(); 
//$clicks->readonly  =  true; 

$tabla->title =  'Clicks';     
$tabla->showtitle = true;
$tabla->show_inputsearch = false;
$tabla->page = $page;
$tabla->page_num_items = Vars::getArrayVar(CFG::$vars['links']['options'],'num_rows',10);
$tabla->show_empty_rows =true;

$tabla->addCol($id);
$tabla->addCol($user_id);
$tabla->addCol($tb_name);
$tabla->addCol($item_id);
$tabla->addCol($clicks);


$tabla->perms['view']   = Administrador();      
$tabla->perms['reload'] = Administrador();  
$tabla->perms['edit']   = Administrador();
$tabla->perms['add']    = Administrador();
$tabla->perms['delete'] = Administrador();
$tabla->perms['setup']  = Administrador();  

class myTableClicks extends TableMysql{
    
    public function click($params){
        $result = array();
        $result['error']=0;
        $result['msg'] = 'ok';
        $tb = $params['tablename'];
        $id_item = $params['id_item']>0;
        if($tb && $id_item){
            if ($_SESSION['userid']){
                $row = $this->getFieldValue("SELECT ID FROM CFG_CLICKS WHERE  T4BLE_NAME='{$tb}' AND ID_ITEM={$params['id_item']} AND ID_USER={$_SESSION['userid']}");
                if($row>0)  $sql = "UPDATE CFG_CLICKS SET CLICKS=CLICKS+1 WHERE T4BLE_NAME='{$tb}' AND ID_ITEM={$params['id_item']} AND ID_USER={$_SESSION['userid']}";
                      else  $sql = "INSERT INTO CFG_CLICKS (CLICKS,T4BLE_NAME,ID_ITEM,ID_USER) VALUES(1,'{$tb}',{$params['id_item']},{$_SESSION['userid']})";
                 $this->sql_exec($sql);
            }
            $this->sql_exec( "UPDATE $tb  SET CLICKS=CLICKS+1 WHERE ID={$params['id_item']}");
            $result['sql']= "UPDATE $tb  SET CLICKS=CLICKS+1 WHERE ID={$params['id_item']}";
        }
        echo json_encode($result);
    }

}