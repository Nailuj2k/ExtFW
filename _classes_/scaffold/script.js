var form_visible = false;
var form_search_focus = false;
var keys = ''; var keyslen = 10;
var page_num = false;
var pageTimeout = false;
var changeInputTimeout = false;
var onChange = false;
var onBeforeShowRow = false;
var onAfterShowRow = false;
var onSelectRow = false;
var onKeyDown = false;
var onFocus = false;
var onBlur = false;
var onTableEvent = false;
var edit_visible = false;
var upping = false;
var keystart = ':'; //String.fromCharCode(186);
var src_save_0 = '_images_/famfam/yes_gray.png';
var src_save_1 = '_images_/famfam/yes.png';
var src_edit_0 = '_images_/famfam/edit.png';
var src_edit_1 = '_images_/famfam/no.png';
var tables_loaded = false;
var n_tables = -1;
var n_tables_loaded = 0;
var selected_table = false;
var selected_table_id = false;
var default_selected_table = false;
var proccess_keydown = true;

var FORMS = new Array();
var fstatus,percent,bar=false;
var submit_activated = false;
//var wysiwyg_editor = false;
//var tables = new Array();

function toggleDebug() { if ($("#debug").is(":hidden")) $("#debug").css({ opacity: .8 }).show("slide", { direction: "left" }, 1000); else $("#debug").hide("slide", { direction: "left" }, 1000); }
//function getTime() {var now = new Date();return strTime(now.getHours(),now.getMinutes(),now.getSeconds()); }
//function appendMsg(html) { if(trim(html)!='')  $('#debug').append('# '+getTime()+' :: '+html+'<br />').animate({ scrollTop: $("#debug").prop("scrollHeight") }); }

// Esta función es para interpolar un valor de una escala a otra,
function Interpolar_Y(y, y0, y1, x0, x1) {
  return parseInt(x0 + (((x1 - x0) / (y1 - y0)) * (y - y0)));
}


/**
 *
 * http://stackoverflow.com/questions/149055/how-can-i-format-numbers-as-money-in-javascript
 * 	
 * Number.prototype.format(n, x, s, c)
 * 
 * @param integer n: length of decimal
 * @param integer x: length of whole part
 * @param mixed   s: sections delimiter
 * @param mixed   c: decimal delimiter
 */
Number.prototype.format = function (n, x, s, c) {
  var re = '\\d(?=(\\d{' + (x || 3) + '})+' + (n > 0 ? '\\D' : '$') + ')',
    num = this.toFixed(Math.max(0, ~~n));
  return (c ? num.replace('.', c) : num).replace(new RegExp(re, 'g'), '$&' + (s || ','));
};


/*
var ua = navigator.userAgent,
  event = (ua.match(/iPad/i)) ? "touchstart" : "click";
*/
/****************************
$('body').append('<pre id="debug" class="prettyprint linenums shadow"></pre><a class="btn btn-small" id="slbtn">Debug</a>');
$("#slbtn").click(function(){ toggleDebug(); });
*************************************/
$('body').on('click', '.datatable .actions a', function (e) { e.preventDefault(); });

//  ++++++ actions buttons - edit +++++++++
// $('.datatable .actions a.edit').live('click',function(){
$('body').on('click', '.datatable .actions a.edit', function () {
  var T = $(this).closest('.tb_id').attr('id');
  var X = window['X_' + T];
  var editing = ($(this).find('img').attr('src') === src_edit_1);
  //var Row = (X['output'] == 'table') ? $(this).closest('tr') : $(this).closest('.div_item');
  
  var Row =  $(this).closest('tr'); // || $(this).closest('.div_item');
  if( Row.length <1) Row = $(this).closest('.div_item');

  var rowId = Row.attr('id').replace('row-', '');

  if (X[rowId] == undefined) X[rowId] = new Array();
  if (editing) {
    X[rowId]['editing'] = true;
    $(this).find('img').attr('src', src_edit_0);
    Row.find('.save img').attr('src', src_save_0);
    Row.find('.editable,.editable-select').each(function () {
      colId = $(this).attr('id').replace('cell-' + rowId + '-', '');
      cellId = rowId + '-' + colId
      Cell = $('#' + T + ' #cell-' + cellId);
      if (X[cellId] == undefined) X[cellId] = new Array();
      Cell.text(X[cellId]['origText'])
        .css('padding', '2px 3px 0px 3px');
    });
  } else {
    if (Row.hasClass('edit')) {
      X[rowId]['editing'] = false;
      editRow(T, rowId);
    } else {
      alert('57 Acceso denegado');
    }
  }
});

function getInfo() {
  var url = AJAX_URL + '/' + module_name + '/ajax/module=' + module_name + '/op=info';
  $.modalform({ 'title': 'Información', 'url': url, 'buttons': 'ok' });
}

function getCache() {
  var url = AJAX_URL + '/' + module_name + '/ajax/module=' + module_name + '/op=cache';
  $.modalform({ 'title': 'Información', 'url': url, 'buttons': 'ok' });
}

function getDescribeTable(T) {
  var url = AJAX_URL + '/' + module_name + '/ajax/module=' + module_name + '/op=describe/table=' + T;
  $.modalform({ 'title': 'Tabla: ' + T, 'url': url, 'buttons': 'ok' });
}
/*
function editRow(T, rowId) {
  var X = window['X_' + T]
  var url = AJAX_URL + '/' + module_name + '/ajax/module=' + module_name + '/op=edit/table=' + T + '/id=' + rowId + '/page=' + X['page']; 
  $.modalform({ 'title': X['title'] + ' / Modificar fila: ' + rowId, 'url': url , 'buttons': 'save cancel'});
}
*/
function editRow(tablename, id, module = null, page = null) {   
    let title = '';
    if(page==null){
        var X = eval('X_' + tablename);
        var page = X['page'];    
        title = X['title'];
    }else{
        title = tablename;
    }
    title +=  ' > Modificar fila: ' + id
    var module = module ?? module_name;
    var url = AJAX_URL + '/' + module + '/ajax/module=' + module + '/op=edit/table=' + tablename + '/id=' + id + '/page=' + page;
    $.modalform({ title, 'url': url });
}

function addRow(T, parentId, groupId) {
  var X = window['X_' + T]
  var url = AJAX_URL + '/' + module_name + '/ajax/module=' + module_name + '/op=add/table=' + T;
  if (parentId) url += '/parent=' + parentId;
  if (groupId) url += '/group=' + groupId;
  $.modalform({ 'title': X['title'] + ' / Añadir fila ', 'url': url , 'buttons': 'save cancel'});
}

$('body').on('dblclick', '.datatable table td', function (e) {
  e.preventDefault();
  if ($(this).hasClass('editable') || $(this).hasClass('editable-select')) {
    //alert('no');
  } else {
    var X = window['X_' +  $(this).closest('.tb_id').attr('id')];
    if (X['output'] != 'table') return false;
    var T = $(this).closest('.tb_id').attr('id');
    //  var Row = (X['output'] == 'table') ? $(this).closest('tr') : $(this).closest('.div_item');
    var Row =  $(this).closest('tr')
    if(Row.length < 1) Row = $(this).closest('.div_item');


    var rowId = Row.attr('id').replace('row-', '');
    if (Row.hasClass('edit')) {
      editRow(T, rowId);
    } else {
      var url = AJAX_URL + '/' + module_name + '/ajax/module=' + module_name + '/op=view/table=' + T + '/id=' + rowId;
      if (X['detail']) $.modalform({ 'url': url, 'buttons': 'ok' });
    }
  }
});

function cancelEdit() {
  var press = jQuery.Event("keydown");                          //new
  press.ctrlKey = false;                                        //new
  press.which = 27;                                             //new
  $('.editing_on').removeClass('editing_on').trigger(press);    //new
  $('.row_editing_on').find('.save img').attr('src', src_save_0);//new
  $('.row_editing_on').find('.edit img').attr('src', src_edit_0);//new
  $('.row_editing_on').removeClass('row_editing_on');           //new
  edit_visible = false;
}

function getText(element) {
  element.find("span").remove();
  return element.html();
}

//var ua = navigator.userAgent;
//if(ua.match(/iPad/i)){

// http://appcropolis.com/implementing-doubletap-on-iphones-and-ipads/  

