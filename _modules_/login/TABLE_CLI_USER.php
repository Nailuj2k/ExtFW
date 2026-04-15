<?php
/* Auto created */

$tabla = new TableMysql('CLI_USER');

$user_id = new Field();
$user_id->type      = 'int';
$user_id->len       = 10;
$user_id->fieldname = 'user_id';
$user_id->label     = 'User';
$user_id->editable  = false ;
$user_id->sortable  = true;
$user_id->searchable  = true;
$tabla->addCol($user_id);

$id_lang = new Field();
$id_lang->type      = 'int';
$id_lang->len       = 5;
$id_lang->fieldname = 'id_lang';
$id_lang->label     = 'Id lang';
$id_lang->editable  = false ;
$id_lang->sortable  = true;
$id_lang->searchable  = true;
$tabla->addCol($id_lang);

$username = new Field();
$username->type      = 'varchar';
$username->len       = 50;
$username->fieldname = 'username';
$username->label     = 'Username';
$username->editable  = false ;
$username->sortable  = true;
$username->searchable  = true;
$tabla->addCol($username);

$user_password = new Field();
$user_password->type      = 'varchar';
$user_password->len       = 64;
$user_password->fieldname = 'user_password';
$user_password->label     = 'User password';
$user_password->editable  = false ;
$user_password->sortable  = true;
$user_password->searchable  = true;
$tabla->addCol($user_password);

$user_level = new Field();
$user_level->type      = 'int';
$user_level->len       = 8;
$user_level->fieldname = 'user_level';
$user_level->label     = 'User level';
$user_level->editable  = false ;
$user_level->sortable  = true;
$user_level->searchable  = true;
$tabla->addCol($user_level);

$user_date_created = new Field();
$user_date_created->type      = 'int';
$user_date_created->len       = 8;
$user_date_created->fieldname = 'user_date_created';
$user_date_created->label     = 'User date created';
$user_date_created->editable  = false ;
$user_date_created->sortable  = true;
$user_date_created->searchable  = true;
$tabla->addCol($user_date_created);

$user_last_login = new Field();
$user_last_login->type      = 'int';
$user_last_login->len       = 16;
$user_last_login->fieldname = 'user_last_login';
$user_last_login->label     = 'User last login';
$user_last_login->editable  = false ;
$user_last_login->sortable  = true;
$user_last_login->searchable  = true;
$tabla->addCol($user_last_login);

$user_email = new Field();
$user_email->type      = 'varchar';
$user_email->len       = 150;
$user_email->fieldname = 'user_email';
$user_email->label     = 'User email';
$user_email->editable  = false ;
$user_email->sortable  = true;
$user_email->searchable  = true;
$tabla->addCol($user_email);

$user_url = new Field();
$user_url->type      = 'varchar';
$user_url->len       = 50;
$user_url->fieldname = 'user_url';
$user_url->label     = 'User url';
$user_url->editable  = false ;
$user_url->sortable  = true;
$user_url->searchable  = true;
$tabla->addCol($user_url);

$user_fullname = new Field();
$user_fullname->type      = 'varchar';
$user_fullname->len       = 150;
$user_fullname->fieldname = 'user_fullname';
$user_fullname->label     = 'User fullname';
$user_fullname->editable  = false ;
$user_fullname->sortable  = true;
$user_fullname->searchable  = true;
$tabla->addCol($user_fullname);

$user_ip = new Field();
$user_ip->type      = 'varchar';
$user_ip->len       = 15;
$user_ip->fieldname = 'user_ip';
$user_ip->label     = 'User ip';
$user_ip->editable  = false ;
$user_ip->sortable  = true;
$user_ip->searchable  = true;
$tabla->addCol($user_ip);

$user_salt = new Field();
$user_salt->type      = 'varchar';
$user_salt->len       = 3;
$user_salt->fieldname = 'user_salt';
$user_salt->label     = 'User salt';
$user_salt->editable  = false ;
$user_salt->sortable  = true;
$user_salt->searchable  = true;
$tabla->addCol($user_salt);

