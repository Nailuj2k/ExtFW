<?php 

$tabla = new TableMysql('DOKU_FILES');

$id            = new Field();
$id->type      = 'int';
$id->len       = 7;
$id->width     = 15;
$id->fieldname = 'ID';
$id->label     = 'Id';   
$id->hide    = true;

$user = new Field();
$user->fieldname = 'USER_ID';
$user->label     = 'Usuario'; 
$user->type      = 'select';
$user->len       = 7;                             //FIX Users in area!!!

$_users    = AreasACL::getUsersWithPermissionInAnyArea('dokux', 'doku_view');
foreach ($_users as $u)  $user->values[$u['ID']]  = $u['NAME'];

//$user->values    = AreasACL::getUsersWithPermissionInAnyArea('dokux', 'doku_view');
//$user->values    = $tabla->toarray('doku_admins', TB_USER ,'user_id','username'," WHERE user_id IN (SELECT id_user FROM ".TB_ACL_USER_ROLES." WHERE id_role IN (SELECT role_id FROM ".TB_ACL_ROLES." WHERE role_name IN ('HulammWare')))",false);
//$user->values_all= $tabla->toarray('doku_admins', TB_USER ,'user_id','username',"",false);

$user->editable  = $doku_oper; //juxACL->userHasRoleName('HulammWare');
//$user->multiselect = true;
$user->default_value = $_SESSION['userid'];
//$user->size     = 40;
//$user->classname = 'fullname';
//$user->max_chars = 30;
$user->allowNull = true;
$user->searchable= true;
$user->filtrable= true;
//$user->editable= false;
//$user->readonly= true;

$servicio= new Field();
$servicio->type      = 'int';
$servicio->len       = 5;
$servicio->fieldname = 'SERVICIO';
$servicio->label     = 'Área / Servicio';
$servicio->type      = 'select';
//$servicio->values=array();
//$servicio->values['0']= 'Todos';
/*
function _ta($rows){
    $result=[];
    foreach($rows as $row) { 
        $result[$row['ID']] = $row['NAME'];
    }
    return $result;
} 
*/
//foreach ($myareas as $area)  $servicio->values[$area['ID']]  = $area['NAME'];
$servicio->values    =  AreasACL::getAreas($_SESSION['userid']); //$tabla->toarray('doku_areas',   "SELECT ID, NAME FROM DOKU_AREAS WHERE ACTIVE=1",true);
$servicio->values_all = AreasACL::getAreas();  
$servicio->editable  =  $doku_admin; //juxACL->userHasRoleName('HulammWare'); 
//$servicio->default_value=$_SESSION['DOKU_AREA']['ID']?$_SESSION['DOKU_AREA']['ID']:'0';
$servicio->allowNull=true;
$servicio->clearValue='0';
$servicio->clearText='Todos';
$servicio->width = 100;
$servicio->filtrable = true;  
$servicio->sortable = true;  
$servicio->searchable = true;
$servicio->inline_edit  = true;


$n_expediente = new Field();
$n_expediente->type      = 'varchar';
$n_expediente->len       = 11;
$n_expediente->fieldname = 'N_EXPEDIENTE';
$n_expediente->label     = 'Exp.';
$n_expediente->editable  = true ;
$n_expediente->sortable  = true;
$n_expediente->searchable= true;



$identifier = new Field();
$identifier->fieldname = 'IDENTIFIER';
$identifier->label     = 'Identificador';   
$identifier->len       = 100;
$identifier->type      = 'varchar';
$identifier->editable  =  $doku_oper; //juxACL->userHasRoleName('HulammWare');
$identifier->size      = 70;
$identifier->width      = 70;
$identifier->placeholder = 'DNI, NIF, NIE, NHC, etc.';
$identifier->searchable = true;
$identifier->filtrable  = true;
$identifier->inline_edit  = true;
/*
$name = new Field();
$name->fieldname = 'PACIENTE_NAME';
$name->label     = 'Nombre';   
$name->len       = 100;
$name->type      = 'varchar';
$name->editable  = $_ACL->userHasRoleName('HulammWare');
$name->size      = 70;
$name->width      = 150;
$name->sortable   = true;
$name->searchable = true;
$name->filtrable  = true;
$name->inline_edit  = true;
$name->classname = 'fullname';
*/
$date = new Field();
$date->label     = 'Fecha'; 
$date->type      = 'date';
$date->fieldname = 'DATE';  
$date->sortable  = true;
$date->editable  = true;
$date->filtrable = true;  
$date->len=10;
$date->width=60;
$date->editable=false;
$date->readonly=true;

$name = new Field();
$name->fieldname = 'NAME';
$name->label     = 'Nombre';   
$name->len       = 100;
$name->type      = 'varchar';
$name->editable  =   $doku_admin;//$_ACL->userHasRoleName('HulammWare_Admins');
$name->width      = 450;
$name->classname = 'fullname';
$name->searchable=true;

