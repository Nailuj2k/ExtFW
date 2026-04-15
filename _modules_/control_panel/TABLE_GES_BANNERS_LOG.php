<?php

$tabla = new TableMysql('GES_BANNERS_LOG');

$id = new Field();
$id->type      = 'int';
$id->len       = 7;
$id->fieldname = 'ID';
$id->label     = 'Id';
$id->width     = 25;
$id->hide     = true;
$tabla->addCol($id);

$id_banner = new Field();
$id_banner->type      = 'int';
$id_banner->len       = 5;
$id_banner->fieldname = 'ID_BANNER';
$id_banner->label     = 'Banner';
//$id_banner->hide  = true;
$id_banner->width     = 25;
$id_banner->editable  = false;
$id_banner->readonly = true;
$tabla->addCol($id_banner);

$date = new Field();
$date->type      = 'datetime';
$date->fieldname = 'TIME';
$date->label     = 'Fecha';   
$date->len       = 16;
$date->width     = 120;
$date->filtrable = true;
$date->searchable = true;
$date->editable = false;
$date->readonly = true;
$tabla->addCol($date);

$ip = new Field();
$ip->type      = 'varchar';
$ip->fieldname = 'IP';
$ip->label     = 'IP';   
$ip->len       = 15;
$ip->filtrable = true;
$ip->searchable = true;
$ip->editable = false;
$ip->readonly = true;
$tabla->addCol($ip);

$token            = new Field();
$token->type      = 'varchar';
//$token->width     = 65;
$token->fieldname = 'TOKEN';
$token->label     = 'Token';   
$token->len = 65;
$token->readonly    = true;
//$token->hide    = true;
$tabla->addCol($token);

$browser = new Field();
$browser->type      = 'varchar';
$browser->fieldname = 'BROWSER';
$browser->label     = 'Browser';   
$browser->len       = 50;
$browser->filtrable = true;
$browser->editable = false;
$browser->readonly = true;
$tabla->addCol($browser);

$version = new Field();
$version->type      = 'varchar';
$version->fieldname = 'VERSION';
$version->label     = 'Ver';   
$version->len       = 15;
$version->filtrable = true;
$version->editable = false;
$version->readonly = true;
$version->hide = true;
$tabla->addCol($version);

$platform = new Field();
$platform->type      = 'varchar';
$platform->fieldname = 'PLATFORM';
$platform->label     = 'Platform';   
$platform->len       = 50;
$platform->filtrable = true;
$platform->editable = false;
$platform->readonly = true;
$tabla->addCol($platform);

$city = new Field();
$city->type      = 'varchar';
$city->fieldname = 'CITY';
$city->label     = 'Ciudad';   
$city->len       = 15;
$city->filtrable = true;
$city->editable = false;
$city->readonly = true;
$tabla->addCol($city);

$region = new Field();
$region->type      = 'varchar';
$region->fieldname = 'REGION';
$region->label     = 'Región';   
$region->len       = 15;
$region->filtrable = true;
$region->editable = false;
$region->readonly = true;
$tabla->addCol($region);

$country = new Field();
$country->type      = 'varchar';
$country->fieldname = 'COUNTRY';
$country->label     = 'País';   
$country->len       = 15;
$country->filtrable = true;
$country->editable = false;
$country->readonly = true;
$tabla->addCol($country);

$action = new Field();
$action->fieldname = 'ACTION';
$action->label     = 'Acción'; 
$action->type      = 'select';
$action->values    = array('1'=>'show','2'=>'click'); 
$action->editable  = Administrador();
$action->default_value = 1;
$action->filtrable = true;
$action->editable = true;
$tabla->addCol($action);

$uri = new Field();
$uri->type      = 'varchar';
$uri->fieldname = 'REQUEST_URI';
$uri->label     = 'Uri';   
$uri->len       = 250;
$uri->filtrable = true;
$uri->searchable = true;
$uri->editable = false;
$uri->readonly = true;
//$uri->hide = true;
$tabla->addCol($uri);

$uagent = new Field();
$uagent->type      = 'varchar';
$uagent->fieldname = 'USER_AGENT';
$uagent->label     = 'User agent';   
$uagent->len       = 250;
$uagent->filtrable = true;
$uagent->editable = false;
$uagent->readonly = true;
$uagent->hide = true;
$tabla->addCol($uagent);

