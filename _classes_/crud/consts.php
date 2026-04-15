<?php 

/***** Tabs *****/    
/*
define('TAB_HEADER','<div class="form-tabs" id="ftabs-%s"><ul>%s</ul>');
define('TAB_TAB_TAB','<li><a href="#ftab-%s">%s</a></li>');
define('TAB_TAB_BEGIN','<div id="ftab-%s">');
define('TAB_TAB_END','</div>');
define('TAB_FOOTER','</div>'); // <script type="text/javascript">$(function(){$("#ftabs-%s").tabs();});</script>');
*/
/***** Links *****/
define('BUTTON_EDIT'        ,'<i class="fa fa-pencil-square-o button edit" op="edit" aria-hidden="true"></i> ');
define('BUTTON_DELETE'      ,'<i class="fa fa-close button delete" op="delete" aria-hidden="true"></i>');
define('BUTTON_ADD'         ,'<i class="fa fa-plus button add" op="add" aria-hidden="true"></i>');
define('BUTTON_RELOAD'      ,'<i class="fa fa-repeat button reload" op="reload" aria-hidden="true"></i>');
define('BUTTON_SETUP'       ,'<i class="fa fa-wrench button setup" op="setup" aria-hidden="true"></i>');
define('BUTTON_VIEW'        ,'<i class="fa fa-eye button view" op="view" aria-hidden="true"></i>');
define('BUTTON_CLOSE'       ,'<i class="fa fa-close button close" op="close" aria-hidden="true"></i>');


/***** Forms *****/
define('FORM_BEGIN', '<!--<h3>[LEGEND]</h3>-->'
                   .BUTTON_CLOSE
                   . '<form accept-charset="utf-8" '
                   . 'enctype="multipart/form-data" '
                   . '  class="form-inlineNO [CLASS]" '
                   . '     id="[ID]" '
                   . '   name="[NAME]" '
                   . ' action="[ACTION]" '
                   . ' method="[METHOD]">');

define('FORM_END','</form>');

define('FORM_FIELDSET_BEGIN', '<div class="form-group row row-buttons">');
define('FORM_FIELDSET_END','</div>');

define('MARKUP_LABEL', '<label for="[NAME]" class="form-label col-form-label">[LABEL]</label>');

define('MARKUP_INPUT_HTML', '<span class="form-group row form-html" id="[ID]">[VALUE]</span>' );

define('MARKUP_INPUT_HIDDEN', '<input id="[ID]" name="[NAME]" type="hidden" value="[VALUE]">' );
define('MARKUP_INPUT_CHECKBOX', '<input id="[ID]" name="[NAME]" type="checkbox" value="[VALUE]">[HELP]' );


/****
    public function render(){

        echo '<label class="ios">'.$this->label.'<input type="checkbox"  name="'.$this->name.'" id="'.$this->name.'" value="1" '.(($this->value)?'checked':'').' /><i></i></label>';
        if($this->javascript){
          ?>
          <script type="text/javascript">
           $(document).ready(function() { 
             var e = $('#<?=$this->name?>');
             <?php  echo $this->javascript; ?>
           });
          </script>
<style>
label.ios {
  display: inline-block;
  width: 100%;
  background: #fff;
  padding: 10px 15px;
  border-top: 1px solid #BBB;
}
label.ios:last-child {
  border-bottom: 1px solid #BBB;
}
label.ios > input {
  display: none;
}
label.ios i {
  display: inline-block;
  float: right;
  padding: 2px;
  width: 40px;
  height: 20px;
  border-radius: 13px;
  vertical-align: middle;
  -webkit-transition: .25s .09s;
  transition: .25s .09s;
  position: relative;
  background: #d8d9db;
  box-sizing: initial;
}
label.ios i:after {
  content: " ";
  display: block;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: #fff;
  position: absolute;
  left: 2px;
  -webkit-transition: .25s;
  transition: .25s;
}
label.ios > input:checked + i {
  background: #4bd865;
}
label.ios > input:checked + i:after {
  -webkit-transform: translateX(20px);
          transform: translateX(20px);
}
label.ios:hover {
  cursor: pointer;
}
</style>
          <?php 
        }
    }
**/

