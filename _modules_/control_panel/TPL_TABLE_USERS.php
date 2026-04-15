<?php

$tabla->profile = ($_ARGS['target']);

$id            = new Field();
$id->type      = 'int';
$id->width       = 30;
$id->len       = 10;
$id->fieldname = 'user_id';
$id->label     = 'Id';   
$id->searchable = true;

$username = new Field();
$username->type      = 'varchar';
$username->len       = 50;
//$username->width     = 110;
$username->fieldname = 'username';
$username->label     = 'Login';   
$username->editable   = true;//Root();  //!$tabla->profile && CFG::$vars['login']['username']['required'];
$username->searchable = true;
$username->filtrable = true;
//$username->hide = /*!(Root() ||*/ !CFG::$vars['login']['username']['required'];
//$username->readonly  = /*!Root() ||*/ CFG::$vars['login']['username']['required']; //Administrador();   
$username->classname = 'fullname';
$username->max_chars = 90;             // limit size text in tables 
$username->size=30;
$username->required=true;

$auth_provider = new Field();
$auth_provider->fieldname = 'AUTH_PROVIDER';
$auth_provider->label     = 'Auth'; 
$auth_provider->len       = 15;
$auth_provider->width     = 25;
$auth_provider->type      = 'select';
$auth_provider->values    = array(/*''=>'',*/'google'=>'Google','twitter'=>'Twitter');
$auth_provider->editable  = false;
$auth_provider->allowNull = true;
$auth_provider->default_value ='';
$auth_provider->fieldset ='password';

$auth_id = new Field();
$auth_id->fieldname = 'AUTH_ID';
$auth_id->label     = 'Auth Id'; 
$auth_id->len       = 50;
$auth_id->type      = 'varchar';
$auth_id->readonly  = true;
$auth_id->hide      =  true;
$auth_id->fieldset ='password';

$auth_picture = new Field();
$auth_picture->fieldname = 'AUTH_PICTURE';
$auth_picture->label     = 'Auth Picture'; 
$auth_picture->len       = 150;
$auth_picture->type      = 'varchar';
$auth_picture->readonly  = true;
$auth_picture->hide      = true;
$auth_picture->fieldset ='password';

    $upassword = new Field();
    $upassword->type      = 'varchar';
    $upassword->len       = 64;
    $upassword->fieldname = 'user_password';
    $upassword->label     = 'Hash passwd';   
    $upassword->editable   = false;//Root();
    $upassword->hide = true;
    $upassword->readonly=true;
    $upassword->fieldset ='password';

if($tabla->tablename==TB_USER){
    $password = new Field();
    $password->type      = 'varchar';
    $password->len       = 45;
    $password->width     = 80;
    $password->fieldname = 'password';
    $password->label     = 'Contraseña';   
    $password->editable   = !$tabla->profile && Administrador();
    $password->calculated = true;
    $password->hide = true;
    //$password->readonly  = Administrador();   
    $password->fieldset ='password';

    $password2 = new Field();
    $password2->type      = 'varchar';
    $password2->len       = 45;
    $password2->width     = 80;
    $password2->fieldname = 'password2';
    $password2->label     = 'Confirmar contraseña';   
    $password2->editable   = !$tabla->profile && Administrador();
    $password2->calculated = true;
    $password2->hide = true;
    //$password2->readonly  = Administrador();   
    $password2->fieldset ='password';

        if(Root()){
            $signature = new Field();
            $signature->type      = 'varchar';
            $signature->len       = 45;
            $signature->width     = 80;
            $signature->fieldname = 'user_signature';
            $signature->label     = 'Signature';   
            $signature->editable   = Root();
            $signature->hide   = true;
            $signature->fieldset ='password';

            $confirm_code = new Field();
            $confirm_code->type      = 'varchar';
            $confirm_code->len       = 100;
            $confirm_code->width     = 80;
            $confirm_code->fieldname = 'user_confirm_code';
            $confirm_code->label     = 'Confirm code';   
            $confirm_code->editable   = Root();
            $confirm_code->hide   = true;
            $confirm_code->fieldset ='password';

        }
}

$fullname = new Field();
$fullname->type      = 'varchar';
$fullname->len       = 150;
$fullname->width     = 200;
$fullname->fieldname = 'user_fullname';
$fullname->label     = 'Nombre completo';
//$fullname->classname = 'fullname';
$fullname->searchable = true;
$fullname->editable  = true;   
$fullname->filtrable = true;
$fullname->required = true;
$fullname->size=50;

$email = new Field();
$email->type      = 'varchar';
$email->fieldname = 'user_email';
$email->label     = 'Email';   
$email->editable  = true ;
$email->len       = 150;
$email->searchable = true;
$email->editable  = true;   
$email->filtrable = true;
//$email->hide = !CFG::$vars['login']['username']['required'];
$email->required = true;
$email->textafter = ' Email obligatorio.';
$email->classname = 'fullname';
$email->width = 200;             // limit size text in tables 
$email->size=30;

$card_id = new Field();
$card_id->type      = 'varchar';
$card_id->fieldname = 'user_card_id';
$card_id->label     = t('CARD_ID');   
$card_id->editable  = true ;
$card_id->len       = 20;
$card_id->searchable = true;
$card_id->editable  = true;   
$card_id->filtrable = false;
$card_id->required = (CFG::$vars['login']['card_id']['required']===true);
$card_id->hide = true;
$card_id->editable  = true;   
$card_id->textafter = (CFG::$vars['login']['card_id']['required']===true)?' Identificador obligatorio.':' ... ';

$level = new Field();
$level->type      = 'int';
$level->fieldname = 'user_level';
$level->label     = 'Nivel';   
$level->editable  = true ;
$level->len       = 8;
$level->editable  = Administrador();   
$level->filtrable = true;

$user_score = new Field();
$user_score->type      = 'int';
$user_score->fieldname = 'user_score';
$user_score->label     = 'Score';   
$user_score->editable  = true ;
$user_score->len       = 10;
$user_score->editable  = Administrador();   
$user_score->filtrable = true;
$user_score->hide = true;

$last_login = new Field();
$last_login->type      = 'int';
$last_login->fieldname = 'user_last_login';
$last_login->label     = 'Última visita';   
$last_login->len       = 16;
$last_login->width     = 120;
//$last_login->readonly  = !Administrador();   
$last_login->hide  = !Administrador();

$ip = new Field();
$ip->type      = 'varchar';
$ip->fieldname = 'user_ip';
$ip->label     = 'IP';   
//$ip->readonly  = true ;
$ip->len       = 15;
$ip->width     = 80;
$ip->filtrable = true;
//$ip->readonly  = !Administrador();   

$notify   = new Field();
$notify->type      = 'bool';
$notify->fieldname = 'user_notify';
$notify->label     = 'Not.'; //'Recibir notificaciones';   
$notify->editable  = true;   
$notify->filtrable = true;
$notify->default_value = 1;
//$notify->class_name = 'checkbox-help';   
$notify->textafter = '<span class="warning">Si desactiva esta opción sólo recibirá mensajes marcados como importantes</span>';

$datos   = new Field();
$datos->type      = 'bool';
$datos->fieldname = 'user_lpd_data';
$datos->label     = 'Uso de datos.'; //'Recibir notificaciones';   
$datos->editable  = true;   
$datos->filtrable = true;
$datos->default_value = 0;
//$datos->class_name = 'checkbox-help';   
$datos->textafter = '<span style="font-size:0.8em;line-height: 33px;vertical-align: top;">Consentimiento del uso de datos para los fines indicados en la política de privacidad Sus datos seguros.</span>';
$datos->hide=true;

$publi   = new Field();
$publi->type      = 'bool';
$publi->fieldname = 'user_lpd_publi';
$publi->label     = 'Recibir publicidad.'; //'Recibir notificaciones';   
$publi->editable  = true;   
$publi->filtrable = true;
$publi->default_value = 0;
//$publi->class_name = 'checkbox-help';   
$publi->textafter = '<span style="font-size:0.8em;line-height: 33px;vertical-align: top;">Consentimiento del uso de los datos personales del cliente para recibir publicidad</span>';
$publi->hide=true;

$activo   = new Field();
$activo->type      = 'bool';
$activo->fieldname = 'user_active';
$activo->label     = 'Activo';   
$activo->editable  = !$tabla->profile && Administrador();   
$activo->filtrable = true;

$online   = new Field();
$online->type      = 'bool';
$online->width     = 30;
$online->fieldname = 'user_online';
$online->label     = 'Online';   
$online->editable  = false;
$online->classname = 'online';          
$online->filtrable = true;
//$online->classes   = array('0'=>'offline','1'=>'online','NULL'=>'unknown');
$online->readonly  = !$tabla->profile && $tabla->tablename==TB_USER; // Administrador();   

/***********************************************/
$url = new Field();
$url->type      = 'varchar';
$url->fieldname = 'user_url';
$url->label     = 'Url';   
$url->editable  = true ;
$url->len       = 80;
$url->editable  = true;   

$lang = new Field();
$lang->fieldname = 'id_lang';
$lang->label     = 'Idioma'; 
$lang->width     = 50;
$lang->type      = 'select';
$lang->source    = 'langs';  // array name
$lang->editable  = true;
$lang->default_value = 1;

