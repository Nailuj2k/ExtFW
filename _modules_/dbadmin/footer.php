<script type="text/javascript">
    let btn = $('#submit');
   // let selected_table = '';
    let tables = {}
    let rows_per_page = 10;
    let all=true;

    function sqlExec(sql){   // https://api.jquery.com/jquery.ajax/

            $.dialog.removeAll();

            $('#sql').val(sql)
            
            sql = str2crypt( sql, '<?=$_SESSION['one_time_token'] ?>' );

            $.ajax({
                   method: 'POST',
                   url: 'dbadmin/ajax/tables',
                   data:{'sql':sql}, 
                   dataType: "json",
                   beforeSend: (xhr, settings) => { 
                       $('.ajax-loader').show();  
                       $('#submit').addClass('disabled');    
                   }  
            })
            .done(
                data => {
                    if(data.type == 'select'||data.type == 'tables'){  
                       console.log('DATA',data);
                        if (data.error){
                            show_error('#select',data.error,15000); //$('#select'),`La instrucción ${data.sql} no ha devuelto filas o es incorrecta`,5000);        
                        }else if (data.rows==null || data.rows==false){
                            show_alert('#select',`La instrucción ${data.sql} no ha devuelto filas o es incorrecta`,5000);        
                            if( tables[selected_table] !== undefined) tables[selected_table].page = 0;              
                        }else{
                            $('#'+data.type+' tbody').empty();       
                            let keys = [];
                            data.rows.map(
                                row =>   {  
                                    if (keys.length==0)  $('#'+data.type+' thead').html( `<tr><th>${Object.keys(row).join('</th><th>')}</th></tr>`  )
                                    //if(data.type == 'select')
                                    //$('#'+data.type+' tbody').append( `<tr><td><pre>${Object.values(row).join('</pre></td><td>') }</pre></td></tr>`  )
                                    //else
                                    $('#'+data.type+' tbody').append( `<tr><td>${Object.values(row).join('</td><td>') }</td></tr>`  )


                                }
                            )
                            $('#output').html( `SQL: <b>${data.sql}</b>` );
                            // hljs.highlightAll();
                            <?php if(CFG::$vars['options']['highlight_code']===true ) { ?>/*********hljs.highlightAll();***/Prism.highlightAll();<?php } ?>
                            if(data.type == 'tables') {
                                all = true;
                                $('#tables thead tr th:first-child').append('<i class="btn fa fa-refresh" title="Actualizar"></i><i class="btn fa fa-filter" title="Mostrar/ocultar tablas vacías"></i>')
                                // $("#tables td:contains('0')").css('color','red');
                                document.querySelectorAll('#tables td').forEach(td => {
                                    if (td.textContent.trim() === '0') {
                                        const fila = td.closest('tr');
                                        if (fila) { fila.querySelectorAll('td').forEach(otherTd => { otherTd.style.color = 'var(--red)'; }); }
                                    } 
                                });
                                _tooltip('#tables .fa', {    theme: 'yellow', content: (o) => o.getAttribute('title') });

                            }

                        }
                    }else if(data.type == 'exec'){
                        if (data.error){
                            show_error('#select',`Error ejecutando: ${data.sql}<br>${data.error}`,10000);
                        }else{
                            show_info('#select',`SQL ejecutado: ${data.sql}<br>Filas afectadas: ${data.affected}`,5000);
                        }                                             
                    }else{    
                        show_error('#select',`${data.statusText} ${data.status}: ${data.resmsgonseText}`,5000);                      
                    }
                }
             )
            .fail( data => {
                $('.ajax-loader').hide(); 
                $('#submit').removeClass('disabled');      
                show_error('#select',`${data.statusText} ${data.status}: ${data.responseText}`,5000);
             })
            .always( () => {  
                $('.ajax-loader').hide(); 
                $('#submit').removeClass('disabled');        
             })
           // .then()
    }


    function sqlDump(tablename){  

        tablename = str2crypt( tablename, '<?=$_SESSION['one_time_token'] ?>' );

        $.ajax({
            method: 'POST',
            url: 'dbadmin/ajax/dump',
            data:{'sql':'DUMP '+tablename}, 
            dataType: "json",
            beforeSend: (xhr, settings) => { 
                $('.ajax-loader').show();  
                $('#submit').addClass('disabled');    
            }  
        })
        .done( data => {

            show_info( '#select',`SQL DUMP ejecutado ${tablename}`,5000 );                                             

        })
        .fail( data => {
            $('.ajax-loader').hide(); 
            $('#submit').removeClass('disabled');      
            show_error('#select',`${data.statusText} ${data.status}: ${data.responseText}`,5000);
        })
        .always( () => {  
            $('.ajax-loader').hide(); 
            $('#submit').removeClass('disabled');        
        })
        //.then()
        
    }
    
    $('#sql-editor-toolbar .btn').click(function(e){

        
        let cmd = $(this).data('sql');
        
        <?php if ($cfg['db']['type']  == 'sqlite'){  ?>
            let showcreate_sql = `SELECT sql FROM sqlite_master WHERE type='table' AND name='${selected_table}';`;
        <?php }else{?>
            let showcreate_sql = `SHOW CREATE TABLE ${selected_table};`;
        <?php } ?>


        if(!selected_table && cmd && cmd!=='exec'){
            // $('#sql').val('Seleccione una tabla primero.');
            $.dialog.removeAll();
            show_error('#sql-editor-query-editor','Seleccione una tabla primero.',5000);
            return;
        }

        if      (cmd=='exec')      sqlExec($('#sql').val()); 
        else if (cmd=='dump')      sqlDump(selected_table)
        else if (cmd=='insert')    $('#sql').val(`INSERT INTO ${selected_table} (<fields>) \nVALUES (<values>)`)
        else if (cmd=='update')    $('#sql').val(`UPDATE ${selected_table} \nSET <field>='<value>'`)
        else if (cmd=='add_col')   $('#sql').val(`ALTER TABLE ${selected_table} \nADD COLUMN <column> <text|int(num)|varchar(num)> [NOT NULL] \n[AFTER <column>]`)
        else if (cmd=='alter_col') $('#sql').val(`ALTER TABLE ${selected_table} \nCHANGE <column> <column> <text|int(num)|varchar(num)> [NOT NULL] \n[AFTER <column>]`)
        else if (cmd=='drop_col')  $('#sql').val(`ALTER TABLE ${selected_table} \nDROP COLUMN <column>`)
        else if (cmd=='autoinc')   $('#sql').val(`ALTER TABLE ${selected_table} MODIFY COLUMN <column> INT(5) UNSIGNED PRIMARY KEY AUTO_INCREMENT`);
        else if (cmd=='showcreate')  $('#sql').val( showcreate_sql );
        else if (cmd=='prev'||cmd=='next'||cmd=='select')  {
             if (cmd=='select')  tables[selected_table].page = 1  
             if (cmd=='prev')    tables[selected_table].page--  
             if (cmd=='next')    tables[selected_table].page++  
             $('#page-num').html(tables[selected_table].page)
             sqlExec(`SELECT * FROM ${selected_table} LIMIT ${(tables[selected_table].page*rows_per_page)-rows_per_page},${rows_per_page}`)  
        }

    });
    

    $(function() {  
        sqlExec('SHOW TABLES');  
        _tooltip('.btn');
    });

    $('#tables').on('click', 'tr:first-child .fa-refresh',function() {     
        sqlExec('SHOW TABLES');  
    });

    $('#tables').on('click', 'tr:first-child .fa-filter',function() {        

        all = !all;
       if(all) {
           $('#tables tr').show();
       }else{
           $("#tables td:last-child").each(     //wquery not detect :last-child
               function(e,v){ 
                
                    if($(v).html()<1)
                         $(this).closest('tr').hide(); 

               }
           );
       }
       //$("#tables td:last-child:contains('0')").css('color', 'red');

    });

    $('#tables').on('click', 'td:first-child',function() {     
        $('#tables .selected').removeClass('selected');
        $(this).addClass('selected');
        selected_table = $(this).text()
        if( tables[selected_table] === undefined) tables[selected_table] = {name:selected_table,page:0}      
        sqlExec( `DESCRIBE ${selected_table}` ); 
    });
    /*
    $('.inner-page').on('click', '#full-view',function() {     
        let o = $('._box .title-bar i');
        $('._box').toggleClass('full');
        o.toggleClass('fa-window-maximize');
        o.toggleClass('fa-window-restore');
	    o.attr('title', o.hasClass('fa-window-restore') ? 'Restaurar' : 'Maximizar');
    });
    */

    $('#btn-full-view').click(function() {     
       // let o = $('._box .title-bar i');
        $('#sql-editor').toggleClass('full').toggleClass('inner');
        $(this).html( $('#sql-editor').hasClass('full') ? '<i class="fa fa-window-restore"></i>' : '<i class="fa fa-window-maximize"></i>' );
        $(this).attr('title', $('#sql-editor').hasClass('full') ? 'Restaurar' : 'Maximizar');
    });


    $(function() {    


        //new Splitter({ orientation: 'vertical'  , elementLeftOrTop: 'col-left' , elementRightOrBottom: 'col-right'    , size: 5, minSize: 150 });
        new Splitter({ orientation: 'horizontal', elementLeftOrTop: 'row-top', elementRightOrBottom: 'row-bottom', size: 5, minSize: 150 });
        //new Splitter({ orientation: 'vertical'  , elementLeftOrTop: 'panel3' , elementRightOrBottom: 'panel4'    , size: 5, minSize: 50 });                                    

    });