define('MARKUP_INPUT_CHECKBOX_TOGGLE', '<div class="XXform-control toggle toggle-modern"  id="[ID]" name="[NAME]" data-toggle-on="[VALUE]" data-toggle-height="20" data-toggle-width="60"></div>'); 
//<div style="max-width:50px;" class="form-control toggle toggle-modern [CLASS]" id="[ID]" name="[NAME]" type="checkbox" value="[VALUE]" [READONLY] ></div>');
                           
define('MARKUP_INPUT_TEXT', '<input class="form-input XXform-control enter_as_tab [CLASS]" id="[ID]" name="[NAME]" type="[TYPE]" value="[VALUE]" '
                          . '    [PLACEHOLDER]  [SIZE]  [MAXSIZE] [READONLY] >' );

define('MARKUP_INPUT_NUMBER_XX', 
     '<div class="XXform-control input-number">'
    .'<input type="button" value="-" class="button-minus" data-field="[NAME]" [READONLY]>'
    .'<input type="number" id="[ID]" name="[NAME]" step="[STEP]" max="[MAX]" value="[VALUE]" class="quantity-field [CLASS]" [READONLY]>'
    .'<input type="button" value="+" class="button-plus" data-field="[NAME]" [READONLY]>'
    .'</div>');
define('MARKUP_INPUT_NUMBER', 
     '<div class="XXform-control input-number" id="input-number-[ID]"><label for="[NAME]" style="visibility:hidden;font-size:0px;display:inline;">Cantidad</label>'
    .'<span class="button-number-minus"><i class="fa fa-minus"></i></span>'
    .'<input type="text" id="[ID]" name="[NAME]" step="[STEP]" max="[MAX]" value="[VALUE]" class="input-number-input enter_as_tab [CLASS]" [READONLY] />'
    .'<span class="button-number-plus"><i class="fa fa-plus"></i></span>'
    .'</div>');
    /*
    .'<script type="text/javascript">'
    .'  $(function(){ '
    .'    $("#input-number-[ID] .button-number-plus").click(function(){' 
    .'        console.log("plus");'
    .'        $("#[ID]").val( parseInt($("#[ID]").val())+1);'
    .'    });'
    .'    $("#input-number-[ID] .button-number-minus").click(function(){' 
    .'        console.log("minus");'
    .'        $("#[ID]").val( parseInt($("#[ID]").val())-1);'
    .'    });'
    .'  });'
    .'</script>');
    **/
/*****
function incrementValue(e) {
  e.preventDefault();
  var fieldName = $(e.target).data('field');
  var parent = $(e.target).closest('div');
  var currentVal = parseInt(parent.find('input[name=' + fieldName + ']').val(), 10);

  if (!isNaN(currentVal)) {
    parent.find('input[name=' + fieldName + ']').val(currentVal + 1);
  } else {
    parent.find('input[name=' + fieldName + ']').val(0);
  }
}

function decrementValue(e) {
  e.preventDefault();
  var fieldName = $(e.target).data('field');
  var parent = $(e.target).closest('div');
  var currentVal = parseInt(parent.find('input[name=' + fieldName + ']').val(), 10);

  if (!isNaN(currentVal) && currentVal > 0) {
    parent.find('input[name=' + fieldName + ']').val(currentVal - 1);
  } else {
    parent.find('input[name=' + fieldName + ']').val(0);
  }
}

$('.input-group').on('click', '.button-plus', function(e) {
  incrementValue(e);
});

$('.input-group').on('click', '.button-minus', function(e) {
  decrementValue(e);
});                           
*/                           
define('MARKUP_INPUT_SELECT', '<select class="xxform-input form-select XXform-control enter_as_tab [CLASS]" id="[ID]" name="[NAME]" [READONLY]>[OPTIONS]</select>');