$avatar = new Field();
$avatar->fieldname = 'user_url_avatar';
$avatar->label     = 'Foto';   
$avatar->len       = 50;
$avatar->width     = 30;
$avatar->type      = 'file';
$avatar->editable = true;

$avatar->image_editor  = true;
$avatar->image_upload_url = '/'.MODULE.'/ajax/op=function/function=imagereceive/table='.$tabla->tablename.'/id=';
/*$avatar->image_width  = '220px';*/
$avatar->image_height = '315px';


/*
$avatar->crop        = true;

$avatar->crop_width  = 220;
$avatar->crop_height = 315;
//$avatar->crop_urlpath    = 'media/inventario/activos/fotos/'.$parent.'/';//"/_classes_/scaffold/ajax.php?
$avatar->crop_urlpath    = SCRIPT_DIR_MEDIA.'/avatars/';//"/_classes_/scaffold/ajax.php?
$avatar->crop_upload_url = '/'.MODULE.'/ajax/op=function/function=imagereceive/table='.$tabla->tablename.'/id=';
$avatar->crop_resize_url = '/'.MODULE.'/ajax/op=function/function=resizeimage/table='.$tabla->tablename.'/id=';
$avatar->crop_delete_url = '/'.MODULE.'/ajax/op=function/function=deleteimage/table='.$tabla->tablename.'/id=';
$avatar->crop_trim_button = true; //Administrador();
$avatar->crop_resize_button =Administrador();
$avatar->crop_face_detect = false; //Administrador();
*/
$avatar->uploaddir = SCRIPT_DIR_MEDIA.'/avatars/';
$avatar->inline_edit = false;
$avatar->accepted_extensions =  $accepted_img_extensions; //array( '.jpg');  
$avatar->fieldset = 'avatar';
$avatar->prefix_filename = true;
$avatar->action_if_exists_disabled = true;
$avatar->action_if_exists = 'replace';


$notes = new Field();
$notes->type      = 'textarea';
$notes->fieldname = 'user_notes';
$notes->label     = 'Notas';   
$notes->editable = true;
$notes->hide     = false;
$notes->width     = 200;
$notes->height     = 80;
$notes->searchable  = true;
$notes->filtrable = true;
$notes->hide = true;
$notes->wysiwyg = true;
$notes->fieldset = 'notes';

$user_salt = new Field();
$user_salt->type      = 'varchar';
$user_salt->fieldname = 'user_salt';
$user_salt->label     = 'Salt';   
$user_salt->hide  = true;//!Root();
$user_salt->len       = 3;
$user_salt->filtrable = true;
$user_salt->editable=false;   
/*
$sign_public_key = new Field();
$sign_public_key->type      = 'textarea';
$sign_public_key->fieldname = 'sign_public_key';
$sign_public_key->label     = 'sign_public_key';   
$sign_public_key->editable = true;
$sign_public_key->hide     = true;
$sign_public_key->width     = 200;
$sign_public_key->height     = 80;
$sign_public_key->searchable  = true;
$sign_public_key->filtrable = true;
$sign_public_key->hide = true;
$sign_public_key->wysiwyg = false;
$sign_public_key->fieldset = 'keys';

$enc_public_key = new Field();
$enc_public_key->type      = 'textarea';
$enc_public_key->fieldname = 'enc_public_key';
$enc_public_key->label     = 'enc_public_key';   
$enc_public_key->editable = true;
$enc_public_key->hide     = true;
$enc_public_key->width     = 200;
$enc_public_key->height     = 80;
$enc_public_key->searchable  = true;
$enc_public_key->filtrable = true;
$enc_public_key->hide = true;
$enc_public_key->wysiwyg = false;
$enc_public_key->fieldset = 'keys';

$keys_updated_at = new Field();
$keys_updated_at->type = 'datetime';
$keys_updated_at->fieldname = 'keys_updated_at';
$keys_updated_at->label = 'Fecha';  
$keys_updated_at->editable  = true;  
$keys_updated_at->hide = true;
$keys_updated_at->default_value    = 'current_timestamp()';
$keys_updated_at->fieldset = 'keys';
*/

if(CFG::$vars['login']['nostr']['enabled']){
  $nostr_pubkey = new Field();
  $nostr_pubkey->type      = 'textarea';
  $nostr_pubkey->fieldname = 'nostr_pubkey';
  $nostr_pubkey->label     = 'nostr_pubkey';   
  $nostr_pubkey->editable = true;
  $nostr_pubkey->hide     = true;
  $nostr_pubkey->width     = 200;
  $nostr_pubkey->height     = 80;
  $nostr_pubkey->searchable  = true;
  $nostr_pubkey->filtrable = true;
  $nostr_pubkey->hide = true;
  $nostr_pubkey->wysiwyg = false;
  $nostr_pubkey->fieldset = 'nostr';


  $nostr_banner = new Field();
  $nostr_banner->fieldname = 'NOSTR_BANNER';
  $nostr_banner->label     = 'Nostr banner';   
  $nostr_banner->len       = 50;
  $nostr_banner->width     = 30;
  $nostr_banner->type      = 'file';
  $nostr_banner->editable  = true;
  $nostr_banner->hide      = true;
  $nostr_banner->image_height = '315px';
  $nostr_banner->uploaddir = SCRIPT_DIR_MEDIA.'/nostr/banners/';
  $nostr_banner->inline_edit = false;
  $nostr_banner->accepted_extensions =  $accepted_img_extensions; //array( '.jpg');  
  $nostr_banner->fieldset = 'nostr';
  $nostr_banner->prefix_filename = true;
  $nostr_banner->action_if_exists_disabled = true;
  $nostr_banner->action_if_exists = 'replace';


}

if($tabla->tablename==TB_USER){
$verify   = new Field();
$verify->type      = 'bool';
$verify->width     = 30;
$verify->fieldname = 'user_verify';
$verify->label     = 'Verificado';   
$verify->editable  = Administrador();
$verify->filtrable = true;
//$verify->readonly  = !$tabla->profile && $tabla->tablename==TB_USER; // Administrador();   

}

$api_key = new Field();
$api_key->type      = 'varchar';
$api_key->fieldname = 'api_key';
$api_key->label     = 'API Key';   
$api_key->hide  = true;//!Root();
$api_key->len       = 50;
$api_key->searchable = true;
$api_key->editable  = Administrador();
$api_key->readonly  = !Administrador();   


$balance_sats = new Field();
$balance_sats->type      = 'int';
$balance_sats->fieldname = 'balance_sats';
$balance_sats->label     = 'Sats';   
$balance_sats->textafter = '<span class="info" style="margin:0 0 0 5px;font-size:0.8em;">Balance en Satoshis (1 BTC = 100.000.000 Sats)</span>';
$balance_sats->len       = 10;
$balance_sats->width    = 45;
$balance_sats->editable  = Root();
$balance_sats->readonly  = !Root();
$balance_sats->fieldset  = 'btc';

$lightning_address = new Field();
$lightning_address->type      = 'varchar';
$lightning_address->fieldname = 'lightning_address';
$lightning_address->label     = 'Lightning Address';   
//$lightning_address->hide  = true;//!Root();
$lightning_address->len       = 255;
$lightning_address->hide = true;
$lightning_address->searchable = true;
$lightning_address->editable  = true;
$lightning_address->fieldset  = 'btc';
$lightning_address->placeholder = 'user@domain';
$lightning_address->textafter = '<span class="info" style="margin:5px 0;font-size:0.8em;">Ejemplos: acho@walletofsatoshi.com, jander@ln.tips, grijander@getalby.com. <br>A esta dirección se enviarán los satoshis cuando se retire saldo.</span>';


$PIN = new Field();
$PIN->type      = 'varchar';
$PIN->len       = 255;
$PIN->fieldname = 'PIN';
$PIN->label     = 'PIN';
$PIN->editable  = true;
$PIN->sortable  = false;
$PIN->hide  = true;
$PIN->default_value = '';
$PIN->fieldset='password';

$device_link_token = new Field();
$device_link_token->type      = 'varchar';
$device_link_token->len       = 64;
$device_link_token->fieldname = 'device_link_token';
$device_link_token->label     = 'Device Link Token';
$device_link_token->editable  = true;
$device_link_token->sortable  = false;
$device_link_token->hide  = true;
$device_link_token->default_value = '';
$device_link_token->fieldset='password';

$device_link_expires = new Field();
$device_link_expires->type      = 'int';
$device_link_expires->len       = 11;
$device_link_expires->fieldname = 'device_link_expires';
$device_link_expires->label     = 'Device Link Expires';
$device_link_expires->editable  = true;
$device_link_expires->sortable  = false;
$device_link_expires->hide  = true;
//$device_link_expires->default_value = '';
$device_link_expires->fieldset='password';


$tabla->title = $tabla->tablename==TB_USER?'Usuarios':'Clientes';
$tabla->page_num_items = 12;
//$tabla->ajax_url = AJAX_URL.'?module='.$module_name;
$tabla->verbose = false;
$tabla->showtitle = false;
$tabla->inline_edit = true;

$tabla->page = $page;