/*************************************/
$('body').on('dblclick', '.datatable table td.editable', function () {
  var X = window['X_' + $(this).closest('.tb_id').attr('id')];
  if (X['output'] != 'table') return false;
  var T = $(this).closest('.tb_id').attr('id');

   //  var Row = (X['output'] == 'table') ? $(this).closest('tr') : $(this).closest('.div_item');
  var Row =  $(this).closest('tr')
  if(Row.length < 1) Row = $(this).closest('.div_item');

  var rowId = Row.attr('id').replace('row-', '');
  var colId = $(this).closest('td').attr('id').replace('cell-' + rowId + '-', '')
  var cellId = rowId + '-' + colId
  var cell = $('#' + T + ' #cell-' + cellId);
  cancelEdit();
  edit_visible = true;
  Row.find('.save img').attr('src', src_save_1);
  Row.find('.edit img').attr('src', src_edit_1);
  Row.addClass('row_editing_on');                                //new
  if (X[cellId] == undefined) X[cellId] = new Array();
  X[cellId]['editing'] = true;
  X[cellId]['id'] = rowId;
  X[cellId]['origText'] = getText(cell); //cell.text();
  if (X[colId]['type'] == 'textarea')
    var input = $('<textarea class="input-edit" name="' + cellId + '" title="' + rowId + ':' + colId + '"></textarea>');
  // else if(X[colId]['type']=='progress')
  //   var input = $('<input type="text" value="" class="input-edit" name="'+cellId+'" title="'+rowId+':'+colId+'">');
  else if (X[colId]['type'] == 'bool')
    var input = $('<input type="checkbox" value="" class="input-edit" name="' + cellId + '" title="' + rowId + ':' + colId + '">');
  else //if(X[colId]['type']=='string')
    var input = $('<input type="text" value="" class="input-edit" name="' + cellId + '" title="' + rowId + ':' + colId + '">');
  input.width(cell.width())
    .val(X[cellId]['origText'])
    .appendTo(cell.empty().width(cell.width()))
    .keydown(function (e) {
      if (e.which == 27) {
        //cancelEdit();
        //cell.text( X[cellId]['origText']);// hacer click en cancelar edit row
        Row.find('.edit').click();
      } else if (e.which == 13) {

        //cancelEdit();
        Row.find('.save').click();
      }
    })
    /*
    .keypress(function(e){
      code=e.keyCode?e.keyCode:e.which;
      if(code.toString()==13){
        Row.find('.save').click();
      }
    })
    */
    .focus()
    .addClass('editing_on')
    ;                                      //new
  if (X[colId]['type'] == 'bool') {
    if (X[cellId]['origText'] == '1') input.attr('checked', true);
  }
  if (X[colId]['type'] == 'textarea')
    input.css('position', 'absolute')   //FIX cancel other editing rows !! by check if currentRow var is set  
      .css('z-index', '2')
      .css('height', '50px')
      .width(cell.width());
});

$('body').on('dblclick', '.datatable table td.editable-select', function () {
  var X = window['X_' + $(this).closest('.tb_id').attr('id')];
  if (X['output'] != 'table') return false;
  var T = $(this).closest('.tb_id').attr('id');
  var Row = $(this).closest('tr');
  var rowId = Row.attr('id').replace('row-', '');
  var colId = $(this).closest('td').attr('id').replace('cell-' + rowId + '-', '')
  var cellId = rowId + '-' + colId
  var cell = $('#T-' + T + ' #cell-' + cellId);
  var pk = $(this).closest('.tb_id').data('pk');
  cancelEdit();
  edit_visible = true;
  Row.find('.save img').attr('src', src_save_1);
  Row.find('.edit img').attr('src', src_edit_1);
  Row.addClass('row_editing_on');                                //new
  if (X[cellId] == undefined) X[cellId] = new Array();
  X[cellId]['editing'] = true;
  X[cellId]['id'] = rowId;
  X[cellId]['origText'] = cell.text(); // cell.find(':selected').text();
  X[cellId]['origVal'] = cell.attr('val');  //cell.find('select').val();     //
  //  if(X[colId]['name']=='tasks_state')    Row.removeClass('tasks_state-'+X[cellId]['origVal']);
  //  if(X[colId]['name']=='tasks_priority') Row.removeClass('tasks_priority-'+X[cellId]['origVal']);
  //  cell.removeClass('tasks_priority-'+X[cellId]['origVal']);
  $('<select name="' + cellId + '" title="' + rowId + ':' + colId + '" class="input-edit"></select>') //+edit_cell_buttons)
    .width(cell.width())
    .appendTo(cell.empty().width(cell.width()).css('padding', '0px'))
    .keypress(function (e) {
      code = e.keyCode ? e.keyCode : e.which; if (code.toString() == 27) {
        //alert(X[cellId]['origText']);
        cancelEdit();
        cell.text(X[cellId]['origText']);
      }
    })
    .load(X[colId]['ajax_url'] + '/selected=' + X[cellId]['origVal'] + '/table=' + T + '/pk=' + pk + '/id=' + rowId + '/fieldname=' + cell.data('fieldname'))  //'_modulos_/'+module_name+'/index.php?ajax=levels&selected=')
    //.focus()
    .addClass('editing_on')
    .change(function () { if (onChange) onChange(T, X[colId]['name'], Row, $(this).attr('value')); })
    //.focus( function(){ if(onFocus)  onFocus( T, X[colId]['name'],Row,$(this).attr('value') ); })
    //.blur(  function(){ if(onBlur)   onBlur(  T, X[colId]['name'],Row,$(this).attr('value') ); })
    ;
  //console.log('URL_SELECT',X[colId]['ajax_url']+'/selected='+X[cellId]['origVal']+'/table='+T+'/pk='+pk+'/id='+rowId+'/fieldname='+cell.data('fieldname'));
});

//  ++++++ save +++++++++
$('body').on('click', '.datatable .actions a.save', function () {
  var T = $(this).closest('.tb_id').attr('id');
  var X = window['X_' + T];
  //var currentRow = $(this).closest('tr');

  //var currentRow = (X['output'] == 'table') ? $(this).closest('tr') : $(this).closest('.div_item');
  var currentRow =  $(this).closest('tr')
  if(currentRow.length < 1) currentRow = $(this).closest('.div_item');
  
  var dlgname = 'dlg-save-' + T;
  var editing = (currentRow.find('.save img').attr('src') === src_save_0);
  if (editing) {
    shake($(this).find('img'));
    return false;
  }

  var rowId = currentRow.attr('id').replace('row-', '');
  var rowValue = false;
  /****************
  $.modalform({ 'html':'<h4>¿Guardar cambios?</h4>', 'buttons':'ok cancel'}, function(accept) {
    if(accept) { 
      *************/
  currentRow.find('.editable').each(function () {
    $(this).find('.input-edit').each(function () {
      if ($(this).hasClass('editing_on')) {
        var colId = $(this).closest('td').attr('id').replace('cell-' + rowId + '-', '');
        var cellId = rowId + '-' + colId
        var Cell = $('#' + T + ' #cell-' + cellId);
        var rowName = X[colId]['name'];
        if (X[colId]['type'] == 'bool') var rowValue = ($(this).attr('checked')) ? '1' : '0';
        else var rowValue = $(this).val();
        if (X[cellId]) var oldValue = X[cellId]['origText'];
        if (rowValue && rowValue != oldValue) {
          $(this).parent().text(rowValue);
          $.post(AJAX_URL + '/' + module_name + '/ajax/module=' + module_name + '/op=updatefield', { "table": T, "col": colId, "key": rowId, "field": rowName, "value": rowValue }, function (data, textStatus, jqXHR) {
            if (data.error == 0) {
              if (onChange) onChange(T, rowName, currentRow, rowValue);
              showMessageInfo(data.msg);
              if (X[colId]['type'] == 'bool') {
                if (rowValue == '1') Cell.removeClass('unchecked').addClass('checked');
                else Cell.removeClass('checked').addClass('unchecked');
              }
            } else {
              showMessageError(data.msg);
              if (data.error == 3) Cell.text(oldValue);
            }
          }, 'json');
        } else {  // if( rowValue)
          Cell.text(oldValue);
        }
      }  //if($(this).hasClass('editing_on')){
    });
  });

  currentRow.find('.editable-select').each(function () {
    $(this).find('.input-edit').each(function () {
      if ($(this).hasClass('editing_on')) {
        var colId = $(this).closest('td').attr('id').replace('cell-' + rowId + '-', '');
        var cellId = rowId + '-' + colId
        var Cell = $('#' + T + ' #cell-' + cellId);
        var rowName = X[colId]['name'];
        var rowValue = $(this).val();
        if (X[cellId]) var oldValue = X[cellId]['origText']; //(X[cellId]) ? X[cellId]['origText'] : false;
        // console.log('editing_on',rowValue,oldValue);
        if (rowValue && rowValue != oldValue) {
          $(this).parent().text($(this).parent().find(':selected').text()).val(rowValue);
          X[cellId]['origVal'] = rowValue;
          //  console.log(AJAX_URL+'/'+module_name+'/ajax/module='+module_name+'/op=updatefield/table='+T+'/col='+colId+'/key='+rowId+'/field='+rowName+'/value='+rowValue);
          $.post(AJAX_URL + '/' + module_name + '/ajax/module=' + module_name + '/op=updatefield', { "table": T, "col": colId, "key": rowId, "field": rowName, "value": rowValue }, function (data, textStatus, jqXHR) {
            if (data.error == 0) {
              if (onChange) onChange(T, rowName, currentRow, rowValue);
              showMessageInfo(data.msg);
            } else {
              showMessageError(data.msg);
              if (data.error == 3) Cell.text(oldValue);
            }
          }, 'json');
        } else {  // if( rowValue)
          Cell.text(oldValue);
        }
        Cell.css('padding', '2px 3px 0px 3px');
      }  //if($(this).hasClass('editing_on')){
    });
  });
  currentRow.find('.save img').attr('src', src_save_0);
  currentRow.find('.edit img').attr('src', src_edit_0);
  /////// edit_visible = false;
  X['state'] = 'normal';
  setTimeout(function () {
    edit_visible = false;
  }, 500);
  //FIX        currentRow.find('td').effect("highlight", {}, 3000);
  /**********
  }else{
    edit_visible = false;
    currentRow.find('.editable,.editable-select').each( function(){
      colId = $(this).attr('id').replace('cell-'+rowId+'-','');
      cellId = rowId+'-'+colId
      Cell = $('#'+T+' #cell-'+cellId); 
      if(X[cellId]!=undefined) Cell.text(X[cellId]['origText']).css('padding','2px 3px 0px 3px');
    });
    currentRow.find('.save img').attr('src',src_save_0);
    currentRow.find('.edit img').attr('src',src_edit_0);
    X['state'] = 'normal';
    currentRow.effect("highlight", {}, 3000);
  }
});
  *******************/
});

