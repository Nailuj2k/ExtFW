$(function(){

  var dropbox = $('#dropbox_upload');
  var message = $('.message', dropbox);
  var dragOpen =false;
  var tm = false;
///////// https://github.com/weixiyen/jquery-filedrop
 // _classes_/scaffold/ajax_post_file.php?table=clam_user_files&field=file_name&module=&if_exists=replace&parent=1
//  var ajax_url = '_classes_/scaffold/ajax_post_file.php?table=' + upload_tablename + '&field=' + upload_fieldname +'&module=' + upload_modulename;
  var ajax_url = _MODULE_+'/ajax/op=upload';
    //////////////////////////////////////if (upload_if_exists) ajax_url += '/if_exists='+upload_if_exists;
  ////////console.log('URL',ajax_url);

  
        
        $('#dokux_inbox').on('dragenter', function(){
            $('#dropbox_upload').css('z-index','20');
            dragOpen = true;
            clearTimeout(tm);
            tm = setTimeout(function(){
                if(!dragOpen)  $('#dropbox_upload').css('z-index','-1');
            },2000);
        });

        $('#dokux_inbox').on('dragleave', function(e){
            // Ignorar si el drag se movió a un elemento hijo (no salió realmente del inbox)
            var related = e.relatedTarget;
            if (related && document.getElementById('dokux_inbox').contains(related)) return;
            if(dragOpen){
               dragOpen=false;
            }
        });
  
        $('#dropbox_upload #btn-close-dropbox').on('click',function(){
            $('#dropbox_upload').css('z-index','-1').find('.preview').remove();
        });
  
  dropbox.filedrop({
    
    // The name of the $_FILES entry:
    paramname:upload_fieldname,
    //fallback_id : 'fallback',
    maxfiles: 25,
    maxfilesize: 30,
    url: ajax_url,
    fallback_id: 'acho',
    fallback_dropzoneClick : true,

    /**
	allowedfiletypes: ['image/jpeg','image/png','image/gif'],	// filetypes allowed by Content-Type.  Empty array means no restrictions
	allowedfileextensions: ['.jpg','.jpeg','.png','.gif'], // file extensions allowed. Empty array means no restrictions
    */ 

    uploadFinished:function(i,file,response){
      //////////////console.log('RESPONSE',response);


      $.data(file).addClass('done');
      // response is the JSON object that post_file.php returns
      if     (response.replaced)              $.data(file).find('.document-name').html('<span class="replaced">'+response.local_file+'</span>');
      else if(response.errorcode=='notallow') $.data(file).find('.document-name').html('<span class="notallow" title="'+response.msg+'">No permitido</span>');
      else if(response.error>0)               $.data(file).find('.document-name').html('<span class="errornum" title="'+response.msg+'">Error '+response.error+'</span>');
      else                                    $.data(file).find('.document-name').text(response.local_file);
    },

    error: function(err, file) {
      switch(err) {
        case 'BrowserNotSupported':
          //console.log(err,file);
          showMessage(msg_browsernotsupported);
          break;
        case 'TooManyFiles':
          showMessage(msg_toomanyfiles);
          break;
        case 'FileTooLarge':
          //alert(file.name+' '+msg_filetoolarge);
          //showMessage(msg_filetoolarge.format(file.name));
         // $.data(file).find('.document-name').html('ERROR');
          createError(file);
        
          dragOpen=true;
          clearTimeout(tm);

          break;
        default:
          break;
      }
    },
    
	dragLeave: function() {
		// user dragging files out of #dropzone
        //tm = setTimeout(function(){
          //if(!dragOpen) 
             // $('#dropbox_upload').css('z-index','-1');
        //},1000);
            if(dragOpen){
               dragOpen=false;
            }

	},

    // Called before each upload is started
    beforeEach: function(file){
      //console.log('BEFORE',file);
      //$('.upload_multiple').html(' <i class="fa fa-remove fa-inverse"> </i> Cerrar').css('right','30px').removeClass('btn-warning').addClass('btn-info');
      /*
      if(!file.type.match(/^image\//)){
        alert(msg_onlyimagesallowed);
        // Returning false will cause the
        // file to be rejected
        return false;
      }
      */
    },
    
    uploadStarted:function(i, file, len){

		// a file began uploading
		// i = index => 0, 1, 2, 3, 4 etc
		// file is the actual file of the index
		// len = total files user dropped
      if(i==0)  $('#dropbox_upload').css('z-index','1');

      dragOpen=true;
      clearTimeout(tm);
      if(file.type.match(/^image\//))
        createImage(file);
      else
        createIcon(file);  
    },

	afterAll: function() {
		// runs after all files have been uploaded or otherwise dealt with
        if(suspended){
            variableInterval();
        }
        tm = setTimeout(function(){
           //if(!dragOpen)  
               $('#dropbox_upload').css('z-index','-1');
               dragOpen = false;
        },3000);
	},
    
    progressUpdated: function(i, file, progress) {
      $.data(file).find('.progress').width(progress);
      $.data(file).find('.document-name').text(progress+'%');
    }
       
  });
  
  var template_error = '<div class="preview fail">'+
                   '<span class="imageHolder">'+
                      '<img style="height:100px;width:100px;"  />'+
                      '<span class="failed"></span>'+
                      '<div class="document-name">Tamaño excedido</div>'+
                   '</span>'+
                   '<div class="progressHolder">'+
                     '<div class="xprogress"></div>'+
                   '</div>'+
                 '</div>'; 
  
  var template_img = '<div class="preview">'+
                   '<span class="imageHolder">'+
                      '<img />'+
                      '<span class="uploaded"></span>'+
                      '<div class="document-name"></div>'+
                   '</span>'+
                   '<div class="progressHolder">'+
                     '<div class="progress"></div>'+
                   '</div>'+
                 '</div>'; 
  
  var template = '<div class="preview">'+
                   '<span class="imageHolder">'+
                      '<img  style="height:100px;width:100px;" />'+
         //             '<img style="height:100px;width:100px;" src="/_images_/filetypes/icon_pdf.png"/>'+
                      '<span class="uploaded"></span>'+
                      '<div class="document-name"></div>'+
                   '</span>'+
                   '<div class="progressHolder">'+
                     '<div class="progress"></div>'+
                   '</div>'+
                 '</div>'; 
  
  
  function getExtension(filename){   
    /*
    var filename = /.*(?=\.)/.exec(filename);
    var extension = /[^\.]*$/.exec(filename);
    if(!filename){
      filename = full;
      extension = 'txt';
    }	  
    return extension;
    */	
    return filename.substring(filename.lastIndexOf('.')+1); 
  }

  function createTemplate(filename){
    var extension = getExtension(filename);
    image_src = '_images_/filetypes/icon_'+extension+'.png';
    return '<div class="preview">'+
                   '<span class="imageHolder">'+
                      '<img style="height:100px;width:100px;" src="'+image_src+'"/>'+
                      '<span class="uploaded"></span>'+
                      '<div class="document-name"></div>'+
                   '</span>'+
                   '<div class="progressHolder">'+
                     '<div class="progress"></div>'+//extension+
                   '</div>'+
                 '</div>'; 
  }
  
  function createError(file){
    var extension = getExtension(file.name);
    /****************
    if(file.type.match(/^image\//))
       console.log('IMAGE file.name',file.name,extension);
    else
       console.log('FILE file.name',file.name,extension);
    */
    var preview = $(template_error), 
        image = $('img', preview);
    var reader = new FileReader();
    image.width = 100;
    image.height = 100;
    reader.onload = function(e){
//        console.log('e.target.result',e.target.result);
      
      if(file.type.match(/^image\//))
          image.attr('src',e.target.result);
      else{
          if(extension=='docx')extension='doc';
          if(extension=='xlsx')extension='xls';
          if(extension=='rar'||extension=='7z')extension='zip';
          image.attr('src','/_images_/filetypes/icon_'+extension+'.png');
      }
    };
    reader.readAsDataURL(file);   
    message.hide();
    preview.appendTo(dropbox);
    $.data(file,preview);
    $.data(file).addClass('fail');
  }
  
  function createImage(file){

    var preview = $(template_img), 
        image = $('img', preview);
    var reader = new FileReader();
    image.width = 100;
    image.height = 100;
    reader.onload = function(e){
      // e.target.result holds the DataURL which
      // can be used as a source of the image:
      image.attr('src',e.target.result);
    };
    // Reading the file as a DataURL. When finished,
    // this will trigger the onload function above:
    reader.readAsDataURL(file);
    message.hide();
    preview.appendTo(dropbox);
    // Associating a preview container
    // with the file, using jQuery's $.data():
    $.data(file,preview);
  }
  
  function createIcon(file){
    var extension = getExtension(file.name);
    var preview = $(createTemplate(file.name)), 
        image = $('img', preview);
    var  docname = $('.document-name', preview);
    var reader = new FileReader();
    image.width = 100;
    image.height = 100;
    reader.onload = function(e){
      // e.target.result holds the DataURL which
      // can be used as a source of the image:
      docname.text(e.name);
      image.attr('src','/_images_/filetypes/icon_'+extension+'.png');
    };
    // Reading the file as a DataURL. When finished,
    // this will trigger the onload function above:
    reader.readAsDataURL(file);
    message.hide();
    preview.appendTo(dropbox);
    // Associating a preview container
    // with the file, using jQuery's $.data():
    $.data(file,preview);
  }
  

  function showMessage(msg){
    message.html(msg);
  }

});