<?php

$tabla = new TableMysql( 'CFG_ALERTS' ); // (str_replace('TABLE_', '', get_file_name(__FILE__)) );

$id            = new Field();
$id->type      = 'int';
$id->len       = 5;
$id->width     = 15;
$id->fieldname  = 'ID';
$id->label     = 'Id';   
//$id->hide      = true;

$date = new Field();
$date->type      = 'date';
$date->fieldname  = 'D4TE';
$date->label     = 'Fecha Inicio';   
$date->editable  = Administrador();   

$date_end = new Field();
$date_end->type      = 'date';
$date_end->fieldname = 'D4TE_END';
$date_end->label     = 'Fecha Fin';   
$date_end->editable  = Administrador();   
$date_end->allowNull  = true;   
//$date_end->inline_edit   = true;

$categorie = new Field();
$categorie->fieldname = 'ID_CATEGORIE';
$categorie->label     = 'Categoría'; 
$categorie->type      = 'select';
$categorie->len       = 5;
$categorie->width     = 105;
$categorie->values    =  ['0'=>'Otros','1'=>'RRHH','2'=>'Informática'];
$categorie->editable   = true;
$categorie->inline_edit   = true;
$categorie->allowNull  = false;

if($_SESSION['RRHH_CATEGORIE'] =='rrhh')
    $categorie->default_value='1';
else
    $categorie->default_value='0';

$description = new Field();
$description->type      = 'varchar';
$description->fieldname = 'DESCRIPTION';
$description->label     = 'Descripción';   
//$description->hide     = true;
$description->searchable = true;
$description->editable = true;  
$description->len = 200;  
$description->inline_edit   = true;
if(count($tabla->langs)>0) {
    $description->translatable = true;  
    $description->langs =  $tabla->langs;
}

$text = new Field();
$text->type      = 'textarea';
$text->fieldname = 'TEXT';
$text->label     = 'Texto';   
$text->editable = true;
$text->searchable = true;
$text->filtrable = false;  
//$description->collapsed  = true;
$text->hide = true;
$text->fieldset = 'texto';
$text->wysiwyg = false;
if(count($tabla->langs)>0) {
    $text->translatable = true;  
    $text->langs =  $tabla->langs;
}

$type = new Field();
$type->fieldname = 'TYPE';
$type->label     = 'Tipo';   
$type->len       = 7;
$type->type      = 'select';
$type->values    = array('1'=>'Aviso normal','2'=>'Popup','3'=>'Imagen','4'=>'Redirect');
$type->editable  = true;
$type->inline_edit   = true;
$type->filtrable = true;
$type->default_value=1;
$type->hide = !Administrador();

$tabla->title =  'Avisos';
$tabla->showtitle = false;
$tabla->verbose = false;
$tabla->show_inputsearch = false;
$tabla->page = $page;
$tabla->page_num_items = Vars::getArrayVar(CFG::$vars['alerts']['options'],'num_rows',10);
$tabla->show_empty_rows =true;
$tabla->addCol($id);
$tabla->addCol($date);
$tabla->addCol($date_end);
$tabla->addCol($description);
$tabla->addCol($categorie);
$tabla->addCol($text);
$tabla->addCol($type);

$tabla->addActiveCol();
$tabla->addWhoColumns();
$tabla->orderby = 'ACTIVE DESC,D4TE DESC,LAST_UPDATE_DATE DESC';
$tabla->colByName('ACTIVE')->editable = Administrador() || $_ACL->hasPermission('alerts_view'); 

$tabla->perms['view']   = Usuario();
$tabla->perms['reload'] = Administrador() || $_ACL->hasPermission('alerts_view');  
$tabla->perms['edit']   = Administrador() || $_ACL->hasPermission('alerts_edit');
$tabla->perms['add']    = Administrador() || $_ACL->hasPermission('alerts_add');
$tabla->perms['delete'] = Administrador() || $_ACL->hasPermission('alerts_delete');
$tabla->perms['setup']  = Root() || $_ACL->userHasRoleName('Administradores');  
$tabla->perms['filter']  = true;  

if($_SESSION['RRHH_CATEGORIE'] =='rrhh'){
 
   $tabla->where = ' ID_CATEGORIE=1';

   if(!Administrador()) $tabla->where .= ' AND TYPE IN (1,3) AND ACTIVE=1';

}else{

}