$user_active = new Field();
$user_active->type      = 'int';
$user_active->len       = 1;
$user_active->fieldname = 'user_active';
$user_active->label     = 'User active';
$user_active->editable  = false ;
$user_active->sortable  = true;
$user_active->searchable  = true;
$tabla->addCol($user_active);

$user_verify = new Field();
$user_verify->type      = 'int';
$user_verify->len       = 1;
$user_verify->fieldname = 'user_verify';
$user_verify->label     = 'User verify';
$user_verify->editable  = false ;
$user_verify->sortable  = true;
$user_verify->searchable  = true;
$tabla->addCol($user_verify);

$user_online = new Field();
$user_online->type      = 'int';
$user_online->len       = 1;
$user_online->fieldname = 'user_online';
$user_online->label     = 'User online';
$user_online->editable  = false ;
$user_online->sortable  = true;
$user_online->searchable  = true;
$tabla->addCol($user_online);

$user_url_avatar = new Field();
$user_url_avatar->type      = 'varchar';
$user_url_avatar->len       = 200;
$user_url_avatar->fieldname = 'user_url_avatar';
$user_url_avatar->label     = 'User url avatar';
$user_url_avatar->editable  = false ;
$user_url_avatar->sortable  = true;
$user_url_avatar->searchable  = true;
$tabla->addCol($user_url_avatar);

$user_signature = new Field();
$user_signature->type      = 'varchar';
$user_signature->len       = 45;
$user_signature->fieldname = 'user_signature';
$user_signature->label     = 'User signature';
$user_signature->editable  = false ;
$user_signature->sortable  = true;
$user_signature->searchable  = true;
$tabla->addCol($user_signature);

$user_notes = new Field();
$user_notes->type      = 'textarea';
$user_notes->fieldname = 'user_notes';
$user_notes->label     = 'User notes';
$user_notes->editable  = false ;
$user_notes->sortable  = true;
$user_notes->searchable  = true;
$tabla->addCol($user_notes);

$user_confirm_code = new Field();
$user_confirm_code->type      = 'varchar';
$user_confirm_code->len       = 100;
$user_confirm_code->fieldname = 'user_confirm_code';
$user_confirm_code->label     = 'User confirm code';
$user_confirm_code->editable  = false ;
$user_confirm_code->sortable  = true;
$user_confirm_code->searchable  = true;
$tabla->addCol($user_confirm_code);

$user_notify = new Field();
$user_notify->type      = 'int';
$user_notify->len       = 1;
$user_notify->fieldname = 'user_notify';
$user_notify->label     = 'User notify';
$user_notify->editable  = false ;
$user_notify->sortable  = true;
$user_notify->searchable  = true;
$tabla->addCol($user_notify);

$id_pais = new Field();
$id_pais->type      = 'int';
$id_pais->len       = 5;
$id_pais->fieldname = 'id_pais';
$id_pais->label     = 'Id pais';
$id_pais->editable  = false ;
$id_pais->sortable  = true;
$id_pais->searchable  = true;
$tabla->addCol($id_pais);

$id_provincia = new Field();
$id_provincia->type      = 'int';
$id_provincia->len       = 5;
$id_provincia->fieldname = 'id_provincia';
$id_provincia->label     = 'Id provincia';
$id_provincia->editable  = false ;
$id_provincia->sortable  = true;
$id_provincia->searchable  = true;
$tabla->addCol($id_provincia);

$id_municipio = new Field();
$id_municipio->type      = 'int';
$id_municipio->len       = 5;
$id_municipio->fieldname = 'id_municipio';
$id_municipio->label     = 'Id municipio';
$id_municipio->editable  = false ;
$id_municipio->sortable  = true;
$id_municipio->searchable  = true;
$tabla->addCol($id_municipio);

$id_localidad = new Field();
$id_localidad->type      = 'int';
$id_localidad->len       = 5;
$id_localidad->fieldname = 'id_localidad';
$id_localidad->label     = 'Id localidad';
$id_localidad->editable  = false ;
$id_localidad->sortable  = true;
$id_localidad->searchable  = true;
$tabla->addCol($id_localidad);

