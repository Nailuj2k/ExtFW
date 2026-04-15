<script type="text/javascript">

    $(function() { 

        // this file is rendered at the page end, when all javascript files called width HTML::js() hav been loaded
        // otherwise some javascript calls may fail

        // collapse / expand hidden rows in tables with nested rows
        $('body').on('click','#<?=TB_ITEM?> .link-more',function(e){
            var rId = $(this).closest('tr').attr('id').replace('row-',''); 
            console.log('rId: '+rId);
            var row =  $(this).closest('table').find('.parent-'+rId);
            console.log('row: '+row);
            if (row.hasClass('row-hidden')) row.removeClass('row-hidden').show('fast'); 
                                       else row.hide('fast').addClass('row-hidden'); 
            //row.slideToggle(1000);
            if( $(this).find('i').hasClass('fa-plus-square-o')) {
                $(this).find('i').removeClass('fa-plus-square-o').addClass('fa-minus-square-o');
            }else{
                $(this).closest('table').find('.parent-'+rId).each(function(){
                    $(this).find('i.fa-minus-square-o').removeClass('fa-minus-square-o').addClass('fa-plus-square-o');
                    $('.parent-'+$(this).attr('id').replace('row-','')).hide();
                });
                $(this).find('i.fa-minus-square-o').removeClass('fa-minus-square-o').addClass('fa-plus-square-o');
            }
        });

        // load & render form in configuration.php tab
        $('#tab-cfg-cfgfile').load('/control_panel/ajax/form_cfg','',function(){ 
            
            //FIX TABS $('#ftabs-form_cfg').tabs(); 
            const tabs = new SimpleTabs('#ftabs-form_cfg');               

        });
        
        <?php if ($message_warning){ ?>
            show_alert('#inner_control_panel','<?=$message_warning?>',20000) 
        <?php } ?>

        // SHOP stats · This is a experiment
        $('#btn-load-stats').click(function(){
            $('#stats-content').load('stats/789634nv0943v7536y45/html');
        });

        // load SHOP stats when click in tab
        setTimeout(function(){
            $('#btn-load-stats').click();
        },2000);
        
        // filter users by role
        $( "#customfilteruser" ).change(function () {
            var id_role = $(this).val();
            var url = '<?=Vars::mkUrl(MODULE,'ajax')?>'+'/op=function/function=customfilter/table=<?=TB_USER?>';
            console.log(url);
            $.post(url,{"filter":id_role ,"page":1},function(data, textStatus, jqXHR){
                if(data.error==0){
                    showMessageInfo(data.msg);
                }else{
                    showMessageError(data.msg);
                }             
                load_page('control_panel','<?=TB_USER?>',1); //,1,1,1);
            },'json');  

            var str = "";
            $( '#customfilteruser option:selected' ).each(function() {  str += $( this ).text() + " ";  });
            console.log(str); //$( '#customfilter' ).text( str );

        }); //.change();  

        // download banners csv file
        $('.banners-date-pick')
        .change(function(){ $('#btn-download-csv-banners').attr('href','<?=MODULE?>/csv/banners/'+$('#banners-date_from').val()+'/'+$('#banners-date_to').val()); })
        .change(); 

        var htaccess_modified = false;
        
        // Configurar event listener para detectar cambios en el editor de .htaccess
        // Ahora wQuery es completamente compatible con jQuery
        $(document).on('change keyup paste', '#editor_htaccess', function() {
            console.log('.htaccess content changed');
            htaccess_modified = true; 
            $('#btn-htaccess').text('Guardar .htaccess'); 
            $('#btn-htaccess-cancel').show();
        });

        $('#btn-htaccess').click(function(){  
            if(htaccess_modified==true){
                save_htaccess();
            }else{
                load_htaccess();
            }
        });

        $('#btn-htaccess-cancel').click(function(){
                load_htaccess();
        });

        function load_htaccess(){
                $.ajax({
                    method: "POST",
                    url: "edit/ajax",
                    data: { 'op': 'getfile', 'file':'htaccess' },
                    dataType: "json",
                    beforeSend: function( xhr, settings ) { }
                }).done(function( data ) {
                    console.log('EDITOR HTACCESS:',data);
                    //editor.setValue(crypt2str(data.text,'<?=$_SESSION['token']?>' ),-1);
                    //editor.clearSelection();
 
                    $('#editor_htaccess').val(crypt2str(data.text,'<?=$_SESSION['token']?>' ))

                    $('#btn-htaccess-cancel').hide();
                    //editor.getSession().on('change', function() { htaccess_modified = true; $('#btn-htaccess').text('Guardar .htaccess'); $('#btn-htaccess-cancel').show();});

                    htaccess_modified = false;
                    $('#btn-htaccess').text('Recargar .htaccess').highlight();  //BUTTON
                }).fail(function() {
                }).always(function() {
                });                        
        }
        async function save_htaccess(){
            if (htaccess_modified==true){
                //var str = str2crypt( editor.getValue(), '<?=$_SESSION['token']?>' );
                //console.log(str);

                /***************
                $.ajax({
                    method: "POST",
                    url: "edit/ajax",
                    data: { 'op': 'settext', 'id':1, 'file':'htaccess', 'text': str },
                    dataType: "json",
                    beforeSend: function( xhr, settings ) { }
                }).done(function( data ) {
                    console.log('TEXT',data);
                    if (data.error==0){
                        htaccess_modified = false;
                        $('#btn-htaccess').text('Recargar .htaccess').highlight();  //BUTTON
                        $('#btn-htaccess-cancel').hide();
                        showMessageInfo(data.msg);
                    }else{
                        showMessageError(data.msg);
                    }
                }).fail(function(data) {
                    console.log('FAIL',data);
                    showMessageError('No va fino esto.');
                }).always(function(data) {
                    console.log('ALWAYS',data);
                });

                *****************/

                //var str = str2crypt( files[active_id].value, '<?=$_SESSION['token']?>' );
                try{
                                   // str2crypt( files[active_id].value, '<?=$_SESSION['token']?>' );
                  //  const str = await str2crypt( editor.getValue(), '<?=$_SESSION['token']?>' );
                    const str = await str2crypt( $('#editor_htaccess').val(), '<?=$_SESSION['token']?>' );

                    console.log('STR.LENGTH',str.length);
                    console.log('STR.VALUE',str);
                    
                    const formData = new FormData();
                    formData.append('op'  , 'settext');
                    formData.append('id'  , 1);  //active_id);
                    formData.append('file', 'htaccess');  //files[active_id].file);
                    formData.append('text', new Blob([str], { type: 'text/plain' }));

                    //formData.append('token', '<?=$_SESSION['token']?>'); 
                    //formData.append('rootdir','<?=$root_dir?>')

                    const response = await fetch('edit/ajax', {
                        method: 'POST',
                       //headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: formData
                    });

                    if (!response.ok) {
                        const errorText = await response.text();
                        throw new Error(`Error HTTP: ${response.status} - ${errorText}`);
                    }
       
                    const data = await response.json();
                    console.log('Respuesta del servidor:', data);
                    if (data.error==0){
                        showMessageInfo(data.msg);                          
                    }else{
                        showMessageError(data.msg);
                    }
                    
                } catch (error) {

                    console.error('Error:', error);
                    showMessageError('No va fino esto: '+error);

                }


            }else{
                showMessageWarning('Documento no modificado');                   
            }                        
        }
        //load_htaccess();

        // update logos, favicons, ...
        d = new Date();
 
        // updates logic
        $('#buttons-update li input').click(function(e){
            e.stopPropagation();
        });
          
        // $('#buttons-update  .download').click(function(e){
        //     e.stopPropagation();
        //     console.log('CLICK',$(this).data('href'));
        // });
        /* 
        $('#tab-cfg-ssh').bind('click', function(e) {
            console.log('FOCUS SSH COMMAND INPUT');
            $('#comando').focus().click();
        });
        */
        $('#tab-cfg-ssh #comando').bind('keypress', function(e) {
            var code = (e.keyCode ? e.keyCode : e.which);
            var ssh_url= '<?=MODULE?>/ajax/ssh/';
            if(code == 13) { //Enter keycode
                let cmd = $('#comando').val();
                console.log('CMD',cmd);
                console.log('URL',ssh_url);

                $('.ajax-loader').show(); 

                $.ajax({
                    method: "POST",
                    url: ssh_url,
                    data: { 'cmd': cmd },  // 'cd < ?= $_SERVER['DOCUMENT_ROOT']? >;'+
                    dataType: "json",
                    beforeSend: function( xhr, settings ) {
                        console.log('BEFORESEND:',ssh_url) 
                    }

                }).done(function( data ) {
                    
                    console.log(data);

                    if(data.error==1) {

                        $('#cmd-output').append('\n#<span style="color:red">FAIL'+data.command+'\n'+data.msg+'</span>'); //.replaceAll('\n','<br />'));

                        showMessageError(data.msg);

                   }else { 

                       if(data.command)
                           $('#cmd-output').append('\n#'+data.command+'\n'+data.msg); //.replaceAll('\n','<br />'));
                       //$('#result').show('fast').append('\n'+data.msg); //.replaceAll('\n','<br />'));
                        
                       setTimeout(function() { $('#cmd-output').scrollTop($('#cmd-output')[0].scrollHeight); }, 100);

                   }

                }).fail(function(data) {

                   console.log('ERROR',data);

                }).always(function() {

                   $('.ajax-loader').hide(); 

                });

            }

        }).focus();


        $('#buttons-update li>a').click(function(e){

           // e.preventDefault();
           // e.stopPropagation();

          //$('#link-install').hide('fast');
            if ($(this).hasClass('disabled')){
                //showMessageInfo('Nop!');
            }else{
                
                let _repo = false;
                let _module = false;
                let element = $(this).closest('li');
                var op=element.data('op');
                var update_url= '<?=MODULE?>/ajax/update/'+op;
                if (element.hasClass('zip-module')||element.hasClass('zip-theme')){

                    _repo   = element.find('.input_zip_repo').val()   ?? false;
                    _module = element.find('.input_zip_module').val() ?? false;
                    _theme  = element.find('.input_zip_theme').val()  ?? false;
                    update_url += '/'+(_module?_module:_theme);

                    console.log('OP',op,'MODULE',_module,'URL',update_url) 

                    if(
                        
                        ((op=='zip/zip_module'  && !_module)/*   ||   (op=='zip_module'  && (!_repo||!_module))*/)
                        ||
                        ((op=='zip/zip_theme'  && !_theme)  /* ||   (op=='zip_theme'  && (!_repo||!_theme))*/)

                      ) {
                        console.log('NO_MODULE_OR_REPO');
                        return false;
                    }
                }

                element.addClass('disabled');
                var n=0,n2=0,m=0,r='',r2='',d='',p=element.find('.progress_download'),pi=element.find('.progress_download_info');
                pi.html('');
                //interval_download=setInterval(function(){n++; m++;if(m>1)m=0;if(m==1)d=d+'· ';switch(n){case 0:r='—';break;case 1:r='\\';break;case 2:r='|';break;case 3:r='/';n=-1;break;}p.html(d+r);},100);
                var interval_download = setInterval(function(){
                    n++; 
                    n2++; 
                    m++;
                    if(m>1)m=0;
                    if(m==1)d=d+'· ';
                    switch(n2){
                        case 0:r2='—';break;
                        case 1:r2='\\';break;
                        case 2:r2='|';break;
                        case 3:r2='/';
                        n2=-1;//break;
                    }
                    switch(n){
                        case 0:r='⠇';break;
                        case 1:r='⠏';break;
                        case 2:r='⠋';break;
                        case 3:r='⠙';break;
                        case 4:r='⠹';break;
                        case 5:r='⠸';break;
                        case 6:r='⠼';break;
                        case 7:r='⠴';break;
                        case 8:r='⠦';break;
                        case 9:r='⠧';
                        n=-1;//break;
                    }
                    p.html(r+' '+d+' '+r2);
                },100);

                $.ajax({
                   method: "POST",
                   url: update_url,
                   data: { 'host': _repo },
                   dataType: "json",
                   beforeSend: function( xhr, settings ) {
                    //console.log('UPDATE_URL',update_url,op)
                    //UPDATE_URL control_panel/ajax/update/zip/instal zip/instal                    
                    
                    }
                }).done(function( data ) {
                   console.log('op',op);        // op zip/instal
                   console.log(data);
                   console.log('DATA.MSG',data.msg);
                   if(data.error==1) {

                       pi.html(' <span style="color:red">FAIL</span>');
                       showMessageError(data.msg);

                   }else {

                       pi.html('<?=$check_mark?>');
                       let urlv = data.url_version ? data.url_version : data.url;
                       if (data.url) pi.append(' <a class="download" title="Descargar archivo" href="'+data.url+'" download="'+urlv+'"><span style="font-size:1.4em;">📥</span></a>');
                       if (data.redirect) pi.append(' <a class="btn btn-small btn-danger" style="vertical-align:super;padding: 0px 10px;border-radius:3px;cursor:pointer;font-weight:normal;color:white;" title="Ir a '+data.redirect+'" href="'+data.redirect+'"> Install </a>');
                       showMessageInfo( data.msg );

                   }
                     //  $('#url-backup').attr('href',data.url).show('fast');
                   if (data.html) $('#result').show('fast').html(data.html);
                 //$('#link-install').show('fast');
                }).fail(function(data) {
                   console.log('ERROR',data);

                }).always(function() {
                   clearInterval(interval_download);
                   p.html('· · '+d+' '); 
                   $('.ajax-loader').hide(); //.removeClass('waiting');
                   element.removeClass('disabled');

                });
            }
        });

        // create and save sitemap file, and optionally send it to googles and other bigbrothers evils
        $('body').on('click','#write-sitemap-file',function(e){
            $.ajax({
                   method: "POST",
                   url: "<?=Vars::mkUrl(MODULE,'ajax/write-sitemap-file')?>",
                   data:{}, 
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

        //$('#<?=TB_ITEM?> tbody tr td').draggable();
        //$("#tabs").draggable();
        //$("#<?=TB_ITEM?>").tableDnD();

        $('#write-cc-file').click(function(e){
            $.ajax({
                   method: "POST",
                   url: "<?=Vars::mkUrl(MODULE,'ajax/write-cc-file')?>",
                   data:{ 'lang_cc': '<?=$_SESSION['lang']?>'}, 
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

        $('#set-mode-auto').click(function(e){
            $.ajax({
                   method: "POST",
                   url: "<?=Vars::mkUrl(MODULE,'ajax/set-mode-auto')?>",
                   dataType: "json",
                   beforeSend: function( xhr, settings ) {
                       $('.ajax-loader').show(); //.addClass('waiting');
                   }
             }).done(function( data ) {
                    notify( data.msg, 'info' , 4000);
                    $('#set-mode-auto b').html(data.mode);
            }).fail(function() {
                    notify( 'error' , 'error', 4000);
            }).always(function() {
                    $('.ajax-loader').hide(); //.removeClass('waiting');
            });
        });

        $('#drop_file_zone_favicon     .drag_upload_file').css('background','url(/media/images/favicon.png'    +'?'+d.getTime()+')');
        $('#drop_file_zone_logo        .drag_upload_file').css('background','url(/media/images/logo.png'       +'?'+d.getTime()+')');
        $('#drop_file_zone_logo_email  .drag_upload_file').css('background','url(/media/images/logo_email.png' +'?'+d.getTime()+')');
        $('#drop_file_zone_logo_footer .drag_upload_file').css('background','url(/media/images/logo_footer.png'+'?'+d.getTime()+')');
        $('#drop_file_zone_favicon     .drag_upload_file,#drop_file_zone_logo .drag_upload_file,#drop_file_zone_logo_email .drag_upload_file,#drop_file_zone_logo_footer .drag_upload_file').css('background-size','contain').css('background-repeat','no-repeat');

        document.addEventListener("drop", function(event) {
            if ( event.target.className == "drag_upload_file" ) {
                event.preventDefault();
                let fileobj = event.dataTransfer.files[0];
                let parentId = event.target.parentNode.id
                if(parentId == 'drop_file_zone_favicon')     ajax_file_upload(fileobj,'favicon');
                if(parentId == 'drop_file_zone_logo')        ajax_file_upload(fileobj,'logo');
                if(parentId == 'drop_file_zone_logo_email')  ajax_file_upload(fileobj,'logo_email');
                if(parentId == 'drop_file_zone_logo_footer') ajax_file_upload(fileobj,'logo_footer');
            }
        });

        // By default, data/elements cannot be dropped in other elements. To allow a drop, we must prevent the default handling of the element
        document.addEventListener("dragover", function(event) {
            if ( event.target.className == "drag_upload_file" ) 
            event.preventDefault();
        });

        // https://artisansweb.net/drag-drop-file-upload-using-javascript-php/
        function ajax_file_upload(file_obj,filetype) {
            var element = '#drop_file_zone_'+filetype+' .drag_upload_file';
            console.log(element);
            if(file_obj != undefined) {
                var form_data = new FormData();                  
                form_data.append('file', file_obj);
             // form_data.append('type', file_obj.data('type');
                console.log('file_obj',file_obj);
                
                // Ahora wQuery.ajax maneja FormData correctamente
                $.ajax({
                    method: "POST",
                    url: "<?=Vars::mkUrl(MODULE,'ajax/save_file')?>/type="+filetype,
                    data: form_data,
                    // Estas opciones son importantes para FormData (como en jQuery)
                    processData: false,  // No procesar los datos
                    contentType: false,  // No establecer Content-Type (fetch lo hace automáticamente)
                    dataType: "json",
                    beforeSend: function(xhr, settings) { 
                        $('.ajax-loader').show(); 
                    }
                }).done(function(response) {
                    console.log('RESPONSE', response);
                    showMessageInfo(response.msg);
                    
                    d = new Date();
                    $(element)
                        .css('background','url('+response.thumb+'?'+d.getTime()+')')
                        .css('background-size','contain')
                        .css('background-repeat','no-repeat');

                    if(filetype=='logo') $('.logo img').attr('src',response.url+'?'+d.getTime());
                    if(filetype=='logo_footer') $('.logo_footer').attr('src',response.url+'?'+d.getTime());
                    
                }).fail(function(jqXHR, textStatus, errorThrown) {
                    console.error('ERROR', jqXHR);
                    showMessageError(jqXHR.responseText || 'Error en la subida del archivo');
                }).always(function() {
                    $('.ajax-loader').hide();
                });
            }
        }

        $('#create_favicons').click(function(){
            $.ajax({
                type: 'POST',
                url: '<?=MODULE?>/ajax/create_favicons',
                dataType: "json",
                success:function(response) {
                    showMessageInfo(response.msg);
                }
            });
        });

      
        $('body').on('change','#FIELD_TYPE',function(e){
            console.log('change',$(this).val());
            let dat=$(this).val()=='date';                      
            if (dat)  $("#FIELD_LEN").val("").closest(".control-group").hide("fast");
            let yes=$(this).val()=='bool';                       
            if (yes) $("#FIELD_LEN").val(1).closest(".control-group").hide("fast");
                else $("#FIELD_LEN").closest(".control-group").show("fast");
            let tex=$(this).val()=='textarea';                       
                if (tex) $("#FIELD_LEN").val("").closest(".control-group").hide("fast");
                else $("#FIELD_LEN").closest(".control-group").show("fast");
                if (tex) $("#WYSIWYG").closest(".control-group").show("fast");
                else $("#WYSIWYG").val("0").closest(".control-group").hide("fast");
            let sel=$(this).val()=='select';                       
                if (sel) $("#FIELD_LEN").val("").closest(".control-group").hide("fast");
                if (sel) $("#WYSIWYG").val("0").closest(".control-group").hide("fast");
                if (sel) $("#LOOKUP_FIELD_KEY").closest(".control-group").show("fast");
                    else $("#LOOKUP_FIELD_KEY").val("").closest(".control-group").hide("fast");
                if (sel) $("#LOOKUP_FIELD_NAME").closest(".control-group").show("fast");
                    else $("#LOOKUP_FIELD_NAME").val("").closest(".control-group").hide("fast");
                if (sel) $("#LOOKUP_FIELD_TABLE").closest(".control-group").show("fast");
                    else $("#LOOKUP_FIELD_TABLE").val("").closest(".control-group").hide("fast");
            let file=$(this).val()=='file';                       
                if (file) $("#UPLOADDIR").closest(".control-group").show("fast");
                    else $("#UPLOADDIR").closest(".control-group").hide("fast");
                if (file) $("#EXTENSIONS").closest(".control-group").show("fast");
                    else $("#EXTENSIONS").closest(".control-group").hide("fast");
                if (file) $("#MASK").closest(".control-group").show("fast");
                    else $("#MASK").closest(".control-group").hide("fast");
        });
        $("#FIELD_TYPE").change();

        $('body').on('change','#FRAME_TYPE',function(e){

            let type=$(this).val();                       
            if(type=='0'){
               $('#FILE_NAME').closest(".control-group").show("fast");
               $('#SOURCE').closest(".control-group").hide("fast");
            }else if(type=='2'){
               $('#FILE_NAME').closest(".control-group").hide("fast");
               $('#SOURCE').closest(".control-group").show("fast").find('label').html('YouTube ID');

            }else if(type=='1'){
               $('#FILE_NAME').closest(".control-group").hide("fast");
               $('#SOURCE').closest(".control-group").show("fast").find('label').html('Ruta vídeo mp4');
            }
      
        });

        $("#FRAME_TYPE").change();


        /********** *
        document.body.addEventListener('htmx:trigger', function (evt) {
            evt.preventDefault();
        });

        console.log('jau');

        $('#btn_recursively').click(function(evt){
            evt.preventDefault();
        });
        ************/

        //htmx.process(document.querySelector('#btn_delete_recursively'))


        $('#log-options .btn-log').click(function(){  //load-log-files
            let btn = $(this);
            let option = $(this).data('op');
            $.ajax({
                   method: 'POST',
                   url: '<?=MODULE?>/ajax/logfiles',
                   data: {'op':option}, 
                   dataType: "json",
                   beforeSend: function( xhr, settings ) {
                       $('.ajax-loader').show(); 
                       btn.addClass('disabled');
                   }
            }).done(function( data ) {
               if(option=='load'){
                   $('#logs-files').empty().html(data.logfiles);
               }else if(option=='load-cache'){
                   $('#logs-files').empty().html(data.cachefiles);
               }else if(option=='delete-log'){
                   $('#logs-files').empty();
                   $('#load-log-files').click();
               }else if(option=='delete-cache'){
               }
            }).fail(function() {
                showMessageError( "error" );
            }).always(function() {
                $('.ajax-loader').hide();
                btn.removeClass('disabled');
            });
           
        });


        /*
        $('#logs-files a').click(function(){
            var filename = $(this).data('logfile'); 
            $.ajax({
                   method: 'POST',
                   url: '<?=MODULE?>/ajax/logfile',
                   data: {'filename':filename}, 
                   dataType: "json",
                   beforeSend: function( xhr, settings ) {
                       $('.ajax-loader').show(); 
                       btn.addClass('disabled');
                   }
            }).done(function( data ) {

               $('#result').empty().append('<ul></ul>');

               let delay = 100;  
               let num_docs = 0;
               if(num_docs>0)
                   $('#result').append('<p>'+num_docs+' documentos.</p>');
               else
                   $('#result').append('<div class="alert" style="background-color:#c10957;color:white;PADDING:10PX;">No hay documentos para esta fecha :(</div>');

            }).fail(function() {
                showMessageError( "error" );
            }).always(function() {
                $('.ajax-loader').hide();
                btn.removeClass('disabled');
            });
           
        });
        **/




    });








<?php if(CFG::$vars['log']){ ?> 

        const logLines = document.getElementById('log-lines');
        var log_polling = false; // Variable para controlar el estado de
        var log_interval = 2000;
        var logIntervalId = null;
        var log_views_ajax_lines = 'yes'; // 'no'|'yes'
        //start-stop-log-live

        document.getElementById('start-stop-log-live').addEventListener('click', function() {
            log_polling = !log_polling;
            if (log_polling) {
                this.innerHTML = 'Detener &nbsp; <i class="fa fa-pause"></i>';
                updateLog(); // Llamada inmediata
                logIntervalId = setInterval(updateLog, log_interval);
                this.classList.add('btn-danger');
                this.classList.remove('btn-primary');
            } else {
                this.innerHTML = 'Reanudar &nbsp; <i class="fa fa-play"></i>';
                this.classList.remove('btn-danger');
                this.classList.add('btn-primary');
                clearInterval(logIntervalId);
            }
        });

        let lastPosition = 0;  // Posición desde la que leímos la última vez

        function updateLog() {
            console.log('UPDATE_LOG');
            fetch('<?=MODULE?>/ajax/log/ajax_lines=' + log_views_ajax_lines + '/pos=' + lastPosition)
                .then(response => response.text())
                .then(text => {
                    //console.log('RESPONSE TEXT:', text);
                    let data;
                    try {
                        data = JSON.parse(text);
                    } catch (e) {
                        console.error('Error al parsear JSON:', e, text);
                        $('#start-stop-log-live').click(); // Detener polling
                        clearInterval(logIntervalId);
                        log_polling = false;
                        return;
                    }
                    if (data.new_content) {
                        logLines.append( data.new_content );
                        lastPosition = data.new_position;
                        // Scroll suave al final
                        logLines.scrollTo({
                            top: logLines.scrollHeight,
                            behavior: 'smooth'
                        });
                    }
                })
                .catch(err => console.error('Error:', err));
        }

        function onActivateTab(event,id){
            if (id=='tab-cfg-log' || id=='tab-cfg-log-live'){
                //console.log('ACTIVATE LOGS TAB');
                if (log_polling){
                    updateLog(); // Llamada inmediata
                    logIntervalId = setInterval(updateLog, log_interval);
                }
            }else{
                //console.log('DEACTIVATE LOGS TAB');
                clearInterval(logIntervalId);
                log_polling = false;
            }
        }

        // Carga inicial (últimas 200 líneas aprox.)
        //updateLog();

        // Actualiza cada 2 segundos (ajusta según necesidad)
        //setInterval(updateLog, log_interval);
    

<?php } ?> 




</script>