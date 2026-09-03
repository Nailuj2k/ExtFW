<?php 

$tabla = new myTableMysql('GES_BANNERS');

$id = new Field();
$id->type      = 'int';
$id->len       = 5;
$id->fieldname = 'ID';
$id->label     = 'Id';
$id->width     = 25;
$tabla->addCol($id);

$id_user = new Field();
$id_user->fieldname = 'id_user';
$id_user->label     = 'Usuario'; 
$id_user->width     = 150;
$id_user->max_chars = 30;        
$id_user->classname = 'fullname';
$id_user->type      = 'select';
$id_user->values    =  $tabla->toarray('users',    "SELECT user_id AS ID, CONCAT(username,' - ',IFNULL(user_fullname,'..')) AS NAME FROM ".TB_USER." WHERE user_level<3000 AND user_active=1 ORDER BY NAME",true); 
$id_user->values_all=  $tabla->toarray('users_all',  TB_USER,  'user_id', 'username'  ,' WHERE user_level<31000',true); 
$id_user->editable  = Administrador();
$id_user->default_value = 0; //$_SESSION['socioid'];
//$id_user->not_null  = true;
$id_user->allowNull = true;   
$id_user->filtrable = true;   
$id_user->multiselect = true;   
$tabla->addCol($id_user);

$title = new Field();
$title->type      = 'varchar';
$title->len       = 250;
$title->fieldname = 'TITLE';
$title->label     = 'Title';
$title->editable  = true ;
$title->sortable  = true;
$title->width     = 300;
$tabla->addCol($title);

$url = new Field();
$url->type      = 'varchar';
$url->len       = 250;
$url->fieldname = 'LINK';
$url->label     = 'Link Url';
$url->editable  = true ;
$url->sortable  = true;
$url->width     = 100;
$url->classname = 'fullname';
$tabla->addCol($url);

$target = new Field();
$target->type      = 'bool';
$target->len       = 5;
$target->fieldname = 'TARGET';
$target->label     = 'Abrir en nueva ventana';
//$target->textafter     = '<span class="info">Abrir en nueva ventana</span>';
$target->editable     = true;
$target->hide     = true;
$target->width     = 25;
$tabla->addCol($target);

$nofollow = new Field();
$nofollow->type      = 'bool';
$nofollow->len       = 5;
$nofollow->fieldname = 'NOFOLLOW';
$nofollow->label     = 'Marcar como NOFOLLOW';
//$target->textafter     = '<span class="info">Abrir en nueva ventana</span>';
$nofollow->editable     = true;
$nofollow->hide     = true;
$nofollow->width     = 25;
$tabla->addCol($nofollow);

$popup = new Field();
$popup->type      = 'bool';
$popup->len       = 5;
$popup->fieldname = 'POPUP';
$popup->label     = 'Mostrar como popup';
//$target->textafter     = '<span class="info">Abrir en nueva ventana</span>';
$popup->editable     = true;
$popup->hide     = true;
$popup->width     = 25;
$tabla->addCol($popup);


$id_type = new Field();
$id_type->fieldname = 'ID_TYPE';
$id_type->label     = 'Tipo'; 
$id_type->width     = 150;
$id_type->max_chars = 30;        
$id_type->type      = 'select';
$id_type->values    = $tabla->toarray('types'     , "SELECT ID, NAME FROM GES_BANNERS_TYPES WHERE ACTIVE = 1",true); 
$id_type->values_all= $tabla->toarray('types_all' , "SELECT ID, NAME FROM GES_BANNERS_TYPES",true);
$id_type->editable  = Administrador();
$id_type->default_value = 0; //$_SESSION['socioid'];
//$id_user->not_null  = true;
//$id_type->allowNull = true;   
//$id_type->filtrable = true;   
//$id_type->multiselect = true;   
$tabla->addCol($id_type);
/*
$id_content = new Field();
$id_content->fieldname = 'ID_CONTENT';
$id_content->label     = 'Contenido'; 
$id_content->width     = 150;
$id_content->max_chars = 30;        
$id_content->type      = 'select';
$id_content->values    = array('1'=>'Imagen local','2'=>'Imagen remota','3'=>'Código html o Js'); 
$id_content->editable  = Administrador();
$id_content->default_value = 1; //$_SESSION['socioid'];
$tabla->addCol($id_content);
*/
$code = new Field();
$code->type      = 'textarea';
$code->fieldname = 'CODE';
$code->label     = 'Código';   
$code->editable = true;
$code->hide     = true;
$code->width     = 350;
$code->textafter     = '<span class="info">Sólo en banners de tipo código</span>';
$code->filtrable = false;
$code->wysiwyg = false;
$code->fieldset = 'Código';
$code->default_value = '<div style="background-color:red;">TEST</div>'."\n"
                     . '<script type="text/javascript">'."\n"
                     . '  $(function() {'."\n"
                     . '     // javascript code ... '."\n"
                     . '  });'."\n"
                     . '</script>';