//  ++++++ delete +++++++++
$('body').on('click', '.datatable .actions a.delete', function () {
  var T = $(this).closest('.tb_id').attr('id');
  var X = window['X_' + T]

  //  var currentRow = (X['output'] == 'table') ? $(this).closest('tr') : $(this).closest('.div_item');
  var currentRow =  $(this).closest('tr')
  if(currentRow.length < 1) currentRow = $(this).closest('.div_item');

  var rowId = currentRow.attr('id').replace('row-', '');
  var detail_tables = (jQuery.isArray(X['detail_tables'])) ? X['detail_tables'] : false;

  $.modalform({'title':'Eliminar fila', 'html': '<h4>Va a eliminar esta entrada ¿está seguro? </h4><br/ >Esta acción no podrá deshacerse.', 'buttons': 'ok cancel' }, function (accept) {
    if (accept) {
      //console.log('accept',accept,AJAX_URL+'/'+module_name+'/ajax/module='+module_name+'/op=delete'+'/table='+T+'/id='+rowId);
      $.get(AJAX_URL + '/' + module_name + '/ajax/module=' + module_name + '/op=delete' + '/table=' + T + '/id=' + rowId, function (data) {
        if (data.error == 0) {       //if( msg.toLowerCase().indexOf('ok')>-1)
          if (X['output'] == 'table') {
             if(console_log)  console.log('DELETED.ROW', rowId )

            //FIX currentRow.effect("transfer", { to: $("#recycle-icon") }, 800);   
            //FIX currentRow.fadeOut('slow');// .css('text-decoration','line-through'); 
            if (data.deleted == 'soft') currentRow.addClass('deleted'); //css('text-decoration','line-through'); 
            else currentRow.css('text-decoration', 'line-through'); //.fadeOut('slow');
            setTimeout(function () { $('#recycle-icon').attr('src', '_images_/famfam/trash-full.png'); }, 800);


            if (detail_tables) {
              for (var i = 0; i < detail_tables.length; i++) {
                if (detail_tables[i]) {
                  load_page(module_name, detail_tables[i], 1, rowId);
                }
              }
            }


          } else {
            if(console_log)  console.log('DELETED.ITEM', rowId )
            currentRow.addClass('animated hinge');
            setTimeout(function () { currentRow.fadeOut('slow'); }, 1800);
          }

          // Se pueden añadir, y las añadiremos, mas líneas como esta para cada cosa que
          // ocurra, el nombre del 'event' nos lo inventamos. Sólo tenemos que definir
          // la funcion onTableEvent en donde queramos usarla. Como argumentos le metemos 
          // cualquuier cosa, pero al menos un 'event' y un 'tablename', que nos servirán
          // para saber qué hacer.
          if (onTableEvent) onTableEvent({ 'event': 'after_delete', 'table': T , 'row': rowId});

          showMessageInfo(data.msg);
        } else {
          if (onTableEvent) onTableEvent({ 'event': 'after_delete_error', 'table': T, 'row': rowId });
          showMessageError(data.msg);
          $.modalform({ 'title': 'ERROR', 'text': data.msg, 'buttons': 'close' });
        }
      }, 'json')//.done(function() {
        //alert( "second success" );
        //})
        .fail(function (data) {
          show_error($('#T-' + T), 'Error en la llamada a la URL: ' + AJAX_URL + '/' + module_name + '/ajax/module=' + module_name + '/op=delete' + '/table=' + T + '/id=' + rowId, 10000);
        })
        .always(function () {
          //alert( "finished" );
        });
    } else {
      if (onTableEvent) onTableEvent({ 'event': 'cancel_delete', 'table': T, 'row': rowId });
    }
  });
});

$('body').on('click', '.datatable a.page_link', function (e) {

  var T = $(this).closest('.datatable').attr('id').replace('T-', '');
  let page = $(this).data('page');
  let parent = $(this).data('parent');  // if($tabla->parent_key && $tabla->parent_value) $tabla->paginator_link .= ",{$tabla->parent_value}";
  //console.log('CLIC:',module_name,T,page);       
  load_page(module_name, T, page, parent);

})