$tabla->detail_tables[] = 'CFG_ALERTS_FILES';

class Alerts_Events extends defaultTableEvents implements iEvents{ 

    private function _is_upper_all ($in_string)  {
        return($in_string === strtoupper($in_string) ? true : false);
    }

    function _is_upper($texto) {
        $totalLetras = 0;
        $mayusculas = 0;

        // Recorremos cada carácter
        for ($i = 0; $i < strlen($texto); $i++) {
            $char = $texto[$i];
            if (ctype_alpha($char)) {
                $totalLetras++;
                if (ctype_upper($char)) {
                    $mayusculas++;
                }
            }
        }

        // Evitamos división por cero
        if ($totalLetras === 0) {
            return false;
        }

        // Comprobamos si más del 50% son mayúsculas
        return ($mayusculas / $totalLetras) > 0.5 ? 'tre' : false;
    }


    function OnDrawRow($owner,&$row,&$class){

        if ( $row['D4TE']>date('Y-m-d') )                                                    $class .= ' pending';
        if ( ( $row['D4TE_END'] && $row['D4TE_END']<date('Y-m-d') ) || $row['ACTIVE']!='1' ) $class .= ' inactive';

        $row['D4TE']     = $row['D4TE']     ? DateTime::createFromFormat('Y-m-d', $row['D4TE']    )->format('d/m/Y') : '';
        $row['D4TE_END'] = $row['D4TE_END'] ? DateTime::createFromFormat('Y-m-d', $row['D4TE_END'])->format('d/m/Y') : '<span style="display:block;text-align:center;font-size:1.5em;line-height:0.5em;color:#84bff0">∞</span>'; 

    }

    function OnInsert($owner,&$result,&$post) {     
        if($this->_is_upper($post['DESCRIPTION'])) {
            $result['error']=6;
            $result['msg']='Demasiadas mayúsculas en el nombre.'; 
        }
    }


    function OnUpdate($owner, &$result, &$post){ 
        if($this->_is_upper($post['DESCRIPTION'])) {
            $result['error']=6;
            $result['msg']='Demasiadas mayúsculas en el nombre.'; 
        }
    }

    function OnAfterShowForm($owner,&$form,$id){
        if ($owner->state != 'filter'){
            ?>
            <script type="text/javascript">
                var stopwords = ['y','de','por','del', 'la', 'el', 'lo', 'a', 'las'];

                function _is_upper(texto) {
                    let totalLetras = 0;
                    let mayusculas = 0;
                    for (let i = 0; i < texto.length; i++) {
                        const char = texto[i];
                        if (/[a-zA-Z]/.test(char)) {
                            totalLetras++;
                            if (char === char.toUpperCase()) {
                                mayusculas++;
                            }
                        }
                    }
                    if (totalLetras === 0) return false;
                    return (mayusculas / totalLetras) > 0.5 ? 'tre' : false;
                }

                function titleCase(str) {
                    if(_is_upper(str)) {
                        var splitStr = str.toLowerCase().split(' ');
                        //console.log(splitStr);
                        for (var i = 0; i < splitStr.length; i++) {
                            // You do not need to check if i is larger than splitStr length, as your for does that for you
                            // Assign it back to the array
                            //  console.log('*'+splitStr[i]+'*');
                            if (stopwords.indexOf(splitStr[i])==-1)
                                splitStr[i] = splitStr[i].charAt(0).toUpperCase() + splitStr[i].substring(1);     

                            var n = splitStr[i].indexOf('-');
                            if (n>-1) {
                                splitStr[i]=splitStr[i].replace(splitStr[i].charAt(n)+splitStr[i].charAt(n+1),(splitStr[i].charAt(n)+splitStr[i].charAt(n+1)).toUpperCase());
                            }

                        }
                        // Directly return the joined string
                        return splitStr.join(' '); 
                    }else{
                        return str;
                    }
                }

                
                $("#DESCRIPTION").keyup(function(){
                  $(this).val(titleCase($(this).val())) ;
                });


            </script>
            <?php
        }
    }
}

$tabla->events = New Alerts_Events();