$tabla->addCol($code);

$image = new Field();
$image->fieldname = 'IMAGE';
$image->label     = 'Imagen';   
$image->len       = 50;
$image->type      = 'file';
/*
$image->crop        = true;
$image->crop_width  = 220;
$image->crop_height = 315;
$image->crop_urlpath     = '_images_/images/';//"/_classes_/scaffold/ajax.php?
$image->crop_upload_url = AJAX_URL.'?module='.MODULE.'&ajax=function&function=imagereceive&table='.$tabla->tablename.'&id=';
$image->crop_resize_url = AJAX_URL.'?module='.MODULE.'&ajax=function&function=resizeimage&table='.$tabla->tablename.'&id=';
$image->crop_delete_url = AJAX_URL.'?module='.MODULE.'&ajax=function&function=deleteimage&table='.$tabla->tablename.'&id=';
$image->crop_trim_button = true; //Administrador();
$image->crop_resize_button =Administrador();
$image->crop_face_detect =Administrador();
*/
$image->editable = true;
$image->uploaddir = CFG::$vars['files_private_path'].'banners';
$image->inline_edit = false;
$image->accepted_extensions = array( '.jpg', '.png', '.gif');  //$accepted_img_extensions;  
$image->fieldset = 'Imagen';
$image->prefix_filename = true;
$image->hide = true;
$tabla->addCol($image);
/*
$w = new Field();
$w->type      = 'int';
$w->len       = 7;
$w->fieldname = 'W';
$w->label     = 'Anchura';
$w->calculated  = true ;
$tabla->addCol($w);

$h = new Field();
$h->type      = 'int';
$h->len       = 7;
$h->fieldname = 'H';
$h->label     = 'Altura';
$h->calculated  = true ;
$tabla->addCol($h);
*/
$startdate = new Field();
$startdate->type      = 'varchar';
$startdate->fieldname = 'START_DATE';
$startdate->label     = 'Inicio';   
$startdate->editable  = true;
$startdate->len = 15;
$startdate->width = 90;
$startdate->datepicker =true;   
$startdate->default_value=DateTime::createFromFormat('d/m/Y',date('d/m/Y'))->format('d-m-Y');
$tabla->addCol($startdate);

$enddate = new Field();
$enddate->type      = 'varchar';
$enddate->fieldname = 'END_DATE';
$enddate->label     = 'Fin';   
$enddate->editable  = true;
$enddate->len = 15;
$enddate->width = 90;
$enddate->datepicker =true;   
$enddate->default_value = DateTime::createFromFormat('d/m/Y',date('d/m/Y'))->add(new DateInterval('P2M'))->format('d-m-Y');
$tabla->addCol($enddate);

/*
$id_ubication = new Field();
$id_ubication->fieldname = 'ID_UBICATION';
$id_ubication->label     = 'Ubicación'; 
$id_ubication->width     = 150;
$id_ubication->max_chars = 30;        
$id_ubication->type      = 'select';
$id_ubication->values    = $tabla->toarray('ubications'     , "SELECT ID, NAME FROM GES_BANNERS_UBICATIONS WHERE ACTIVE = 1",true); 
$id_ubication->values_all= $tabla->toarray('ubications_all' , "SELECT ID, NAME FROM GES_BANNERS_UBICATIONS",true);
$id_ubication->editable  = Administrador();
$id_ubication->default_value = 0; //$_SESSION['socioid'];
//$id_user->not_null  = true;
//$id_ubication->allowNull = true;   
//$id_ubication->filtrable = true;   
//$id_ubication->multiselect = true;   
$tabla->addCol($id_ubication);

*/


$shows = new Field();
$shows->type      = 'int';
$shows->len       = 5;
$shows->fieldname = 'SHOWS';
$shows->label     = 'Shows';
$shows->width     = 25;
$shows->default_value = '0';
$shows->calculated  = true ;
$tabla->addCol($shows);

$clicks = new Field();
$clicks->type      = 'int';
$clicks->len       = 5;
$clicks->fieldname = 'CLICKS';
$clicks->label     = 'clicks';
$clicks->width     = 25;
$clicks->default_value = '0';
$clicks->calculated  = true ;
$tabla->addCol($clicks);

