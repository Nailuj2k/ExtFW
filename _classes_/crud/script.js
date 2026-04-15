$(document).ready(function(){ 
 
    var selected_table = false;
    var form_visible   = false;
 //   var reloaded_[TABLENAME]=false;

    //<table parent-value="" parent-key="" tablename="familias" id="table-familias" class="datatable table-bordered table-striped table-inverseX table-hover table-sm">
    /***
    $('body').on('click','.datatable .actions .button',function(event){
         var button = $(this);
         var op  = $(this).attr('op');
         var tablename = $(this).closest('.datatable').attr('tablename');
         var id  = (op=='reload'||op=='add') ? $(this).closest('.datatable').attr('parent-value') : $(this).closest('.row').attr('value');
         console.log('buttonclick: '+' op:'+op+' id:'+id);
         if(op=='delete'||op=='reload')loadTable(button,base_url,tablename,id,op,'message','json');
                                  else loadTable(button,base_url,tablename,id,op,'form','html');
    });
    ***/
  

    if(typeof _ID_!=='undefined' && typeof base_url!=='undefined'){

        $('body').on('click','#table-'+_ID_+' .actions .button.setup',function(event){
            var id  = $(this).closest('.datatable').attr('parent-value'); 
            loadTable($(this),base_url+'/'+_ID_,_ID_,id,'setup','message','json');
        });

        $('body').on('click','#table-'+_ID_+' .actions .button.reload',function(event){
            var id  = $(this).closest('.datatable').attr('parent-value'); 
            loadTable($(this),base_url+'/'+_ID_,_ID_,id,'reload','message','json');
        });

        $('body').on('click','#table-'+_ID_+' .actions .button.add',function(event){
            var id  = $(this).closest('.datatable').attr('parent-value');
            loadTable($(this),base_url+'/'+_ID_,_ID_,id,'add','form','html');
        });
        loadTable(false,base_url+'/'+_ID_,_ID_,false,'show','table','json');
        console.log( 'BASE_URL: '+ base_url+'/'+_ID_);

        $('body').on('click','.datatable',function(){
            var Tb = $(this).attr('tablename'); //.replace('T-','');
            $('#table-'+selected_table).removeClass('active');
            selected_table = Tb;
            $('#table-'+selected_table).addClass('active');
            console.log('selected_table: '+selected_table);
        });

        $('body').on('click','.datatable .row .cell',function(event){
            var row =  $(this).closest('.row');
            row.parent().find('tr').removeClass('active');    
            row.addClass('active');
            console.log('row: '+row);
            //console.log('click '+row.attr('id'));
        });
    }

    $(document).on('keydown', function(e){
      
        form_visible=$('#div-'+selected_table).find('.form').is(':visible');
        
        if(!selected_table) return true; //; false

        console.log('e.which: '+e.which);
        
        if      (e.which == 40) {  //DOWN ARROW
    
            if(!form_visible) {
                e.preventDefault();
                var next_row = $('#table-'+selected_table+' .active').next();
                if(next_row.attr('id')) next_row.find('.cell').first().click(); 
                console.log(e.which+' DOWN '+selected_table);
            }

        }else if(e.which == 38) {  //UP ARROW
       
            if(!form_visible) {
                e.preventDefault();
                var prev_row = $('#table-'+selected_table+' .active').prev();
                if(prev_row.attr('id')) prev_row.find('.cell').first().click(); 
                console.log(e.which+' UP '+selected_table);
            }

        }  else if(e.keyCode == 9) { //TAB

            if(!form_visible) {
                e.preventDefault();
                console.log(e.which+' TAB '+selected_table);
                var tables = $('#table-'+selected_table).closest('.scaffold').parent().children('.scaffold');
                $.each(tables, function(index,value ) {
                   var id= $(this).find('.datatable').attr('tablename');
                   firsttable = tables.eq(0).find('.datatable').attr('tablename');
                   if(selected_table==id){
                        var nexttable = tables.eq(index + 1).find('.datatable').attr('tablename');
                        if(typeof nexttable==='undefined') nexttable = firsttable;
                        $('#table-'+selected_table).removeClass('active');
                        selected_table = nexttable;
                        $('#table-'+selected_table).addClass('active').find('.cell').first().click();            
                        return false;
                    }
                });
            }
        
        }else if(e.keyCode == 17) { 
            e.preventDefault();
        
        }else if(e.keyCode == 18) { 
            e.preventDefault();
      
        }else if(e.keyCode == 13) { //ENTER
            
            if(!form_visible){
                e.preventDefault();
                $('#table-'+selected_table).find('.row.active').find('.button.edit').click();
            }else{
                var target = e.target;
                if($(e.target).hasClass('enter_as_tab')){
                    e.preventDefault();
                    var inputs = $('.enter_as_tab');
                    inputs.eq( inputs.index(target)+ 1 ).focus();
                }
            }
            
        }else if(e.which == 27) { //ESC
      
            if(form_visible){
                e.preventDefault();
                $('#div-'+selected_table).find('.form').find('.button.close').click();
            }

        }
      
    });
    /*
    var datatables = $('.scaffold .datatable');
    $.each(datatables, function( ) {
       console.log( $(this).attr('id') );
    });   
    */
/*****
CKEDITOR.editorConfig = function( config ) {
    config.autoParagraph = false;
    config.toolbarGroups = [
        { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
    ];

    config.removeButtons = 'Strike,Subscript,Superscript,RemoveFormat';
};
**/
});
            //try {
               
               // http://stackoverflow.com/questions/446594/abort-ajax-requests-using-jquery
               
               function loadTable(sender,ajax_url,table,rowid,option,target,datatype){
                   $.ajax({
                       method: 'POST',
                       url: ajax_url, //+'/table='+table+'/op='+option+'/id='+rowid,
                       dataType:  datatype,
                       data: { op:option, id: rowid, table: table },
                       beforeSend: function( xhr, settings ){
                           console.log('::loadTable::ajax::beforeSend: '+ajax_url+'/op='+option+'/id='+rowid+'/table='+table+'/target='+target+'/type='+datatype);
                           if(sender)sender.addClass('waiting').closest('table').prop("disabled",true);
                           $('#div-'+table+' .ajax-loader').show();
                       }
                   }).done(function( data ) {
                       console.log('::loadTable::ajax::done::target['+target+']::data::op['+option+']');
                       //console.log(data);
                       if(target=='message'){   
                           if(data.error) showMessageError('Error '+data.error+'<br />'+data.msg);  
                                     else showMessageInfo('Info: '+data.msg +' '+(data.id?data.id:''));   //  FIX !!!!
                           if (option=='delete') {                             
                               var obj= '#row-'+data.tb+'-'+data.pk+'-'+data.id;
                               if     (data.error==1) shake($( obj).closest('.datatable'));
                               else if(data.error==0) $(obj).addClass('deleted')
                                                            .find('.button')
                                                            .removeClass('delete').removeClass('edit')
                                                            .removeClass('view').removeClass('button');

                           }else if(option=='setup'){
                              loadTable(false,ajax_url,table,rowid,'setup','table','json');
                               //$('#div-'+table+' .table .empty-row .msg-not-exists').html('<pre class="message">'+data.sql+'</pre>');
                           }else if(option=='reload'){
                               loadTable(false,ajax_url,table,rowid,'show','table','json');
                           }else if(option=='add'){
                               loadTable(false,ajax_url,table,rowid,'show','table','json');
                           }
                       }else if(target=='form'){   
                           if (option=='view') data = button_close+'<h3 style="text-transform:capitalize;">'+table+'</h3><pre style="margin:20px auto">'+JSON.stringify(JSON.parse(data).row, null, '\t')+'</pre>';      
                           console.log('target',target);
                           $('#div-'+table+' .form .content').html(data).closest('.form').fadeIn('fast')
                                                             .find('.button.close').click(function(){console.log('close');$(this).closest('.form').fadeOut('fast')});
                       }else if(target=='table'){   
                           $('#div-'+table+' .table').html(data.html);
                       }else{
                           console.log('::loadTable::ajax::done::????');
                       }
                   }).fail(function() {
                       console.log('::loadTable::ajax::fail::');
                       showMessageError( "error" );
                   }).always(function() {
                       console.log('::loadTable::ajax::always::');
                       if(sender)sender.removeClass('waiting').closest('table').prop("disabled",false);
                       $('#div-'+table+' .ajax-loader').hide();
                   });
               }
               
               function loadDetailTables(table,detailtables,id){
                   if(typeof id==='undefined') return false;
                   var detail_tables = detailtables.split(',');
                   $.each(detail_tables, function( index, value ) {
                       if (value) {
                           loadTable(false, module_name+'/ajax/table='+value, value,id,'show','table','json');
                       }
                   });
               }
              
                                  
            //}
			//catch(err) {
              
            //    alert( 'ERROR: ' + err.message );
            //}




       // console.log('init');


//////////});
