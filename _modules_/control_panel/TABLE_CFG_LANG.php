<?php 

$tabla = new TableMysql(TB_LANG);

$lang_id = new Field();
$lang_id->type      = 'int';
$lang_id->len       = 5;
$lang_id->fieldname = 'lang_id';
$lang_id->label     = 'Id';
$lang_id->editable  = false ;
$lang_id->sortable  = true;
$lang_id->searchable  = true;
$tabla->addCol($lang_id);

$lang_name = new Field();
$lang_name->type      = 'varchar';
$lang_name->len       = 15;
$lang_name->fieldname = 'lang_name';
$lang_name->label     = 'Name';
$lang_name->editable  = true;
$lang_name->sortable  = true;
$lang_name->searchable  = true;
$tabla->addCol($lang_name);

$lang_cc = new Field();
$lang_cc->type      = 'varchar';
$lang_cc->len       = 5;
$lang_cc->fieldname = 'lang_cc';
$lang_cc->label     = 'Country code';
$lang_cc->editable  = true;
$lang_cc->sortable  = true;
$lang_cc->searchable  = true;
$tabla->addCol($lang_cc);

$lang_urlflag = new Field();
$lang_urlflag->type      = 'varchar';
$lang_urlflag->len       = 50;
$lang_urlflag->fieldname = 'lang_urlflag';
$lang_urlflag->label     = 'Flag';
$lang_urlflag->width  = 40;
$lang_urlflag->editable  = true;
$lang_urlflag->sortable  = false;
$lang_urlflag->searchable  = true;
$tabla->addCol($lang_urlflag);

$lang_language_string = new Field();
$lang_language_string->type      = 'varchar';
$lang_language_string->len       = 50;
$lang_language_string->fieldname = 'lang_language_string';
$lang_language_string->label     = 'Language string';
$lang_language_string->editable  = false ;
$lang_language_string->sortable  = true;
$lang_language_string->searchable  = true;
$tabla->addCol($lang_language_string);

$buttons = new Field();
$buttons->fieldname  = 'BUTTONS';
$buttons->label      = '';   
$buttons->len        = 7;
//$buttons->width      = 85;
$buttons->type       = 'varchar';
$buttons->calculated = true;
$buttons->editable   = true;//Administrador();
$buttons->visible    = false;
$buttons->sortable = false;
$tabla->addCol($buttons);

$lang_active = new Field();
$lang_active->type      = 'bool';
$lang_active->len       = 1;
$lang_active->fieldname = 'lang_active';
$lang_active->label     = 'Active';
$lang_active->editable  = true ;
$lang_active->sortable  = true;
$lang_active->searchable  = true;
$tabla->addCol($lang_active);

$tabla->title = 'Idiomas';
$tabla->verbose=false;
$tabla->output='table';
$tabla->page = $page;
$tabla->page_num_items = Vars::getArrayVar(CFG::$vars['langs']['options'],'num_rows',20);
$tabla->show_empty_rows = true;
$tabla->show_inputsearch =true;

$tabla->perms['delete'] = Administrador();
$tabla->perms['edit']   = Administrador();
$tabla->perms['add']    = Administrador();
$tabla->perms['setup']  = Root();
$tabla->perms['reload'] = true;
$tabla->perms['filter'] = true;
$tabla->perms['view']   = true;

//$tabla->addActiveCol();

class langEvents extends defaultTableEvents implements iEvents{

  function OnDrawRow($owner,&$row,&$class){
      $row['lang_urlflag']  =  "<img style=\"height:12px;\" src=\"{$row['lang_urlflag']}\"    >";
  }
  
  function OnInsert($owner,&$result,&$post) {  }
  function OnUpdate($owner,&$result,&$post) {  }
  function OnDelete($owner,&$result,$id)    {  }

    function OnShow($owner){
        ?>  
        <script type="text/javascript">
            $(function() {
                $('.write-cc-file').click(function(e){
                    $.ajax({
                           method: "POST",
                           url: "<?=Vars::mkUrl(MODULE,'ajax/write-cc-file')?>",
                           data:{ 'lang_cc': $(this).data('lang')}, 
                           dataType: "json",
                           beforeSend: function( xhr, settings ) {
                               $('.ajax-loader').show(); //.addClass('waiting');
                           }
                     }).done(function( data ) {
                            showMessageInfo( data.msg );
                    }).fail(function() {
                            showMessageError( "error" );
                    }).always(function() {
                            $('.ajax-loader').hide(); //.removeClass('waiting');
                    });
                });
            });
        </script>
        <?php 
    }

    function OnCalculate($owner,&$row) { 
        // file_exists('../../cards/card_'. strtoupper($row['user_dni']).'.jpg'))     
        if($row['lang_active'])
        $row['BUTTONS']  = '<a title="Guardar traducción" class="btn-link write-cc-file" data-lang="'.$row['lang_cc'].'"><i  style="color:#0097df;" class="fa fa-save"></i></a>';
    }
}

$tabla->events = New langEvents();



