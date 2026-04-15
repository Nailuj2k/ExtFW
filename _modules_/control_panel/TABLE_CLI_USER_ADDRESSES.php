<?php 

//$tabla = new Shopping_Cart('CLI_ORDERS');
$tabla = new myTableMysql('CLI_USER_ADDRESSES');
$tabla->output='table';
$tabla->page = $page;

$id            = new Field();
$id->type      = 'int';
$id->width     = 50;
$id->fieldname = 'USER_ADDRESS_ID';
$id->label     = 'Id';   
$id->len = 12;
$id->hide    = true;

$id_user = new Field();
$id_user->fieldname = 'id_user';
$id_user->label     = t('USERNAME'); 
$id_user->len     = 10;
$id_user->type      = 'int';
$id_user->hide      = true;

$name            = new Field();
$name->type      = 'varchar';
$name->fieldname = 'NAME';
$name->label     = t('FULL_NAME');
$name->len       = 100;
$name->editable  = true;
$name->filtrable = true;

$phone = new Field();
$phone->type      = 'varchar';
$phone->len       = 45;
$phone->width     = 12;
$phone->fieldname = 'PHONE';
$phone->label     = t('PHONE');
//$phone->hide   = true;
$phone->editable   = true;
//$phone->fieldset = 'ubicacion';

$pais = new Field();
$pais->len       = 5;
$pais->width     = 120;
$pais->fieldname = 'ID_COUNTRY';
$pais->label     = t('COUNTRY');
$pais->type      = 'select';
$pais->values    = $tabla->toarray('paises' ,    'SELECT pais_id AS ID, pais_name AS NAME FROM CFG_PAIS',true); 
$pais->editable  = true;
$pais->hide  = true;
$pais->default_value=724;
//$pais->fieldset = 'ubicacion';
$pais->filtrable = true;
$pais->child_ajax_url   = '/control_panel/ajax/op=list';
$pais->child_fieldname  = 'ID_STATE';
//$pais->child_source_sql = "SELECT provincia_id AS id, provincia_name AS name FROM CFG_PROVINCIA WHERE id_pais=[value]";
$pais->child_source_sql = "provincia_from_pais";

//$pais->child_source_sql = "SELECT provincia_id AS id, provincia_name AS name FROM CFG_PROVINCIA WHERE id_pais=[value] AND NOT FIND_IN_SET( provincia_id,(SELECT CONTENT FROM CLI_DESTINOS WHERE ID_FIELD=2 AND ACTIVE<>'1'))";

$provincia = new Field();
$provincia->len       = 5;
$provincia->width     = 120;
$provincia->fieldname = 'ID_STATE';
$provincia->label     = t('STATE');
$provincia->type      = 'select';
$provincia->values    = $tabla->toarray('provincias' ,  'SELECT provincia_id AS ID, provincia_name AS NAME FROM CFG_PROVINCIA',true); 
$provincia->values_all= $tabla->toarray('provincias' ,  'SELECT provincia_id AS ID, provincia_name AS NAME FROM CFG_PROVINCIA',true); // WHERE provincia_id IN (SELECT DISTINCT(id_provincia) FROM '.TB_USER.')',true); 
$provincia->editable  = true;
//$provincia->hide=true;
//$provincia->fieldset = 'ubicacion';
$provincia->filtrable = true;
$provincia->child_ajax_url   = '/control_panel/ajax/op=list';
$provincia->child_fieldname  = 'ID_CITY';
//$provincia->child_source_sql = "SELECT municipio_id AS id, municipio_name AS name FROM CFG_MUNICIPIO WHERE id_provincia='[value]'";
$provincia->child_source_sql = "municipio_from_provincia";
//$provincia->child_allow_null=true;