$filename = new Field();
$filename->fieldname = 'FILE_NAME';
$filename->label     = 'Documento';   
$filename->len       = 100;
$filename->type      = 'file';
$filename->editable  =  false; //$_ACL->userHasRoleName('HulammWare');
$filename->uploaddir = DOKUX_FILES_DIR; //'./media/doku/files';
$filename->parent_id = true;
$filename->action_if_exists_disabled = true;
$filename->action_if_exists = 'replace';
$filename->width      = 450;
$filename->classname = 'fullname';
//$filename->max_chars = 20;             // limit size text in tables 
//$filename->searchable=true;
$filename->hide=true;

$tipo= new Field();
$tipo->type      = 'int';
$tipo->len       = 5;
$tipo->fieldname = 'TYPE';
$tipo->label     = 'Tipo';
$tipo->type      = 'select';
$tipo->values    =  $tabla->toarray('doku_types',   "SELECT ID, NAME FROM DOKU_FILETYPES WHERE (SERVICIO = ".$_SESSION['DOKU_AREA']['ID']." OR SERVICIO='0') AND ACTIVE=1",true);
$tipo->values_all = $tabla->toarray('doku_types',   "SELECT ID, NAME FROM DOKU_FILETYPES",true);  
$tipo->values['0']= 'Desconocido';
$tipo->values_all['0']= 'Desconocido';
$tipo->editable  =  $doku_oper; //juxACL->userHasRoleName('HulammWare'); 
$tipo->default_value='0';
$tipo->allowNull=true;
$tipo->clearValue='0';
$tipo->clearText='Desconocido';
$tipo->width = 220;
$tipo->filtrable = true;  
$tipo->sortable = true;  
$tipo->searchable = true;
$tipo->inline_edit  = true;

$notas = new Field();
$notas->type      = 'textarea';
$notas->fieldname = 'NOTES';
$notas->label     = 'Texto';   
$notas->editable =  $doku_oper; //juxACL->userHasRoleName('HulammWare');
$notas->hide     = true;
$notas->searchable = true;
$notas->filtrable = false;  
$notas->wysiwyg = false;  
$notas->fieldset = 'texto';  

$btn_text = new Field();
$btn_text->fieldname = 'BTN_TEXT';
$btn_text->label     = '';   
$btn_text->len       = 4;
$btn_text->width     = 20;
$btn_text->type      = 'varchar';
$btn_text->calculated  = true;

$validado = new Field();
$validado->type      = 'bool';
$validado->fieldname = 'VALIDATED';
$validado->label     = 'Validado';   
$validado->editable = true;
$validado->filtrable = true;
$validado->default_value = 0;

$hash = new Field();
$hash->label     = 'Hash'; 
$hash->type      = 'varchar';
$hash->fieldname = 'HASH';  
$hash->sortable  = true;
$hash->editable  = true;
$hash->filtrable = false;  
$hash->len=40;
$hash->width=60;
$hash->editable=false;
$hash->readonly=true;
$hash->hide=true;

$btn_proccess = new Field();
$btn_proccess->fieldname = 'BTN_PROCCESS';
$btn_proccess->label     = '';   
$btn_proccess->len       = 4;
$btn_proccess->width     = 20;
$btn_proccess->type      = 'varchar';
$btn_proccess->calculated  = true;

$btn_download = new Field();
$btn_download->fieldname = 'BTN_DOWNLOAD';
$btn_download->label     = '';   
$btn_download->len       = 4;
$btn_download->width     = 20;
$btn_download->type      = 'varchar';
$btn_download->calculated  = true;

$tabla->title = 'Documentos procesados';
$tabla->showtitle =false;
$tabla->output='table';
$tabla->page = $page;
$tabla->page_num_items = $_SESSION['DOKU_TABLE_FILES_ROWS'];

$tabla->addCol($id);
$tabla->addCol($user);
$tabla->addCol($servicio);
$tabla->addCol($n_expediente);
$tabla->addCol($tipo);
$tabla->addCol($date);
$tabla->addCol($identifier);
$tabla->addCol($name);
$tabla->addCol($filename);
$tabla->addCol($notas);
$tabla->addCol($btn_text);
$tabla->addCol($validado);
$tabla->addCol($hash);
$tabla->addCol($btn_proccess);
$tabla->addCol($btn_download);
//$tabla->addCol($user);

//$tabla->addActiveCol();
$tabla->addWhoColumns();

//$tabla->show_empty_rows=false;

$tabla->perms['filter'] = true;
$tabla->perms['reload'] = true;
$tabla->perms['view']   =  $doku_user;//$_ACL->userHasRoleName('HulammWare');;
$tabla->perms['add']    =  false; //$_ACL->userHasRoleName('HulammWare');
$tabla->perms['delete'] =  $doku_admin;//$_ACL->hasPermission('doku_delete'); // $doku_admin; //juxACL->userHasRoleName('HulammWare');
$tabla->perms['edit']   =  $doku_oper; //juxACL->userHasRoleName('HulammWare');
$tabla->perms['setup']  =  $doku_admin;// $_ACL->userHasRoleName('Administradores');
$tabla->orderby = 'ID DESC';