//$tabla->cache=false;
$tabla->addCol($id);
$tabla->addCol($username);
$tabla->addCol($fullname);
//$tabla->addCol($level);
$tabla->addCol($email);
$tabla->addCol($card_id);
$tabla->addCol($avatar);
//$tabla->addCol($lang);
$tabla->addCol($last_login);
$tabla->addCol($ip);
$tabla->addCol($auth_provider);
$tabla->addCol($auth_id);
$tabla->addCol($auth_picture);
if($tabla->tablename==TB_USER){
  $tabla->addCol($activo);
  $tabla->addCol($password);
  $tabla->addCol($password2);
  $tabla->addCol($upassword);
  $tabla->addCol($user_salt);
  $tabla->addCol($verify);
  if(Root()) $tabla->addCol($signature);
  if(Root()) $tabla->addCol($confirm_code);
}
$tabla->addCol($user_score);
$tabla->addCol($datos);

//$tabla->addCol($sign_public_key);
//$tabla->addCol($enc_public_key);
//$tabla->addCol($keys_updated_at);
if(CFG::$vars['login']['nostr']['enabled']){
    $tabla->addCol($nostr_pubkey);
    $tabla->addCol($nostr_banner);
}

if(CFG::$vars['users']['field']['publi'])
    $tabla->addCol($publi);
////////////////////////////////////////$tabla->addCol($notify);
if (!$tabla->profile) $tabla->addCol($notes);

$tabla->addCol($online);

if(CFG::$vars['plugins']['tip_ln']){
    $tabla->addCol($balance_sats);
    $tabla->addCol($lightning_address); 
}

$tabla->addCol($device_link_token);
$tabla->addCol($device_link_expires);

if(CFG::$vars['users']['field']['country']) {
    $pais = new Field();
    $pais->len       = 5;
    $pais->width     = 120;
    $pais->fieldname = 'ID_COUNTRY';
    $pais->label     = 'País'; 
    $pais->type      = 'select';
    $pais->values    = $tabla->toarray('paises' ,    'SELECT pais_id AS ID, pais_name AS NAME FROM CFG_PAIS',true); 
    $pais->editable  = true;
    $pais->hide  = true;
    //    $pais->default_value=724;
    $pais->fieldset = 'ubicacion';
    $pais->filtrable = true;
    if(CFG::$vars['users']['field']['state']) {
        $pais->child_ajax_url   = '/control_panel/ajax/op=list';
        $pais->child_fieldname  = 'ID_STATE';
        $pais->child_source_sql = "provincia_from_pais";
    }
    $tabla->addCol($pais);
}

if(CFG::$vars['users']['field']['state']) {
    $provincia = new Field();
    $provincia->len       = 5;
    $provincia->width     = 120;
    $provincia->fieldname = 'ID_STATE';
    $provincia->label     = 'Provincia'; 
    $provincia->type      = 'select';
    $provincia->values    = array(); 
    $provincia->values_all= $tabla->toarray('provincias' ,  'SELECT provincia_id AS ID, provincia_name AS NAME FROM CFG_PROVINCIA',true);  
    $provincia->editable  = true;
    $provincia->hide=true;
    $provincia->fieldset = 'ubicacion';
    $provincia->filtrable = true;
    $tabla->addCol($provincia);
}


$extra_fields = $tabla->query2array("SELECT * FROM CFG_EXTRA_FIELDS WHERE T4BLE_NAME='".$tabla->tablename."' AND ACTIVE = '1'");  

//$extra_fields = $tabla->query2array("SELECT T4BLE_NAME,FIELD_NAME,FIELD_TYPE,FIELD_LEN,FIELD_LABEL,FIELDSET,WYSIWYG,TEXT//AFTER,PLACEHOLDER,UPLOADDIR,EXTENSIONS,MASK"
//                                   ." FROM CFG_EXTRA_FIELDS WHERE T4BLE_NAME='".$tabla->tablename."' AND ACTIVE = '1'");


//$print_label = true;
foreach ($extra_fields as $k => $v) {
    //$column = 'IND'.$i;
    $column = $v['FIELD_NAME'];
    ${$column} = new Field();
    ${$column}->fieldname = $column;
    ${$column}->label = $v['FIELD_LABEL'];
    ${$column}->type  = $v['FIELD_TYPE'];

    ${$column}->textafter   = $v['TEXTAFTER'];
    ${$column}->placeholder   = $v['PLACEHOLDER'];


    if($v['FIELD_TYPE']=='date'){
        
    }else{
        ${$column}->len   = $v['FIELD_LEN'];
    }
    if($v['EDITABLE']=='true'){
        ${$column}->editable = true;
    }else if($v['EDITABLE']){
        ${$column}->editable = $_ACL->userHasRoleName($v['EDITABLE']);
        ${$column}->readonly = !${$column}->editable;
    }else{
        ${$column}->editable = false;
        ${$column}->readonly = true;
    }
    // ${$column}->width = 20;
    //${$column}->hide = true;
    if($v['FIELDSET']) ${$column}->fieldset = Str::SanitizeName($v['FIELDSET']);
    if($v['FIELD_DEFAULT_VALUE']) ${$column}->default_value = $v['FIELD_DEFAULT_VALUE'];
    
    if($v['FIELD_TYPE']=='textarea'){
        ${$column}->wysiwyg = $v['WYSIWYG']=='1';
        ${$column}->width     = 200;
        ${$column}->height     = 80;
    }

    if($v['FIELD_TYPE']=='file')  {
      ${$column}->uploaddir   = str_replace(['[SCRIPT_DIR_MEDIA]','[TABLENAME]'],[SCRIPT_DIR_MEDIA,$v['T4BLE_NAME']],$v['UPLOADDIR']);
   //   ${$column}->accepted_doc_extensions   = explode(',',$v['EXTENSIONS']);
      ${$column}->accepted_doc_extensions   = array('.png');
      ${$column}->action_if_exists_disabled = true;
      ${$column}->action_if_exists = 'replace';
      ${$column}->len   = 100;
      ${$column}->textafter=$v['EXTENSIONS'].'::'.print_r(${$column}->accepted_doc_extensions,true);
      ${$column}->mask   = $v['MASK'];
    }else{
      ${$column}->mask   = false;   
    }

    if($v['FIELD_TYPE']=='select'){
        ${$column}->values  = $tabla->toarray( 'v_'.$column ,  'SELECT '.$v['LOOKUP_FIELD_KEY'].' AS ID, '.$v['LOOKUP_FIELD_NAME'].' AS NAME FROM '.$v['LOOKUP_FIELD_TABLE'],true);  
        ${$column}->values_all  = ${$column}->values;
        if($v['ALLOW_NULL']){
            ${$column}->allowNull = true;
            ${$column}->default_value = 0;
        }
        /*
        ${$column}->classname = 'fullname';
        ${$column}->max_chars = 90;             // limit size text in tables 
        ${$column}->width     = 200;
        */
        //${$column}->hide = false;
    }
    
    
    if($v['HIDE']) ${$column}->hide   = true;
    if($v['SEARCHABLE']) ${$column}->searchable   = true;
    if($v['FILTRABLE']) ${$column}->filtrable   = true;
    
      //if($v['FIELD_TYPE']=='bool')  {${$column}->class_name='check-inline'; ${$column}->displaytype = 'inline';}
       //if($print_label){
        //  ${$column}->textbefore = '<div class="check-group"><div class="check-title">Efectos secundarios</div>';
        //  $print_label=false;
        //}
        $tabla->addCol(${$column});
    }
    $tabla->addCol($api_key);
    $tabla->addCol($PIN);
    $tabla->addWhoColumns();
    
    //$tabla->colByName('ACTIVE')->filtrable=true;
    //$tabla->colByName('CREATED_BY')->hide=(false);
    //$tabla->colByName('CREATION_DATE')->hide(false);
    //$tabla->colByName('LAST_UPDATED_BY')->hide(false);
    //$tabla->colByName('LAST_UPDATE_DATE')->hide(false);
    
    
$tabla->searchOperator = 'AND';
$tabla->inline_edit = true;

/*
$tabla->filter  = " user_id IN (SELECT id_user FROM ".TB_ACL_USER_ROLES." WHERE id_role ";
$tabla->filter .= " IN (SELECT role_id FROM ".TB_ACL_ROLES." WHERE role_name ";
$tabla->filter .= "  IN ('Socio','Directiva')";
$tabla->filter .= " ))";
*/

$tabla->perms['view']  = true;  

if(Administrador()){  

  $tabla->perms['delete'] = Administrador() && $tabla->tablename==TB_USER;  
  $tabla->perms['edit']   = true;   
  $tabla->perms['add']    = Administrador() && $tabla->tablename==TB_USER;  
  $tabla->perms['pdf']    = true; // Administrador();  
  $tabla->perms['print']  = false;  
  $tabla->perms['setup']  = true && $tabla->tablename==TB_USER; 
  $tabla->perms['filter'] = true && $tabla->tablename==TB_USER;  
  $tabla->perms['reload'] = true;  
  $tabla->page_num_items = Vars::getArrayVar(CFG::$vars['users']['options'],'num_rows',20);

  $tabla->perms['print']  = Administrador();  
//$tabla->perms['view']   = Coordinador();  

//$tabla->input_page_num = true;

}else if($_ACL->hasPermission('pedidos_admin')){
 
  $tabla->perms['edit']   = true;   
  $tabla->perms['pdf']    = true; // Administrador();  
  $tabla->perms['add']    = true;  
  $tabla->perms['print']  = true;  
  $tabla->perms['setup']  = true; 
  $tabla->perms['filter'] = true;  
  $tabla->perms['reload'] = true;  
  $tabla->page_num_items = 12;

  $tabla->perms['print']  = Administrador();  
//$tabla->perms['view']   = Coordinador();  


}elseif($_SESSION['userid']>0){ //$_SESSION['validuser']*//*Usuario()*/ ){

  $_SESSION['_CACHE'][TB_USER]['filterstring'] = false;
  $_SESSION['_CACHE'][TB_USER]['filterindex'] = false;
  $tabla->perms['add']   = false;   
  $tabla->perms['edit']   = false;   
  $tabla->perms['filter'] = false;  
  $tabla->perms['print']  = false;  
  $tabla->page_num_items = 1;
  $tabla->show_inputsearch=false;
  $tabla->where = 'user_id='.$_SESSION['userid'];

}else{
  //die('Access denied.');
}