/**/
function upload_files(title,modulename,tablename,fieldname,parent=false,prefix=false,acceptfiles='.jpg,.jpeg,.png,.pdf,.docx,.txt'){

    $("body").dialog({
        title: title + '['+parent+']',
        content: '<div class="dropzone-container"><div id="miDropzone" class="dropzone"><div class="dz-message">Arrastra aquí tus archivos<br />(o haz clic para seleccionarlos)</div></div></div>',
        width: "800px",
        height: "500px",
        buttons: [$.dialog.closeButton],
        onLoad: function(){
                             
            upload_modulename = modulename;
            upload_tablename = tablename;
            upload_fieldname = fieldname;
            upload_parent     = parent; //'< ?=$cfg_upload['parent']? >';
            upload_prefix     = prefix; //'< ?=$cfg_upload['prefix_filename']? >';

            var ajax_url = upload_modulename + '/ajax/op=post_file/table=' + upload_tablename + '/field=' + upload_fieldname + '/module=' + upload_modulename;
            //if (upload_if_exists) ajax_url += '/if_exists=' + upload_if_exists;
            if (upload_parent) ajax_url += '/parent=' + upload_parent;
            if (upload_prefix) ajax_url += '/tnprefix=' + upload_prefix;

            // 4) Desactivar autoinicialización (opcional, pero recomendable)
            Dropzone.autoDiscover = false;

            // 5) Configuración de Dropzone sobre el form con id="miDropzone"
            const miZona = new Dropzone("#miDropzone", {
                url: ajax_url,   //"test/ajax/op=dropzone",       // Endpoint PHP al que se hará POST
                paramName: upload_fieldname,               // Nombre del campo que llegará a $_FILES
                maxFilesize: 10,                 // Tamaño máximo x archivo (MB)
                parallelUploads: 5,              // Número de subidas simultáneas
                uploadMultiple: false,           // Enviar de uno en uno para ver progreso individual
                maxFiles: 30,                    // Número máximo de archivos permitidos
                acceptedFiles: acceptfiles, // Ajusta según tus necesidades
              //addRemoveLinks: true,            // Agregar link para cancelar/quitar archivo
                dictDefaultMessage: "Arrastra archivos aquí o haz clic para seleccionarlos",
                dictRemoveFile: "Eliminar",
                dictFallbackMessage: "Tu navegador no soporta arrastrar y soltar archivos",
                dictFileTooBig: "El archivo es muy grande ({{filesize}}MB). Tamaño máximo: {{maxFilesize}}MB.",
                dictInvalidFileType: "No puedes subir archivos de este tipo.",
                dictMaxFilesExceeded: "Ya llegaste al límite de archivos permitidos.",
                init: function() {
                    // 'this' = instancia de Dropzone
                    this.on("sending", function(file, xhr, formData) {
                        // Se ejecuta justo antes de enviar cada archivo.
                        // Si hicieras un formulario con datos extra: formData.append("miCampo","valor");
                    });

                    this.on("uploadprogress", function(file, progress, bytesSent) {
                        // 'progress' está en porcentaje (0 a 100)
                        // Dropzone ya dibuja la barra de progreso por defecto,
                        // pero aquí podrías capturar el progreso si quisieras un log.
                        if(console_log) console.log(`Progreso ${file.name}: ${Math.round(progress)}%`);
                    });

                    this.on("success", function(file, response) {
                        // Se ejecuta al terminar de subir cada archivo exitosamente
                        if(console_log) console.log(`Respuesta del servidor para ${file.name}:`, response);
                    });

                    this.on("error", function(file, errorMessage) {
                        // En caso de error en la subida
                        if(console_log) console.error(`Error subiendo ${file.name}:`, errorMessage);
                        error(`Error subiendo ${file.name}:` + errorMessage);
                    });
                }
            });    
        },
        onClose: function(){
            load_page(modulename, tablename, 1,parent)
        }
    });
}
/**/

$('body').on('click', '.upload_files', function (e) {

  var T = $(this).closest('.datatable').attr('id').replace('T-', '');

  if(console_log) console.log('T',T);

  //let page = $(this).data('page');
  let parent = $(this).data('parent');
  let prefix=false;
  
  // load_files('Upload to '+T, module_name, T, 'FILE_NAME',parent, prefix);
    if (typeof load_files  === 'function') {
        load_files(module_name,T,parent); 
    }else{
        error('Función no definida: load_files')
    } 

})

function moveCursorToEnd(el) {
  el.focus()
  if (typeof el.selectionStart == "number") {
      el.selectionStart = el.selectionEnd = el.value.length;
  } else if (typeof el.createTextRange != "undefined") {           
      var range = el.createTextRange();
      range.collapse(false);
      range.select();
  }
}

function load_page(module, table, page, parent, row) {
  $('#' + table).append('<p class="ajaxloader" style="text-align:center;position:absolute;top:50px;width:100%;padding:20px auto;"><img style="width:56px;" src="_images_/indicator.gif"></p>').css({ opacity: 0.3 });
  edit_visible = false;
  //selected_table = false; 
  //selected_table_id = false;
  var url = AJAX_URL + '/' + module + '/ajax/op=show/table=' + table + '/page=' + page + (parent ? '/parent=' + parent : '');
  $('#ajax-indicator').html('<img src="_images_/indicator.gif">');
  $('#T-' + table).load(url, function () {
    var T = table;
    var X = window['X_' + table]
     //console.log('LOAD_PAGE','X_'+table,X);
    if (row) {
      $('#' + T + ' #row-' + row + ' td').highlight();
      if (tables_loaded && !parent) $('#' + T + ' #row-' + row).find('td').first().click().focus();
    } else {
      if (upping) {
        upping = false;
        if (tables_loaded && !parent) $('#' + T).find('td').last().click().focus();
        $('#' + T).find('td').parent().last().addClass('active');
      } else {
        if (tables_loaded && !parent) $('#' + T).find('td').first().click().focus();
        $('#' + T).find('td').parent().first().addClass('active');
      }
    }
    $.each($('#' + T + ' .cell-decimal'), function (i) {
      let precission = $(this).data('precission');
      var n = parseFloat($(this).html());
      $(this).html(n.format(precission ? precission : 2, 3, '.', ','));
    });
    if (!row) {
      if (window['idTimeOut_' + table]) clearTimeout(window['idTimeOut_' + table]);
      window['idTimeOut_' + table] = setTimeout(function () { loadDetailTables(module, T, X); }, 100);
    }
    $('#ajax-indicator').html('&nbsp;');
    $('#' + T).css({ opacity: 1 });
    $('#' + T + ' .ajaxloader').hide();
    if (!parent) n_tables_loaded++;
    if (n_tables_loaded == n_tables) tables_loaded = true;
    //console.log('LOADED',table,n_tables_loaded,tables_loaded,n_tables);
    //    if(table=='TSK_TASKS')init_sortable();
    if (window['sortable_' + table]) init_sortable();



    //$( '#'+T+' .list_items_section, .div_item' ).disableSelection();                          
    //if(page) $('#T-<?=$tablename?> .input_page_num').val(page);
  })//.fail  (function (data) { console.log('FAIL',data)    })
    //.always(function (data) { console.log('ALWAYS',data)   });
}

function loadDetailTables(module, T, X) {
  
  if(!X || !X['detail_tables']) return false;  

  if ($.isArray(X['detail_tables'])) {

    rowId = X['output'] == 'table' 
            ? $('#' + T).find('td').parent().first().attr('id') 
            : $('#' + T).find('.div_item').first().attr('id');
    if (rowId) {
      rowId = rowId.replace('row-', '');
      for (var i = 0; i < X['detail_tables'].length; i++) load_page(module, X['detail_tables'][i], 1, rowId);
      /*
      if (X['detail_field']) {
        $.get(AJAX_URL + '/' + module + '/module=' + module + '/op=get/table=' + T + '/id=' + rowId, function (d, t, x) {
          $('#detail_' + T).html(d);
        }).fail(function (data) {
          show_error($('#T-' + T), 'Error en la llamada a la URL: ' + AJAX_URL + '/' + module + '/module=' + module + '/op=get/table=' + T + '/id=' + rowId, 10000);
        }).always(function () {
          //alert( "finished" );
        });
      }
      */
    } else {
      for (var i = 0; i < X['detail_tables'].length; i++) load_page(module, X['detail_tables'][i], 1, -1);
    }
  }
}


//  ++++++ create/update table +++++++++
$('body').on('click', '.datatable a.setup', function (e) {
  var T = $(this).closest('.datatable').attr('id').replace('T-', '');
  var X = window['X_' + T]
  var url = AJAX_URL + '/' + module_name + '/ajax/module=' + module_name + '/op=setup/table=' + T;
  $.modalform({ 'title': X['title'] + ' / Configuración ', 'url': url, 'buttons': 'ok' });
});

//  ++++++ add +++++++++
$('body').on('click', '.datatable a.add, .datatable .add_item', function () {
  var T = $(this).closest('.datatable').attr('id').replace('T-', '');
  //PARENT 
  var parentId = $(this).attr('id');
  var X = window['X_' + T]       // var X = window['X_'+T];
  //alert('351 '+T+'::'+parentId);
  if (X['output'] == 'group') var groupId = ($(this).closest('.div_section').attr('id') == undefined)
    ? 1
    : $(this).closest('.div_section').attr('id').replace('group_', '');
  else var groupId = 1;
  addRow(T, parentId, groupId);
});

//  ++++++ view +++++++++
$('body').on('click', '.datatable a.print', function (event) {
  var T = $(this).closest('.tb_id').attr('id');
  var X = window['X_' + T];
  var Row = (X['output'] == 'table') ? $('#' + T + ' tr.active') : '0';
  var rowId = Row.attr('id').replace('row-', '');
  var url = AJAX_URL + '/' + module_name + '/ajax/module=' + module_name + '/op=print/table=' + T + '/id=' + rowId;
  var windowName = "Imprimir!";    //if(windowName) windowName.close();
  window.open(url, windowName, "width=820,height=850");
  event.preventDefault();
});

