<?php

/*


Table MKP_MARKETPLACE

Columns

ID
TYPE [theme, module, plugin, ...]
NAME - identifier. Ej: 'blog'
TITLE - NAme, ej: Blog
ID_USER - Author. FK reference to CLI_USER.user_id 
REPO  - url of repo, blank or null if repo is this web
WEB  - url of product
DESCRIPTION - Ej: Standard blog, code based in module news
DATE - Creation Date
LAST_UPDATE - LAst version date
LICENSE - [CreativeCommons,Apache, GNU, etc]
PRICE - Decimal 
CURRENCY - Currency price: EUR,USD,BTC,etc
VERSION - varchar  default '0.0.1'

*/


$tabla = new TableMysql('MKP_MARKETPLACE');

$tabla->addCols([
    $tabla->field(          'ID',     'int'  )->len(  5)->editable(false)->hide(true),
    $tabla->field(        'NAME', 'varchar'  )->len(30)->searchable(true)->required(true),
    $tabla->field(       'TITLE', 'varchar'  )->len(100)->searchable(true)->required(true),
    $tabla->field(        'REPO', 'varchar'  )->len(100)->searchable(true)->required(true)->default_value($_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST']),
    $tabla->field( 'DESCRIPTION','textarea'  )->wysiwyg(false)->searchable(true)->/*required(true)->*/fieldset('Descripción'),
    $tabla->field(        'DATE',    'date'  )->required(true)->filtrable(true)->searchable(true), 
    $tabla->field( 'LAST_UPDATE',    'date'  )->required(true)->filtrable(true)->searchable(true),  
    $tabla->field(     'ID_USER',  'select'  )->len(  5)->required(true)->values( $tabla->toarray( 'values_users', "SELECT user_id AS ID, CONCAT(username,' - ',IFNULL(user_fullname,'..')) AS NAME FROM ".TB_USER."  ORDER BY NAME",true)  ), 
    $tabla->field(       'STATE',  'select'  )->len(  5)->required(true)->values( ['0'=>'--','1'=>'Verificado'] ),
    $tabla->field(        'TYPE',  'select'  )->len(  2)->required(true)->values( ['1'=>'module','2'=>'theme','3'=>'system'] )->default_value('1')
]);

$tabla->perms['view']   = Usuario(); 
$tabla->perms['reload'] = Usuario();  
$tabla->perms['filter']  = Usuario();  
$tabla->perms['edit']   = Administrador() || $_ACL->hasPermission('mkp_edit');
$tabla->perms['add']    = Administrador() || $_ACL->hasPermission('mkp_add');
$tabla->perms['delete'] = Administrador() || $_ACL->hasPermission('mkp_delete');
$tabla->perms['setup']  = Root() || $_ACL->userHasRoleName('Administradores');  


$tabla->page_num_items = 6;

$tabla->markup_footer_row  = '<div class="inner_footer noshadow">[CONTENT]</div>';  
$tabla->markup = '<div id="[ID]" class="mkp-list tb_id">'
                . '[BODY]'
                . '</div>'
                . '[FOOTER]'
                . '<div class="ajax-loader" style="display:none;"><div class="loader"></div></div>'
                ;  
    
$tabla->markup_row  = '<div id="row-[ID]" class="mkp-item div_item shadow [CLASS]">'
                    . '<div class="cells">[CELLS]</div>'
                    . '<div class="actions">[ACTIONS]</div>'
                    . '</div>';

$tabla->markup_cell  = '<span id="[ID]" class="[CLASS]" style="[STYLE]" data-fieldname="[FIELDNAME]" data-precission="[PRECISSION]" data-th="[LABEL]" val="[VAL]">[CONTENT]</span>';  

$tabla->markup_row_empty  = '<div class="codepen-item row-empty">[CONTENT]</div>';


class mkpEvents extends defaultTableEvents implements iEvents{ 
 
    function OnBeforeShow($owner){

        ?>
        <script>

            function Load(dialogBody,html){

                let loadinghtml = '<div class="bubblingG"><span id="bubblingG_1"></span><span id="bubblingG_2"></span><span id="bubblingG_3"></span></div>';
                $(dialogBody).html(loadinghtml);

            }

            function Loaded(dialogBody,html){
                const datosrecibidos = JSON.parse(html);
                if(datosrecibidos.error==0){
                   if(datosrecibidos.message)
                       $(dialogBody).html(`<div class="info" style="margin-bottom:0;position:absolute;top:0;left:0;right:0;bottom:0;align-content:center;"> ${datosrecibidos.message} 🙂</div>`);
                   else  
                       $(dialogBody).html(datosrecibidos.msg[0]);
                }else{
                   $(dialogBody).html(datosrecibidos.msg[0]);
                }

            }

        </script>
        <?php

    }

    function OnDrawRow($owner,&$row,&$class){


        $_type = $owner->colByName('TYPE')->values[$row['TYPE']];

        $sql_img = "SELECT NAME,ID_PROVIDER,FILE_NAME FROM MKP_MARKETPLACE_FILES WHERE ITEM_ID={$row['ID']} AND MINI='1' ORDER BY ID DESC";   

        $images = Table::sqlQuery($sql_img);

        $image = $images ? './media/MKP/files/'.$row['ID'].'/'.$images[0]['FILE_NAME'] : '';
        
        if($images){
            $image_big =  './media/MKP/files/'.$row['ID'].'/'.BIG_PREFIX.$images[0]['FILE_NAME'];
            $image =  file_exists($image_big) ? $image_big : './media/MKP/files/'.$row['ID'].'/'.$images[0]['FILE_NAME'];
        }
        
        $row['TITLE'].= '<style>#row-'.$row['ID'].' .cells{background: url('.$image.'?ver='.$ver.');</style>'; 

        //Ejemplo update/install: control_panel/ajax/update/module/drawing/host=url_with_zipfile
        $link_update = SCRIPT_DIR.'/control_panel/ajax/update/'.$_type.'/'.$row['NAME'].'/host='.str_replace('https://','',$row['REPO']);


        //Ejemplo create: control_panel/ajax/update/zip/zip_module/drawing
        $link_create = /*CFG::$vars['repo']['url']*/ $row['REPO'] == $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST']
                     ? SCRIPT_DIR.'/control_panel/ajax/update/zip/zip_'.$_type.'/'.$row['NAME']
                     : false;

        $row['TITLE'].= '<div style="background-color:white;font-size:10px;">'
                     .  '<a class="open_ajax" data-href="'.$link_update.'" data-width="500px" data-height="250px" title="'.$link_update.'">UPDATE</a>'
                     .  ($link_create ? ' · <a class="open_ajax" data-href="'.$link_create.'" data-width="500px" data-height="250px" title="'.$link_create.'">CREATE</a>' : '')
                     .  '</div>';          

    }
    
    function OnAfterDrawRow($owner,&$row,&$markup){   

        //$markup = str_replace('[CONTENT]', $row['NAME'].'<br>'.$row['TITLE'],$markup);
        
    }

    function OnAfterShow($owner){     } 

    function OnBeforeShowForm($owner,&$form,$id) {
        if($owner->state=='filter')return false;

        if($owner->state=='update'){
            if(!$id) $id = $owner->nextInsertId();  //CHECK is valid id !!
            if($id && $owner->perms['edit']) {
                 Table::$module_name = 'marketplace';
                 $parent = $id;
                 $markup_ajax_loader = '<p style="text-align:center;border:1px solid green;"><img style="width:56px;" src="'.IMG_AJAX_LOADER.'"></p>';
                 $html_marketplace_files = new formElementHtml();
                 $html_marketplace_files->html = '<div class="datatable" id="T-MKP_MARKETPLACE_FILES">'.$markup_ajax_loader.'</div>'
                                              . '<script>load_page("marketplace","MKP_MARKETPLACE_FILES",1,'.$id.',1);</script>' ;
                 Table::show_table('MKP_MARKETPLACE_FILES','marketplace',false);
                 $fs_marketplace_files = new fieldset('marketplace_files','Archivos'); //['.$id.']
                 $fs_marketplace_files->displaytype = 'tab';
                 $fs_marketplace_files->addElement($html_marketplace_files);
                 $form->addElement($fs_marketplace_files);
             }
         }
         //parent::OnBeforeShowForm($owner,$form,$id);
    }


}

$tabla->events = New mkpEvents();    