$municipio = new Field();
$municipio->len       = 5;
$municipio->width     = 120;
$municipio->fieldname = 'ID_CITY';
$municipio->label     = t('CITY');
$municipio->type      = 'select';
$municipio->values    = $tabla->toarray('municipios' ,  'SELECT municipio_id AS ID, municipio_name AS NAME FROM CFG_MUNICIPIO WHERE municipio_id IN (SELECT DISTINCT(ID_CITY) FROM '.$tabla->tablename.')',true); 
$municipio->values_all= $tabla->toarray('municipios' ,  'SELECT municipio_id AS ID, municipio_name AS NAME FROM CFG_MUNICIPIO WHERE municipio_id IN (SELECT DISTINCT(ID_CITY) FROM '.$tabla->tablename.')',true); 
$municipio->editable  = true;
//$municipio->hide=true;
//$municipio->fieldset = 'ubicacion';
$municipio->filtrable = true;

if(CFG::$vars['users']['field']['county']){
    $municipio->child_ajax_url   =  '/control_panel/ajax/op=list';
    $municipio->child_fieldname  = 'ID_COUNTY';
    //$municipio->child_source_sql = "SELECT localidad_id AS id, concat(localidad_cp,' ',localidad_name) AS name FROM CFG_LOCALIDAD WHERE id_municipio='[value]'";
    $municipio->child_source_sql = "localidad_from_municipio";
    $municipio->child_allow_null=true;
}

$localidad = new Field();
$localidad->width     = 120;
$localidad->fieldname = 'ID_COUNTY';
$localidad->label     = t('COUNTY');
if(CFG::$vars['users']['field']['county']){
    $localidad->len       = 5;
    $localidad->type      = 'select';
    $localidad->values    = $tabla->toarray('localidades' ,  "SELECT localidad_id AS ID, concat(localidad_cp,' ',localidad_name) AS NAME FROM CFG_LOCALIDAD WHERE localidad_id IN (SELECT DISTINCT(ID_COUNTY) FROM ".$tabla->tablename.")",true); 
    $localidad->values_all= $tabla->toarray('localidades' ,  'SELECT localidad_id AS ID, localidad_name AS NAME FROM CFG_LOCALIDAD WHERE localidad_id IN (SELECT DISTINCT(ID_COUNTY) FROM '.$tabla->tablename.')',true); 
}else{
    $localidad->len       = 50;
    $localidad->type      = 'varchar';
}
$localidad->allowNull  = true;
$localidad->editable  = true;
//$localidad->fieldset = 'ubicacion';
$localidad->filtrable = true;
$localidad->hide=true;

$zip            = new Field();
$zip->type      = 'varchar';
$zip->fieldname = 'ZIP';
$zip->label     = t('ZIP');   
$zip->len       = 10;
//$zip->hide      = true;
$zip->editable  = true;
//$zip->fieldset = 'ubicacion';
$zip->filtrable = true;

$address = new Field();
$address->type      = 'varchar';
$address->len       = 100;
$address->width     = 200;
$address->fieldname = 'ADDRESS';
$address->label     = t('ADDRESS');   
$address->editable  = true;
$address->hide  = true;
//$address->fieldset = 'ubicacion';

$card_id            = new Field();
$card_id->type      = 'varchar';
$card_id->fieldname = 'CARD_ID';
$card_id->label     = t('CARD_ID');   
$card_id->len       = 10;
$card_id->editable  = true;
$card_id->filtrable = true;
$card_id->hide = true;
$card_id->required  = CFG::$vars['shop']['card_id']['required'];


$email            = new Field();
$email->type      = 'varchar';
$email->fieldname = 'EMAIL';
$email->label     = t('EMAIL');   
$email->len       = 100;
$email->width     = 200;
$email->editable  = true;
$email->required  = true;
//$email->hide      = true;
$email->filtrable = true;

$tipo= new Field();
$tipo->type      = 'int';
$tipo->len       = 5;
$tipo->fieldname = 'ID_TYPE';
$tipo->label     = t('TYPE').$_ARGS['type'];
$tipo->type      = 'select';
$tipo->values    = array('1'=>'Envío','2'=>'Facturación');         
if($_ARGS['type']) $tipo->default_value=$_ARGS['type'];
$tipo->editable  = true; //Administrador() ; 
$tipo->width = 100;

