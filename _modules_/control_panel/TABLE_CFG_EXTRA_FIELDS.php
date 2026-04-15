<?php

$tabla = new TableMysql('CFG_EXTRA_FIELDS');


$id            = new Field();
$id->type      = 'int';
$id->width       = 15;
$id->fieldname = 'FIELD_ID';
$id->label     = 'Id';   
$id->hide      = true;

$tb_name=new Field();
$tb_name->fieldname = 'T4BLE_NAME';
$tb_name->label     = 'Tabla';   
$tb_name->type      = 'select';
$tb_name->values    =  array('CLI_PAGES'=>'Páginas','CLI_USER'=>'Usuarios','CLI_PRODUCTS'=>'Productos','ACL_ROLES'=>'Roles');

if(defined('MODULE_WINES')) $tb_name->values['CLI_BODEGAS']='Bodegas';

$tb_name->values_all=  $tb_name->values;
$tb_name->editable  =  true; 
$tb_name->len       =  20; 
$tb_name->default_value ='CLI_USER';
$tb_name->allowNull = false;

$name = new Field();
$name->fieldname = 'FIELD_NAME';
$name->label     = 'Nombre';   
$name->len       = 40;
$name->type      = 'varchar';
$name->editable  = Administrador();
$name->javascript = '$("#FIELD_NAME").change(function(){'
                        . '    label=$("#FIELD_LABEL").val();'
                        . '    if (!label) $("#FIELD_LABEL").val($("#FIELD_NAME").val().capitalize());'
                        . '});';

$lookup_field_table = new Field();
$lookup_field_table->fieldname = 'LOOKUP_FIELD_TABLE';
$lookup_field_table->label     = 'Lookup field table';   
//$lookup_field_table->values    = $tables_values;
$lookup_field_table->type      = 'varchar';
$lookup_field_table->len       = 40;
$lookup_field_table->editable  = Administrador();
$lookup_field_table->hide  = true;
/*
$lookup_field_table->child_ajax_url   = '/control_panel/ajax/op=list';
$lookup_field_table->child_fieldname  = 'ID_STATE';
$lookup_field_table->child_source_sql = "SHOW FIELDS FROM {$this->tablename}";
*/
$lookup_field_key = new Field();
$lookup_field_key->fieldname = 'LOOKUP_FIELD_KEY';
$lookup_field_key->label     = 'Lookup field key';   
$lookup_field_key->len       = 40;
$lookup_field_key->type      = 'varchar';
$lookup_field_key->editable  = Administrador();
$lookup_field_key->hide  = true;

$lookup_field_name = new Field();
$lookup_field_name->fieldname = 'LOOKUP_FIELD_NAME';
$lookup_field_name->label     = 'Lookup field name';   
$lookup_field_name->len       = 40;
$lookup_field_name->type      = 'varchar';
$lookup_field_name->editable  = Administrador();
$lookup_field_name->hide  = true;