$tabla->orderby = 'user_id desc';

//if (!$tabla->profile) 
  $tabla->addFieldset($tabla->default_fieldset_name,'Datos personales');

if (!$tabla->profile) $tabla->addFieldset('permisions','Roles y permisos');  //fs_roles

$tabla->addFieldset('avatar','Avatar');
$tabla->addFieldset('password','Contraseña');

if (!$tabla->profile)  
    $tabla->addFieldset('notes','Notas');

if(CFG::$vars['login']['nostr']['enabled'])
    $tabla->addFieldset('nostr','Nostr');

if(CFG::$vars['plugins']['tip_ln'])
    $tabla->addFieldset('btc','<i class="fa fa-bitcoin"></i> Bitcoin');

//$tabla->detail_tables=array($tabla->tablename.'_contacts',$tabla->tablename.'_files');
//$tabla->detail_tables[]='CLI_CUSTOMER_CONTACTS';


class userEvents extends defaultTableEvents implements iEvents{ 
   
  function OnShow($owner){
  }
  
    function OnBeforeShow($owner) {
      if(Administrador() ){  
      //if ($_SESSION['_CACHE'][TB_USER]['filterstring'])
      //  $owner->where  = $_SESSION['_CACHE'][TB_USER]['filterstring'];
      }
    }

    private function deleteSelectValues() {
        $_SESSION['_CACHE']['values']['users'] = false; 
        $_SESSION['_CACHE']['values']['users_all'] = false;
    }