$bydefault = new Field();
$bydefault->fieldname = 'BYDEFAULT';
$bydefault->label     = t('BY_DEFAULT');   
$bydefault->type      = 'bool';
$bydefault->width     = 20;
$bydefault->editable  = true;
$bydefault->sortable  = true;
$bydefault->default_value = 0;

if(!Administrador() || MODULE=='login' || $_SESSION['userid']) {

     $card_id->default_value = $tabla::getFieldValue('SELECT user_card_id FROM '.TB_USER.' WHERE user_id = '.$_SESSION['userid']);
   //  $row['IDENTIFIER'] =  $owner::getFieldValue('SELECT IDENTIFIER FROM CLI_CUSTOMERS WHERE CUSTOMER_ID = '.$row['CUSTOMER_ID']);
}


$tabla->addCol($id);
$tabla->addCol($id_user);
$tabla->addCol($name);
$tabla->addCol($card_id);
$tabla->addCol($phone);
$tabla->addCol($email);
$tabla->addCol($pais);
$tabla->addCol($provincia);
$tabla->addCol($municipio);
$tabla->addCol($localidad);
$tabla->addCol($address);
$tabla->addCol($zip);
$tabla->addCol($tipo);
$tabla->addCol($bydefault);

//$tabla->colByName('PHONE')->hide=true;

$tabla->addWhoColumns();
$tabla->addActiveCol();

$tabla->perms['view'] = true;  

$tabla->setParent('id_user', $parent); 

if(!Administrador() || MODULE=='login') {
   $tabla->where = 'id_user='.$_SESSION['userid'];
   $tabla->title = 'Mis Direcciones';
   $tabla->showtitle=false;
   $tabla->perms['edit']   = $parent==$_SESSION['userid'];
   $tabla->perms['add']    = $parent==$_SESSION['userid'];
   $tabla->perms['delete'] = $parent==$_SESSION['userid'];
}else{
   $tabla->title = 'Direcciones';
   $tabla->showtitle=true;
   $tabla->perms['edit']   = Administrador() ;
   $tabla->perms['add']    = Administrador();
   $tabla->perms['delete']    = Administrador();
}



$tabla->perms['setup']  = Root();  
$tabla->perms['reload'] = Root();  
$tabla->perms['pdf']    = false; // Administrador();  
$tabla->perms['print']  = false;  
$tabla->perms['filter'] = true;  
$tabla->page_num_items = 4;
$tabla->show_inputsearch =false;



$tabla->orderby = 'USER_ADDRESS_ID DESC';

class myTableMysql extends TableMysql{