//  ++++++ csv +++++++++                                      
$('body').on('click', '.datatable a.csv', function (event) {
  var T = $(this).closest('.tb_id').attr('id');
  var X = window['X_' + T];
  var Row = (X['output'] == 'table') ? $('#' + T + ' tr.active') : '0';
  //LOG console.log('Row',Row);
  if (Row.hasClass('row-empty')) {
    alert('No hay filas');
  } else {
    var rowId = Row.attr('id').replace('row-', '');
    var url = AJAX_URL + '/' + module_name + '/module=' + module_name + '/op=csv/table=' + T + '/id=' + rowId + '/csv';
    top.location.href = url;
    // $.modalform({ 'title' : 'Descargar Excel',  'url': url, 'buttons':'ok'  }); //,function(accept) { Accept(); }); 
    //event.preventDefault();
  }
});

//  ++++++ excel +++++++++                                      
$('body').on('click', '.datatable a.excel', function (event) {
  var T = $(this).closest('.tb_id').attr('id');
  var X = window['X_' + T];
  var Row = (X['output'] == 'table') ? $('#' + T + ' tr.active') : '0';
  var rowId = Row.attr('id').replace('row-', '');
  var url = AJAX_URL + '/' + module_name + '/ajax/module=' + module_name + '/op=excel/table=' + T + '/id=' + rowId;
  $.modalform({ 'title': 'Descargar Excel', 'url': url, 'buttons': 'ok' }); //,function(accept) { Accept(); }); 
  event.preventDefault();
});

//  ++++++ pdf +++++++++
$('body').on('click', '.datatable a.pdf', function (event) {
  var T = $(this).closest('.tb_id').attr('id');
  var X = window['X_' + T];
  var Row = (X['output'] == 'table') ? $('#' + T + ' tr.active') : '0';
  var rowId = Row.attr('id').replace('row-', '');
  //   var url = AJAX_URL+'/'+module_name+'/ajax/module='+module_name+'/op=pdf/table='+T+'/id='+rowId;
  var url = AJAX_URL + '/' + module_name + '/module=' + module_name + '/op=pdf/table=' + T + '/id=' + rowId + '/pdf';
  // top.location.href=url;    
  var windowName = "Listado PDF";
  window.open(url, windowName, "width=800,height=850");
  event.preventDefault();
});

//  ++++++ wordNO +++++++++                                     
$('body').on('click', '.datatable a.wordNO', function (event) {
  var T = $(this).closest('.tb_id').attr('id');
  var parentId = $(this).attr('id');
  var X = window['X_' + T];
  var url = AJAX_URL + '/' + module_name + '/ajax/module=' + module_name + '/op=word/table=' + T + '/id=' + rowId;
  top.location.href = url;
});

//  ++++++ word +++++++++
$('body').on('click', '.datatable a.word', function (event) {
  var T = $(this).closest('.tb_id').attr('id');
  var X = window['X_' + T];
  var Row = (X['output'] == 'table') ? $('#' + T + ' tr.active') : '0';
  var rowId = Row.attr('id').replace('row-', '');
  var url = AJAX_URL + '/' + module_name + '/ajax/module=' + module_name + '/op=word/table=' + T + '/id=' + rowId;
  $.modalform({ /*'title' : 'Norl', */ 'url': url, 'buttons': 'ok' }); //,function(accept) { Accept(); }); 
  event.preventDefault();
});

//  ++++++ view +++++++++
$('body').on('click', '.datatable a.view', function (event) {
  var T = $(this).closest('.tb_id').attr('id');
  var X = window['X_' + T];
  var Row = /*(X['output'] == 'table') ?*/ $('#' + T + ' tr.active');// : '0';
  if(Row.length <1) Row = $(this).closest('.div_item'); 
  if (Row.hasClass('row-empty')) {
    alert('No hay filas');
  } else {
    var rowId = Row.attr('id').replace('row-', '');
    var url = AJAX_URL + '/' + module_name + '/ajax/module=' + module_name + '/op=view/table=' + T + '/id=' + rowId;
    //    var windowName = "Ver";    //if(windowName) windowName.close();
    //    window.open(url, windowName, "width=800,height=850");
    $.modalform({ 'title' : 'View',  'url': url, 'buttons': 'ok' });
    //$.modalform({ 'title' : 'Error', 'text': '280 FIX / '+data.msg, 'buttons':'close' });
  }
  event.preventDefault();
});

//  ++++++ pdf +++++++++
/**
$('body').on('click','.datatable a.pdf',function(e){
  var T =  $(this).closest('.tb_id').attr('id');            
  var parentId = $(this).attr('id');
  var X = window['X_'+T];
  var url = AJAX_URL+'/'+module_name+'/ajax/module='+module_name+'/op=pdf/table='+selected_table+'/id='+selected_table_id;
  top.location.href=url;
});
*********/

$('body').on('click', '.datatable a.reload', function (event) {
  var T = $(this).closest('.datatable').attr('id').replace('T-', '');
  var parentId = $(this).attr('id');
  var get_url = AJAX_URL + '/' + module_name + '/ajax/module=' + module_name + '/table=' + T + '/op=reload'; //+(parentId ? '/parent='+parentId : '');
  if(console_log) console.log('reload: ',get_url,parentId);
  $.post(get_url, { "table": T }, function (data) {
    if(console_log) console.log('RELOAD.data',data);
    if (data.error == 0) {
      showMessageInfo(data.msg);
      load_page(module_name, T, 1, parentId);
    } else {
      showMessageError(data.msg);
    }
  }, 'json');
  //event.preventDefault();
});

$('body').on('click', '.datatable a.gallery', function (event) {
  var T = $(this).closest('.datatable').attr('id').replace('T-', '');
  var parentId = $(this).attr('id');
  var get_url = AJAX_URL + '/' + module_name + '/ajax/module=' + module_name + '/table=' + T + '/op=gallery'; //+(parentId ? '/parent='+parentId : '');
  $.post(get_url, { "table": T }, function (data) {
    if (data.error == 0) {
      showMessageInfo(data.msg);
      load_page(module_name, T, 1, parentId);
    } else {
      showMessageError(data.msg);
    }
  }, 'json');
});

$('body').on('click', '.datatable a.filter', function (e) {
  var T = $(this).closest('.datatable').attr('id').replace('T-', '');
  var X = window['X_' + T]
  var url = AJAX_URL + '/' + module_name + '/ajax/module=' + module_name + '/op=filter/table=' + T;
  $.modalform({ 'title': X['title'] + ' / Filtro ', 'url': url , 'buttons': 'save cancel'});
});


//  ++++++ key events +++++++++
/*
$('.datatable').on('click','.tb_id',function(){
    console.log('Datatable  click');
  var Tb = $(this).attr('id'); //.replace('T-','');
  $('#'+selected_table).removeClass('active');
  selected_table = Tb;
  $('#'+selected_table).addClass('active');
});
*/