    function OnBeforeShowForm($owner,&$form,$id){
        global $_ACL;
        if($id &&  $owner->state == 'update') $fila = $owner->getRow($id);
        if (!$owner->profile) {
            $html = '';
            if (($_ACL->hasPermission('access_admin') && $owner->state == 'update') || ($owner->state != 'filter' && Administrador())){
                $userACL = new ACL($id);                   //   $itemACL = new ACL('',$id);
                $userACL->buildACL();                      //   $itemACL->buildACL();
                $rPerms = $userACL->perms;
                $aPerms = $userACL->getAllPerms('full');
                $aRoles = $_ACL->getAllRoles('full');   //   $aRoles = $_ACL->getAllRoles('full');
            }

            //$html .= print_r($aRoles,true);
            //$html .= print_r($userACL->getUserRoles('full'),true);

            if( $_ACL->hasPermission('access_admin') && $owner->state == 'update'){

                ///////$html .= print_r($aRoles,true);

               //   $html .= '<table style="margin-left:10px;margin-right:5px;max-width:300px;display:inline-table;" class="zebra fixed_headers table_roles rw">';
                $html .= '<table style="max-width:300px;display:inline-table;" class="zebra fixed_headers table_roles rw">';
                $html .= '<thead>';
                $html .= '  <tr><th colspan="3" class="thc">Roles User ID:'.$userACL->userID.'</th></tr>';  // <th class="thc">'.t('Miembro').'</th>
                $html .= '  <tr><th>Role</th><th id="role-yes" class="thc">'.t('Si').'</th><th id="role-no" class="thc">'.t('No').'</th></tr>';
                $html .= '</thead>';
                $html .= '<tbody>'; // style="max-height:400px;overflow:auto;display:block;width:100%;">';
                foreach ($aRoles as $k => $v){
                  $_user_has_role = $userACL->userHasRole($v['ID']);
                  $html .= '  <tr class="role-'.($_user_has_role?'yes':'no').'">';
                  $html .= '    <td>'.$v['Name'].'</td>';
                  $html .= '    <td class="tdc"><input type="radio" name="role_'.$v['ID'].'" id="role_'.$v['ID'].'_1" value="1"';
                  if ( $_user_has_role) $html .= ' checked="checked"';
                  $html .= '/></td>';
                  $html .= '<td class="tdc"><input type="radio" name="role_'.$v['ID'].'" id="role_'.$v['ID'].'_0" value="0"';
                  if (!$_user_has_role) $html .= ' checked="checked"';
                  $html .='/></td>';
                  $html .= '</tr>';
                }
                $html .= '</tbody>';
                $html .= '</table>';

                $html .= '  <script>';
                $html .= '      $(\'#role-yes\').click(function(){  $(\'.role-yes\').toggle(); $(this).toggleClass(\'role-hidden\'); });';
                $html .= '      $(\'#role-no\') .click(function(){  $(\'.role-no\') .toggle(); $(this).toggleClass(\'role-hidden\'); });';
                $html .= '  </script>';

                $html .= '  <table style="max-width:300px;display:inline-table;" class="zebra fixed_headers table_perms rw">';
                $html .= '  <thead>';
                $html .= '  <tr><th colspan="2">'.t('PERMISSIONS','Permisos').'</th></tr>';
                $html .= '  <tr><th>'.t('PERM','Permiso').'</th><th>'.t('VALUE','Valor').'</th></tr>';
                $html .= '  </thead>';
                $html .= '  <tbody>';
                foreach ($aPerms as $k => $v){
                  /**
                  $html .= '<tr><td>' . $v['Name'] . '</td>';
                  $html .= '<td>';
                //$html .= print_r($rPerms[$v['Key']],true);
                  $html .=$rPerms[$v['Key']]['value'].'  :: inheritted['.$rPerms[$v['Key']]['inherited'].']';
                  $html .= '</td>';
                  $html .= '</tr>';
                  **/
                  $html .= '<tr><td>' . $v['Name'] . '</td>';
                  $html .= '<td><select style="color:'.($rPerms[$v['Key']]['value'] == true?'green':'red').';" name="perm_' . $v['ID'] . '">';
                  $html .= '<option value="1"';
                  if ($userACL->hasPermission($v['Key']) && $rPerms[$v['Key']]['inherited'] == true) $html .= ' selected="selected"';
                  $html .= ' style="color:green;">Allow</option>';
                  $html .= '<option value="0"';
                  if ($rPerms[$v['Key']]['value'] === false && $rPerms[$v['Key']]['inherited'] != true) $html .= ' selected="selected"';
                  $html .= ' style="color:red;">Deny</option>';
                  $html .= '<option value="X"';
                  if ($rPerms[$v['Key']]['inherited'] == true || !array_key_exists($v['Key'],$rPerms)){
                    $html .= ' selected="selected"';
                    $iVal = ($rPerms[$v['Key']]['value'] == true ) ? '(Allow)'  : '(Deny)';
                  }
                  $html .= '>Inherit '.$iVal.'</option>';
                  $html .= '</select></td></tr>';
                }
                $html .= '</tbody>';
                $html .= '</table>';
               //$html .= '<pre style="max-height:80px;">'.print_r($rPerms,true).'</pre>';

            } else if ( $owner->state != 'filter' && Administrador() ) { 
                $html .= '<table class="zebra fixed_headers table_roles ro">';
                $html .= '<thead>';
                $html .= '<tr><th>Roles</th></tr>';  // <th class="thc">'.t('Miembro').'</th>
                $html .= '</thead>';
                $html .= '<tbody>';
                foreach ($aRoles as $k => $v){if($userACL->userHasRole($v['ID'])){$html .= '<tr><td>'.$v['Name'].'</td></tr>';}}
                $html .= '</tbody>';
                $html .= '</table>';
                $html .= '<table class="zebra fixed_headers table_perms ro">';
                $html .= '<thead>';
                $html .= '<tr><th colspan="2">'.t('PERMISSIONS','Permisos').'</th></tr>';  // <th class="thc">'.t('Miembro').'</th>
                $html .= '<tr><th>'.t('PERM','Permiso').'</th><th>'.t('VALUE','Valor').'</th></tr>';
                $html .= '</thead>';
                $html .= '<tbody>';
                foreach ($aPerms as $k => $v){
                  $allow = false;
                  $allowhtml = '';
                  if ($userACL->hasPermission($v['Key']) && $rPerms[$v['Key']]['inheritted'] != true){
                    $allow = true;
                    $allowhtml .= 'Allow';
                  }
                  // if ($rPerms[$v['Key']]['value'] === false && $rPerms[$v['Key']]['inheritted'] != true) $allowhtml .= 'Deny';
                  if ($rPerms[$v['Key']]['inheritted'] == true || !array_key_exists($v['Key'],$rPerms)){
                    if  ($rPerms[$v['Key']]['value'] == true ) $allow = true;
                    $iVal = ($rPerms[$v['Key']]['value'] == true ) ? '(Allow)'  : '(Deny)';
                    $allowhtml .= 'Inherit '.$iVal;
                  }
                  if($allow) $html .= '<tr><td>' . $v['ID'] .' - '. $v['Name'] . '</td><td>'.$allowhtml.'</td></tr>';
                }
                $html .= '</tbody>';
                $html .= '</table>';
            }

            // $html .= '<pre>'.print_r($userACL->getUserPerms($id),true).'</pre>' ;

            if($html){
              $html_permisions = new formElementHtml();
              $html_permisions->html = $html;
              if ($owner->state != 'filter'){
                $fs_permisions = $owner->fieldsets['permisions']; // new fieldset('permisions','Roles y permisos');  //lo hemos creado arriba
                $fs_permisions->displaytype = 'tab';
                $fs_permisions->addElement($html_permisions);
                //$form->addElement($fs_permisions);    // fielset  ya estña añadido
              }
            }
        } // if (!$owner->profile) 


        if ($owner->state=='update'&&!$owner->profile){
            /**
            if(/ *$owner->profile &&* / $_SESSION['userid']==$id){
                $tit = 'Enviar mensaje a '.CFG::$vars['site']['title'];
                $btn_id='send_message_to_site';
            }else 
            **/
            if($owner->tablename==TB_USER && Administrador()) {  
                $tit = 'Enviar mensaje al usuario <b>'.$fila['user_fullname'].'</b>';
                $btn_id='send_message_to_customer';
            }else if($owner->tablename!==TB_USER && $_ACL->hasPermission('pedidos_admin')){  
                $tit = 'Enviar mensaje al cliente <b>'.$fila['user_fullname'].'</b>';
                $btn_id='send_message_to_customer';
            }
            if($tit)  {
                $html_send_email = '<div style="text-align:right;width:auto;">
                                    <div class="ajax-loader" style="display:none;"><div class="loader"></div></div>
                                    <div style="line-height:2em;">'.$tit.'</div>
                                    <input type="text" id="message_subject" value="Mensaje para '.$fila['user_fullname'].' <'.$fila['user_email'].'>" style="line-height:1.5em;display:block;width:100%;max-width: -webkit-fill-available;max-width: -moz-available;border:1px solid #d5d5d5;">

                                    <textarea id="message_body" style="display:block;width:100%;max-width: -webkit-fill-available;max-width: -moz-available;height:100px;border:1px solid #d5d5d5;"></textarea><br />
                                <!--<a class="btn btn-primary" s12/05/2022tyle="text-align:center;" id="btn_last_messages"><i class="fa fa-list-ul"></i> &nbsp; Mensajes &nbsp;  &nbsp; </a>-->
                                    <div><span id="messages-status" style="float:left;">...</span><a class="btn btn-primary" style="text-align:center;" id="'.$btn_id.'"><i class="fa fa-paper-plane-o"></i> &nbsp; Enviar &nbsp;  &nbsp; </a></div>
                                    </div>
                                    <div id="last_messages"></div> ';   //SELECT ID,ID_USER,TYPE,EMAIL,SUBJECT,MESSAGE,EVENT_DATE,LOG_EVENTS WHERE ID_USER = '.$id.' ORDER BY EVENT_DATE DESC LIMIT 10'.'</p>
                $html_send_emails = new formElementHtml();
                $html_send_emails->html = $html_send_email;
                if ($owner->state != 'filter'){
                    $fs_send_emails = new fieldset('send_emails',t('MESSAGES','Mensajes'));  //fs_roles
                    $fs_send_emails->displaytype = 'tab';
                    $fs_send_emails->addElement($html_send_emails);
                    $form->addElement($fs_send_emails);
                }
                
            }
        }

        if ($owner->state=='insert' || $owner->state=='update'){
          ?>
          <script type="text/javascript">
         // var url_check_email   = 'control_panel/ajax/op=getfield/table=<?=TB_USER?>/field=user_id/key=user_email';
            var url_check_email   = 'control_panel/ajax/op=method/table=<?=TB_USER?>/method=validate/field=user_email';
         // var url_check_card_id = 'control_panel/ajax/op=getfield/table=<?=TB_USER?>/field=user_id/key=user_card_id';
            var url_check_card_id = 'control_panel/ajax/op=method/table=<?=TB_USER?>/method=validate/field=user_card_id';
            var input_email   = $("#user_email");
            var input_card_id = $("#user_card_id");
            var user_email_ok, user_card_id_ok = false;
            var value_ok    =  <?php if ($owner->state=='insert') echo '0'; else echo '1'; ?>;
            var value_where =  <?php if ($owner->state=='update') echo "'user_id<>{$fila['user_id']}'";  else echo '0'; ?>;
            function valid_data() {
               return (<?=CFG::$vars['login']['card_id']['required']===true?'user_card_id_ok && ':''?> user_email_ok);
            }
            input_email.change(function() { 
              var user_email = $(this).val();
              input_email.css('background-color','#dbeeff').parent().find('.text-after').html(' ... ').css('color','gray');
              if(user_email){
                console.log(url_check_email,{"value":user_email,"where":value_where});
                $.post(url_check_email,{"value":user_email,"where":value_where},function(data, textStatus, jqXHR){  
                  console.log(data);
                  if(data.valid<1){
                    input_email.css('background-color','#dbeeff').parent().find('.text-after').html(' '+data.msg+'  <i class="fa fa-warning" style="color:#ff0000;"></i>').css('color','red');
                    $('#btnsubmit').attr('disabled', '');
                    user_email_ok = false;
                    input_email.focus();
                  }else if(data.field>0){
                    input_email.css('background-color','#f99999').parent().find('.text-after').html(' Nombre de usuario o email ocupado <i class="fa fa-warning" style="color:#ff0000;"></i>').css('color','red');
                    $('#btnsubmit').attr('disabled', '');
                    user_email_ok = false;
                    input_email.focus();
                  }else{
                    user_email_ok = true;
                    input_email.css('background-color','#dbeeff').parent().find('.text-after').html(' Nombre de usuario o email válido <i class="fa fa-check" style="color:#00e409;"></i>').css('color','green');
                    if (valid_data()) $('#btnsubmit').removeAttr('disabled');
                  }
                },'json');
              }else{
                  input_email.css('background-color','#dbeeff').parent().find('.text-after').html(' Email obligatorio').css('color','gray');
              }
            });
            
            input_card_id.change(function() { 
              var user_card_id = $(this).val();
              input_card_id./*css('background-color','#dbeeff').*/parent().find('.text-after').html(' ... ').css('color','gray');
              if(user_card_id){
                console.log(url_check_card_id+'/value='+user_card_id+'/where='+value_where);
                $.post(url_check_card_id,{"value":user_card_id,"where":value_where},function(data, textStatus, jqXHR){  
                    console.log(data);
                  if(data.valid<1){
                    input_card_id.css('background-color','#dbeeff').parent().find('.text-after').html(' '+data.msg+'  <i class="fa fa-warning" style="color:#ff0000;"></i>').css('color','red');
                    $('#btnsubmit').attr('disabled', '');
                    user_card_id_ok = false;
                    input_card_id.focus();
                  }else if(data.field>0){
                    input_card_id.css('background-color','#f99999').parent().find('.text-after').html(' Ya hay un usuario con este DNI <i class="fa fa-warning" style="color:#ff0000;"></i>').css('color','red');
                    $('#btnsubmit').attr('disabled', '');
                    user_card_id_ok = false;
                    input_card_id.focus();
                  }else{
                    user_card_id_ok = true;
                    input_card_id./*css('background-color','#dbeeff').*/parent().find('.text-after').html(data.msg+' <i class="fa fa-check" style="color:#00e409;"></i>').css('color','green');
                    if (valid_data()) $('#btnsubmit').removeAttr('disabled');
                  }
                },'json');
              }else{
                 input_card_id./*css('background-color','#dbeeff').*/parent().find('.text-after').html( '<?=CFG::$vars['login']['card_id']['required']?'Identificador obligatorio':''?>').css('color','gray');
              }
            });
            input_email.change();
            input_card_id.change();
            <?php if ($owner->state=='update'){?>
                /****
                $('#send_message_to_site').click(function(){
                    var message=$('#message_body').val();
                    var id_user=<?=$id?>;
                    var el=$(this).closest('div');
                    if(!message) return false;
                    console.log(id_user,message);
                    el.html('<h3><br />Enviando mensaje ...</h3>');
                    //$('.ajax-loader').show();
                    $.post('control_panel/ajax/op=method/method=sendmessage/table=<?=TB_USER?>',{"from":id_user,"message":message},function(data, textStatus, jqXHR){  
                        console.log(data);
                        el.html('<h4><br />'+data.msg+'</h4>');
                        $('.ajax-loader').hide();

                    },'json');
                });
                ***/
                $('#send_message_to_customer').click(function(){
                    var message=$('#message_body').val();
                    var id_user=<?=$id?>;
                    var el=$(this).closest('div');
                    if(!message) return false;
                    $('#messages-status').html('Enviando mensaje ...');
                    //$('.ajax-loader').show();
                    $.post('control_panel/ajax/op=method/method=sendmessage/table=<?=TB_USER?>',{"to":id_user,"message":message},function(data, textStatus, jqXHR){  
                        $('#message_body').val('');
                        update_btn();
                        $('#messages-status').html('<div class="info"><span>'+data.msg+'</span></div>');
                        setTimeout(function(){get_last_messages(id_user,true);},600);
                        $('.ajax-loader').hide();
                    },'json');
                });
                function get_last_messages(id,highlight){
                    $.post('control_panel/ajax/op=method/method=list_messages/table=<?=TB_USER?>',{"id":id,'target':'user'},function(data, textStatus, jqXHR){  
                        //console.log(data);
                        $('#last_messages').html(data.msg);
                        setTimeout(function(){if(highlight) $('#last_messages').find('tbody tr:first-child').css('background-color','#ffff99').highlight();},300);
                    },'json');
                }

                $('body').on('click','.message-reply',function(){
                    let tr = $(this).closest('tr');
                    $('#message_subject').val( 'Re: '+tr.find('.message-subject').html() );
                    $('#message_body').val( '  > <?=$fila['user_fullname']?> escribió: \n  > '+tr.find('.message-body').html()+'\n  > \n' ).focus();                   
                })

                $('body').on('click','.message-view',function(){
                    $('.ajax-loader').show();
                    let tr = $(this).closest('tr');
                    // console.log(tr.data('user'));
                    var url_get_msg   = 'control_panel/ajax/op=getfield/table=LOG_EVENTS/field=MESSAGE/key=ID/value='+tr.data('id');
                    //console.log(url_get_msg);
                    $.post(url_get_msg,function(data, textStatus, jqXHR){  
                        console.log(data.field);
                        show_info('#fs_div_send_emails',data.field,10000,function(e){e.animate({'top':'+=100'});});
                        $('.ajax-loader').hide();
                    },'json');


                   // $('#message_subject').val( 'Re: '+tr.find('.message-subject').html() );
                   // $('#message_body').val( '  > <?=$fila['user_fullname']?> escribió: \n  > '+tr.find('.message-body').html()+'\n  > \n' ).focus();                   
                })
                /*     
                function message_reply(sender){ //$('.message-reply').click(function(){
                    let tr = sender.closest('tr');
                    console.log(tr);
                    $('#message_subject').val( 'Re: '+tr.find('.message_subject').val() );
                    $('#message_body').val( '<?=$fila['user_fullname']?> escribió:<blockquote>'+tr.find('.message_body').val()+'</blockquote>' );                   
                }
                */    
                function update_btn(){
                    if ($('#message_body').val()=='') $('#send_message_to_customer').addClass('disabled'); else $('#send_message_to_customer').removeClass('disabled'); 
                }

                $('#message_body').change(function(){ update_btn(); }).keyup(function(){ update_btn(); });
                
                //$('#btn_last_messages').click(function(){
                  //  get_last_messages(<?=$id?>,true);
                //});

                get_last_messages(<?=$id?>,false);
                update_btn();

            <?php }?>

          </script>
          <?php 
        }



    }


