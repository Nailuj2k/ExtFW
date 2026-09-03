    if (typeof txt_viewer_initialized === 'undefined') var txt_viewer_initialized = false;

    var txt_viewer_full_screen = false;     
    var txt_viewer_instances   = 0;     
    $(function() {     

        if(!txt_viewer_initialized){
            txt_viewer_initialized = true;     
            /*
            $('body').on('click','.load_txt',function(e){
                console.log('.load_file_pdf',pdf_viewer_instances);
                if(txt_viewer_instances>0) return false;
                txt_viewer_instances++;
                console.log('.open_file_txt OK',txt_viewer_instances);
                e.preventDefault();
                $('dialog-file-txt').fadeIn(); //('fast');
                $('#dialog-file-txt .title-bar').html($(this).data('title'));
            });
            */

            $('body').on('click', '.close-box',function() {     
                txt_viewer_instances--;
                $(this).closest('.extfw_ware_box').hide('fast'); //addClass('animated').addClass('rollOut'); //hide('fast');
                // $(this).closest('.extfw_ware_box').fadeOut();
            });



            $('body').on('click','#txt_viewer_maximize',function(e){
                console.log('txt_viewer_maximize');
                txt_viewer_full_screen = !txt_viewer_full_screen;
                e.stopPropagation();
                if (txt_viewer_full_screen) {
                   $('#dialog-file-txt').addClass('fullscreen').removeClass('def_screen')// css('position','fixed').css('z-index','11').css('top','15px').css('right','15px').css('bottom','15px').css('left','15px').css('margin':'0');
                   $('#txt_viewer_maximize .fa').removeClass('fa-window-maximize').addClass('fa-window-restore').attr('title','Restaurar');
                }else{
                   $('#dialog-file-txt').addClass('def_screen').removeClass('fullscreen')//.css('position','absolute').css('z-index','3').css('top','22px').css('right','0px').css('bottom','0px').css('left','0px');
                   $('#txt_viewer_maximize .fa').removeClass('fa-window-restore').addClass('fa-window-maximize').attr('title','Maximizar');
                }
            });

        }
    });

    $('body').on('click','.open_file_txt',function(e){
        // console.log('open_file_txt',$(this));
        // console.log('data-href', $(this).data('href'));
        // console.log('data-title',$(this).data('title'));
        load_txt( $(this) );
    });

    function load_txt( element ){
        let filename = element.data('href');
        let title = element.data('title');

        $('#dialog-file-txt #dialog-file-text').html('<p>Cargando texto ... '+filename+'</p>');
        $('#dialog-file-txt').show('fast').css('z-index','2'); //.draggable({handle:'.title-bar'});
            
        $.ajax({
            method: "POST",
            url: filename,
            beforeSend: function( xhr, settings ) { }
        }).done(function( data ) {
            //console.log('done',data);
            $('#dialog-file-txt .title-bar span').html(title);
            $('#dialog-file-txt #dialog-file-text').html('<pre style="margin:0;">'+data+'</pre>');
        }).fail(function(data) {
            // console.log('fail',data);
        }).always(function(data) {
            // console.log('always',data);
        });
        return false;

    }