$lat = new Field();
$lat->fieldname  = 'LAT';
$lat->label      = 'Lat';   
$lat->len        = 15;
$lat->width      = 25;
$lat->type       = 'varchar';
//$lat->calculated = true;
//$lat->hide    = true;
//$lat->fieldset = 'Imagen';
//$lat->sortable = false;
$lat->editable = true;
$tabla->addCol($lat);

$lon = new Field();
$lon->fieldname  = 'LON';
$lon->label      = 'Lon';   
$lon->len        = 15;
$lon->width      = 25;
$lon->type       = 'varchar';
//$lon->calculated = true;
//$lon->hide    = true;
//$lon->fieldset = 'Imagen';
//$lon->sortable = false;
$lon->editable = true;
$tabla->addCol($lon);


//$tabla->addWhoColumns();

$tabla->title = 'Seguimiento';
$tabla->page_num_items = 20;
$tabla->verbose = false;
$tabla->showtitle = false;

$tabla->perms['delete'] = Root(); //Administrador();
$tabla->perms['edit']   = true;//Root(); //Administrador();
$tabla->perms['add']    = Root(); //Administrador();
$tabla->perms['setup']  = Root();
$tabla->perms['reload'] = true;
$tabla->perms['filter'] = true;
$tabla->perms['view']   = true;
$tabla->perms['show']   = true;
//$tabla->colByName('CREATION_DATE')->hide = false;

$tabla->orderby = 'TIME DESC';
if($parent) $tabla->setParent('ID_BANNER', $parent ); 

//$tabla->sql_query('TRUNCATE TABLE GES_BANNERS_LOG');

class GES_BANNERS_LOG_Events extends defaultTableEvents implements iEvents{
/*
  function OnCalculate($owner,&$row) { 
      $new_arr[]= unserialize(file_get_contents('http://www.geoplugin.net/php.gp?ip='.$row['IP']));
      // echo "Latitude:".$new_arr[0]['geoplugin_latitude']." and Longitude:".$new_arr[0]['geoplugin_longitude'];
      $row['LAT'] = $new_arr[0]['geoplugin_latitude'];
      $row['LON'] = $new_arr[0]['geoplugin_longitude'];
  }
*/
  function OnDrawRow($owner,&$row,&$class){
  
     if($row['ACTION']=='2') {
          $class.=' click';
     }


  }

  function OnInsert($owner,&$result,&$post) {  

     $br = Browser::get();
     $lo = Location::details();
     $post['USER_AGENT'] = $br['userAgent'];
     $post['BROWSER'] = $br['name'];
     $post['VERSION'] = $br['version'];
     $post['PLATFORM'] = $br['platform'];
     $post['IP'] = Location::ip();
     /******
      $new_arr[]= unserialize(file_get_contents('http://www.geoplugin.net/php.gp?ip='.$post['IP']));
      // echo "Latitude:".$new_arr[0]['geoplugin_latitude']." and Longitude:".$new_arr[0]['geoplugin_longitude'];
      $post['LAT'] = $new_arr[0]['geoplugin_latitude'];
      $post['LON'] = $new_arr[0]['geoplugin_longitude'];
     *********/

     $post['COUNTRY'] = $lo->country;
     $post['REGION'] = $lo->region;
     $post['CITY'] = $lo->city;
     $post['TIME'] = $owner->sql_currentdate(); //time(); //DateTime::createFromFormat('d/m/Y H:i:s',date('d/m/Y H:i:s'))->format('d-m-Y H:i:s');
     $post['REQUEST_URI'] = $_SERVER['REQUEST_URI'];
  
  }

  function OnShow($owner){
  /*
     $br = Browser::get();
*/
  }



    function OnUpdate($owner,&$result,&$post) { 
      $new_arr[]= unserialize(file_get_contents('http://www.geoplugin.net/php.gp?ip='.$post['IP']));
      // echo "Latitude:".$new_arr[0]['geoplugin_latitude']." and Longitude:".$new_arr[0]['geoplugin_longitude'];
      $post['LAT'] = $new_arr[0]['geoplugin_latitude'];
      $post['LON'] = $new_arr[0]['geoplugin_longitude'];

 }


}

$tabla->events = New GES_BANNERS_LOG_Events();