    function sendmessage($owner,$args){
        $result = array();
        $result['error']=0;

        $m = new Mailer();
        
        if     ($args['from'])  $userid=$args['from']; 
        else if($args['to'])    $userid=$args['to'];
        $title = isset($args['title']) && $args['title']?$args['title']:false;
        $sql = "SELECT user_fullname AS NAME,user_email AS EMAIL FROM ".TB_USER." WHERE user_id=".$userid;
        $row=$owner->getFieldsValues( $sql );

        if      ($args['from']){
            $m->Subject = $title?$title:'Mensaje de '.$row['NAME'].' <'.$row['EMAIL'].'>';
            $m->SetFrom(CFG::$vars['site']['from_email'],CFG::$vars['site']['from_name']) ;
            $m->AddAddress(CFG::$vars['site']['email'],CFG::$vars['site']['title']) ;
            $m->AddReplyTo($row['EMAIL'],$row['NAME']);
            $msg = 'Mensaje enviado a '.CFG::$vars['site']['title'];
            $type='2';
            $email = $row['EMAIL'];
        }else if($args['to'])  {
            $m->Subject = $title?$title:'Mensaje desde '.CFG::$vars['site']['title'];
            $m->SetFrom(CFG::$vars['site']['from_email'],CFG::$vars['site']['from_name']) ;
            $m->AddAddress($row['EMAIL'],$row['NAME']);
            $msg = 'Mensaje enviado a '.$row['NAME'].' ['.$row['EMAIL'].']';
            $type='1';
            $email = $row['EMAIL'];
        }
        $m->body = str_replace("\n",'<br />',$args['message']); //'Se ha realizado un nuevo pedido'
        $log_sql = 'INSERT INTO '.TB_LOG.' (TYPE,ID_USER,EVENT_DATE,EMAIL,SUBJECT,MESSAGE) VALUES(\''.$type.'\','.$userid.','.$owner->sql_currentdate().',\''.$email.'\',\''.Str::escape($m->Subject).'\',\''.Str::escape($m->body).'\')';
        $m->AddBCC('julian.torres.sanchez@gmail.com','Store MSG');
        $okis = $m->Send();
        //$type->values    = array('0'=>'unknown','1'=>'msg_to_user','2'=>'msg_from_user' );
        if($okis){
            $owner->sql_exec($log_sql);
        } else{       
            $result['error']=1;
        }
        $result['msg']=$okis?$msg:'No se pudo enviar el mensaje. :(';
   
        echo json_encode($result);   
    }


    function list_messages($owner,$args){
        $result = array();
        $result['error']=0;
        $rows_last_messages = Table::sqlQuery('SELECT ID,ID_USER,TYPE,EMAIL,SUBJECT,MESSAGE,EVENT_DATE FROM LOG_EVENTS WHERE  TYPE IN (1,2) AND ID_USER = '.$args['id'].' ORDER BY EVENT_DATE DESC LIMIT 10');
        $result['msg'] = '<table class="zebra" style="margin-top:6px;">'
                       .'<tr><thead>'
                    // . '<th>Id</th>'
                    // . '<th>Usuario</th>'
                       . '<th>Mensaje</th>'
                       . '<th>Fecha</th>'
                       . '<th>Asunto</th>'
                       . '<th>Mensaje</th>'
                       . '<th></th><th></th></tr></thead><tbody>';
        foreach ($rows_last_messages as $message){
            $result['msg'].='<tr data-id="'.$message['ID'].'" data-user="'.$message['ID_USER'].'" class="message-'.($message['TYPE']==1?'sent':'rcvd').'">'
                       // . '<td>'.$message['ID'].'</td>'
                       // . '<td>'.$message['ID_USER'].'</td>'
                          . '<td>'.($args['target']&&$args['target']=='profile' ? ($message['TYPE']==2?'Enviado':'Recibido') : ($message['TYPE']==2?'Recibido':'Enviado')).'</td>'
                          . '<td style="width:140px;">'.fuzzyDate(strtotime($message['EVENT_DATE'])).'</td>'
                          . '<td class="message-subject">'.Str::limit_text($message['SUBJECT'],45).'</td>'
                          . '<td class="message-body">'.Str::limit_text(strip_tags($message['MESSAGE']),75).'</td>'
                          . '<td>'.($message['TYPE']>0?'<i style="cursor:pointer;" class="fa fa-reply message-reply" title="'.t('REPLY').'"></i>':'').'</td>'
                          . '<td><i style="cursor:pointer;" class="fa fa-eye message-view" title="'.t('VIEW').'"></i></td> '
                          . '</tr>';
        }
        $result['msg'] .= '</tbody></table>';
        echo json_encode($result);   

    }


  function validate($owner,$args){
      $result = array();
      /*
      // control_panel/ajax/op=getfield/table=<?=TB_USER?>/field=user_id/key=user_card_id';
      // control_panel/ajax/op=method/table=TB_USER/method=validate/field=user_card_id/value=5
      Vars::debug_var($args);
      [0] => control_panel
      [1] => ajax
      [2] => op=method
      [3] => table=TB_USER
      [4] => method=validate
      [5] => field=user_card_id
      [6] => value=5
      [output] => ajax
      [op] => method
      [table] => TB_USERr
      [method] => validate
      [field] => user_card_id
      [value] => 5
      */

      //returns: 1 = NIF ok, 2 = CIF ok, 3 = NIE ok, -1 = NIF bad, -2 = CIF bad, -3 = NIE bad, 0 = ??? bad
      if      ($args['field']=='user_email')  {
          $valid = Str::valid_email($args['value']);
          $result['valid'] = $valid===true?'1':'0';
          $result['msg']   = $valid===true?'Email correcto':'Email no válido: '.$args['value'];;
      }else if($args['field']=='user_card_id')  {
          $valid = Str::valid_nif_cif_nie($args['value']);
          if      ($valid ==  1) $result['msg'] = 'NIF ó DNI correcto';
          else if ($valid ==  2) $result['msg'] = 'CIF correcto';
          else if ($valid ==  3) $result['msg'] = 'NIE correcto';
          else if ($valid == -1) $result['msg'] = 'NIF o DNI no válido: '.$args['value'];
          else if ($valid == -2) $result['msg'] = 'CIF no válido: '.$args['value'];
          else if ($valid == -3) $result['msg'] = 'NIE no válido: '.$args['value'];
          else if ($valid ==  0) $result['msg'] = 'Identificador no válido: '.$args['value'];
          $result['valid'] = $valid;
      }
      $sql = "SELECT count(*) FROM ".TB_USER." WHERE ".$args['field']." = '".$args['value']."' ";
      if ($args['where']) $sql .= ' AND '.$args['where'];
      $result['field']=$owner->getFieldValue( $sql );
      $result['sql']=$sql;
      $result['error']=0;
      echo json_encode($result);   
  }

