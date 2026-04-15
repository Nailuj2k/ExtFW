<?php 

/**
 * CLI_USER_KEYS - Tabla para gestionar múltiples dispositivos autorizados por usuario
 * Cada registro representa un dispositivo/navegador autorizado para login sin contraseña
 */

$tabla = new TableMysql('CLI_USER_KEYS');
$tabla->output='table';
$tabla->page = $page;

$id            = new Field();        //
$id->type      = 'int';
$id->width     = 50;
$id->fieldname = 'ID';       //Primary key auto invcrement
$id->label     = 'Id';   
$id->len = 12;
//$id->hide    = true;

$id_user = new Field();
$id_user->fieldname = 'id_user';          //User foreign key
$id_user->label     = 'Usuario';
$id_user->len     = 10;
$id_user->type      = 'int';
$id_user->hide      = true;

$device_id = new Field();
$device_id->type      = 'varchar';
$device_id->fieldname = 'device_id';     // UUID único del dispositivo generado en cliente
$device_id->label     = 'Device ID';
$device_id->len       = 36;
$device_id->editable  = false;
$device_id->sortable  = true;
$device_id->searchable  = true;
$device_id->filtrable = true;

$device_name            = new Field();
$device_name->type      = 'varchar';
$device_name->fieldname = 'device_name';
$device_name->label     = 'Nombre';    // Nombre que el usuario da al dispositivo (ej: "Firefox en MacBook", "Chrome en móvil")
$device_name->len       = 100;
$device_name->editable  = true;
$device_name->filtrable = true;

$sign_public_key = new Field();
$sign_public_key->type      = 'textarea';
$sign_public_key->fieldname = 'sign_public_key';  //  Clave pública ECDSA para verificar firmas
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
$enc_public_key->fieldname = 'enc_public_key';           // Clave pública ECDH para cifrado
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

$user_agent = new Field();
$user_agent->type      = 'varchar';
$user_agent->len       = 255;
$user_agent->fieldname = 'user_agent';
$user_agent->label     = 'User agent';
$user_agent->editable  = false ;
$user_agent->sortable  = true;
$user_agent->searchable  = true;

$last_used_at = new Field();
$last_used_at->type = 'unixtime';
//$last_used_at->len = 15;
$last_used_at->fieldname = 'last_used_at';
$last_used_at->label = 'Último uso';  
$last_used_at->editable  = false; 
$last_used_at->readonly  = true;
//$last_used_at->default_value    = time();
$last_used_at->fieldset = 'keys';
$last_used_at->width  = 300;


$tabla->addCol($id);
$tabla->addCol($id_user);
$tabla->addCol($device_id);
$tabla->addCol($device_name);
$tabla->addCol($sign_public_key);
$tabla->addCol($enc_public_key);
$tabla->addCol($user_agent);
$tabla->addCol($last_used_at);
$tabla->addActiveCol();


$tabla->perms['setup']  = Root();  
$tabla->perms['reload'] = Root();  
$tabla->perms['pdf']    = false; // Administrador();  
$tabla->perms['print']  = false;  
$tabla->perms['add'] = true;  
$tabla->perms['edit'] = true; 
$tabla->perms['delete'] = true;  
$tabla->perms['filter'] = true;  
$tabla->page_num_items = 4;
$tabla->show_inputsearch =false;
$tabla->orderby='last_used_at DESC';

$tabla->setParent('id_user', $parent); 

class userKeysEvents extends defaultTableEvents implements iEvents{

    private function getBrowserIconHTML($userAgent) {
        $ua = strtolower($userAgent);

        if (strpos($ua, 'edg/') !== false) {
            return '<i class="fa fa-edge"></i> Edge';
        } elseif (strpos($ua, 'chrome/') !== false) {
            return '<i class="fa fa-chrome"></i> Chrome';
        } elseif (strpos($ua, 'firefox/') !== false) {
            return '<i class="fa fa-firefox"></i> Firefox';
        } elseif (strpos($ua, 'safari/') !== false && strpos($ua, 'chrome/') === false) {
            return '<i class="fa fa-safari"></i> Safari';
        } elseif (strpos($ua, 'opera/') !== false || strpos($ua, 'opr/') !== false) {
            return '<i class="fa fa-opera"></i> Opera';
        }

        return '<i class="fas fa-browser"></i> Navegador';
    }

    function OnDrawRow($owner,&$row,&$class){
        $class .= ' device-'.$row['device_id'];
        $row['user_agent'] = '<span title="'.htmlspecialchars($row['user_agent']).'">'.$this->getBrowserIconHTML($row['user_agent']).'</span>';
        //$row['last_used_at'] = fuzzyDate($row['last_used_at']);
    }

    function OnInsert($owner,&$result,&$post) {
         $post['last_used_at'] = time();
    }
    function OnUpdate($owner,&$result,&$post) {  }
    function OnDelete($owner,&$result,$id)    {  }
    function OnShow($owner)                   {  }
    function OnCalculate($owner,&$row)        {  }
    function OnAfterShow($owner)              {


        ?>
        <script type="text/javascript">
            $(document).ready(function() {

                console.log('OnAfterShow');
                let deviceId = localStorage.getItem('passwordless_device_id');
                if (deviceId) {
                    console.log('Highlighting device row: ' + deviceId);
                    $('.device-' + deviceId+' td').css('background-color', '#35a4dc').css('color', '#ffeb0b');
                }   

            });
        </script>
        <?php

    }

}

$tabla->events = New userKeysEvents();