$user_card_id = new Field();
$user_card_id->type      = 'varchar';
$user_card_id->len       = 20;
$user_card_id->fieldname = 'user_card_id';
$user_card_id->label     = 'User card';
$user_card_id->editable  = false ;
$user_card_id->sortable  = true;
$user_card_id->searchable  = true;
$tabla->addCol($user_card_id);

$user_lpd_data = new Field();
$user_lpd_data->type      = 'int';
$user_lpd_data->len       = 1;
$user_lpd_data->fieldname = 'user_lpd_data';
$user_lpd_data->label     = 'User lpd data';
$user_lpd_data->editable  = false ;
$user_lpd_data->sortable  = true;
$user_lpd_data->searchable  = true;
$tabla->addCol($user_lpd_data);

$user_lpd_publi = new Field();
$user_lpd_publi->type      = 'int';
$user_lpd_publi->len       = 1;
$user_lpd_publi->fieldname = 'user_lpd_publi';
$user_lpd_publi->label     = 'User lpd publi';
$user_lpd_publi->editable  = false ;
$user_lpd_publi->sortable  = true;
$user_lpd_publi->searchable  = true;
$tabla->addCol($user_lpd_publi);

$created_by = new Field();
$created_by->type      = 'int';
$created_by->len       = 5;
$created_by->fieldname = 'CREATED_BY';
$created_by->label     = 'Created by';
$created_by->editable  = false ;
$created_by->sortable  = true;
$created_by->searchable  = true;
$tabla->addCol($created_by);

$creation_date = new Field();
$creation_date->type      = 'datetime';
$creation_date->fieldname = 'CREATION_DATE';
$creation_date->label     = 'Creation date';
$creation_date->editable  = false ;
$creation_date->sortable  = true;
$creation_date->searchable  = true;
$tabla->addCol($creation_date);

$last_updated_by = new Field();
$last_updated_by->type      = 'int';
$last_updated_by->len       = 5;
$last_updated_by->fieldname = 'LAST_UPDATED_BY';
$last_updated_by->label     = 'Last updated by';
$last_updated_by->editable  = false ;
$last_updated_by->sortable  = true;
$last_updated_by->searchable  = true;
$tabla->addCol($last_updated_by);

$last_update_date = new Field();
$last_update_date->type      = 'datetime';
$last_update_date->fieldname = 'LAST_UPDATE_DATE';
$last_update_date->label     = 'Last update date';
$last_update_date->editable  = false ;
$last_update_date->sortable  = true;
$last_update_date->searchable  = true;
$tabla->addCol($last_update_date);

$auth_id = new Field();
$auth_id->type      = 'varchar';
$auth_id->len       = 50;
$auth_id->fieldname = 'AUTH_ID';
$auth_id->label     = 'Auth';
$auth_id->editable  = false ;
$auth_id->sortable  = true;
$auth_id->searchable  = true;
$tabla->addCol($auth_id);

$auth_provider = new Field();
$auth_provider->type      = 'varchar';
$auth_provider->len       = 15;
$auth_provider->fieldname = 'AUTH_PROVIDER';
$auth_provider->label     = 'Auth provider';
$auth_provider->editable  = false ;
$auth_provider->sortable  = true;
$auth_provider->searchable  = true;
$tabla->addCol($auth_provider);

$auth_picture = new Field();
$auth_picture->type      = 'varchar';
$auth_picture->len       = 150;
$auth_picture->fieldname = 'AUTH_PICTURE';
$auth_picture->label     = 'Auth picture';
$auth_picture->editable  = false ;
$auth_picture->sortable  = true;
$auth_picture->searchable  = true;
$tabla->addCol($auth_picture);

$rfid = new Field();
$rfid->type      = 'varchar';
$rfid->len       = 10;
$rfid->fieldname = 'RFID';
$rfid->label     = 'Rfid';
$rfid->editable  = false ;
$rfid->sortable  = true;
$rfid->searchable  = true;
$tabla->addCol($rfid);

$balance = new Field();
$balance->type      = 'decimal';
$balance->len       = '7,2';
$balance->fieldname = 'BALANCE';
$balance->label     = 'Balance';
$balance->editable  = false ;
$balance->sortable  = true;
$balance->searchable  = true;
$tabla->addCol($balance);