  function OnAfterShowForm($owner,&$form,$id){
            if($_SESSION['username']==$row['username']){
                if ($_SESSION['user_url_avatar']!=$_img) {
                    $_SESSION['user_url_avatar'] = $_img;
                }
            }
            ?>
            <script>
                console.log('OnAfterShowForm');
            </script>
            <?php           
  }

  function OnUpdate($owner, &$result, &$post){ 
     //global $_ACL;
      $post['user_email']=trim($post['user_email']);
      if(!$post['username']) $post['username']=trim($post['user_email']);

     if ( Cliente() && !Administrador()  ){
       $owner->perms['edit'] = ($post['user_id']==$_SESSION['userid']); 
     }  

     if($post['PIN']){
        /*
         if( !ctype_digit( $post['PIN'] ) ){
            $result['error']=4;
            $result['msg']='El PIN solo puede contener dígitos numéricos';
            return;
         }else if (strlen($post['PIN'])<4){
             $result['error']=4;
            $result['msg']='El PIN debe tener al menos 4 dígitos';
            return;
        }else
        */
        if (strlen($post['PIN'])==4){
            $post['PIN'] = password_hash($post['PIN'], PASSWORD_BCRYPT); 
        }
     }

     if( Str::valid_email($post['user_email']) && Str::valid_email($post['username']) )  $post['username'] = $post['user_email'];

     //$_ACL->addUserRole( $post['user_id'] , 'Clientes');
   
    $this->deleteSelectValues();   
    if($owner->tablename==TB_USER){
        if(strlen($post['password'])>7){ 
          if  ($post['password']==$post['password2']){
            //$owner->sql_exec("UPDATE {$owner->tablename} set user_password=MD5('{$post['password']}') WHERE user_id={$post['user_id']}");  
              $u =  $post['user_id'];
              $n =  $post['username']; 

              //$salt =  $owner->getFieldValue('SELECT user_salt FROM '.$owner->tablename.' WHERE user_id = '.$post['user_id']);
              //$hash = hash('sha256', $salt. hash('sha256', $post['password']) );

              $hash =   password_hash($post['password'], PASSWORD_BCRYPT); 

              $query = "UPDATE ".$owner->tablename." SET user_password = '".$hash."' WHERE user_id = '$u'";
              $post['user_password'] =$hash;
              
              //if(Login::sqlQuery($query)){
              if($owner->sql_exec($query)){
                    $result['msg'] = t('PASSWORD_CHANGED'); //. ' '.$salt.' '.$query;;
              }else{
                    $result['msg'] = t('ERROR_CHAGING_PASSWORD');//.' '.$query ;  //.' '.Login::lastError() ;  
                    $result['error']=3;
              }
            
          }else{
             $result['error']=3;
             $result['msg']='Las contraseñas no coinciden'; 
          }
        }else if(strlen($post['password'])>0){
           $result['error']=3;
           $result['msg']='La contraseña debe tener 7 carácteres como mínimo'; 
        } 
    }

  }
  
  function OnAfterUpdate($owner,&$result,&$post){
    global $_ACL;
    if( $_ACL->hasPermission('access_admin') ){
      foreach ($post as $k => $v){
        if (substr($k,0,5) == "perm_"){
          $permID = str_replace("perm_","",$k);
          if ($v == 'X')
            $sql = sprintf("DELETE FROM ".TB_ACL_USER_PERMS." WHERE id_user = %u AND id_permission = %u",
                           $post['user_id'],$permID);
          else
            $sql = sprintf("REPLACE INTO ".TB_ACL_USER_PERMS." SET id_user = %u, id_permission = %u, user_perm_value = %u, user_perm_add_date = '%s'",
                           $post['user_id'], $permID, $v, date ("Y-m-d H:i:s") );
          Table::sqlExec($sql);
        }
      }
      foreach ($post as $k => $v){
       if (substr($k,0,5) == "role_"){
          $roleID = str_replace("role_","",$k);
          if ($v == '0' || $v == 'X')
            $sql = sprintf("DELETE FROM ".TB_ACL_USER_ROLES." WHERE id_user = %u AND id_role = %u",
                           $post['user_id'],$roleID);
          else{
             $sql = sprintf("REPLACE INTO ".TB_ACL_USER_ROLES." SET id_user = %u, id_role = %u, user_role_add_date = '%s'", $post['user_id'],$roleID, date ("Y-m-d H:i:s") );
             //$sql = sprintf("REPLACE INTO ".TB_ACL_USER_ROLES." SET id_user = %u, id_role = %u", $post['user_id'],$roleID );
          }
          //$result['msg'] .= $sql;

          Table::sqlExec($sql);
        }
      }
      unset($_SESSION['ACL']);
      $_ACL = new ACL();
      $_ACL->buildACL();
    }else{  
    }

        //if ( Root() || Administrador() ){

        //}else{
            if($_SESSION['userid'] && $post['user_card_id'] && $post['user_id'] && $post['user_id']>0 && $post['user_id']==$_SESSION['userid']){
                $owner->sql_exec("UPDATE CLI_USER_ADDRESSES SET CARD_ID='".$post['user_card_id']."' WHERE id_user={$post['user_id']} AND IFNULL(CARD_ID,'')=''");
            }
        //}

  }  

  function OnDrawRow($owner,&$row,&$class){
  
    if($row['AUTH_PROVIDER']){ 
        $_url = $row['AUTH_PICTURE'];  //Login::getUrlAvatar();
    }else{
        $_avatar_default_image = /*'https://'.$_SERVER['HTTP_HOST'].*/"./_images_/avatars/avatar.gif";
        $_avatar_size  = 40;      
        $_dirphotos = './media/avatars/';
        // $_dirphotos = $avatar->uploaddir.'/';
        // $avatar->uploaddir = '/home/extralab/domains/extralab.net/public_html/tienda/media/avatars';
        $_img = $row['user_url_avatar'];
        $_email = $row['user_email'];                            
        if (!$_img || !file_exists($_dirphotos.$_img)){ 
            $_url = $row['AUTH_PICTURE']?$row['AUTH_PICTURE']:$_avatar_default_image;
            //$_url = "https://www.gravatar.com/avatar.php?gravatar_id=".md5($_email)."&default=".urlencode($_avatar_default_image)."&size=".$_avatar_size; 
            //$row['AVATAR']='<img class="avatar"   src="'.$_url.'"  title="'.$_email.'" border="0">';  //
        }else{
            $_url = $_dirphotos.$_img.'?ver='.md5($row['LAST_UPDATE_DATE']??'');
            //$row['AVATAR']='<img class="avatar"   src="'.$_url.'?hash='.time().'" title="'.$_email.'"  border="0">';  
        }       
    }
    /**/

    $row['user_url_avatar']='<img class="avatar" src="'.$_url.'" title="'.$row['user_email'].'" width="20" height="20" border="0">';

    $row["user_last_login"] = fuzzyDate($row["user_last_login"]);
     
    if ( Usuario() && !Administrador()  ){
      $class='';
      if($row['user_id']==$_SESSION['userid']){
        $owner->perms['edit'] = true;
      }
      if ($owner->perms['edit']===true) $class = ' edit'; 
    }
  }
  /*******  
  function OnDrawRow($owner,&$row,&$class){
       //$row['user_url_avatar']='<img src="'.$row['THUMB'].'">';
       $row['user_url_avatar']=$row['THUMB'];
  }
  ****/
  function OnBeforeUpdate($owner,$id){  
    global $_ACL;
    if ( Usuario() && !Administrador() && !$_ACL->hasPermission('pedidos_admin') ){
       $iduser = $owner->getFieldValue('SELECT user_id FROM '.$owner->tablename.' WHERE user_id = '.$id);
       $owner->perms['edit'] = ($iduser==$_SESSION['userid']); //  && $row['guardias_validate']=='0'  );
     }  
  }
  
  function OnDrawCell($owner,&$row,&$col,&$cell){
   //    $owner->colByName('user_notify')->label     = 'Recibir notificaciones';   
    if($col->fieldname=='user_notify' &&  in_array($owner->state, ['update','insert','filter']))  $col->label     = 'Recibir notificaciones';   
    if($owner->state=='update'){
      if($col->fieldname=='user_url_avatar') {
        if($row['AUTH_PROVIDER']){ 
            $_url = $row['AUTH_PICTURE'];  //Login::getUrlAvatar();
        }else{
            $_avatar_default_image = SCRIPT_DIR_MEDIA."/avatars/avatar.gif";
            $_avatar_size  = 40;
            $_dirphotos = SCRIPT_DIR_MEDIA.'/avatars/';
            $_img = $row['user_url_avatar'];
            $_email = $row['user_email'];                             // $row[$owner->pk->fieldname]y76
            if (!$_img || !file_exists($_dirphotos.$_img)) //$t_URLIMAGE  = 'nophoto.gif';
              $_url = $row['AUTH_PICTURE']?$row['AUTH_PICTURE']:$_avatar_default_image;
            else
              $_url = $_dirphotos.$_img.'?date='.date('Ymdhis'); //$row['LAST_UPDATE_DATE'];
        }
      ?>
      <script type="text/javascript">
          $('#fs_default').append('<img style="position:absolute;right:20px; top:40px;z-index:10000;max-height:80px;" src="<?=$_url?>">');
      </script>
      <?php
      }

    }
  }
  function OnDelete($owner,&$result,$id)    { $this->deleteSelectValues(); }
  