    public function getAddressAsString($params){
        $result = array();
        $result['error']=0;       
        $id_county = self::getFieldValue('SELECT ID_COUNTY FROM '.$this->tablename." WHERE id_user = ".$_SESSION['userid']." AND ID_TYPE='".$params['type']."' AND ".($params['id'] ? " USER_ADDRESS_ID=".$params['id'] : " BYDEFAULT='1' "));

        if(CFG::$vars['users']['field']['county']){
            if($id_county){ 
                $sql = "SELECT ca.*,CONCAT(d.localidad_name,'<br />',ca.ZIP,' ',c.municipio_name,'<br />',b.provincia_name,'<br />',a.pais_name) AS SHIPPING_ADDRESS "   // d.localidad_name,'<br />',d.localidad_cp,'<br />',
                     ." FROM ".$this->tablename." ca,CFG_PAIS a,CFG_PROVINCIA b,CFG_MUNICIPIO c,CFG_LOCALIDAD d " 
                     ." WHERE a.pais_id    = ca.ID_COUNTRY "
                     ." AND b.provincia_id = ca.ID_STATE "                //FIX WHERE order_id ???
                     ." AND c.municipio_id = ca.ID_CITY "
                     ." AND d.localidad_id = ca.ID_COUNTY "
                     ." AND ca.id_user = ".$_SESSION['userid']." AND ca.ID_TYPE='".$params['type']."'"
                     ." AND ".($params['id'] ? " USER_ADDRESS_ID=".$params['id'] : " BYDEFAULT='1' ")
                     ." LIMIT 1 ";  // AND ca.USER_ADDRESS_ID = ".$params['id']."
                
            }else{
                $sql = "SELECT ca.*,CONCAT(ca.ZIP,' ',c.municipio_name,'<br />',b.provincia_name,'<br />',a.pais_name) AS SHIPPING_ADDRESS "   // d.localidad_name,'<br />',d.localidad_cp,'<br />',
                     ." FROM ".$this->tablename." ca,CFG_PAIS a,CFG_PROVINCIA b,CFG_MUNICIPIO c " //,CFG_LOCALIDAD d "
                     ." WHERE a.pais_id = ca.ID_COUNTRY "
                     ." AND b.provincia_id = ca.ID_STATE "                //FIX WHERE order_id ???
                     ." AND c.municipio_id = ca.ID_CITY "
        //             ." AND (d.localidad_id = ca.ID_COUNTY OR ca.ID_COUNTY ='')"
                     ." AND ca.id_user = ".$_SESSION['userid']." AND ca.ID_TYPE='".$params['type']."'"
                     ." AND ".($params['id'] ? " USER_ADDRESS_ID=".$params['id'] : " BYDEFAULT='1' ")
                     ." LIMIT 1 ";  // AND ca.USER_ADDRESS_ID = ".$params['id']."
            }
        }else{
                $sql = "SELECT ca.*,CONCAT(ca.ZIP,' ',ca.ID_COUNTY,'<br />',c.municipio_name,'<br />',b.provincia_name,'<br />',a.pais_name) AS SHIPPING_ADDRESS "   // d.localidad_name,'<br />',d.localidad_cp,'<br />',
                     ." FROM ".$this->tablename." ca,CFG_PAIS a,CFG_PROVINCIA b,CFG_MUNICIPIO c " //,CFG_LOCALIDAD d "
                     ." WHERE a.pais_id   = ca.ID_COUNTRY "
                     ." AND b.provincia_id = ca.ID_STATE "                //FIX WHERE order_id ???
                     ." AND c.municipio_id = ca.ID_CITY "
                     ." AND ca.id_user = ".$_SESSION['userid']." AND ca.ID_TYPE='".$params['type']."'"
                     ." AND ".($params['id'] ? " USER_ADDRESS_ID=".$params['id'] : " BYDEFAULT='1' ")
                     ." LIMIT 1 ";  // AND ca.USER_ADDRESS_ID = ".$params['id']."
        }


        $result['sql']=$sql;
        $row = $this->getFieldsValues($sql);
        $result['row']=$row;
        $result['address'] = '<b>'.$row['NAME'].'</b><br />'.$row['ADDRESS'].'<br />'. $row['SHIPPING_ADDRESS'].'<br />Tel: '.$row['PHONE'];
        if (CFG::$vars['shop']['card_id']['required']) $result['address'] .= '<br />'.t('CARD_ID').': '.$row['CARD_ID'];
        $result['id']=$row['USER_ADDRESS_ID'];
        echo json_encode($result);
    }

}

class addressesEvents extends defaultTableEvents implements iEvents{ 
  
