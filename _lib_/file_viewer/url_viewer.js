    if (typeof url_viewer_initialized === 'undefined') var url_viewer_initialized = false;

    var url_viewer_full_screen = false;     
    var url_viewer_instances   = 0;     
    $(function() {     

        if(!url_viewer_initialized){
            url_viewer_initialized = true;     
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
                url_viewer_instances--;
                $(this).closest('.extfw_ware_box').hide('fast'); //addClass('animated').addClass('rollOut'); //hide('fast');
                // $(this).closest('.extfw_ware_box').fadeOut();
            });



            $('body').on('click','#url_viewer_maximize',function(e){
                console.log('url_viewer_maximize');
                url_viewer_full_screen = !url_viewer_full_screen;
                e.stopPropagation();
                if (url_viewer_full_screen) {
                   $('#dialog-file-url').css('position','fixed').css('z-index','11').css('top','15px').css('right','15px').css('bottom','15px').css('left','15px');
                   $('#url_viewer_maximize .fa').removeClass('fa-window-maximize').addClass('fa-window-restore').attr('title','Restaurar');
                }else{
                   $('#dialog-file-url').css('position','absolute').css('z-index','3').css('top','22px').css('right','0px').css('bottom','0px').css('left','0px');
                   $('#url_viewer_maximize .fa').removeClass('fa-window-restore').addClass('fa-window-maximize').attr('title','Maximizar');
                }
            });

        }
    });

    $('body').on('click','.open_file_url',function(e){
        // console.log('open_file_txt',$(this));
        // console.log('data-href', $(this).data('href'));
        // console.log('data-title',$(this).data('title'));
        load_url( $(this) );
    });

    function load_url( element ){
        let filename = element.data('href');
        let title = element.data('title');

        $('#dialog-file-url #dialog-file-url').html('<p>Cargando html ... '+filename+'</p>');
        $('#dialog-file-url').show('fast').css('z-index','2'); //.draggable({handle:'.title-bar'});
            
        $.ajax({
            method: "POST",
            url: filename, 
            beforeSend: function( xhr, settings ) { }
        }).done(function( data ) {
            //console.log('done',data);
            $('#dialog-file-url .title-bar span').html(title);
            $('#dialog-file-url #dialog-file-html').html(data);
        }).fail(function(data) {
            // console.log('fail',data);
        }).always(function(data) {
            // console.log('always',data);
        });
        return false;

    }