$type = new Field();
$type->fieldname = 'FIELD_TYPE';
$type->label     = 'Tipo';   
$type->type      = 'select';
$type->values    =  array('varchar'=>'varchar','bool'=>'bool','int'=>'int','textarea'=>'textarea','select'=>'select','date'=>'date','file'=>'file');
$type->len       =  10; 
$type->editable  =  true; 
$type->default_value ='int';
//$type->child='FIELD_LEN';
/*
$type->javascript = '$(\'body\').on(\'change\',\'#FIELD_TYPE\',function(e){'
                  . '    let dat=$(this).val()==\'date\';'                       // . '    yes=$(this).is(":checked");'
                  . '    if (dat)  $("#FIELD_LEN").val("").closest(".control-group").hide("fast");'
                 // . '       else $("#FIELD_LEN").closest(".control-group").show("fast");'
                  . '   let yes=$(this).val()==\'bool\';'                       // . '    yes=$(this).is(":checked");'
                  . '    if (yes) $("#FIELD_LEN").val(1).closest(".control-group").hide("fast");'
                  . '        else $("#FIELD_LEN").closest(".control-group").show("fast");'
                  . '  let tex=$(this).val()==\'textarea\';'                       // . '    yes=$(this).is(":checked");'
                  . '       if (tex) $("#FIELD_LEN").val("").closest(".control-group").hide("fast");'
                  . '       else $("#FIELD_LEN").closest(".control-group").show("fast");'
                  . '       if (tex) $("#WYSIWYG").closest(".control-group").show("fast");'
                  . '       else $("#WYSIWYG").val("0").closest(".control-group").hide("fast");'
                  . '  let sel=$(this).val()==\'select\';'                       // . '    yes=$(this).is(":checked");'
                  . '       if (sel) $("#FIELD_LEN").val("").closest(".control-group").hide("fast");'
                  . '       if (sel) $("#WYSIWYG").val("0").closest(".control-group").hide("fast");'
                  . '       if (sel) $("#LOOKUP_FIELD_KEY").closest(".control-group").show("fast");'
                  . '           else $("#LOOKUP_FIELD_KEY").val("").closest(".control-group").hide("fast");'
                  . '       if (sel) $("#LOOKUP_FIELD_NAME").closest(".control-group").show("fast");'
                  . '           else $("#LOOKUP_FIELD_NAME").val("").closest(".control-group").hide("fast");'
                  . '       if (sel) $("#LOOKUP_FIELD_TABLE").closest(".control-group").show("fast");'
                  . '           else $("#LOOKUP_FIELD_TABLE").val("").closest(".control-group").hide("fast");'
                  . '  let file=$(this).val()==\'file\';'                       // . '    yes=$(this).is(":checked");'
                  . '      if (file) $("#UPLOADDIR").closest(".control-group").show("fast");'
                  . '           else $("#UPLOADDIR").closest(".control-group").hide("fast");'
                  . '      if (file) $("#EXTENSIONS").closest(".control-group").show("fast");'
                  . '           else $("#EXTENSIONS").closest(".control-group").hide("fast");'
                  . '      if (file) $("#MASK").closest(".control-group").show("fast");'
                  . '           else $("#MASK").closest(".control-group").hide("fast");'
                  . '});'
                  . '$("#FIELD_TYPE").change();';
*/
$wysiwyg   = new Field();
$wysiwyg->type      = 'bool';
$wysiwyg->width     = 30;
$wysiwyg->fieldname = 'WYSIWYG';
$wysiwyg->label     = 'Wysiwyg';   
//$wysiwyg->textafter = 'Usar editor ';   
$wysiwyg->editable  = true;
$wysiwyg->hide = true;

$len = new Field();
$len->fieldname = 'FIELD_LEN';
$len->label     = 'Longitud';   
$len->len       = 40;
$len->type      = 'varchar';
$len->editable  = Administrador();
$len->default_value ='10';
//$len->parent='FIELD_TYPE';

$label =  new Field();
$label->fieldname = 'FIELD_LABEL';
$label->label     = 'Etiqueta';   
$label->len       = 40;
$label->type      = 'varchar';
$label->editable  = Administrador();

$default_value = new Field();
$default_value->fieldname = 'FIELD_DEFAULT_VALUE';
$default_value->label     = 'Default value';   
$default_value->len       = 40;
$default_value->type      = 'varchar';
$default_value->editable  = Administrador();

$null   = new Field();
$null->type      = 'bool';
$null->width     = 30;
$null->fieldname = 'ALLOW_NULL';
$null->label     = 'Allow null';   
$null->editable  = true;
$null->hide = true;

$hide   = new Field();
$hide->type      = 'bool';
$hide->width     = 30;
$hide->fieldname = 'HIDE';
$hide->label     = 'Oculto';   
$hide->editable  = true;
$hide->hide = true;

$editable =  new Field();
$editable->fieldname = 'EDITABLE';
$editable->label     = 'Editable';   
$editable->len       = 40;
$editable->type      = 'varchar';
$editable->placeholder  = 'Role name or empty for true';
$editable->default_value  = 'true';
$editable->editable  = true;
$editable->hide  = true;

$fieldset =  new Field();
$fieldset->fieldname = 'FIELDSET';
$fieldset->label     = 'Fieldset';   
$fieldset->len       = 40;
$fieldset->type      = 'varchar';
$fieldset->default_value  = '';
$fieldset->editable  = Administrador();