//  ++++++ click row +++++++++
$('body').on('click', ' .datatable .div_section .div_item, .datatable table .table-row td', function () {
  //$('.datatable table .cell td').live('click',function(){
  //  $('.div_item, .datatable table td').bind('click touch', function(e){
  var T = $(this).closest('.tb_id').attr('id');

  $('#' + selected_table).removeClass('active');
  selected_table = T;
  $('#' + selected_table).addClass('active');

 // console.log('.table-row td click',selected_table);


  var X = window['X_' + T];
  if (X == undefined) return false;
  //var detail_field = X['detail_field'];

  //var Row = (X['output'] == 'table') ? $(this).closest('tr') : $(this);
  var Row =  $(this).closest('tr')
  if(Row.length < 1) Row = $(this);  

  if (onSelectRow) onSelectRow(T, Row);
  if (Row.attr('id') == undefined) {
    //load_page(module_name,tablename_detail,1,rowId);
  } else {
    Row.parent().find('tr').removeClass('active');
    Row.addClass('active');
    if (Row.attr('id')) {
      var rowId = Row.attr('id').replace('row-', '');
      var detail_tables = (jQuery.isArray(X['detail_tables'])) ? X['detail_tables'] : false;
      /**/
      //console.log(T+': '+'click '+rowId); //'Load '+selected_table_id);
      if (onBeforeShowRow) onBeforeShowRow(T, rowId);
      clearTimeout(X['idTimeOut']);
      X['idTimeOut'] = setTimeout(function () {
        //console.log(T+': '+'load  '+selected_table_id); //'Load '+selected_table_id);
        /*
        if (detail_field) {
          //alert(AJAX_URL+'?module='+module_name+'&ajax=get&table='+T+'&id='+rowId);
          //if (selected_table_id !== rowId) 
          $.get(AJAX_URL + '/' + module_name + '/ajax/module=' + module_name + '/op=get', { "table": T, "id": rowId }, function (data, textStatus, jqXHR) {
            //LOG console.log(T+': '+'get_detail: '+rowId + ' '+X['detail_field']);
            $('#detail_' + T).html(data).show('slow');
          }).fail(function (data) {
            show_error($('#T-' + T), 'Error en la llamada a la URL: ' + AJAX_URL + '/' + module_name + '/ajax/module=' + module_name + '/op=get', 10000);
          }).always(function () {
            //alert( "finished" );
          });
        }
        */
        //console.log(T+':'+rowId);
        if (onAfterShowRow) onAfterShowRow(T, rowId);
        if (detail_tables) {
          for (var i = 0; i < detail_tables.length; i++) {
            if (detail_tables[i]) {
              load_page(module_name, detail_tables[i], 1, rowId);
            }
          }
        }
      }, 500);
      selected_table_id = rowId;
      //editRow(T,rowId);
    }
  }
});

$('body').on('click', '.order', function () {
  //LOG console.log('order');
  var T = $(this).closest('.tb_id').attr('id');
  var F = $(this).attr('id').replace('column_', '');
  var X = window['X_' + T];
  var parentId = X['parent'];
  $.post(AJAX_URL + '/' + module_name + '/ajax/module=' + module_name + '/op=order', { "table": T, "col": F },
    function (data) {
      if (data.error == 0) {       //if( msg.toLowerCase().indexOf('ok')>-1)
        load_page(module_name, T, 1, parentId);
        //$('.order').removeClass('asc').removeClass('desc');
        //$('#column_'+data.col).addClass(data.asc);
        showMessageInfo(data.msg);
      } else {
        showMessageError(data.msg);
      }
    }, 'json');
});

$('.div_item').on('click', 'dt', function () {
  var T = $(this).closest('.tb_id').attr('id');
  var X = window['X_' + T];
  //var detail_field = X['detail_field'];
  //var Row = (X['output'] == 'table') ? $(this).closest('tr') : $(this).closest('.div_item');
  var Row =  $(this).closest('tr')
  if(Row.length < 1) Row = $(this).closest('.div_item');

  if (Row.attr('id') == undefined) {
    //load_page(module_name, tablename_detail ,1,rowId);
  } else {
    Row.parent().find('tr').removeClass('active');
    Row.addClass('active');
    if (Row.attr('id')) {
      var rowId = Row.attr('id').replace('row-', '');
      //function editRow(T,rowId){
      //var X =  eval('X_'+T);
      var url = AJAX_URL + '/' + module_name + '/ajax/module=' + module_name + '/op=edit/table=' + T + '/id=' + rowId + '/page=' + X['page'];
      $.modalform({ 'title': X['title'] + ' / Modificar fila: ' + rowId, 'url': url });
      //}
    }
  }
});

$('body').on('click', '.input-icon-reset', function () {
  $(this).val('');
  var T = $(this).attr('table');
  $.post(AJAX_URL + '/' + module_name + '/ajax/module=' + module_name + '/op=search',
    { "table": T, "searchstring": '' },
    function (data) {
      if (data.error == 0) {
        load_page(module_name, T, 1);
        showMessageInfo(data.msg);
      } else {
        showMessageError(data.msg);
      }
    }, 'json');
  return false;
});

/** *
$('body').on('click', '.search-form-input', function () {
  var url = AJAX_URL + '/' + module_name + '/ajax/module=' + module_name + '/op=search';
  var T = $(this).attr('table');
  var s = $(this).val();
  if (s) {
    console.log('SEARCH URL', url)
    $.get(url, { "table": T, "searchstring": s },
      function (data) {
        if (data.error == 0) {       //if( msg.toLowerCase().indexOf('ok')>-1)
          load_page(module_name, T, 1);
          showMessageInfo(data.msg);
        } else {
          showMessageError(data.msg);
        }
      }, 'json');
  }
});
**/

$('body').on('focus', '.search-form-input input', function () {
  form_search_focus = true;
  //console.log('form_search_focus',form_search_focus);
});

$('body').on('blur', '.search-form-input input', function () {
  form_search_focus = false;
  //console.log('form_search_focus',form_search_focus);
});


$('body').on('keydown', '.search-form-input input', function (e) {

    form_search_focus = true;

    if (e.keyCode == 13) { //ENTER
        searchtable(e);
    }

});

function searchtable(e){

    var T = $(e.target).attr('table');

    $.post(AJAX_URL + '/' + module_name + '/ajax/module=' + module_name + '/op=search',
      {
        "table": T,
        "searchstring": $(e.target).val()
      },
      function (data) {
        if (data.error == 0) {       //if( msg.toLowerCase().indexOf('ok')>-1)
          //load_page(module_name, T, 1);
          
          var X = window['X_' + T];
          var parentId = X['parent']??1;
          load_page(module_name, T, 1, parentId);

          showMessageInfo(data.msg);
        } else {
          showMessageError(data.msg);
        }

         setTimeout(function(){    
             if(console_log) console.log('ID',e.target.id)
             let el = document.querySelector('#' + e.target.id)
             moveCursorToEnd(el)
         },200);

      }, 'json');  
      
}