    public function OnBeforeShow($owner){
        $sql ='SELECT * FROM CLI_USER_ADDRESSES WHERE id_user='.$_SESSION['userid'];
        $xx = Table::sqlQuery($sql);
        if($xx){

        }else{
             $sql1 = "INSERT INTO CLI_USER_ADDRESSES (id_user,NAME,CARD_ID,PHONE,EMAIL,ID_COUNTRY,ID_STATE,ID_CITY,ID_COUNTY,ADDRESS,ZIP,ID_TYPE,BYDEFAULT,ACTIVE ) "
                    ."SELECT CUSTOMER_ID,NAME,CARD_ID,PHONE,EMAIL,ID_COUNTRY,ID_STATE,ID_CITY,ID_COUNTY,ADDRESS,ZIP, '1','1','1' FROM CLI_ORDERS WHERE CUSTOMER_ID=".$_SESSION['userid'];
             Table::sqlExec($sql1);
             $sql2 = "INSERT INTO CLI_USER_ADDRESSES (id_user,NAME,CARD_ID,PHONE,EMAIL,ID_COUNTRY,ID_STATE,ID_CITY,ID_COUNTY,ADDRESS,ZIP,ID_TYPE,BYDEFAULT,ACTIVE ) "
                    ."SELECT CUSTOMER_ID,INVOICE_NAME,INVOICE_CARD_ID,INVOICE_PHONE,INVOICE_EMAIL,INVOICE_ID_COUNTRY,INVOICE_ID_STATE,INVOICE_ID_CITY,INVOICE_ID_COUNTY,INVOICE_ADDRESS,INVOICE_ZIP, '2','1','1' FROM CLI_ORDERS WHERE CUSTOMER_ID=".$_SESSION['userid'];
             Table::sqlExec($sql2);
        }
    }

   
  function OnDrawRow($owner,&$row,&$class){  }
  function OnBeforeSaveFile($owner,&$col, $local_file,&$result ){  }
  function OnCalculate($owner,&$row){  }
  function OnAfterShowForm($owner,&$form,$id){  }
  function OnDelete($owner,&$result,$id){  }
  function OnUpdateOrInsert($owner,&$result,&$post){
        if($post['BYDEFAULT']==1){
           $owner->sql_exec('UPDATE '.$owner->tablename." SET BYDEFAULT=0 WHERE ID_TYPE='{$post['ID_TYPE']}' AND id_user={$_SESSION['userid']}");
        }else{
            $r = $owner->recordCount('SELECT count(*) FROM '.$owner->tablename." WHERE ID_TYPE = '{$post['ID_TYPE']}' AND BYDEFAULT = 1 AND id_user={$_SESSION['userid']}");
            if ($r<1) $post['BYDEFAULT'] = 1;
        }
  }
  function OnInsert($owner,&$result,&$post){ 
      $this->OnUpdateOrInsert($owner,$result,$post);
  }
  function OnUpdate($owner,&$result,&$post){
     if ( Root() || Administrador() ){
          $owner->perms['edit'] = true;
     }else{
          $owner->perms['edit'] = ($post['id_user']==$_SESSION['userid']); 
     }  

    $this->OnUpdateOrInsert($owner,$result,$post);
  }
    /*
    Columna: USER_ADDRESS_ID int(12) unsigned NOT NULL auto_increment
    Columna: id_user int(10)
    Columna: NAME varchar(100)
    Columna: PHONE varchar(45)
    Columna: ID_COUNTRY int(5)
    Columna: ID_STATE int(5)
    Columna: ID_CITY int(5)
    Columna: ID_COUNTY int(5)
    Columna: ADDRESS varchar(100)
    Columna: ZIP varchar(10)
    Columna: CARD_ID varchar(10)
    Columna: ID_TYPE int(5)
    Columna: BYDEFAULT int(1)
    Columna: ACTIVE int(1)
    Columna: EMAIL varchar(100)
    */
  function OnAfterUpdate($owner,&$result,&$post){
     /**
      if($post['ID_TYPE']==2){ 
          $r0 = $owner->recordCount('SELECT count(*) FROM '.$owner->tablename." WHERE ID_TYPE = '1' AND id_user={$_SESSION['userid']}");
          $sql ='INSERT INTO '.$owner->tablename." (id_user,NAME,PHONE,ID_COUNTRY,ID_STATE,ID_CITY,ID_COUNTY,ADDRESS,ZIP,CARD_ID,EMAIL,BYDEFAULT,ID_TYPE) "
                                        ."SELECT '{$_SESSION['userid']}',NAME,PHONE,ID_COUNTRY,ID_STATE,ID_CITY,ID_COUNTY,ADDRESS,ZIP,CARD_ID,EMAIL,'1','1' FROM {$owner->tablename} WHERE ID_TYPE='2' AND USER_ADDRESS_ID = {$post['USER_ADDRESS_ID']}";
          //$result['msg']=$sql;
          if ($r0<1) $owner->sql_query($sql);
      }else  if($post['ID_TYPE']==1){ 
          $r0 = $owner->recordCount('SELECT count(*) FROM '.$owner->tablename." WHERE ID_TYPE = '2' AND id_user={$_SESSION['userid']}");
          $sql ='INSERT INTO '.$owner->tablename." (id_user,NAME,PHONE,ID_COUNTRY,ID_STATE,ID_CITY,ID_COUNTY,ADDRESS,ZIP,CARD_ID,EMAIL,BYDEFAULT,ID_TYPE) "
                                        ."SELECT '{$_SESSION['userid']}',NAME,PHONE,ID_COUNTRY,ID_STATE,ID_CITY,ID_COUNTY,ADDRESS,ZIP,CARD_ID,EMAIL,'1','2' FROM {$owner->tablename} WHERE ID_TYPE='1' AND USER_ADDRESS_ID = {$post['USER_ADDRESS_ID']}";
          //$result['msg']=$sql;
          if ($r0<1) $owner->sql_query($sql);
      }else{
       ****/
          //$result['msg']=print_r($post,true);
           $r1 = $owner->recordCount('SELECT count(*) FROM '.$owner->tablename." WHERE ID_TYPE = '1' AND BYDEFAULT = 1 AND id_user={$_SESSION['userid']}");
           $r2 = $owner->recordCount('SELECT count(*) FROM '.$owner->tablename." WHERE ID_TYPE = '2' AND BYDEFAULT = 1 AND id_user={$_SESSION['userid']}");
           if ($r1<1) $owner->sql_exec('UPDATE '.$owner->tablename." SET BYDEFAULT=1 WHERE ID_TYPE='1' AND id_user={$_SESSION['userid']}");
           if ($r2<1) $owner->sql_exec('UPDATE '.$owner->tablename." SET BYDEFAULT=1 WHERE ID_TYPE='2' AND id_user={$_SESSION['userid']}");
     // }
 
        //if ( Root() || Administrador() ){

        //}else{
            if($_SESSION['userid'] && $post['CARD_ID'] && $post['id_user'] && $post['id_user']>0 && $post['id_user']==$_SESSION['userid']){
                  //$result['msg'] = 'UPDATE '.$owner->tablename." SET CARD_ID='".$post['CARD_ID']."' WHERE id_user={$post['id_user']} AND IFNULL(CARD_ID,'')=''";
                  $owner->sql_exec('UPDATE '.$owner->tablename." SET CARD_ID='".$post['CARD_ID']."' WHERE id_user={$post['id_user']} AND IFNULL(CARD_ID,'')=''");
            }
        //}
 
    }

}

$tabla->format_item['body'] = '<table class="zebra">
                                  <tr><td>USER_ADDRESS_ID</td><td>[USER_ADDRESS_ID]</td></tr>
                                  <tr><td>NAME</td><td>[NAME]</td></tr>
                                  <tr><td>PHONE</td><td>[PHONE]</td></tr>
                                  <tr><td>EMAIL</td><td>[EMAIL]</td></tr>
                                  <tr><td>ID_COUNTRY</td><td>[ID_COUNTRY]</td></tr>
                                  <tr><td>ID_STATE</td><td>[ID_STATE]</td></tr>
                                  <tr><td>ID_CITY</td><td>[ID_CITY]</td></tr>
                                  <tr><td>ID_COUNTY</td><td>[ID_COUNTY]</td></tr>
                                  <tr><td>ADDRESS</td><td>[ADDRESS]</td></tr>
                                  <tr><td>ZIP</td><td>[ZIP]</td></tr>
                                  </table>';

$tabla->events = New addressesEvents();