define('MARKUP_SELECT_CHILD',
     '<script type="text/javascript">'
    .'  $(function(){ '
    .'    $("#[NAME]").change(function(){' 
    .'        var child = $("#[CHILD_FIELDNAME]");'
    .'        var select_val = $(this).val();'
    .'        var child_sel_value = child.val() ? child.val() : 0; '
    .'        child.removeAttr("disabled").val(0).html("");'
    .'        if (select_val>0){ '   
    .'            var child_sql = "[CHILD_SOURCE_SQL]";' //.replace("[value]", select_val);'
  //.'            console.log(\'AJAX_URL: [AJAX_URL]\');'
  //.'            console.log(\'SQL: \'+child_sql);'
    .'            $.post("[AJAX_URL]", {"sql": child_sql,"value":select_val,"selected":child_sel_value,"null":1,"nullkey":"0","nullvalue":"-","driver":"mysql"},function(data){'
  //.'                console.log(\'DATA\',data);'
    .'                if(data!=0) child.html(data).change(); else child.change().attr("disabled","disabled");'
    .'            });' //.error(function(){  }); '
    .'        }else{child.attr("disabled","disabled");}'
    .'    });'
    .'  });'
    .'</script>');
  
define('MARKUP_TEXTAREA',' <textarea class="form-input form-areas XXform-control [CLASS]" id="[ID]" name="[NAME]" rows="[ROWS]" placeholder="[PLACEHOLDER]" [READONLY]>[VALUE]</textarea>');

/////////////////  https://www.carbon.tools/ !!!!!!!!!!! Inline editor

define('MARKUP_WYSIWYG_TRIX',' <div class="trix-container"><input id="[ID]" name="[NAME]" type="hidden" value="[VALUE]"> <trix-editor input="[ID]"></trix-editor></div>');
define('MARKUP_WYSIWYG_JODIT','<textarea id="[ID]" name="[NAME]">[VALUE]</textarea><script>var editor_[ID] = new Jodit("#[ID]", {defaultMode: Jodit.MODE_SPLIT});</script>');
define('MARKUP_WYSIWYG_CKEDITOR','<textarea id="[ID]" name="[NAME]">[VALUE]</textarea><script>CKEDITOR.replace( \'[NAME]\' ,{height:60}); </script>');


define('MARKUP_ELEMENT' , '<div class="form-control form-group row [CLASS]">'
                        . '  [INPUT]<i class="fa fa-check fa-fw"></i>'
                        . '  [LABEL]'
                        . '</div>'   );
define('MARKUP_ELEMENT_CHECKBOX' , '<div class="form-control control checkbox form-group row [CLASS]">'
                        . '  [INPUT]<i class="fa fa-check fa-fw"></i>'
                        . '  [LABEL]'
                        . '</div>'   );
define('MARKUP_ELEMENT_TEXTAREA' , '<div class="form-control form-group row NOOO form-areas">'
                        . '  [LABEL]'
                        . '  [INPUT]'
                        . '</div>'   );
                     
//define('MARKUP_BUTTON','<button id="[ID]" type="[TYPE]" class="btn btn-success"><i class="fa fa-check"></i> [VALUE]</button>'
//                      . '<script type="text/javascript">$("#[ID]").click(function(){[JAVASCRIPT]});</script>');

define('MARKUP_BUTTON','[BEFORE]<button id="[ID]" name="[NAME]" type="[TYPE]" class="[CLASS]" [EXTRA]><i class="fa fa-check fa-fw"></i> [VALUE]</button>[AFTER]'
                      . '<script type="text/javascript">/*$("#[ID]").click(function(){[JAVASCRIPT]});*/</script>');

/***** Pagerr *****/
define('MARKUP_PAGER'       ,'<div class="pager"> ... 1 2 3 ... <span class="actions">[BUTTON_RELOAD] [BUTTON_ADD] [BUTTON_SETUP]</span></div>');


/***** Main container *****/
define('MARKUP', '<div id="div-[ID]" class="scaffold">'
               . '    <div class="table"></div>'
               . '    <div class="form"><div class="content"></div></div>'
               . '    <div class="ajax-loader"><div class="loader"></div></div>'
               . '</div>'
               . '<script type="text/javascript">'
               . "    var _ID_ = '[ID]';"  // /table=[ID]
               . "    var _MODULE_ = '[ID]';"  // /table=[ID]
               . "    var base_url = '[MODULE]/ajax';"  // /table=[ID]
               . '</script>'
               );

define('MARKUP_FORM','<div id="form-edit-[TABLENAME]">[CONTENT]</div>');

define('MARKUP_DETAIL','<pre>[CONTENT]</pre>');    