$('body').on('keydown', null, function (e) {
  //  $(document).bind('keydown',function(e){
  /*
    var T =  $(this).closest('.tb_id').attr('id');    
    var X = window['X_'+T];
    var detail_field = X['detail_field'];
    var Row = (X['output']=='table') ? $(this).closest('tr') : $(this);
   */

  //if (form_visible) { return false;    }
  if (form_search_focus===true)   return false;

  if (!selected_table) return true;

  if (edit_visible) return true;
  if (!selected_table && default_selected_table) selected_table = default_selected_table;
  if (onKeyDown) onKeyDown(selected_table, selected_table_id, e);

  form_visible = $('.wq-dialog-overlay').is(':visible') || $('#sql-editor').is(':visible');

  //if(!form_visible) return true;
  if (!proccess_keydown) return true;
  //console.log('which:: '+e.which);

  if (e.which == 40) { //DOWN ARROW

    //console.log('DOWN',selected_table);
    if (!form_visible) {//  && !form_search_focus){
      e.preventDefault();
      //  if(!selected_table && default_selected_table) selected_table=default_selected_table;
      var next_row = $('#' + selected_table + ' .active').next();
      if (!next_row.attr('id')) eval($('#pager_' + selected_table + ' .next_on').click());
      else next_row.find('td').first().click();
    }

  } else if (e.which == 38) {// && !form_search_focus) { //UP ARROW
    //console.log('UP',selected_table);

    if (!form_visible) {
      e.preventDefault();
      //if(!selected_table && default_selected_table) selected_table=default_selected_table;
      var prev_row = $('#' + selected_table + ' .active').prev();
      if (!prev_row.attr('id')) {
        upping = true;
        eval($('#pager_' + selected_table + ' .prev_on').click());
      } else prev_row.find('td').first().click(); //attr('id');
    }

  } else if (e.which == 37 || e.which == 33) {//  && !form_search_focus) { // LEFT ARROW

    //console.log('LEFT',selected_table);
    if (!form_visible) {
      // if(!selected_table && default_selected_table) selected_table=default_selected_table;
      e.preventDefault();
      eval($('#pager_' + selected_table + ' .prev_on').click());
      //$('#pager_'+selected_table+' .prev_on').trigger('click');
    }

  } else if (e.which == 39 || e.which == 34) { // RIGHT ARROW
    //console.log('RIGHT',selected_table);

    if (!form_visible) {// && !form_search_focus){
      // if(!selected_table && default_selected_table) selected_table=default_selected_table;
      e.preventDefault();
      eval($('#pager_' + selected_table + ' .next_on').click());
    }

  } else if (e.which == 27) { //ESC

    if (form_search_focus) {
      // $('#'+selected_table).focus();
    }
    else
      if (form_visible) {
        form_visible = false;
        e.preventDefault();
        $('.wq-dialog-overlay').fadeOut(function () { $(this).remove(); });
        //$('.xdsoft_datetimepicker').hide();
      }

  } else if (e.keyCode == 13) { //ENTER
    //LOG console.log('edit_visible',edit_visible);
    //return true;
    if (form_search_focus) {
 
       // e.preventDefault();
       // e.stopPropagation()
        return false;
      
    } else if (!form_visible) {
      //LOG console.log('775 which:: '+e.which);

      //var T = $(e.target).attr('table');
      //var T = (X['output']=='table') ? $(e.target).closest('tr') : $(e.target).closest('.div_item');
      //var T = (X['output']=='table') ? $(e.target).closest('tr') : $(e.target).closest('.div_item');
      //var R = ($(e.target).attr('row')) ? $(e.target).attr('row') : '0';
      //alert(selected_table+'::'+selected_table_id);
      //var rowId = R.replace('row-','');
      //form_visible=true;
      var X = window['X_' + selected_table];
      //console.log('selected_table',selected_table);
      //var Row = (X['output'] == 'table') ? $(this).closest('tr') : $(this).closest('.div_item');
      var Row =  $(this).closest('tr')
      if(Row.length < 1) Row = $(this).closest('.div_item');

      if (Row.hasClass('row_editing_on')) {

      } else {
        if (X['output'] == 'table' && selected_table && selected_table_id) {
          //--old editRow(selected_table,selected_table_id);
          //++new from here
          var R = $('#' + selected_table + ' #row-' + selected_table_id)
          if (R.hasClass('edit')) {
            //X[selected_table_id]['editing']=false;
            editRow(selected_table, selected_table_id);
          } else {
            //alert(R.attr('id'));
            var url = AJAX_URL + '/' + module_name + '/ajax/module=' + module_name + '/op=view/table=' + selected_table + '/id=' + selected_table_id;
            if (X['detail']) $.modalform({ 'url': url, 'buttons': 'ok' });
          }
          //++new to here
        }//else
      }
      //alert('645 Error: Identificador no definido');
      /****
        var _src_ = $('#'+selected_table+' #row-'+selected_table_id ).find('a.edit img').attr('src');
        //appendMsg(_src_ + '==='+ src_edit_1 );
        var editing = (_src_ === src_edit_1);
        if(!editing){
          form_visible=true;
          e.preventDefault();
          //appendMsg('selected_table: ' + selected_table + ': ' +  selected_table_id );
          $('#'+selected_table+' #row-'+selected_table_id ).find('a.edit').click();
        }
        ***/
    } else {
      //LOG console.log('818 which:: '+e.which);
      var target = e.target;
      if ($(e.target).hasClass('input_enter_as_tab')) {
        e.preventDefault();
        var inputs = $('.input_enter_as_tab');
        inputs.eq(inputs.index(target) + 1).focus();
      }
    }
    // $('a.save').click();

  } else if (e.keyCode == 9) { //TAB
    /*
    //alert(e.keyCode);
    if(form_search_focus){
      $('#'+selected_table+' table').focus();
      form_search_focus = false;
    }else{
      $('.search-active').focus();
      form_search_focus = true;
    }
    */
  } else if (e.keyCode == 17) { //TAB
    e.preventDefault();
  } else if (e.keyCode == 18) { //TAB
    e.preventDefault();
    /***************
    }else if(((e.keyCode>=96) && (e.keyCode<=105)) || ((e.which>=49) && (e.which<=57))) {  // Numbers
     // keys +=  e.key ;
        if( (e.keyCode>=96) && (e.keyCode<=105) ) {
            keys +=  e.key ;
      }else{
        keys +=  String.fromCharCode(e.which) ;
        }
        //console.log('NUMBER',e.key,keys);
        while (keys.length >= keyslen) {  keys = keys.substr(1); }
      if(!form_visible){
          //LOG console.log('KEYDOWN: '+e.type + ': ' +  e.which+':'+  String.fromCharCode(e.which) +':'+keys);
      var n=Math.ceil(keys);
      if ( Number.isInteger(n) && (n>0) && (n<10000)  ) {page_num = keys;} else {page_num = false;keys='';}
      clearTimeout(pageTimeout);
      pageTimeout =  setTimeout(function(){
        if(page_num) load_page(module_name, selected_table,page_num); 
        keys='';
        page_num=false;
      }, 1000);
        }
    **************/
  } else { //ESC
    keys += String.fromCharCode(e.which);
    //console.log('ELSE',e.which,keys);  //49 48
    while (keys.length >= keyslen) { keys = keys.substr(1); }
    //  console.log(e.type + ': ' +  e.which+':'+  String.fromCharCode(e.which) +':'+keys);
    //if ( keys.indexOf(keystart+'DEBUG') >= 0 ) { keys=''; toggleDebug();  }
    if (keys.indexOf(keystart + 'INFO') >= 0) { keys = ''; getInfo(); }
    if (keys.indexOf(keystart + 'DESC') >= 0) { keys = ''; getDescribeTable(selected_table); }
    if (keys.indexOf(keystart + 'CACHE') >= 0) { keys = ''; getCache(); }
    //if ( keys.indexOf(keystart+'JAU')   >= 0 ) { $('body').css('background','url(none)').css('background-color','red'); $('#tabs').effect("highlight", {}, 1000); keys=''; }
    //if ( keys.indexOf(keystart+'START') >= 0 ) { circle_active = true; circle(); keys=''; }
    //if ( keys.indexOf(keystart+'STOP')  >= 0 ) { circle_active = false; keys=''; }
    //if ( keys.indexOf(keystart+'ACHO')  >= 0 ) { circle_active = true;for (var i=0; i<5; i++){  makeCircle('content');}circle_active = false;  keys=''; }
    //if ( keys.indexOf(keystart+'ON')    >= 0 ) { startEvents(); keys=''; }
    //if ( keys.indexOf(keystart+'OFF')   >= 0 ) { stopEvents(); keys=''; }
    /*************/
  }
});
//  $(document).bind('keypress',function(e){
$('body').on('keypress', null, function (e) {

  //if(edit_visible) return true;
  //if(onKeyPress) onKeyPress(selected_table, selected_table_id, e );  
  //form_visible=$('.modalformOverlay').is(':visible');
  //e = e || window.event;
  var charCode = (typeof e.which == "number") ? e.which : e.keyCode;
  //appendMsg(e.type + ': ' +  e.which+':'+  String.fromCharCode(charCode) +':'+keys);
  if (charCode && String.fromCharCode(charCode) == ":") {
    // alert("Colon!");
    keys += ':';//String.fromCharCode(e.which);
  }
});

function changeInput(id) {
  /*if(console_log)*/
  // console.log('CHANGEINPUT', id, '#'+id+'_submit');
  if (!FORMS['ID_' + id + '_changed']) {
    $('.btn-close').removeAttr('disabled');
    /*if(console_log)*/
    // console.log('BUTTON','#'+id+'_submit')
    $('#'+id+'_btnsubmit').text('Guardar').removeClass('btn-success').removeClass('btn-warning').addClass('btn-info').removeAttr('disabled').removeClass('btn-inactive');
  //$('#form_' + id + ' .submit').attr('value', 'Guardar').removeClass('btn-success').removeClass('btn-warning').addClass('btn-info').removeAttr('disabled');
    FORMS['ID_' + id + '_changed'] = true;
  }
}

function reloadGrid(module, data) {
  let parent = data.parent ? data.parent : false;
  let row = data.row ? data.row : false;
  if (typeof load_page === "function")
    load_page(module, data.table, data.page, parent, row); //FIX response.ROW for click & select
}