$searchable   = new Field();
$searchable->type      = 'bool';
$searchable->width     = 30;
$searchable->fieldname = 'SEARCHABLE';
$searchable->label     = 'Searchable';   
$searchable->editable  = true;
$searchable->hide = true;

$filtrable   = new Field();
$filtrable->type      = 'bool';
$filtrable->width     = 30;
$filtrable->fieldname = 'FILTRABLE';
$filtrable->label     = 'Filtrable';   
$filtrable->editable  = true;
$filtrable->hide = true;

$placeholder =  new Field();
$placeholder->fieldname = 'PLACEHOLDER';
$placeholder->label     = 'placeholder';   
$placeholder->len       = 100;
$placeholder->type      = 'varchar';
$placeholder->editable  = Administrador();
$placeholder->hide = true;

$textafter =  new Field();
$textafter->fieldname = 'TEXTAFTER';
$textafter->label     = 'textafter';   
$textafter->len       = 100;
$textafter->type      = 'varchar';
$textafter->editable  = Administrador();
$textafter->hide = true;

$uploaddir =  new Field();
$uploaddir->fieldname = 'UPLOADDIR';
$uploaddir->label     = 'uploaddir';   
$uploaddir->len       = 100;
$uploaddir->type      = 'varchar';
$uploaddir->editable  = Administrador();
$uploaddir->default_value = '[SCRIPT_DIR_MEDIA]/[TABLENAME]/uploads';
$uploaddir->hide = true;

$accepted_extensions =  new Field();
$accepted_extensions->fieldname = 'EXTENSIONS';
$accepted_extensions->label     = 'accepted_extensions';   
$accepted_extensions->len       = 100;
$accepted_extensions->type      = 'varchar';
$accepted_extensions->editable  = Administrador();
$accepted_extensions->default_value  = '.pgn,.jpg,.pdf';
$accepted_extensions->textafter  = 'en blanco para extensiones por omisión';
$accepted_extensions->hide = true;

$mask =  new Field();
$mask->fieldname = 'MASK';
$mask->label     = 'Mask filename';   
$mask->len       = 50;
$mask->type      = 'varchar';
$mask->editable  = Administrador();
$mask->default_value  = '[ID]_[LANG]';
$mask->textafter  = 'Plantilla para nombre de archivo';
$mask->hide = true;

$tabla->title = 'Campos extra';
$tabla->verbose=false;
//$tabla->cache = false;
$tabla->output='table';
$tabla->page = $page;
$tabla->page_num_items = Vars::getArrayVar(CFG::$vars['extra_fields']['options'],'num_rows',20);
$tabla->addCol($id);
$tabla->addCol($tb_name);
$tabla->addCol($name);
$tabla->addCol($lookup_field_table);
$tabla->addCol($lookup_field_key);
$tabla->addCol($lookup_field_name);
$tabla->addCol($uploaddir);
$tabla->addCol($accepted_extensions);
$tabla->addCol($mask);
$tabla->addCol($type);
$tabla->addCol($wysiwyg);
$tabla->addCol($len);
$tabla->addCol($label);
$tabla->addCol($default_value);
$tabla->addCol($null);
$tabla->addCol($hide);
$tabla->addCol($editable);
$tabla->addCol($fieldset);
$tabla->addCol($searchable);
$tabla->addCol($filtrable);
$tabla->addCol($placeholder);
$tabla->addCol($textafter);

$tabla->addActiveCol();
//$tabla->addWhoColumns();
$tabla->orderby = 'FIELD_ID';

/*
class typesEvents extends defaultTableEvents implements iEvents{ 
  function OnInsert($owner,&$result,&$post) { }
  function OnUpdate($owner,&$result,&$post) { }
  function OnDelete($owner,&$result,$id)    { }
}

$tabla->events = New typesEvents();
*/
$tabla->perms['delete'] = Root();
$tabla->perms['edit']   = Administrador();
$tabla->perms['add']    = Root();
$tabla->perms['setup']  = Root();  
$tabla->perms['reload'] = true;
$tabla->perms['view']   = true;
