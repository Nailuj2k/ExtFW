    if (typeof json_viewer_initialized === 'undefined') var json_viewer_initialized = false;

    var json_viewer_full_screen = true;     
    var json_viewer_instances   = 0;     
    $(function() {     

        if(!json_viewer_initialized){
            json_viewer_initialized = true;     

            $('body').on('click', '.close-box',function() {     
                json_viewer_instances--;
                $(this).closest('.extfw_ware_box').hide('fast'); //addClass('animated').addClass('rollOut'); //hide('fast');
            });

            $('body').on('click','#json_viewer_maximize',function(e){
                console.log('json_viewer_maximize');
                json_viewer_full_screen = !json_viewer_full_screen;
                e.stopPropagation();
                if (json_viewer_full_screen) {
                   $('#dialog-file-json').css('display','block').css('position','fixed').css('z-index','11').css('top','55px').css('right','15px').css('bottom','15px').css('left','15px');
                   $('#json_viewer_maximize .fa').removeClass('fa-window-maximize').addClass('fa-window-restore').attr('title','Restaurar');
                }else{                                                                           // 22px
                   $('#dialog-file-json').css('display','inline-table').css('position','fixed').css('z-index','3').css('top','290px');
                   $('#json_viewer_maximize .fa').removeClass('fa-window-restore').addClass('fa-window-maximize').attr('title','Maximizar');
                }
            });

            $('body').on('click', '.btn-link',function() {     
                let strclass = $(this).data('class');
                $('#dialog-file-text .'+strclass).toggle();
             // console.log('#dialog-file-text .'+strclass);
            });

        }
    });

    $('body').on('click','.open_file_json',function(e){
        load_json( $(this) );
    });

    function load_json( element ){
        let filename = element.data('href');
        let title = element.data('title');

        $('#dialog-file-json #dialog-file-text').html('<p>Cargando texto ... '+filename+'</p>');
        $('#dialog-file-json').css('display','block').show('fast').css('z-index','11');//.draggable({handle:'.title-bar'});
            
        $.ajax({
            method: "POST",
            url: filename, 
            beforeSend: function( xhr, settings ) { }
        }).done(function( data ) {
            //console.log('done',data);
            $('#dialog-file-json .title-bar span').html(title);
            $('#dialog-file-json #dialog-file-text').html('<pre style="margin:0;font-size:0.8em;">'+data+'</pre>');
        }).fail(function(data) {
            // console.log('fail',data);
        }).always(function(data) {
            // console.log('always',data);
        });
        return false;

    }