function FORM_prepare(id, token, showfileprogress = false, submit_activated = false) {

  if(console_log) console.log('FORM_prepare', token, id, showfileprogress = false, submit_activated = false)
  
  FORMS['ID_' + id + '_changed'] = false;
  FORMS['ID_' + id + '_token'] = token;
  
  if(console_log) console.log('FORMS', FORMS);
  /********************

  // prepare the form when the DOM is ready 
  if (showfileprogress) {
    bar = $('.fileprogress_bar');
    percent = $('.fileprogress_percent');
    fstatus = $('#fileprogress_status');
  }
  $('#form_' + id + ' .submit').attr('disabled', '');

  if (submit_activated) {
      //changeInput(id);
  }

  //  http://malsup.com/jquery/form/#ajaxForm examples
  //  $(document).ready(function() { 
  var options = {
    target: '#result_' + id,   // target element(s) to be updated with server response 
    beforeSend: function () { // onBeforeSend,
      console.log('AJAXFROM.beforesend')
      $('#form_' + id + ' .submit').attr('disabled', '');
      if (showfileprogress) {
        $('.fileprogress_progress').show();
        fstatus.empty();
        let percentVal = '0%';
        bar.width(percentVal);
        percent.html(percentVal);
      }
    },
    uploadProgress: function (event, position, total, percentComplete) { // onUploadProgress 
      console.log('AJAXFROM.uploadProgress')
      if (showfileprogress) {
        let percentVal = percentComplete + '%';
        bar.width(percentVal);
        percent.html(percentVal);
      }
    },
    complete: function (xhr) { // onComplete 
      console.log('AJAXFROM.complete',xhr)

      if (showfileprogress) {
        fstatus.html(xhr.responseText);
        $('.fileprogress_progress').hide();
      }
    },
    beforeSubmit: function (formData, jqForm, options) { // showRequest,  // pre-submit callback 
      console.log('AJAXFROM.beforeSubmit')  
      for (var xx in formData) {

        if (formData[xx].type == 'textarea') {
         
          if(wysiwyg_editor && wysiwyg_editor=='tinymce'){

              // Esto es un put0 parche para cuando se usa el tinymce, del cual gastamos una versión que no 
              // es la última porque la actual es una repu7isim4 mi3rd4, como casi todo lo que hacen los
              // programadores subn0rm41es estos de ahora que lo flipan con el cagarr0 del nodejs, el angular
              // y demás m1erd4s que sólo sirven para que quienes empiezan se hagan la p1cha un put0 lio.
              // Y no es que nodejs sea un c4garro, no, nodejs mola, claro, pero PHP se le mea encima, que
              // haces un put0 jelouguorld con nodejs y te mete 1gb de node_modules, su pu74 madre jajaja.

              // Averiguamos si este textarea es wysiwyg  con tinymce
              if(tinymce.get(formData[xx].name)!=null){
                   
                  // En caso positivo pillamos el contenido del editor wysiwyg y lo ponemos en 
                  // el sitio en donde debería estar pero no está. Quizá con otros editores wysiwyg
                  // suceda lo mismo, pero usamos esta versión de tinymce porque es la que mete menos
                  // mierda en html y css inline.

                  let tiny_content =  tinymce.get(formData[xx].name).getContent();
                  let text_content = formData[xx].value;
                  if(tiny_content!=text_content) {
                      formData[xx].value = tiny_content;
                  }
                  
                  // PD: Si te ofenden los comentarios ponte un WP y que te den por (_._).

              }
          }
          formData[xx].value = str2crypt(formData[xx].value, FORMS['ID_' + id + '_token']);
        }

      }
      var queryString = $.param(formData);
      if (FORMS['ID_' + id + '_changed']) {
        return true;
      } else {
        $('#form_' + id).closest('.modalformOverlay').fadeOut(function () { });
        $('#form_' + id).closest('.modalformBox').remove();
        return false;
      }

    },
    success: function (data, statusText, xhr, $form) { // showResponse, // post-submit callback 
      console.log('AJAXFROM.success')  

      if (showfileprogress) {
        let percentVal = '100%';
        bar.width(percentVal);
        percent.html(percentVal);
      }

      if (data.error == 0) {
        $('#form_' + id).closest('.modalformOverlay').fadeOut(function () { });
        $('#form_' + id).closest('.modalformBox').remove();
        if (typeof module_name !== 'undefined') reloadGrid(module_name, data);
        var currentRow = $(this).closest('tr');
        showMessageInfo('<?=__LINE__?> :: includes/footer :: ' + data.msg);
        if (onTableEvent) onTableEvent(data);
      } else if (data.error == 4) {
        reloadGrid(module_name, data);
        if (data.msg) showMessageInfo('<?=__LINE__?>  :: includes/footer :: ' + data.msg);
        $('#form_' + id).closest('.modalformOverlay').fadeOut(function () { });
        $('#form_' + id).closest('.modalformBox').remove();
      } else {
        //LOG console.log('<?=__LINE__?> '+data.msg);
        if (data.msg) showMessageError(data.msg);
        $.modalform({ 'title': 'Error', 'text': '391 / ' + data.msg, 'buttons': 'close' });
        FORMS['ID_' + id + '_changed'] = false;
      }
      $('body').removeClass('modalopen');

    },
    error: function () { // showError, // post-submit callback 
      console.log('AJAXFROM.error') 
      // alert('227 - Error al enviar datos');
    },
    //url:       url             // override for form's 'action' attribute 
    type: 'post',          // 'get' or 'post', override for form's 'method' attribute 
    dataType: 'json'           // 'xml', 'script', or 'json' (expected server response type) 
    //clearForm: true            // clear all form fields after successful submit 
    //resetForm: true            // reset the form after successful submit 
    // $.ajax options can be used here too, for example: 
    //timeout:   3000 
  };
  // bind form using 'ajaxForm' 
  ****/


    clearTimeout(changeInputTimeout);


    changeInputTimeout =  setTimeout(function(){
        $('#form_' + id + ' input[type="file"],#form_' + id + ' input[type="date"],#form_' + id + ' input[type="radio"], #form_' + id + ' input[type="text"], #form_' + id + ' input[type="checkbox"], #form_' + id + ' select').change(function (e) {
            //console.log('CHANGEINPUT.CHANGE',e.target);
            changeInput(id); 
        });

        $('#form_' + id + '  select, #form_' + id + '  input').click(function (e) { 
            changeInput(id); 
        });

        $('#form_' + id + ' input[type="text"],#form_' + id + ' input[type="date"],#form_' + id + ' input[type="radio"], #form_' + id + ' textarea, .redactor_editor,  .wysiwyg-editor').keydown(function (e) { 
            changeInput(id); 
        });
    },1000);  //$('#form_' + id + '').ajaxForm(options);
  //  }); 

}



$(document).ready(function () {
  n_tables = $('.datatable').length;

  // Verificar si module_name existe antes de cargar las tablas
  if (typeof module_name === 'undefined') {
    if(console_log) console.warn('INFO: module_name no está definido. No se pueden cargar las tablas.');
    return false;
  }
  
  //  ++++++ load all datatables +++++++++
  jQuery.each($('.datatable'), function () {
    var id = $(this).attr('id');
    if (!id) {
      console.warn('INFO: tabla sin ID detectada. Saltando...');
      return true; // continuar con la siguiente iteración
    }
    var tb = id.replace('T-', '');
    let page_number = window['pageNumber_' + tb];
    load_page(module_name, tb, page_number ? page_number : 1);
  });

  $('body').on('click', '.kbn_items .label.label-tag', function (e) {
    e.preventDefault();
    let cl = $(this).data('class');
    let pa = $(this).closest('.kbn_items');
    pa.find('li.' + cl).show('fast');
    pa.find('li:not(.' + cl + ')').hide('fast');
    return false;
  });

  $('body').on('click', '.kbn_items .download-link a', function (e) {
    //e.preventDefault();
    let i = $(this).data('id');
    let v = $(this).data('value');
    $(this).data('value', ++v);
    $('#download-label-' + i).html(v);
    return true;
  });

  $('body').on('click', '.kbn_wrap #links-filters span', function (e) {
    let cl = $(this).data('class');
    //let pa =  $('#CLI_PAGES_FILES.kbn_items');
    let pa = $(this).closest('.datatable').find('.kbn_items');
    if (cl == 'all') {
      pa.find('li').show('fast');
    } else {
      $('.kbn_wrap #links-filters span').removeClass('checked');
      $(this).addClass('checked');
      //if($(this).hasClass('checked')) pa.find('li.'+cl).show('fast');
      //                           else pa.find('li.'+cl).hide('fast');
      pa.find('li.' + cl).show('fast');
      pa.find('li:not(.' + cl + ')').hide('fast');
    }
  });

  $('body').on('click', '.kbn_section_title .toggle', function () {
    $(this).closest('.kbn_section_title').next().toggle('fast'); //find('.kbn_section_items').togle();
  })

});
