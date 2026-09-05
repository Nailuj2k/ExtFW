
<script type="text/javascript">
      var n=0;
      var seconds = 2000;
      var mousehasmoved=false;
      var suspended = true;

      /******************
      $(document).ready(function(){
          $("html").on("dragover", function (e) {
            e.preventDefault();
            e.stopPropagation();
          });
       
          $("html").on("drop", function (e) {
            e.preventDefault();
            e.stopPropagation();
          });
       
          $('#drop_file_area').on('dragover', function () {
            $(this).addClass('drag_over');
            return false;
          });
       
          $('#drop_file_area').on('dragleave', function () {
            $(this).removeClass('drag_over');
            return false;
          });
       
          $('#drop_file_area').on('drop', function (e) {
            e.preventDefault();
            $(this).removeClass('drag_over');
            var formData = new FormData();
            var files = e.originalEvent.dataTransfer.files;
            for (var i = 0; i < files.length; i++) {
              formData.append('FILE_NAME[]', files[i]);
            }
            uploadFormData(formData);
          });
       
          function uploadFormData(form_data) {
            $.ajax({
              url: '< ?=MODULE? >/ajax/op=upload',
              method: "POST",
              data: form_data,
              contentType: false,
              cache: false,
              processData: false,
              success: function (data) {
                $('#uploaded_file').append(data);
              }
            });
          }
      });
      *******************/

      var helps = new Object();
      helps['dokux_inbox'] = '<h4>Bandeja de entrada</h4><p>Arrastre documentos a esta ventana desde el explorador de archivos. Para documentos escaneados, en formato pdf o imagen, pulse en <span class="file_ocr_help" style="color:silver;">Aa</span> para pasarle el OCR e intentar extraerle el texto. Una vez se haya obtenido el texto puede pulsar ➤ para moverlo a Documentos procesados.</p><p>Si se trata de PDF\'s "normales" no será necsario pasarles el OCR.</p><p>Puede eliminarlos pulsando en <span style="color:#dc0106">✖</span></p><p>El sistema detectará archivos duplicados aunque tengan un nombre distinto y no permitirá su traslado a Documents procesados.</p>';
      helps['dokux_files']='<h4>Documentos procesados</h4><p>Los datos de esta tabla pueden ser modificados por el operador mientras no se hayan marcado como validados. Puede filtrar, pulsando en <i class="fa fa-filter"></i>, por fecha, tipo de documento, usuario o validación. Puede buscar por cualquier palabra o palabras, en el tetxo estraudo del OCR, usando el cuadro de búsqueda que hay en la parte inferior izquierda de la tabla. Si hay un filtro activo se buscará en las filas filtradas.</p>';
      helps['dokux_pdf_viewer']='<h4>Vista previa</h4><p>En esta ventana se visualizarán, a modo de vista previa, los documentos, tanto de la entrada como de la tabla de procesados.</p>';
      helps['dokux_cfg-filetypes']='<h4>Tipos de documento</h4><p>Cada Área o servicio tiene sus propios tipos, la columna RegExp contiene una "expresión regular" que se usará para determinar automáticamente el tipo de documento. La columna prioridad determina el orden en que se evaluarán dichas expresiones, de manera que será conveniente poner las mas complejas antes y las mas simples las últimas, para aumentar las probabilidades de acertar el tipo de documento</p>';
      helps['dokux_cfg-users']='<h4>Usuarios</h4><p>En este mundo hay dos tipos de personas, los que tienen una pistola en la mano y los que cavan. Y tu eres de los que cavan.</p><p style="text-align:right;"><i>Clint Eastwood</i></p>';
      helps['doku-bar']='<h4><?=APP_NAME?></h4><p>Así funciona esto: Se escanean todos los documentos que sirvan para algo, con un escaner o incluso con un móvil, y se guardan en forma de pdf o de imagen, jpg, tiff, etc. Después los subimos a esta aplicación, en donde les sacaremos el texto mediante un proceso de <span title="Reconocimiento Óptico de Caracteres">OCR</span>, los etiquetaremos y los clasificaremos y los guardaremos en una ubicación segura. Todo ello con un mínima interacción por parte de la persona que procese los documentos. Y en adelante, cualquier usuario que disponga de los permisos pertinentes podrá filtrar y buscar en los textos de todos ellos.</p><p style="text-align:right;"><i>Departamento de Informática</i></p>';

     /********/
     //$(function() {
          // $("#DOKU_FILES audio + .fa").click(function(e) {
           $('.doku-page').on('click','#DOKU_FILES audio + .fa',function(e){
             e.preventDefault();
             var song = $(this).prev('audio').get(0);
             if (song.paused) {
               song.play();
              console.log('PLAY');
               $(this).addClass("fa-pause");
               $(this).removeClass("fa-play");
             } else {
               song.pause();
               $(this).addClass("fa-play");
               $(this).removeClass("fa-pause");
             }
           });
     // });
      /***/
      $('.doku-page').on('click','.btn-help',function(){
          let w = $(this).closest('.doku-help');
          $('.dialog-info').hide();
          show_info( '#'+w.attr('id'),helps[w.attr('id')]+'<p style="text-align:center"><br /><span class="btn">Okis :)</span></p>' ,30000 );
      });

      $('.doku-page').on('change', '#select_area',function() {     
          console.log('#select_area',$(this).val());
          $.ajax({
             method: "POST",
             url: "<?=MODULE.'/ajax'?>",
             data: { 'op': 'changearea','id':$(this).val() },
             dataType: "json",
             beforeSend: function( xhr, settings ) {/* console.log('changearea'); */ }
          }).done(function( data ) {

             console.log('CHANGEAREA',data.id,data.key);
             console.log('CHANGEAREA',data);
             $('#DOKU_FILES .reload').click();
             $('#DOKU_FILETYPES .reload').click();
          }).fail(function() {
          }).always(function() {
          });        

      });

      $('.doku-page').on('click', '.btn-cfg',function() {     
          $('.dokux_cfg').css('z-index','0');
          let box = $(this).data('box');
          if(box) $('#dokux_cfg-'+box).show('fast').css('z-index','1').draggable({handle:'.title-bar'});
      });

      $('.doku-page').on('click', '.close-box',function() {     
          $(this).closest('.dokux_box').hide('fast'); //addClass('animated').addClass('rollOut'); //hide('fast');
      });

      $('.doku-page').on('click', '.dokux_cfg',function() {     
          $('.dokux_cfg').css('z-index','0');
          $(this).css('z-index','1');
      });

      $('.doku-page').on('click', '#users-reload',function() {   
          $('#btn-cfg-users').click();
      });


      $('.doku-page').on('click', '#full-view',function() {     
          $.ajax({
             method: "POST",
             url: "<?=MODULE.'/ajax'?>",
             data: { 'op': 'maximize' },
             dataType: "json",
             beforeSend: function( xhr, settings ) { /* console.log('maximize');*/  }
          }).done(function( data ) {
             console.log(data);
             if(data.rows><?=DOKU_FILES_ROWS?>){
                 $('#dokux_inbox,#dokux_pdf_viewer').hide();
             }else{
                 $('#dokux_inbox,#dokux_pdf_viewer').show();
             }
             $('#DOKU_FILES .reload').click();
          }).fail(function() {
          }).always(function() {
          });        
      });

      $('.doku-page').on('click', '#btn-cfg-users',function() { 
          //console.log('reload');    
          $.ajax({
             method: "POST",
             url: "<?=MODULE.'/ajax'?>",
             data: { 'op': 'getusers' },
             dataType: "json",
             beforeSend: function( xhr, settings ) { }
          }).done(function( data ) {
             
             console.log(data)
             $('#dokux_cfg-users tbody').empty();

             $.each(data.users, function(key,value){
                 $('#hw_tb_users tbody').append('<tr><td>'+value+'</td></tr>'); //<td>'+value.USER+'</td><td>'+value.NAME+'</td></tr>');
             });

             $.each(data.opers, function(key,value){
                 $('#hw_tb_opers tbody').append('<tr><td>'+value+'</td></tr>'); //<td>'+value.USER+'</td><td>'+value.NAME+'</td></tr>');
             });
             $.each(data.admins, function(key,value){
                 $('#hw_tb_admins tbody').append('<tr><td>'+value+'</td></tr>'); //<td>'+value.USER+'</td><td>'+value.NAME+'</td></tr>');
             });

          }).fail(function() {
          }).always(function() {
          });
      });
      /*****
      $('.doku-page').on('click', '#btn-add-user',function() {     
          $.ajax({
             method: "POST",
             url: "<?=MODULE.'/ajax'?>",
             data: { 'op': 'adduser', 'username':$('#doku-new-username').val() },
             dataType: "json",
             beforeSend: function( xhr, settings ) { }
          }).done(function( data ) {
             console.log(data);
             $('#btn-cfg-users').click();
          }).fail(function() {
          }).always(function() {
          });

      });
      ********/
      function find_value(where,value){
          let found = false;
          value = value.trim();
          $(where).each(function( index ) {
             if ( $( this ).text().trim() == value) { found = true; }
          });
          return found;
      }

      $('#files-reload').click(function(){

          //if(suspended){
          //    variableInterval();
          //}

          //console.log('RELOAD');
          $.ajax({
             method: "POST",
             url: "<?=MODULE.'/ajax'?>",
             data: { 'op': 'parsedir' },
             dataType: "json",
             beforeSend: function( xhr, settings ) { }
          }).done(function( data ) {
              let i=0;

              $('#sys').html('<progress id="progress_cpu" max="100" label="cpu:" value="'+data.info.cpu+'"></progress> '
                           + '<progress id="progress_mem" max="100" label="mem: '+data.info.used_mem + ' / '+ data.info.total_mem+'Gb." value="'+data.info.ram+'"></progress> '
                          // + '<span>MEM:'+ data.info.used_mem + '/'+ data.info.total_mem )+'</span>'
              );  //.highlight();

              $.each(data.files, function(key,value){
                  // console.log(key,value);
                  let ext = value.ext; // value.substring(value.lastIndexOf('.')+1);
                  let name = value.name;
                  let ocr = value.ocr;
                  ++i;
                  setTimeout(()=> { // Simular un delay, para que el servidor no parezca tan rápido
                     let row = find_value('#dokux_inbox .file_row',name);
                     if(row==false){     
                         let tr_ocr = (ext=='pdf'||ext=='jpg'||ext=='webp'||1==1)
                                    ? '<span class="file_ocr file_ocr_'+ocr+'" data-filename="'+name+'" data-ext="'+ext+'" title="OCR">Aa</span>'+
                                      '<span class="file_ai  file_ai_'+ocr+'"  data-filename="'+name+'" data-ext="'+ext+'" title="AI">Ai</span>'
                                    : '';
                         let tr_add = (ext=='pdf'||ext=='jpg'||ext=='webp')
                                    ? '<span class="file_add'+(ocr==1?'':'_no')+'" data-filename="'+name+'" data-ext="'+ext+'" title="Procesar archivo">&#10148;</span>'
                                    : '<span class="file_add" data-filename="'+name+'" data-ext="'+ext+'" title="Procesar archivo">&#10148;</span>';
                         let nt_add =  (ext=='pdf')
                                    ? '<span class="file_note_add" data-filename="'+name+'" data-ext="'+ext+'" title="Añadir nota">✎</span>'
                                    : '';
                         $('#dokux_inbox table')
                           .append('<tr><td class="file_row ocr_'+ocr+'"  data-dir="inbox" data-filename="'+name+'" data-ext="'+ext+'" data-ocr="'+ocr+'">'+name+' '+'</td>'
                                      +'<td>'+ nt_add + '</td>'
                                      +'<td>'+ tr_ocr + '</td>'
                                      +'<td>'+ tr_add + '</td>'
                                      +'<td><span class="file_delete" data-filename="'+name+'" data-ext="'+ext+'" title="Eliminar archivo">&#10006</span></td></tr>')
                           .find('.file_row').last().highlight();
                     }else{
                         //console.log($('#dokux_inbox .file_row'));
                         //console.log(name,ext,ocr);
                     }
                  },i*100);
              });


              $.each($('#dokux_inbox .file_ocr_1'), function(key,value){
                  let v0 = $(this).text().trim();
                  let row = $(this);
                  //console.log('FILE_ROW',row.data('filename'),row.data('ocr'));
                  $.each(data.files, function(index, value) { 
                      if(value.name==row.data('filename')){
                         //if(value.ocr!==row.data('ocr')){
                             // console.log('DATA.FILES',value.name,value.ocr);  
                             if(value.ocr!=1) row.closest('tr').find('.file_ocr').removeClass('file_ocr_1');                         
                         //}
                      }
                  });
              });            
              $.each($('#dokux_inbox .file_ai_1'), function(key,value){
                  let v0 = $(this).text().trim();
                  let row = $(this);
                  //console.log('FILE_ROW',row.data('filename'),row.data('ocr'));
                  $.each(data.files, function(index, value) { 
                      if(value.name==row.data('filename')){
                         //if(value.ocr!==row.data('ocr')){
                             // console.log('DATA.FILES',value.name,value.ocr);  
                             if(value.ocr!=1) row.closest('tr').find('.file_ai').removeClass('file_ai_1');                         
                         //}
                      }
                  });
              });

              $.each($('#dokux_inbox .file_row'), function(key,value){
                  // console.log('DATA.FILES',data.files);
                  let v0 = $(this).text().trim();
                  let found = false;

                  // console.log('Buscando archivo:', v0);
                  
		  //let ocr=$(this).hassClass('ocr_1');
		  
                  $.each(data.files, function(index, value) { 
                      
		      // console.log('Comparando con:', value.name);
		      //if (!v0.value.ocr)   $(this).removeClass('ocr_1');
		      
                      if (v0.trim() === value.name.trim()) {
                          found = true;
                          return false; // Salir del bucle
                      }
                  });
                  
                  if(found === false) $(this).closest('tr').hide('fast'); 
              });
              

          }).fail(function() {
          }).always(function() {
          });
      });

      function gettext(pdfUrl){
        var pdfData = atob(pdfUrl);
        var pdf = PDFJS.getDocument({data: pdfData});
        return pdf.then(function(pdf) { // get all pages text
          var maxPages = pdf.pdfInfo.numPages;
          var countPromises = []; // collecting all page promises
          for (var j = 1; j <= maxPages; j++) {
            var page = pdf.getPage(j);
            var txt = "";
            countPromises.push(page.then(function(page) { // add page promise
              var textContent = page.getTextContent();
              return textContent.then(function(text){ // return content promise
                return text.items.map(function (s) { return s.str; }).join(''); // value page text 
              });
            }));
          }
          // Wait for all pages and join text
          return Promise.all(countPromises).then(function (texts) {
            return texts.join('');
          });
        });
      }

      $('.doku-page').on('click', '.file_proccess',function() {     
          //console.log('!!!!!!!!!!!click en ',$(this).data('filename'));
          var filename = $(this).data('filename');
          var id = $(this).data('id');
          var ext = $(this).data('ext');
          $.ajax({
             method: "POST",
             url: "<?=MODULE.'/ajax'?>",
             data: { 'op': 'fileproccess', 'file':filename, 'id':id, 'ext':ext },
             dataType: "json",
             beforeSend: function( xhr, settings ) { /* console.log('beforeSend',filename,id,ext);*/ }
          }).done(function( data ) {
              console.log('done',data);
             if(data.updated==1){
                if (data.doc_type_name)
                    $('#DOKU_FILES #cell-'+data.id+'-5').text(data.doc_type_name).attr('val',data.new_doc_type).highlight(); //FIX updfate only one row
                if(data.identifier)
                    $('#DOKU_FILES #cell-'+data.id+'-7').text(data.identifier).highlight(); //FIX updfate only one row
                if(data.hash)
                    $('#DOKU_FILES #cell-'+data.id+'-14').text(data.hash).highlight(); //FIX updfate only one row
                //$('#DOKU_FILES .reload').click(); //FIX updfate only one row
             }else{
                showMessageError(data.msg);
             }
          }).fail(function() {
          }).always(function() {
          });
      });

      $('.doku-page').on('click', '.file_viewtext',function() {     
          console.log('click en ',$(this).data('filename'));
          var filename = $(this).data('filename');
          var id = $(this).data('id');
          var ext = $(this).data('ext');
          $.ajax({
             method: "POST",
             url: "<?=MODULE.'/ajax'?>",
             data: { 'op': 'fileviewtext', 'file':filename, 'id':id, 'ext':ext },
             dataType: "json",
             beforeSend: function( xhr, settings ) { /* console.log('beforeSend',filename,id,ext);*/ }
          }).done(function( data ) {
              // console.log('done',data);
              $('.dialog-info').hide();
              show_info( '#dokux_files','<h4>'+data.title+'</h4><pre style="max-height:560px;overflow:auto;">'+data.msg+'</pre><p style="text-align:center"><br /><span class="btn">Okis :)</span></p>' ,30000 );
          }).fail(function() {
          }).always(function() {
          });
      });

      $('.doku-page').on('click', '.file_note_add',function() {     
          let row = $(this);
          let element = $('#dokux_inbox'); //isMobile ? $('#content') : $('#div-checkout-cart-summary');
        //let id = row.data('id');
          let filename = row.data('filename');
          let ext = row.data('ext');
          console.log('NOTE_ADD',filename,ext);
          let html_code = 'Añadir nota.<br />'+
                          '<input type="text" id="tmp_ta" size="50" maxsize="50" placeholder="Nota (Max. 50 chars.)">';
          var x = messageDialog(html_code,function(accept){
              if(accept){
                  let text_note = $('#tmp_ta').val();
                  console.log('OK',text_note);   
                  $.ajax({
                     method: "POST",
                     url: "<?=MODULE.'/ajax'?>",
                     data: { 'op': 'note', 'file':filename, 'text_note':text_note, 'ext':ext },
                     dataType: "json",
                     beforeSend: function( xhr, settings ) { /* console.log('beforeSend',filename,id,ext);*/ }
                  }).done(function( data ) {
                      console.log('done',data);
                 //     row.closest('tr').find('.file_row').click();
                      previewDocument(row);
                   //////////////   loadpdf(data.base64file);
                  }).fail(function() {
                      console.log('error');
                  }).always(function() {
                  });
              }else
                  console.log('Cancelled');          
          });
      });

      function onSelectRow(T, Row ){
        //  $('#img_preview').hide();
        //  $('#dokux_pdf_viewer .button-bar,#canvas_container').hide();
         previewDocument(Row.find('.file_row_'));
      };

      $('.doku-page').on('click', '.file_row',function() {     
          previewDocument($(this));
      });

      function previewDocument(obj) {     
          // console.log('click en ',obj.data('filename'));
          var filename = obj.data('filename');
          var ext = obj.data('ext');
          var filedir = obj.data('dir');
          var row = obj.closest('td.file_row').closest('tr');
          var id = obj.data('id');
          $('.dokux-table tr').removeClass('active');
          row.addClass('active');
          pdf_filename = filename;
          //console.log('done',data.base64file);
          if(ext=='pdf'||ext=='jpg'||ext=='jpeg'||ext=='png'||ext=='webp'){

              $.ajax({
                  method: "POST",
                  url: "<?=MODULE.'/ajax'?>",
                  data: { 'op': 'getpdf', 'file':filename, 'dir':filedir ,'ext':ext },
                  dataType: "json",
                  beforeSend: function( xhr, settings ) { /*console.log('beforeSend','GETPDF',filename,filedir);*/ }
              }).done(function( data ) {


                  if(ext=='jpg'||ext=='jpeg'||ext=='png'||ext=='webp'){
                      if(data.base64file){
                          console.log('DATA BASE64');
                          $('#dokux_pdf_viewer .button-bar,#canvas_container').hide();
                          //console.log('<img src="data:image/' + ext +';base64,' + data.base64file+'">');
                          setTimeout(function(){
                              $('#img_preview').show().html('<img class="fit" src="data:image/' + ext +';base64,' + data.base64file+'">');
                              $('#img_preview img').click(function(){
                                  if($(this).hasClass('fit')) $(this).removeClass('fit').addClass('full'); else $(this).addClass('fit').removeClass('full');
                              });
                          },300);
                      }
                  }else if (ext=='pdf'){ 
                     $('#img_preview').hide();
                     setTimeout(function(){$('#canvas_container,#dokux_pdf_viewer .button-bar').show();},300);
                     loadpdf(data.base64file);
                     if(data.pdf_text) {
                         $('#pdf-text').html('.....');
                         //$('#btn-cfg-pdf_text').click();    // Muestra popup con el texto
                         //$('#pdf-text').html(data.pdf_text);
                         //console.log(data.pdf_text);
                     }
                  }else{
                     $('#img_preview').show().html('algo no va bien');
                     console.log('GETTEXT - Done',ext,data);
                  }

              }).fail(function() {
                  //console.log( "error" );
              }).always(function() {
                 //console.log('always');
              });
          }else if(ext=='zip'){
                 console.log('GETTEXT - ZIP');
                 $('#dokux_pdf_viewer .button-bar,#canvas_container').hide();
                 $('#img_preview').html('ZIP file').show();
                 let basename = filename.replace('.zip',''); 
                  $.ajax({
                     method: "POST",
                     url: "<?=MODULE.'/ajax'?>",
                     data: { 'op': 'gettext','dir':filedir ,'id':id, 'filename':basename ,'ext':ext},
                     dataType: "json",
                  }).done(function( data ) {
                     $('#img_preview').html('<h4>ZIP file: '+filename+'</h4><pre style="text-align:left;margin-left:20px;">'+data.text+'</pre>'); //.show();
                  }).fail(function() {
                  }).always(function() {
                  });
          //CSV
          }else if(ext=='csv'){
                 // console.log('GETTEXT - CSV');
                 $('#dokux_pdf_viewer .button-bar,#canvas_container').hide();
                 $('#img_preview').html('CSV file').show();
                 let basename = filename.replace('.csv',''); 
               
                  $.ajax({
                     method: "POST",
                     url: "<?=MODULE.'/ajax'?>",
                     data: { 'op': 'gettext','dir':filedir ,'id':id, 'filename':basename ,'ext':ext},
                     dataType: "json",
                  }).done(function( data ) {
                     console.log('DATA',data);
                     $('#img_preview').html('<h4>CSV file: '+filename+'</h4><pre style="text-align:left;margin-left:20px;">'+data.text+'</pre>'); //.show();
                  }).fail(function() {
                  }).always(function() {
                  });
          
          }else if(ext=='txt'){
                 // console.log('GETTEXT - CSV');
                 $('#dokux_pdf_viewer .button-bar,#canvas_container').hide();
                 $('#img_preview').html('CSV file').show();
                 let basename = filename.replace('.txt',''); 
               
                  $.ajax({
                     method: "POST",
                     url: "<?=MODULE.'/ajax'?>",
                     data: { 'op': 'gettext','dir':filedir ,'id':id, 'filename':basename ,'ext':ext},
                     dataType: "json",
                  }).done(function( data ) {
                     console.log('DATA',data);
                     $('#img_preview').html('<h4>TXT file: '+filename+'</h4><pre style="text-align:left;margin-left:20px;">'+data.text+'</pre>'); //.show();
                  }).fail(function() {
                  }).always(function() {
                  });
          
          }else if(ext=='mp3'){
                 console.log('GETTEXT - MP3');
                 $('#dokux_pdf_viewer .button-bar,#canvas_container').hide();
                 $('#img_preview').html('MP3 file').show();
                 let basename = filename.replace('.mp3',''); 
                  $.ajax({
                     method: "POST",
                     url: "<?=MODULE.'/ajax'?>",
                     data: { 'op': 'gettext', 'dir':filedir ,'id':id,'filename':basename ,'ext':ext },
                     dataType: "json",
                  }).done(function( data ) {
                     //console.log('PREVIEW',data.text);
                     $('#img_preview').html('<h4>MP3 file: '+filename+'</h4><pre class="shadow" style="border:1px solid #bbb; text-align:left;padding:5px;margin:20px;">'+data.text+'</pre>'); //.show();
                     if(data.thumb){
                         $('#img_preview pre').css('background','url('+data.thumb+')')
                                              .css('background-position','right top')
                                              .css('background-size','contain')
                                              .css('background-repeat','no-repeat');
                     }else{
                         $('#img_preview').append('<div id="thumbs">No thumb ['+data.search+']['+data.hash+']</div>');
                         //console.log('dokux/ajax/op=getthumbs/hash='+data.hash+'/search='+data.search );
                          $.ajax({
                             method: "POST",
                             url: "<?=MODULE.'/ajax'?>",
                             data: { 'op': 'getthumbs', 'hash':data.hash , 'search':data.search },
                             dataType: "json",
                          }).done(function( data ) {
                             console.log(data);
                             if(data.error==0){
                                 $('#img_preview #thumbs').html('<p>'+data.search+'</p>');
                                 createThumbs(data).then(updateThumbs());
                             }else{
                                 $('#img_preview #thumbs').html('No thumb');
                             }
                          }).fail(function() {
                            console.table(data);
                          }).always(function() {
                          });
                     }
                     //console.log('THUMB',data);
                  }).fail(function() {
                  }).always(function() {
                  });
          }else{
              $('#dokux_pdf_viewer .button-bar,#canvas_container').hide();
              $('#img_preview').html(ext+' file').show();
          }
      }

      async function createThumbs(data){
          $(data.thumbs).each( function(key,value){
              let src = value.src; // value.substring(value.lastIndexOf('.')+1);
              $('#img_preview #thumbs').append('<div class="cover"><img class="img" src="'+value+'"><span class="dim"></span><br><button class="save_cover">Guardar</button></div>');
          }); 
      }

      function updateThumbs(){
          setTimeout(function(){
              $('#img_preview .cover .img').each(function(i,j){
                  let w = $(j).width();
                  let h = $(j).height();
                  let r = 0.2;  
                  let dif = Math.abs(w-h);  
                  let ratio = Math.abs(w/h).toFixed(2);  
                  let okis = ratio>(1-r)&&ratio<(1+r);
                  if(!okis) {$(j).closest('.cover').addClass('norl').find('.save_cover').hide(); }
                  $(j).closest('.cover').find('.dim').html(w+' x '+h+' ['+dif+'] '+' ['+ratio+'] ' );
                  console.log(i,w,h);
              });
          },1000);
      }

      $('.doku-page').on('click', '.file_ai',function() {
          var btn = $(this);
          var filename = $(this).data('filename');
          var fila = $(this).closest('tr');
         //var ext = obj.data('ext');
          //var filedir = obj.data('dir');

          let ext = filename.substring(filename.lastIndexOf('.')+1); 
          let basename = filename.replace('.'+ext,''); 
          if($(this).hasClass('rotate')) {
              console.log('WORK IN PROGRESS');
              return false;
          }
          /*
          if($(this).hasClass('file_ai_1')) {
              console.log('AI_ALREADY_PASSED');
              $('#btn-cfg-pdf_text').click();    // Muestra popup con el texto
              $('#dokux_cfg-pdf_text #pdf-text').html('<p>Cargando texto ...</p>');
              //console.log('basename',basename);
              //console.log('<?=MODULE.'/ajax'?>'+'/op=gettext/filename='+basename);
              $.ajax({
                 method: "POST",
                 url: "<?=MODULE.'/ajax'?>",
                 data: { 'op': 'parsefile_with_ai', / *'dir':filedir ,* / 'filename':basename ,'ext':ext },
                 dataType: "json",
                 beforeSend: function( xhr, settings ) { }
              }).done(function( data ) {
                 $('#dokux_cfg-pdf_text .title-bar span').html(filename);
                 $('#dokux_cfg-pdf_text #pdf-text').html('<pre>'+data.text+'</pre>');
              }).fail(function() {
              }).always(function() {
              });
              return false;
          }
          */          

          if($(this).hasClass('file_ai_1')) {
              console.log('AI_ALREADY_PASSED');
              $('#btn-cfg-pdf_text').click();    // Muestra popup con el texto
              $('#dokux_cfg-pdf_text #pdf-text').html('<p>Cargando texto ...</p>');
              //console.log('basename',basename);
              //console.log('<?=MODULE.'/ajax'?>'+'/op=gettext/filename='+basename);
              $.ajax({
                 method: "POST",
                 url: "<?=MODULE.'/ajax'?>",
                 data: { 'op': 'gettext','filename':basename ,'ext':ext },
                 dataType: "json",
                 beforeSend: function( xhr, settings ) { }
              }).done(function( data ) {
                 $('#dokux_cfg-pdf_text .title-bar span').html(filename);
                 $('#dokux_cfg-pdf_text #pdf-text').html('<pre>'+data.text+'</pre>');
              }).fail(function() {
              }).always(function() {
              });
              return false;
          }

          fila.find('.file_ai').addClass('rotate') ;
          $.ajax({
             method: "POST",
             url: "<?=MODULE.'/ajax'?>",
             data: { 'op': 'ai', 'file':basename,'ext':ext, 'service':$('#ai_service').val() },
             dataType: "json",
             beforeSend: function( xhr, settings ) { }
          }).done(function( data ) {

              console.log('done',data);

             if (data.error==0){
                 $('#btn-cfg-pdf_text').click();    // Muestra popup con el texto
                 $('#dokux_cfg-pdf_text .title-bar span').html(filename);
                 $('#dokux_cfg-pdf_text #pdf-text').html('<pre>'+data.text+'</pre>');
                 if (btn[0]) btn[0].classList.add('file_ai_1');
                 fila.find('.file_add_no').addClass('file_add').removeClass('file_add_no');
             }else{
                 console.log('AI error',data.msg);
                 showMessageError(data.msg || 'A este archivo no se le puede hacer AI');
             }


          }).fail(function(xhr) {
             console.log('error',xhr.status,xhr.responseText);
             showMessageError('Error de comunicación con el servidor ('+xhr.status+')');
          }).always(function() {
             fila.find('.file_ai').removeClass('rotate') ;
             console.log('always');
          });          
      });




      $('.doku-page').on('click', '.file_ocr',function() {     
          //console.log('Click en ',$(this).data('filename'));
          var filename = $(this).data('filename');
          var fila = $(this).closest('tr');

          //var ext = obj.data('ext');
          //var filedir = obj.data('dir');

          let ext = filename.substring(filename.lastIndexOf('.')+1); 
          let basename = filename.replace('.'+ext,''); 
          if($(this).hasClass('rotate')) {
              console.log('WORK IN PROGRESS');
              return false;
          }
          if($(this).hasClass('file_ocr_1')) {
              console.log('OCR_ALREADY_PASSED');
              $('#btn-cfg-pdf_text').click();    // Muestra popup con el texto
              $('#dokux_cfg-pdf_text #pdf-text').html('<p>Cargando texto ...</p>');
              //console.log('basename',basename);
              //console.log('<?=MODULE.'/ajax'?>'+'/op=gettext/filename='+basename);
              $.ajax({
                 method: "POST",
                 url: "<?=MODULE.'/ajax'?>",
                 data: { 'op': 'gettext', /*'dir':filedir ,*/ 'filename':basename ,'ext':ext },
                 dataType: "json",
                 beforeSend: function( xhr, settings ) { }
              }).done(function( data ) {
                 $('#dokux_cfg-pdf_text .title-bar span').html(filename);
                 $('#dokux_cfg-pdf_text #pdf-text').html('<pre>'+data.text+'</pre>');
              }).fail(function() {
              }).always(function() {
              });
              return false;
          }

          fila.find('.file_ocr').addClass('rotate') ;
          $.ajax({
             method: "POST",
             url: "<?=MODULE.'/ajax'?>",
             data: { 'op': 'ocr', 'file':basename,'ext':ext },
             dataType: "json",
             beforeSend: function( xhr, settings ) { }
          }).done(function( data ) {
             console.log('done',data);
             if (data.error==0){
                 fila.find('.file_ocr').addClass('file_ocr_1');
                 fila.find('.file_add_no').addClass('file_add').removeClass('file_add_no');
             }else{
                  /*
                  convert -density 600 -quality 100 -depth 8 -alpha remove -background white -flatten -colorspace GRAY /home/dokux/jts27n/hlauniflow_gestion_3445_001zz.pdf -append /home/dokux/jts27n/ocr/hlauniflow_gestion_3445_001zz.jpg
                  convert -density 600 -quality 100 -depth 8 -alpha remove -background white -flatten -colorspace GRAY /home/dokux/jts27n/hlauniflow_gestion_3445_001zz.pdf -append /home/dokux/jts27n/ocr/hlauniflow_gestion_3445_001zz.jpg
                  convert-im6.q16: cache resources exhausted `/home/dokux/jts27n/hlauniflow_gestion_3445_001zz.pdf' @ error/cache.c/OpenPixelCache/4083.
                  convert-im6.q16: width or height exceeds limit `/home/dokux/jts27n/hlauniflow_gestion_3445_001zz.pdf' @ error/cache.c/OpenPixelCache/3912.
                  convert-im6.q16: cache resources exhausted `/home/dokux/jts27n/hlauniflow_gestion_3445_001zz.pdf' @ error/cache.c/OpenPixelCache/4083.
                  */

                 console.log('A este archivo no se le puede hacer ORC');
                 showMessageError('A este archivo no se le puede hacer ORC');
             }
          }).fail(function() {
             console.log( "error" );
          }).always(function() {
             fila.find('.file_ocr').removeClass('rotate') ;
             console.log('always');
          });
      });       

      $('.doku-page').on('click', '.file_add',function() {     
          //console.log('Click en ',$(this).data('filename'));
          let filename = $(this).data('filename');
          //let ext = $(this).data('ext');
          let fila = $(this).closest('tr');
          let ext = filename.substring(filename.lastIndexOf('.')+1); 
          let basename = filename.replace('.'+ext,''); 

          $.ajax({
             method: "POST",
             url: "<?=MODULE.'/ajax'?>",
             data: { 'op': 'fileadd', 'file':filename, 'basename':basename, 'ext':ext },
             dataType: "json",
             beforeSend: function( xhr, settings ) { }
          }).done(function( data ) {
             console.log('done',data);
             if (data.error==0){
                 fila.hide('fast');
                 $('#DOKU_FILES .reload').click(); //.find('tbody tr:first-child').highlight();
                 //.click();
                 setTimeout(function(){
                     $('#DOKU_FILES').find('tbody tr:first-child').highlight();
                    // $('#DOKU_FILES').find('tbody tr:first-child .actions .edit').click();
                 },600);
             }else{
                 showMessageError(data.msg);
             }
          }).fail(function(data) {
             console.log('fail',data);
          }).always(function() {
             //console.log('always');
          });
      });       
      
      $('.doku-page').on('click', '.file_delete',function() {     
          //console.log('Click en ',$(this).data('filename'));
          var filename = $(this).data('filename');
          var fila = $(this).closest('tr');
          let ext = filename.substring(filename.lastIndexOf('.')+1); 
          let basename = filename.replace('.'+ext,''); 
          $.ajax({
             method: "POST",
             url: "<?=MODULE.'/ajax'?>",
             data: { 'op': 'filedelete', 'file':filename, 'basename':basename, 'ext':ext },
             dataType: "json",
             beforeSend: function( xhr, settings ) { }
          }).done(function( data ) {
             //console.log('done',data);
             if (data.error==0){
                 fila.hide('fast');
                 $('#DOKU_FILES .reload').click().find('tr:first-child');
                 $('#DOKU_FILES').find('tbody tr:first-child').highlight();
             }else{
                 showMessageError('que ha pasao???');
             }
          }).fail(function() {
             console.log( "error" );
          }).always(function() {
             //console.log('always');
          });
      });       

      var variableInterval = function(){
          if(suspended){
              seconds=2000;
              n=0;   
          }
          suspended = false;
          var timer = setInterval(function(){
              
              if(n==30){         
                  $("#cursor").css('background-color','orange');
                  seconds = 5000; 
                  clearInterval(timer);
                  variableInterval();
              }
              if(n>42){         
                  $("#cursor").css('background-color','gray');
                  seconds = 5000; 
                  clearInterval(timer);
                  suspended = true;
                   variableInterval();
                  ///////////////////////////////////////////////$('#site-header .logo a').click();
              }
              /**/
              if(mousehasmoved && seconds>30){
                  $("#cursor").css('background-color','red');
                  mousehasmoved=false;
                  n=0; 
                  seconds = 2000; 
                  clearInterval(timer);
                  variableInterval();
              }
              /**/
              n++;
              $('#cursor').html(n); //'seconds :'+seconds+' - Times reload: '+n);
              // console.log('reload!!');
              $('#files-reload').click();
              
          },seconds);
      }

      if(suspended) variableInterval();
      //$('#files-reload').click();

      $('#dokux_inbox').mousemove(function(event) {
          //var parentOffset = $(this).offset();          
           /*
           console.log('MOUSEMOVE');
           var parentOffset = $(this).offset() || {left: 0, top: 0};
           console.log('PARENT.OFFSET',parentOffset.left,parentOffset.top);



          $("#cursor").css({
              left: event.pageX - parentOffset.left + 10,
              top: event.pageY - parentOffset.top + 10
          });
         */

          var $this = $(this);
              var offset = $this.offset();
              
              // Verificar que offset existe
              if (offset) {
                  // Calcular posición relativa al contenedor
                  var relX = event.pageX - offset.left;
                  var relY = event.pageY - offset.top;

                  //console.log('OFFSET',relX,relY);
                  
                  // Actualizar posición del cursor con valores absolutos
                  $("#cursor").css({
                      "position": "absolute",
                      "left": relX + 10 + "px",
                      "top": relY + 10 + "px",
                      "z-index": "9999"
                  });
                }









           mousehasmoved=true;

          // ¯\_(ツ)_/¯

      })

      $('#files-reload').click();

      //$('.dokux_box').draggable({handle:'.title-bar'});



      $('#files-upload').click(function(){
             $('#dokux_upload').show().addClass('animated').addClass('rollIn'); //.draggable({handle:'.title-bar'});
      });

      $(function(){
          setTimeout(function(){
              $('#dokux_upload').show().addClass('animated').addClass('rollIn').draggable({handle:'.title-bar'});
          },1500);
      });

</script>
