            var php_viewer_markup = '<div id="pdf_viewer" class="pdf_viewer shadow">'
                                  +     '<img id="pdf_viewer_icon" src="_images_/filetypes/icon_pdf.png">'
                                  +     '<span id="pdf_viewer_title"></span> '
                                  +     '<a id="pdf_viewer_maximize" title="Maximizar"><i class="fa fa-window-maximize"></i></a>'
                                  +     '<a id="pdf_viewer_close" class="pdf_viewer_close" title="Cerrar"><i class="fa fa-window-close"></i></a>'
                                  +     '<div id="pdf_viewer_file"></div>'
                                  + '</div>';

            if (typeof pdf_viewer_initialized === 'undefined') var pdf_viewer_initialized = false;
            if (typeof pdf_viewer_target      === 'undefined') var pdf_viewer_target = false;
          //console.log('loading pdf_viewer script');
            var pdf_viewer_full_screen = false;     
          //var pdf_viewer_initialized = false;     
            var pdf_viewer_instances   = 0;     
                var _this = false;

            function pdf_render (url,n,scale) {
                eval("var pdfDoc_"+n+"=null;var pageNum_"+n+"=1;var pageRendering_"+n+"=false;var pageNumPending_"+n+"=null;var scale_"+n+"="+scale+";var canvas_"+n+"=document.getElementById('the-canvas_"+n+"');var ctx_"+n+"=canvas_"+n+".getContext('2d');document.getElementById('prev_"+n+"').addEventListener('click',onPrevPage_"+n+");document.getElementById('next_"+n+"').addEventListener('click',onNextPage_"+n+");PDFJS.getDocument('"+url+"').then(function(pdfDoc_){pdfDoc_"+n+"=pdfDoc_;document.getElementById('page_count_"+n+"').textContent=pdfDoc_"+n+".numPages;renderPage_"+n+"(pageNum_"+n+");});function renderPage_"+n+"(num) {pageRendering_"+n+"=true;pdfDoc_"+n+".getPage(num).then(function(page){var viewport_"+n+"=page.getViewport(scale_"+n+");canvas_"+n+".height=viewport_"+n+".height;canvas_"+n+".width=viewport_"+n+".width;var renderContext_"+n+"={canvasContext:ctx_"+n+",viewport:viewport_"+n+"};var renderTask_"+n+"=page.render(renderContext_"+n+");renderTask_"+n+".promise.then(function(){pageRendering_"+n+"=false;if (pageNumPending_"+n+"!==null){renderPage_"+n+"(pageNumPending_"+n+");pageNumPending_"+n+"=null;} });});document.getElementById('page_num_"+n+"').textContent=pageNum_"+n+";}function queueRenderPage_"+n+"(num){if (pageRendering_"+n+"){pageNumPending_"+n+"=num;}else{renderPage_"+n+"(num);}}function onPrevPage_"+n+"(){if(pageNum_"+n+"<=1){return;}pageNum_"+n+"--;queueRenderPage_"+n+"(pageNum_"+n+");}function onNextPage_"+n+"(){if(pageNum_"+n+">=pdfDoc_"+n+".numPages){return;}pageNum_"+n+"++;queueRenderPage_"+n+"(pageNum_"+n+");}");
            }

            $(function() {     

                var old_top = 0;


                if(!pdf_viewer_initialized){
                    pdf_viewer_initialized = true;     

                    $('body').on('click','.open_file_pdf',function(e){
                        $('body > *:not(#pdf_viewer)').addClass('blurred');
                        console.log('.open_file_pdf',pdf_viewer_instances);
                        if(pdf_viewer_instances>0) return false;
                        pdf_viewer_instances++;
                        console.log('.open_file_pdf OK',pdf_viewer_instances);
                        

                        _this = $(this);//.attr('id');


                        if($('#pdf_viewer').length<1)
                            $('body').append(php_viewer_markup);

                        //console.log('_this',_this);
/*
                        old_top = $(this).offset().top
                        console.log('old_top',old_top);
                        if(old_top<=$( window ).height()) old_top =0;
                        console.log('old_top',old_top);
                  */
                        /***
                        if($('#modalformBox_1').length>0){
                            //old_top = $('#modalformBox_1').offset().top
                            $('#modalformBox_1').append(php_viewer_markup);
                        }else if(pdf_viewer_target && $(this).closest(pdf_viewer_target).length>0){
                            //old_top = $(this).closest(pdf_viewer_target).offset().top
                            $(this).closest(pdf_viewer_target).append(php_viewer_markup);
                        }else if($(this).closest('.datatable').length>0){
                            //old_top = $(this).closest('.datatable').offset().top
                            $(this).closest('.datatable').append(php_viewer_markup);
                        }else{
                            //old_top = $(this).closest('div').offset().top
                            $(this).closest('div').append(php_viewer_markup);
                        }
                        **/
                        

                        // increment file download counter w/ ajax
                        e.preventDefault();
                        // console.log( 'HREF', $(this).data('href') );
                        $('#pdf_viewer').fadeIn(); //('fast');
                        PDFObject.embed($(this).data('href'), "#pdf_viewer_file");
                        $('#pdf_viewer_title').html($(this).data('title'));


                        //if($('#modalformBox_1').length>0)
                        //$('#pdf_viewer').detach().appendTo('#modalformBox_1');

                        //if (!pdf_viewer_full_screen) 
                        //$('#pdf_viewer_maximize').click();

        //                $('html, body').animate({ scrollTop:0 }, 500);

                    });

                    $('body').on('click','.pdf_viewer_close',function(e){

                        $('body *').removeClass('blurred');

                        pdf_viewer_instances--;
                        console.log('#pdf_viewer_close');
                        $('.pdf_viewer').fadeOut();
            //            $('html, body').animate({ scrollTop:old_top }, 500);
                        //shake(_this);
                        setTimeout(()=>{_this.parent()/*.closest('li')*/.highlight()},500);
                    });

                    $('body').on('click','#pdf_viewer_maximize',function(e){
                        console.log('pdf_viewer_maximize');
                        pdf_viewer_full_screen = !pdf_viewer_full_screen;
                        e.stopPropagation();
                        if (pdf_viewer_full_screen) {
                           //$('#pdf_viewer').css('position','fixed').css('z-index','11');
                           $('#pdf_viewer').addClass('maximized');
                           $('#pdf_viewer_maximize .fa').removeClass('fa-window-maximize').addClass('fa-window-restore').attr('title','Restaurar');
                        }else{
                           $('#pdf_viewer').removeClass('maximized');
                            //$('#pdf_viewer').css('position','absolute').css('z-index','3');
                           $('#pdf_viewer_maximize .fa').removeClass('fa-window-restore').addClass('fa-window-maximize').attr('title','Maximizar');
                        }
                    });

                }
            });