$shortcode = new Field();
$shortcode->type      = 'varchar';
$shortcode->len       = 100;
$shortcode->width     = 350;
$shortcode->fieldname = 'SHORTCODE';
$shortcode->label     = 'JS Code / PHP Code';
$shortcode->calculated = true;
$shortcode->width = 220;
$shortcode->hide = true;
$shortcode->visible = true;
$shortcode->visible = true;
$tabla->addCol($shortcode);

$preview = new Field();
$preview->fieldname  = 'PREVIEW';
$preview->label      = 'Previsualización';   
$preview->len        = 7;
$preview->width      = 85;
$preview->type       = 'varchar';
$preview->calculated = true;
//$preview->visible = true;
$preview->hide    = true;
$preview->fieldset = 'Imagen';
$preview->sortable = false;
$tabla->addCol($preview);


$tabla->addActiveCol();

//$tabla->addFieldset('log', 'Log'); 


$tabla->name = 'GES_BANNERS';
$tabla->title = 'Banners';
$tabla->verbose=false;
$tabla->output='table';
$tabla->page = $page;
$tabla->page_num_items = 12;
$tabla->show_empty_rows = true;
$tabla->show_inputsearch =true;

$tabla->perms['delete'] = Administrador();
$tabla->perms['edit']   = Administrador();
$tabla->perms['add']    = Administrador();
$tabla->perms['setup']  = Root();
$tabla->perms['reload'] = true;
$tabla->perms['filter'] = true;
$tabla->perms['view']   = true;
$tabla->perms['show']   = true;

class myTableMysql extends TableMysql{

   
    private function insertAction($action,$params) {
        //if($action==2) $sql = 'UPDATE GES_BANNERS SET CLICKS=CLICKS+1 WHERE ID = '.$id;
        //          else $sql = 'UPDATE GES_BANNERS SET SHOWS=SHOWS+1 WHERE ID = '.$id;
        $ok = $this->sql_query($sql);
      
        $br = Browser::get();
     // $lo = Location::details();

        $USER_AGENT = $br['userAgent'];
        $BROWSER = $br['name'];
        $VERSION = $br['version'];
        $PLATFORM = $br['platform'];
        $IP = Location::ip();
        // https://stackoverflow.com/questions/959957/php-short-hash-like-url-shortening-websites

      $new_arr[]= unserialize(file_get_contents('http://www.geoplugin.net/php.gp?ip='.$post['IP']));
      // echo "Latitude:".$new_arr[0]['geoplugin_latitude']." and Longitude:".$new_arr[0]['geoplugin_longitude'];


      $LAT = $new_arr[0]['geoplugin_latitude'];
      $LON = $new_arr[0]['geoplugin_longitude'];

        $TOKEN = hash("crc32",$_SESSION['token']);
      //$COUNTRY = $lo->country;
        $COUNTRY = $new_arr[0]['geoplugin_countryCode'];
      //$REGION = $lo->region;
        $REGION = $new_arr[0]['geoplugin_regionName'];
      //$CITY = $lo->city;
        $CITY = $new_arr[0]['geoplugin_city'];
        $TIME = DateTime::createFromFormat('d/m/Y H:i:s',date('d/m/Y H:i:s'))->format('d-m-Y H:i:s');
        $sql2 = "INSERT INTO GES_BANNERS_LOG (ID_BANNER,USER_AGENT,BROWSER,VERSION,PLATFORM,IP,LAT,LON,TOKEN,COUNTRY,REGION,CITY,TIME,ACTION,REQUEST_URI) "
              ."VALUES( {$params['id']}, '$USER_AGENT','$BROWSER','$VERSION','$PLATFORM','$IP','$LAT','$LON','$TOKEN','$COUNTRY','$REGION','$CITY',NOW(),$action,'{$params['uri']}')";      
        //      echo $sql2;
        $ok2 = $this->sql_query($sql2);
    }