$api_key = new Field();
$api_key->type      = 'varchar';
$api_key->len       = 50;
$api_key->fieldname = 'api_key';
$api_key->label     = 'Api key';
$api_key->editable  = false ;
$api_key->sortable  = true;
$api_key->searchable  = true;
$tabla->addCol($api_key);

$user_score = new Field();
$user_score->type      = 'int';
$user_score->len       = 10;
$user_score->fieldname = 'user_score';
$user_score->label     = 'User score';
$user_score->editable  = false ;
$user_score->sortable  = true;
$user_score->searchable  = true;
$tabla->addCol($user_score);

$balance_sats = new Field();
$balance_sats->type      = 'int';
$balance_sats->len       = 10;
$balance_sats->fieldname = 'balance_sats';
$balance_sats->label     = 'Balance sats';
$balance_sats->editable  = false ;
$balance_sats->sortable  = true;
$balance_sats->searchable  = true;
$tabla->addCol($balance_sats);

$lightning_address = new Field();
$lightning_address->type      = 'varchar';
$lightning_address->len       = 255;
$lightning_address->fieldname = 'lightning_address';
$lightning_address->label     = 'Lightning address';
$lightning_address->editable  = false ;
$lightning_address->sortable  = true;
$lightning_address->searchable  = true;
$tabla->addCol($lightning_address);

$nostr_pubkey = new Field();
$nostr_pubkey->type      = 'textarea';
$nostr_pubkey->fieldname = 'nostr_pubkey';
$nostr_pubkey->label     = 'Nostr pubkey';
$nostr_pubkey->editable  = false ;
$nostr_pubkey->sortable  = true;
$nostr_pubkey->searchable  = true;
$tabla->addCol($nostr_pubkey);

$pin = new Field();
$pin->type      = 'varchar';
$pin->len       = 255;
$pin->fieldname = 'PIN';
$pin->label     = 'Pin';
$pin->editable  = false ;
$pin->sortable  = true;
$pin->searchable  = true;
$tabla->addCol($pin);

$device_link_token = new Field();
$device_link_token->type      = 'varchar';
$device_link_token->len       = 64;
$device_link_token->fieldname = 'device_link_token';
$device_link_token->label     = 'Device link token';
$device_link_token->editable  = false ;
$device_link_token->sortable  = true;
$device_link_token->searchable  = true;
$tabla->addCol($device_link_token);

$device_link_expires = new Field();
$device_link_expires->type      = 'int';
$device_link_expires->len       = 11;
$device_link_expires->fieldname = 'device_link_expires';
$device_link_expires->label     = 'Device link expires';
$device_link_expires->editable  = false ;
$device_link_expires->sortable  = true;
$device_link_expires->searchable  = true;
$tabla->addCol($device_link_expires);

$tabla->name = 'CLI_USER';
$tabla->title = 'CLIUSER';
$tabla->verbose=false;
$tabla->output='table';
$tabla->page = $page;
$tabla->page_num_items = 10;
$tabla->show_empty_rows = true;
$tabla->show_inputsearch =true;

$tabla->perms['delete'] = Administrador();
$tabla->perms['edit']   = Administrador();
$tabla->perms['add']    = Administrador();
$tabla->perms['setup']  = Root();
$tabla->perms['reload'] = true;
$tabla->perms['filter'] = true;
$tabla->perms['view']   = true;


class CLI_USEREvents extends defaultTableEvents implements iEvents{
  function OnInsert($owner,&$result,&$post) { 
      $result['error'] = 5;
      $result['msg'] = '¡Esto es el evento OnInsert!';
  }
  function OnUpdate($owner,&$result,&$post) { 
      $result['error'] =5;
      $result['msg'] = '¡Esto es el evento OnUpdate! ';
  }
  function OnDelete($owner,&$result,$id)    { 
      $result['error'] =5;
      $result['msg'] = '¡Esto es el evento OnDelete!';
  }
}
$tabla->events = New CLI_USEREvents();