  function OnInsert($owner,&$result,&$post) { $this->deleteSelectValues(); 
      $post['user_email']=trim($post['user_email']);
      if(!$post['username']) $post['username']=trim($post['user_email']);
  }

  function OnBeforeSaveFile($owner, &$col, $local_file, &$result ){
    
     if  ($col->fieldname=='user_url_avatar')  {
        $ext = Str::get_file_extension($result['local_file']);
        $result['local_file'] = $result['old'][$owner->pk->fieldname].'.'.$ext;
     }

     if  ($col->fieldname=='NOSTR_BANNER')   {
        $ext = Str::get_file_extension($result['local_file']);
        $result['local_file'] = 'banner_'.$result['old'][$owner->pk->fieldname].'.'.$ext;
     }

  }

  function OnAfterInsert($owner,&$result,&$post) { $this->deleteSelectValues(); 
    if($owner->tablename==TB_USER){
       // $salt  = substr(md5(uniqid(rand(), true)), 0, 3);
        if(strlen($post['password'])>7){ 
          if  ($post['password']==$post['password2']){
            //$owner->sql_exec("UPDATE {$owner->tablename} set user_password=MD5('{$post['password']}') WHERE user_id={$post['user_id']}");  
              $u =  $post['user_id'];
              $n =  $post['username']; 
              //$user_data = Login::getFieldsValues("SELECT * FROM ".$this->tablename." WHERE user_id = '$u'");
             /// $hash = hash('sha256', $salt. hash('sha256', $post['password']) );
              $hash =   password_hash($post['password'], PASSWORD_BCRYPT); 

              //$query = "UPDATE ".$owner->tablename." SET user_password = '{$hash}',user_salt = '{$salt}' WHERE user_id = '{$result['last_insert_id']}'";
              $query = "UPDATE ".$owner->tablename." SET user_password = '{$hash}' WHERE user_id = '{$result['last_insert_id']}'";
              $post['user_password'] =$hash;         
              //if(Login::sqlQuery($query)){
              if($owner->sql_exec($query)){
                    $result['msg'] = t('PASSWORD_CHANGED');
              }else{
                    $result['msg'] = t('ERROR_CHAGING_PASSWORD');  //.' '.Login::lastError() ;  
                    $result['error']=3;
              }
          }else{
             $result['error']=3;
             $result['msg']='Las contraseñas no coinciden'; 
          }
        }else if(strlen($post['password'])>0){
            $result['error']=3; 
            $result['msg']='La contraseña debe tener 7 carácteres como mínimo'; 
        }
    }
  }

  function OnBeforePrint($owner, $template, $id){
  }


  function OnPrint($owner, $template, &$_item_tags, &$_item_values){
    //print_r($_item_values);
    //if not fileexists avatar update avatar set ''
    //if fileexists dni.jpg update avatar set dni.jpg
    if($_SESSION['auth_provider']&&$_SESSION['auth_picture'])
        $_item_values['user_url_avatar']=$_SESSION['auth_picture'];
    else if(!$_item_values['user_url_avatar']) 
        $_item_values['user_url_avatar']='/media/avatars/avatar.gif';
    else
        $_item_values['user_url_avatar']='/media/avatars/'.$_item_values['user_url_avatar'];

    $_item_values['user_last_login'] =  fuzzyDateLite($_item_values['user_last_login']);
     //    if ( MODULE=='control_panel') {

      if ($template=='pdf'){
        //$selected_id_role = $_SESSION['_CACHE'][$owner->tablename]['filterindex'];
        $rolename = $owner->getFieldValue('SELECT role_name FROM '.TB_ACL_ROLES); //.' WHERE role_id = '.$selected_id_role); 
        $html = '';
          $html .= '<div style="margin:10px auto 20px auto;font-size:1.3em;">Listado de usuarios.';
          if($rolename) $html .= ' Grupo: <b>'.$rolename.'</b>';
          $html .= '</div>';
          $tipos = array('1'=>'Dirección postal','2'=>'Teléfono fijo','3'=>'Móvil','4'=>'Correo electrónico','5'=>'Skype','6'=>'Whatsapp');   
          $n = 0;
          $owner->page_num_items = 100;
          $owner->select();
          $sql_users = str_replace('LIMIT  1,100','', $owner->str_select(1) );
          $html .= '<table class="zebra detail"><tr><th>Nombre</th><th>Email</th><th>Móvil</th><th>Fijo</th><th></th></tr>';
          $data = $owner->sql_query($sql_users);
          $class='odd';
          foreach($data as $row){
            $n++; 
            $class=($class=='odd')?'even':'odd';
            $html .= '<tr class="'.$class.'">';
            $html .= '<td>'.$row['user_fullname'].'</td>';
            $html .= '<td>'.$row['user_email'].'</td>';
            $mobile = '';
            $phone = '';
            $guasap = '';
            $skype = '';
            $notify = '';
            /*
            if     ($row['user_notify'])  $notify =' <i class="fa fa-envelope" style="color:#74c6ed;" title="Recibe notificacioens"> </i> ';

            $data_contact = $owner->sql_query('SELECT id_tipo,valor FROM '.$owner->tablename.'_contacts WHERE id_user = '.$row['user_id'].'  GROUP BY id_tipo  ORDER BY id_tipo ');
            foreach($data_contact as $row_contact){
              if     ($row_contact['id_tipo']==3)  $mobile = $row_contact['valor'];
              else if($row_contact['id_tipo']==2)  $phone = $row_contact['valor'];
              else if($row_contact['id_tipo']==5)  $skype = ' <a href=""skype:'.$row_contact['valor'].'?chat""><i class="fa fa-skype" style="color:#24b2f1;" title="skype:'.$row_contact['valor'].'?chat"> </i></a> ';
              else if($row_contact['id_tipo']==6)  $guasap = ' <i class="fa fa-ok-sign" style="color:#2bb204;" title="Whatsapp o como se escriba"> </i> ';
            }
            */
            $html .=  '<td>'.$mobile.'</td>';
            $html .=  '<td>'.$phone.'</td>';
            $html .=  '<td>'.$guasap.$skype.$notify.'</td>';
            $html .=  '</tr>';    
          }
          $html .= '</table>'; 
          $html = 'jhahahahah';
          $owner->html_pdf_content = $html;    
      }  // if ($template!='pdf')
        
  }

  function OnAfterPrint($owner, $template){


  }


}

$tabla->events = New userEvents();

//$tabla->format_item = true;
$style='<style>
#customer {
  border-collapse: collapse;
  margin:30px auto auto 50px;
}

#customer td, #customers th {
  border: 1px solid #ddd;
  padding: 8px;
}

#KKcustomer tr:nth-child(odd){background-color: #f2f2f2;}
#customer tr td{text-align:left;min-width:150px;color:#333;}
#customer tr td:last-child{min-width:250px;}
#customer tr td:first-child{background-color: #f2f2f2;text-align:right;}
#customer tr:hover {background-color: #fafafa;}
#customer th {
  padding-top: 12px;
  padding-bottom: 12px;
  text-align: left;
  background-color: #b9ad7d;
  color: white;
}
</style>';

$tabla->format_item['body'] = '<table class="zebra table-values" id="customer">'
                            . '<tr><th colspan="2"></th></tr>'
                            . '<tr><td>Id</td><td>[user_id]</td></tr>'
                            . '<tr><td>'.t('USERNAME').'</td><td>[username]</td>'
                            . '<td rowspan="8"><img class="editable-image" style="width:118px;" src="[user_url_avatar]"></td>'
                             .'</tr>'
                            . '<tr><td>'.t('NAME').'</td><td>[user_fullname]</td></tr>'
                            . '<tr><td>'.t('EMAIL').'</td><td>[user_email]</td></tr>'
                            . '<tr><td>'.t('DNI/NIF').'</td><td>[user_card_id]</td></tr>'
                            . '<tr><td>'.t('IP').'</td><td>'.get_ip().'</td></tr>'
                         // . '<tr><td>Token</td><td>'.$_SESSION['token'].'</td></tr>'
                            . '<tr><td>'.t('IDENTIFIED_WITH').'</td><td>'.ucwords($_SESSION['auth_provider']).' '.'<span style="color:silver;margin-left:20px;">Auth id: '.$_SESSION['auth_id'].'</span>' .'</td></tr>'                          
                            . '<tr><td>'.t('LAST_CONNECTION').'</td><td>[user_last_login]</td></tr>'
                            . '<tr><td>'.t('SCORE').'</td><td>[user_score]</td></tr>'
                            . ( CFG::$vars['plugins']['tip_ln'] ? '<tr><td>'.t('BALANCE_SATS').'</td><td colspan="2" style="padding-bottom:0;">[balance_sats]<a style="display:inline-block;padding:2px 5px;margin:0 0 0 20px;background-color:orange;border-color:orange;color:white;" onclick="showDialogWithDraw(this)"><i class="fa fa-bitcoin"></i> '.t('WITHDRAW_FUNDS').'</a></td></tr>' : '' )
                            . '<tr><td colspan="3" class="buttons" style="border-top:1px solid #dedede;"></td></tr>'
                            . '</table>';