    public function print_banner($params){
        $result = array();
        $result['error']=0;
        $result['msg'] = 'ok';
        $row = $this->getRow($params['id']);
        if($row){
            $result['link'] = $row['LINK'];
            $style = ' style="display:inline-block;width:'.$row['W'].'px;height:'.$row['H'].'px;" ';
            $style_img = ' style="width:'.$row['W'].'px;height:'.$row['H'].'px;" ';
            $nofollow = $row['NOFOLLOW'] ? 'rel="nofollow"' : '';
            $target   = $row['TARGET']   ? 'target="new"'   : '';
            $popup    = $row['POPUP']    ? 'class="banner-popup"' : ''; 
            $result['type'] = $row['TYPE'];
            if      ($row['TYPE'] == 1){  // Imagen local
              $result['html'] = '<img '.$popup .' src="/'.$row['URL'].'">';
            }else if($row['TYPE'] == 2){  // Imagen remota
              $result['html'] = '<img '.$popup .'  src="/'.$row['URL'].'">';
            }else if($row['TYPE'] == 3){  // Código html o Js
              $result['html'] = $row['CODE'];
            }else{
              $result['html'] = '<div style="background-color:#efefef;border:1px solid silver;display:block;height:100px;"> ... </div>';
            }
            
            //if(!Administrador()) 
                $this->insertAction(1,$params);
        }else{
            $result['html'] = '...';
        }
        echo json_encode($result);
    }

    public function click($params){
        $result = array();
        $result['error']=0;
        $result['msg'] = 'ok';
        //if(!Administrador()) 
            $this->insertAction(2,$params);
        echo json_encode($result);
    }

}

class GES_BANNERSEvents extends defaultTableEvents implements iEvents{

    function OnCalculate($owner,&$row) { 
        $row['PREVIEW'] = '<a class="open_file_image" href="'.$row['URL'].'"><img src="'.$row['URL'].'"></a>';
        $row['SHORTCODE'] = "&lt;?php Banner::get(".$row['ID'].");?&gt;";
        $type = $owner->asArray('SELECT ID,W,H,ID_TYPE FROM GES_BANNERS_TYPES WHERE ID='.$row['ID_TYPE'],false,'ID');
        $row['W'] = $type[$row['ID_TYPE']]['W']; //((print_r($type,true); //;
        $row['H'] = $type[$row['ID_TYPE']]['H'];
        $row['TYPE'] = $type[$row['ID_TYPE']]['ID_TYPE'];
        $row['SHOWS']  = $owner->getFieldValue("SELECT COUNT(ID) FROM GES_BANNERS_LOG WHERE ID_BANNER=".$row['ID']." AND ACTION='1'");
        $row['CLICKS'] = $owner->getFieldValue("SELECT COUNT(ID) FROM GES_BANNERS_LOG WHERE ID_BANNER=".$row['ID']." AND ACTION='2'");
    }

    function OnBeforeShowForm($owner,&$form,$id) {
        if($owner->state == 'update'){
    
            if($id) $row = $owner->getRow($id);
            $html_preview = new formElementHtml();
            $html_preview->html = '' //<pre>'.print_r($row,true).'</pre>'
                                . '<p style="margin:10px 0 10px 0;">Previsualización: </p>'
                                . ( $row['TYPE']<3 ? '<div><a class="open_file_image" href="'.$row['LINK'].'"><img src="'.$row['URL'].'"></a></div>'
                                                   : '<p id="NOdownloads" style="margin:20px auto;/*padding:15px;*/">'.$row['CODE'].'</p>')
                                . '<p style="margin:30px 0 0 0;">Código para insertar en el php:</p>'
                                . '<pre>'.$row['SHORTCODE'].'</pre>';

            $fs_preview = new fieldset('preview','Previsualización');  //fs_roles
            $fs_preview->displaytype = 'tab';
            $fs_preview->addElement($html_preview);
            $form->addElement($fs_preview);
        
            $detail_table = 'GES_BANNERS_LOG';

            Table::$module_name = 'control_panel';
            $parent = $id;
            $markup_ajax_loader = '<p style="text-align:center;border:1px solid green;"><img style="width:56px;" src="'.IMG_AJAX_LOADER.'"></p>';
            $html_code_lines = new formElementHtml();
            $html_code_lines->html = '<div class="datatable" id="T-'.$detail_table.'">'.$markup_ajax_loader.'</div>'
                                   .  '<script>load_page("control_panel","'.$detail_table.'",1,'.$id.',1);</script>' ;
            Table::show_table($detail_table,'control_panel',false);
            $fs_lines = new fieldset('log','Seguimiento');
            $fs_lines->displaytype = 'tab';
            $fs_lines->addElement($html_code_lines);
            $form->addElement($fs_lines);

        }
    } 



    function OnInsert($owner,&$result,&$post) {
        $post['SHOWS']='0';
        $post['CLICKS']='0'; 
    }
  
    function OnUpdate($owner,&$result,&$post) {   }
    function OnDelete($owner,&$result,$id)    {   }
}

$tabla->events = New GES_BANNERSEvents();

