<?php

    //if (MODULE=='page' || MODULE=='news' || MODULE=='blog'){

        if($documents||$files||$gallery){
            ?>
            <script>

                function loadWidget(widget){

                    console.log('loadWidget',widget,_MODULE_+'/editable=<?=$editable?'1':'0'?>/type='+widget+'/id='+_ID_+'/html');

                    $.get(_MODULE_+'/editable=<?=$editable?'1':'0'?>/type='+widget+'/id='+_ID_+'/html',function(data){
                        $('#page-widget-'+widget).html(data);
                    })
                    
                    .done(response => {
                            if (widget == 'gallery') {
                                
                                const images = galleryFromSelector(`[rel="gallery"]`);
                                //const images = galleryFromSelector(`[rel="g2"]`);
                            }
                        })                    
                    
                    .fail(function(){  $('#page-widget-'+widget).html('error');  });
                }
                $(document).ready(function() {
                    $('body').on('click','#gallery-zoom-out',function(e){ $('#gallery').css('column-count',parseInt($('#gallery').css('column-count')) + 1 ); });
                    $('body').on('click','#gallery-zoom-in' ,function(e){ $('#gallery').css('column-count',parseInt($('#gallery').css('column-count')) - 1 ); });
                    $('body').on('click','#gallery-reload'  ,function(e){ loadWidget('gallery'); });
                    $('body').on('click','#files-reload'    ,function(e){ loadWidget('files');    });             
                });

            </script>
            <?php
        }

        if(!$documents && $files)  { 
            ?><script>loadWidget('files');</script><?php
        }

        if(!$documents && $gallery) {
            ?><script>loadWidget('gallery');</script><?php
        }

        if (MODULE=='page' && $item_code)    echo $item_code;
        if (MODULE=='page' && $item_code_js) echo '<script type="text/javascript">'.$item_code_js.'</script>';

    //}


    if ($editable && MODULE=='page' && CFG::$vars['widget']['mapa-web']) { 
        
        ?>
        <style>#sidebar-links{position:fixed;left:0;width:250px;border:2px solid #0a769e;background-color:#fdfdfd;z-index:10;display:none;}</style>
        <div id="sidebar-links" class="shadow"></div>
        <script>
            $(function() { 
                // console.log('CURRENT_ITEM','<?=Menu::$current_item?>');
                setTimeout(() => {                   
                    $('#sidebar-links').show().load('mapa-web/html/item=<?=Menu::$current_item?>');  
                }, 1000);
            }); 
        </script>
        <?php

    } 

    if($editable && $_ID_) {
        
        ?>
        <script>
            $(function() {
                ImageEditor.editable_images('.editable-image','/page/ajax/id='+_ID_+'/op=image-crop');  
            });
        </script>
        <?php

    }