</script>
<!--
<script type="text/javascript" src="<?=SCRIPT_DIR_JS?>/jquery.splitter/jquery.splitter.js"></script>
-->

<!--<script type="text/javascript" src="<?=SCRIPT_DIR_JS?>/detect-element-resize.js"></script>-->
<script>
    /**
(function ($) {
    const SPLITTER_WIDTH = 16;
    $.fn.SplitterLR = function(  options ) {
        $(this).each(function () {
            var element = $(this),
                side_l,side_c,side_r,
                overRight = false,
                overRight_c = false,
                dragged = false,
                dragged_c = false;
                
            side_l = element.children('.l').first(); 
            side_c = element.children('.c').first(); 
            side_r = element.children('.r').first(); 
            
            //side_l.width( element.outerWidth() / 2);
            //side_r.width( element.outerWidth() / 2);
            if(side_c.length==1){
                side_l.css('right', ((element.outerWidth() / 3)*2)+'px' );
                side_c.css('right' , (element.outerWidth() / 3)+'px' );
                side_c.css('left' , (element.outerWidth() / 3)+'px' );
                side_r.css('left' , ((element.outerWidth() / 3)*2)+'px' );
            }else{
                side_l.css('right', (element.outerWidth() / 2)+'px' );
                side_r.css('left' , (element.outerWidth() / 2)+'px' );
            }
            
            element.on( "mousedown", function( e ) {
               //newWidth = parentOffset.relX - side_l.outerWidth();
               if (overRight)   { dragged   = true; }
               if (overRight_c) { dragged_c = true; }
            });
            
            element.on( "mouseup", function( e ) {
                dragged   = false;
                dragged_c = false;
            });
            
            $(document).mouseup( function (e) {
                dragged = false;
                dragged_c = false;
            });
            
            element.on( "mousemove", function( e ) {
                let relX = e.pageX - side_l.offset().left;
                let relY = e.pageY - side_l.offset().top;
                element.find('textarea').val('relx '+relX+' >= '+element.outerWidth());

                if(side_c.length==1)
                var relX_c = e.pageX - side_c.offset().left;


                //if( relX > (element.outerWidth()-4) ) { relX = element.outerWidth()-4 }
                //if( relX < 4                        ) { relX = 4                      } 
                
                //console.log('relX',relX,element.outerWidth())

                if ( ( dragged && relX>0 && relX >= (element.outerWidth()-0)  ) || relX<SPLITTER_WIDTH)  {
                    element.find('textarea').val('OUT');
                    side_l.css("cursor", "default");
                    side_l.removeClass('hover')
                    dragged = false;
                }else if (relX >= side_l.outerWidth() - SPLITTER_WIDTH && relX <= side_l.outerWidth()) {
                    side_l.css("cursor", "col-resize");
                    side_l.addClass('hover')
                    overRight = true;
                } else if (side_c.length==1 && relX_c >= side_c.outerWidth() - SPLITTER_WIDTH && relX_c <= side_c.outerWidth()) {
                    side_c.css("cursor", "col-resize");
                    side_c.addClass('hover')
                    overRight_c = true;
                 } else {
                    side_l.css("cursor", "default");
                    side_l.removeClass('hover')
                    side_c.css("cursor", "default");
                    side_c.removeClass('hover')
                    overRight = false;
                    overRight_c = false;
                    //dragged   = false;
                    //dragged_c = false;
                }
                if (dragged) {
                     if(side_c.length==1){
                        side_l.css('right',(element.outerWidth()-relX)+'px'); 
                        side_c.css('left',relX+'px');
                    }else{
                        side_l.css('right',(element.outerWidth()-relX)+'px'); 
                        side_r.css('left',relX+'px'); 
                    }
                    //onResizeTB(element.find('.splitter.tb'));
                }else if (dragged_c) {
                    side_c.css('right',(element.outerWidth()  - side_l.outerWidth() -relX_c)+'px'); 
                    side_r.css('left', (relX_c+side_l.outerWidth())+'px');  
                    //onResizeTB(element.find('.splitter.tb'));
                }
            });
            return $(this);
        });
    }

    $.fn.SplitterTB = function(  options ) {
        $(this).each(function () {       
            var element = $(this),
                overBottom = false,
                dragged = false;
              //dragged_c = false;

            let side_t = element.children('.t').first(); 
            //let side_c = element.children('.c').first(); 
            let side_b = element.children('.b').first();             
            
            //side_t.height( element.outerHeight() / 2);
            //side_b.height( element.outerHeight() / 2);
            
            side_t.css('bottom', ( element.outerHeight() / 2)   +'px' );
            side_b.css('top' , ( element.outerHeight() / 2)   +'px' );

            element.on( "mousedown", function( e ) {
                if (overBottom) { dragged = true; }
            });
            element.on( "mouseup", function( e ) {
                dragged = false
            });
            
            $(document).mouseup( function (e) {
                dragged = false;
            });
        
            / ****************************
            element.on( "mouseout", function( e ) {
                dragged = false
                //let relY = e.pageY - side_t.offset().top;
                //let relX = e.pageX - side_t.offset().left;
                //element.find('textarea').val('mouseout '+relX+' - '+relY);
            });
            ****************** /
            element.on( "mousemove", function( e ) {
                let relY = e.pageY - side_t.offset().top;
                let relX = e.pageX - side_t.offset().left;
                
                //element.find('textarea').val('mousemove '+relX+' - '+relY);
                element.find('textarea').val('rely '+relY+' >= '+element.outerHeight());
                
                if ( ( dragged && relY>SPLITTER_WIDTH && relY >= element.outerHeight()  ) || relY<SPLITTER_WIDTH)  {
                    element.find('textarea').val('OUT');
                    side_t.css("cursor", "default");
                    side_t.removeClass('hover')
                    dragged = false;
                }else if (relY >= side_t.outerHeight() - SPLITTER_WIDTH && relY <= side_t.outerHeight()) {
                    side_t.css("cursor", "row-resize");
                    side_t.addClass('hover')
                    overBottom = true;
                } else {
                    side_t.css("cursor", "default");
                    side_t.removeClass('hover')
                    overBottom = false;
                }
                if (dragged) {
                    side_t.css('bottom',(element.outerHeight()-relY)+'px'); 
                    side_b.css('top',relY+'px'); 
                    //onResizeTB(element.find('.splitter.tb'));
                }
            });
            return $(this);
        });
    }

    function onResizeLR(element){
        element.each(function(e){
            let element = $(this);
            let side_l = element.children('.l').first(); 
            let side_c = element.children('.c').first(); 
            let side_r = element.children('.r').first(); 
            if(side_c.length==1){
                side_l.css('right', ((element.outerWidth() / 3)*2)+'px' );
                side_c.css('right' ,( element.outerWidth() / 3)   +'px' );
                side_c.css('left' , ( element.outerWidth() / 3)   +'px' );
                side_r.css('left' , ((element.outerWidth() / 3)*2)+'px' );
            }else{
                side_l.css('right', ( element.outerWidth() / 2)   +'px' );
                side_r.css('left' , ( element.outerWidth() / 2)   +'px' );
            }            
        });        
    }

    function onResizeTB(element){
        element.each(function(e){
            let element = $(this);
            //let side_t = element.children('div').first(); 
            //let side_b = element.children('div').first().next(); 
            //side_b.height( element.outerHeight() - side_t.outerHeight() - 0 );
            let side_t = element.children('.t').first(); 
            //let side_c = element.children('.c').first(); 
            let side_b = element.children('.b').first(); 
            //if(side_c.length==1){
            //    side_t.css('bottom', ((element.outerHeight() / 3)*2)+'px' );
            //    side_c.css('bottom' ,( element.outerHeight() / 3)   +'px' );
            //    side_c.css('top' , ( element.outerHeight() / 3)   +'px' );
            //    side_b.css('top' , ((element.outerHeight() / 3)*2)+'px' );
            //}else{
                side_t.css('bottom', ( element.outerHeight() / 2)   +'px' );
                side_b.css('top' , ( element.outerHeight() / 2)   +'px' );
            //}            
        });        
    }

    window.onresize = function() {
       onResizeLR($('.splitter.lr'));
    }

}(jQuery));


$(document).ready(function (e) {
	$(".splitter.lr").SplitterLR();
	$(".splitter.tb").SplitterTB();
});
*/
</script>