/***** Tables *****/    
define('MARKUP_TABLE' ,'<table class="datatable table-bordered table-striped table-inverseX table-hover table-sm" id="table-[TABLENAME]" tablename="[TABLENAME]" parent-key="[PARENT_KEY]" parent-value="[PARENT_VALUE]">'
                      .'<thead>[TITLE][HEADER]</thead><tbody>[ROWS]</tbody><tfoot>[FOOTER]</tfoot>'
                      .'</table>'
                      . '<script type="text/javascript">'
                     . "     console.log('Setting events');"  
                  //   . "      var eventsAssigned =false;"

                     . " if(!eventsAssigned){ "
                     . " eventsAssigned=true; "
                      . "  var timeout_[TABLENAME] = false;"
                                 
                      . "  $('body').on('click','#table-[TABLENAME] .row .cell',function(event){"
                      . "     var row =  $(this).closest('.row');"
                      . "     var id  =  row.attr('value');"
                      . "     clearTimeout(timeout_[TABLENAME]);"
                      . "     timeout_[TABLENAME] = setTimeout(function(){"
                      . "         loadDetailTables('[TABLENAME]','[DETAILTABLES]',id);"
                      . "     }, 500);"
                      . "  });"

                      . "  $('body').on('click','#table-[TABLENAME] .actions .button.view',function(event){"
                    //. "     console.log('Action: view');"  
                      . "     var id  = $(this).closest('.row').attr('value');"
                      . "     loadTable($(this),base_url,'[TABLENAME]',id,'view','form','html');"
                      . "  });"

                      . "  $('body').on('click','#table-[TABLENAME] .actions .button.delete',function(event){"
                      . "     var id  = $(this).closest('.row').attr('value');"
                      . "     console.log('Action: delete '+id);"  
                      . "     loadTable($(this),base_url,'[TABLENAME]',id,'delete','message','json');"
                      . "  });"

                      . "  $('body').on('click','#table-[TABLENAME] .actions .button.edit',function(event){"
                      . "     var id  = $(this).closest('.row').attr('value');"
                      . "     console.log('Action::EDIT base_url',base_url,'[TABLENAME]',id);"  
                      . "     loadTable($(this),base_url,'[TABLENAME]',id,'edit','form','html');"
                      . "  });"
                      
                      . "  clearTimeout(timeout_[TABLENAME]);"
                      . "  timeout_[TABLENAME] = setTimeout(function(){"
                      . "      var id =  $('#table-[TABLENAME]').find('.row').first().attr('value');"
                      . "      $('#table-[TABLENAME]').find('.row').first().addClass('active');"
                      . "      loadDetailTables('[TABLENAME]','[DETAILTABLES]', id  );"
                      . "  }, 500);"
                     
                      . " }  "  // if(!eventsAssigned){

                      . '</script>'

                      );
                    
define('MARKUP_HEADER_TITLE','<tr><th colspan="[COLS]">[TITLE]</th><th></th></tr>');
define('MARKUP_HEADER_ROW'  ,'<tr>[CONTENT]<th></th></tr>');
define('MARKUP_HEADER_CELL' ,'<th>[CONTENT]</th>');                                                                                         
define('MARKUP_ROW_EMPTY_X' ,'<tr class="empty-row"><td colspan="[COLS]">No hay filas</td><td></td></tr>');
define('MARKUP_NOT_EXISTS'  ,'<tr class="empty-row"><td colspan="[COLS]" class="msg-not-exists">No existe la tabla <!--<a onclick="$(\'.setup\').click();">Crear</a>--></td><td></td></tr>');
define('MARKUP_ROW'         ,'<tr  id="row-[TABLENAME]-[PK]-[VALUE]"  class="row"         pk="[PK]"        value="[VALUE]">[CONTENT]<td class="actions">'.BUTTON_VIEW.' '.BUTTON_EDIT.' '.BUTTON_DELETE.'</td></tr>');
define('MARKUP_ROW_EMPTY'   ,'<tr class="empty-row">[CONTENT]<td class="actions"> </td></tr>');
define('MARKUP_CELL'        ,'<td  id="cell-[TABLENAME]-[PK]-[VALUE]-[FIELDNAME]" class="cell" fieldname="[FIELDNAME]" fieldvalue="[FIELDVALUE]">[DISPLAYVALUE]</td>');
define('MARKUP_FOOTER'      ,'<tr><th colspan="[COLS]">[PAGER]</th></tr>');
