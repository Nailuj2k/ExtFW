<?php 
/* Auto created */

$tabla = new TableMysql('CLI_QRCODES');

$id = new Field();
$id->type      = 'int';
$id->len       = 5;
$id->fieldname = 'ID';
$id->label     = 'Id';
//$id->editable  = true ;
$id->sortable  = true;
$tabla->addCol($id);

$from = new Field();
$from->type      = 'varchar';
$from->len       = 100;
$from->fieldname = 'FROM_URL';
$from->label     = 'Desde';
$from->editable  = true ;
$from->sortable  = true;
//$from->textafter  = '';
$from->searchable  = true;
$tabla->addCol($from);

$url = new Field();
$url->type      = 'varchar';
$url->len       = 300;
$url->fieldname = 'TO_URL';
$url->label     = 'Redirige a';
$url->editable  = true ;
$url->sortable  = true;
$url->searchable  = true;
$tabla->addCol($url);

$qrcode = new Field();
$qrcode->fieldname = 'QRCODE';
$qrcode->label     = 'QRCODE';   
$qrcode->len       = 7;
$qrcode->width     = 40;
$qrcode->type      = 'varchar';
$qrcode->editable  = false;
$qrcode->calculated  = true;
$tabla->addCol($qrcode);


$tabla->title = 'Qr Codes';
$tabla->showtitle = true;
$tabla->page = $page;
$tabla->page_num_items = 10;
$tabla->show_empty_rows = true;
$tabla->show_inputsearch =true;

$tabla->orderby='ID DESC';

$tabla->perms['delete'] = $_ACL->userHasRoleName('Administradores');
$tabla->perms['edit']   = $_ACL->userHasRoleName('Administradores');
$tabla->perms['add']    = $_ACL->userHasRoleName('Administradores');
$tabla->perms['setup']  = $_ACL->userHasRoleName('Administradores');
$tabla->perms['reload'] = $_ACL->userHasRoleName('Administradores');
$tabla->perms['view']   = $_ACL->userHasRoleName('Administradores');

class qrcodes_Events extends defaultTableEvents implements iEvents{ 

    function OnShow($owner){  
    }

    function OnDrawRow($owner,&$row,&$class){
        $rewrite = false;
        if(empty($row['TO_URL'])){
            $rewrite = true;
        }else{
            $row['FROM_URL'] = CFG::$vars['proto'].$_SERVER['HTTP_HOST'].'/qr/'.$row['FROM_URL'] ;
        }
        $url = $row['FROM_URL'];
        if($rewrite===true || !file_exists(SCRIPT_DIR_MEDIA.'/qrcode/'.$row['ID'].'.png')){
            include_once(SCRIPT_DIR_LIB.'/phpqrcode/qrlib.php');
            QRcode::png($url, SCRIPT_DIR_MEDIA.'/qrcode/'.$row['ID'].'.png', QR_ECLEVEL_L, 20,4);
        }
    }

    function OnCalculate($owner,&$row){
 
        // displaying
        $hash = md5($row['FROM_URL']);//.$row['TO_URL']);
        //$row['QRCODE'] = '<a class="open_file_image" href="'.SCRIPT_DIR_MEDIA.'/qrcode/'.$row['ID'].'.png?ver=1.2"><img style="height:20px;" src="'.SCRIPT_DIR_MEDIA.'/qrcode/'.$row['ID'].'.png?ver=1.1" /></a>';
        // displaying
        $row['QRCODE'] = '<a class="open_file_image" title="'.CFG::$vars['proto'].$_SERVER['HTTP_HOST'].'/qr/'.$row['FROM_URL'].'" href="'.SCRIPT_DIR_MEDIA.'/qrcode/'.$row['ID'].'.png?ver='.$hash.'"><img style="height:20px;" src="'.SCRIPT_DIR_MEDIA.'/qrcode/'.$row['ID'].'.png?ver='.$hash.'" /></a>';

        
    }

    function OnUpdate($owner, &$result, &$post){ 
        

        $post['FROM_URL'] = Str::sanitizeName($post['FROM_URL']);
        //$post['TO_URL'] = Str::sanitizeUrl($post['TO_URL']);

        $rewrite = $post['FROM_URL']!= $result['old']['FROM_URL'] || $post['TO_URL']!= $result['old']['TO_URL'];

        if($rewrite===true || !file_exists(SCRIPT_DIR_MEDIA.'/qrcode/'.$post['ID'].'.png')){
            $result['msg'] = ' Regenerando QR ';
            include_once(SCRIPT_DIR_LIB.'/phpqrcode/qrlib.php');
            QRcode::png($post['FROM_URL'], SCRIPT_DIR_MEDIA.'/qrcode/'.$post['ID'].'.png', QR_ECLEVEL_L, 20,4);
        }
        


    }  

}

$tabla->events = New qrcodes_Events();

//$tabla->sql_query("TRUNCATE TABLE CLI_QRCODES");