//$tabla->where = "(TYPE IN (SELECT ID FROM DOKU_FILETYPES WHERE (SERVICIO = ".$_SESSION['DOKU_AREA']['ID']." OR SERVICIO='0') AND ACTIVE=1) OR IFNULL(TYPE,'0')='0')";
//$tabla->filter = 'USER_ID=1';
//if($_SESSION['userid']==1){
  if ($_SESSION['DOKU_AREA']['ID'])  $tabla->where = 'SERVICIO = '.$_SESSION['DOKU_AREA']['ID'];
//}

//$_SESSION['_CACHE']['filter']=array(['USER_ID'=>1]);
//$tabla->filterstring = 'USER_ID = 1';

/***********************************

UPDATE DOKU_FILETYPES 
SET SERVICIO=7 WHERE SERVICIO=3

 */


if($_SESSION['userid']==15475474){
    $tabla->verbose=true;
}

class imagesEvents extends defaultTableEvents implements iEvents{ 

  function OnBeforeShow($owner){
  }

  function OnBeforeShowForm($owner,&$form,$id){
  }

  function OnDrawRow($owner,&$row,&$class){
    if(!$row['IDENTIFIER']&&!$row['VALIDATED'] /*|| !$row['PACIENTE_NAME']*/) $class .= ' incomplete';
    $filename = Str::get_file_name($row['FILE_NAME']);
    $name = Str::get_file_name($row['NAME']);
    $ext = Str::get_file_extension($row['FILE_NAME']);
    if      ($ext=='pdf') $icon = '<i class="fa fa-file-pdf-o"></i>';
    else if ($ext=='zip') $icon = '<i class="fa fa-file-zip-o"></i>';
    else if ($ext=='mp3') $icon = '<i class="fa fa-file-audio-o"></i>';
    else if ($ext=='xls'||$ext=='xlsx'||$ext=='csv') $icon = '<i class="fa fa-file-excel-o"></i>';
    else if ($ext=='doc'||$ext=='docx') $icon = '<i class="fa fa-file-word-o"></i>';
    else if ($ext=='jpg'||$ext=='jpeg'||$ext=='png'||$ext=='gif') $icon = '<i class="fa fa-file-image-o"></i>';
                     else $icon = '';
    $row['NAME'] =  '<span  style="cursor:pointer;" class="file_row_" data-dir="files" data-id='.$row['ID'].' data-filename="'.$row['FILE_NAME'].'" data-ext="'.$ext.'" title="Ver documento">'.$icon.' '.$row['NAME'].'</span>';
    $row['HASH']=hash('crc32',$row['HASH']);

     
   if($ext=='mp3'){
        $url = MODULE.'/raw/path=files/filename='.$filename.'/mode=inline/name='.$name.'/ext='.$ext;
        //$row['NAME']=$row['NAME'].'<audio controls><source src="'.$url.'" type="audio/mp3"></audio>';
        $row['NAME']=$row['NAME'].'<audio><source src="'.$url.'" type="audio/mp3"></audio><i style="float:right;cursor:pointer;" class="fa fa-play"></i> ';
    }
  }

  function OnCalculate($owner,&$row){
    global $_ACL,$doku_add,$doku_download;
    $filename = Str::get_file_name($row['FILE_NAME']);
    $name = Str::get_file_name($row['NAME']);
    $ext = Str::get_file_extension($row['FILE_NAME']);
    $row['BTN_TEXT'] = '<a class="file_ocr_help file_viewtext" data-id='.$row['ID'].' data-filename="'.$filename.'" data-ext="'.$ext.'" title="Ver texto OCR" style="color:#00995b;">Aa</a>';
    if ($doku_add)
        $row['BTN_PROCCESS'] = '<a class="file_proccess" data-id='.$row['ID'].' data-filename="'.$filename.'" data-ext="'.$ext.'" title="Procesar"> <i class="fa fa-cog"></i></a>';
    if($doku_download)  
        $row['BTN_DOWNLOAD'] = '<a href="'.MODULE.'/raw/path=files/filename='.$filename.'/name='.$name.'/ext='.$ext.'/" title="Descargar documento"> <i class="fa fa-cloud-download"></i></a>';
  }

  function OnDelete($owner,&$result,$id){
      $row = $owner->getRow($id);
      $filename = HULAMM_WARE_FILES_DIR.'/'.$row['FILE_NAME'];
      unlink(HULAMM_WARE_FILES_DIR.'/'.$row['FILE_NAME']);
      if (file_Exists($filename)){
          $result['error']=1;
          $result['msg']='norl';
      }
      if (file_exists(HULAMM_WARE_FILES_TMB.'/'.$row['HASH'].'.jpg'))  unlink( HULAMM_WARE_FILES_TMB.'/'.$row['HASH'].'.jpg');
  }

  function OnAfterShowForm($owner,&$form,$id){  
  }

  function OnInsert($owner,&$result,&$post) { 
  }
  
  function OnUpdate($owner,&$result,&$post) {     
  }
  
  function OnBeforeSaveFile($owner, &$col, $local_file, &$result ){
  }
 
  function OnAfterInsert($owner,&$result,&$post){
  }

  function OnAfterUpdate($owner,&$result,&$post){
  }
}

$tabla->events = New imagesEvents();
