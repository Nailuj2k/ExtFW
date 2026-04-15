        var page_editing = false;

        function getClassNode(element) {  for (var i = element.attributes.length; i--;)    if (element.attributes[i].nodeName === 'class')      return element.attributes[i];}
        function removeClass(classNode, className) {  var index, classList = classNode.value.split(' ');  if ((index = classList.indexOf(className)) > -1) {    classList.splice(index, 1);    classNode.value = classList.join(' ');  }}
        function hasClass(classNode, className) {  return classNode.value.indexOf(className) > -1;}
        function addClass(classNode, className) {  if (!hasClass(classNode, className))    classNode.value += ' ' + className;}
        function hideElement(el) { el.animate({opacity: '0'}, 150, function(){ el.animate({height: '0px'}, 400, function(){ el.remove(); }); }); }
        var removeResizeFrame = function () {
            document.querySelectorAll(".resize-frame,.resizer,.img-toolbox,.reset-class").forEach((item) => item.parentNode.removeChild(item));
        };

        /*
        Code by celsowm [https://stackoverflow.com/users/284932/celsowm] 
        https://stackoverflow.com/questions/19165944/contenteditable-image-resize-in-chrome-what-is-the-best-solution
        Not much modified - Nailuj 2022
        Added tollbar w/ align, reset & delete buttons - Nailuj 2022
        Reset function commented - Nailuj 2022
        */

        function enableImageResizeInDiv(id) {
            /*
            if (!(/chrome/i.test(navigator.userAgent) && /google/i.test(window.navigator.vendor))) {
                return;
            }
            */
            if(!page_editing) return false;

            let editor = document.getElementById(id);
            let resizing = false;
            let currentImage;
            let createDOM = function (elementType, className, styles, content=false) {
                let ele = document.createElement(elementType);
                ele.className = className;
                setStyle(ele, styles);
                if(content) ele.innerHTML  =content;
                return ele;
            };
            let setStyle = function (ele, styles) {
                for (key in styles) { ele.style[key] = styles[key]; }
                return ele;
            };
            let offset = function offset(el) {
                const rect = el.getBoundingClientRect(),
                scrollLeft = window.pageXOffset || document.documentElement.scrollLeft,
                scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                return { top: rect.top + scrollTop, left: rect.left + scrollLeft }
            };
            let clickImage = function (img) {
                removeResizeFrame();
                if(!page_editing) return false;
                currentImage = img;
                const imgHeight = img.offsetHeight;
                const imgWidth = img.offsetWidth;
                const imgPosition = { top: img.offsetTop, left: img.offsetLeft };
                const editorScrollTop = editor.scrollTop;
                const editorScrollLeft = editor.scrollLeft;
                const top = imgPosition.top - editorScrollTop - 1;
                const left = imgPosition.left - editorScrollLeft - 1;

                editor.append(createDOM('span', 'resize-frame', { position: 'absolute', 
                                                                top: (top + imgHeight - 10) + 'px', 
                                                                left: (left + imgWidth - 10) + 'px', 
                                                                margin: '10px', 
                                                                border: 'solid 3px blue', 
                                                                width: '6px', 
                                                                height: '6px', 
                                                                cursor: 'se-resize', 
                                                                zIndex: 2 }));
                editor.append(createDOM('span', 'resizer top-border',    {position: 'absolute', top: (top  +1          ) + 'px', left: (left           ) + 'px',borderTop:   '1px dashed grey', width: imgWidth + 'px', height: '0px'           }));
                editor.append(createDOM('span', 'resizer left-border',   {position: 'absolute', top: (top  +1          ) + 'px', left: (left           ) + 'px',borderLeft:  '1px dashed grey', width: '0px',           height: imgHeight + 'px'}));
                editor.append(createDOM('span', 'resizer right-border',  {position: 'absolute', top: (top  +1          ) + 'px', left: (left + imgWidth) + 'px',borderRight: '1px dashed grey', width: '0px',           height: imgHeight + 'px'}));
                editor.append(createDOM('span', 'resizer bottom-border', {position: 'absolute', top: (top + imgHeight+1) + 'px', left: (left           ) + 'px',borderBottom:'1px dashed grey', width: imgWidth + 'px', height: '0px'           }));
                
                editor.append(createDOM('span', 'img-toolbox', { position: 'absolute', 
                                                                top: (top - 10 + 2) + 'px',
                                                                left: (left + imgWidth - 10 + 2 ) + 'px', 
                                                                margin: '10px', 
                                                                zIndex: 1 },`<i class="fa fa-eraser" title="${str_reset_styles}"></i><i class="fa fa-align-left"></i><i class="fa fa-align-right"></i><i class="fa fa-align-center"></i><!--<i class="fa fa-align-justify"></i>--><i class="fa fa-remove"  title="${str_delete_image}"></i>`));
                
                /*
                editor.append(createDOM('span', 'reset-class', { position: 'absolute', 
                                                                top: (top + imgHeight - 22) + 'px',
                                                                left: (left + imgWidth - 10) + 'px', 
                                                                margin: '10px', 
                                                                border: 'solid 3px green', 
                                                                width: '6px', 
                                                                height: '6px', 
                                                                cursor: 'pointer', 
                                                                zIndex: 1 }));
                */
                document.querySelector('.resize-frame').onmousedown = () => {
                    resizing = true;
                    return false;
                };
                /* 
                document.querySelector('.reset-class').onmousedown = () => {
                    console.log('click');
                    currentImage.style.width = 'auto';
                    currentImage.style.height = 'auto';
                    return false;
                };
                */     
                document.querySelector('.img-toolbox .fa-eraser').onmousedown = () => {
                    currentImage.classList.remove('float-center');
                    currentImage.classList.remove('float-left');
                    currentImage.classList.remove('float-right');
                    currentImage.style.width = 'auto';
                    currentImage.style.height = 'auto';
                    return false;
                };
                document.querySelector('.img-toolbox .fa-align-left').onmousedown = () => {
                    currentImage.classList.remove('float-center');
                    currentImage.classList.remove('float-right');
                    currentImage.classList.add('float-left');
                    return false;
                };

                document.querySelector('.img-toolbox .fa-align-right').onmousedown = () => {
                    currentImage.classList.remove('float-center');
                    currentImage.classList.remove('float-left');
                    currentImage.classList.add('float-right');
                    return false;
                };

                document.querySelector('.img-toolbox .fa-align-center').onmousedown = () => {
                    currentImage.classList.remove('float-left');
                    currentImage.classList.remove('float-right');
                    currentImage.classList.add('float-center');
                    return false;
                };

                document.querySelector('.img-toolbox .fa-remove').onmousedown = () => {
                    currentImage.remove();
                    return false;
                };

                editor.onmouseup = () => {
                    if (resizing) {
                        currentImage.style.width = document.querySelector('.top-border').offsetWidth + 'px';
                        currentImage.style.height = document.querySelector('.left-border').offsetHeight + 'px';
                    //refresh();
                    //currentImage.click();
                        resizing = false;
                    }
                };
                editor.onmousemove = (e) => {
                    if (currentImage && resizing) {
                        let height = e.pageY - offset(currentImage).top;
                        let width = e.pageX - offset(currentImage).left;
                        height = height < 1 ? 1 : height;
                        width = width < 1 ? 1 : width;
                        const top = imgPosition.top - editorScrollTop - 1;
                        const left = imgPosition.left - editorScrollLeft - 1;
                        setStyle(document.querySelector('.resize-frame'),  { top: (top + height - 10) + 'px', left: (left + width - 10) + "px" });
                    //setStyle(document.querySelector('.reset-class'),   { top: (top + height - 22) + 'px', left: (left + width - 10) + "px" });
                        setStyle(document.querySelector('.img-toolbox'),   { top: (top - 10 + 2) + 'px', left: (left + width - 10 + 2) + "px" });
                        setStyle(document.querySelector('.top-border'),    { width: width + "px" });
                        setStyle(document.querySelector('.left-border'),   { height: height + "px" });
                        setStyle(document.querySelector('.right-border'),  { left: (left + width) + 'px', height: height + "px" });
                        setStyle(document.querySelector('.bottom-border'), { top: (top + height + 1 ) + 'px',  width: width + "px" });
                    }
                    return false;
                };
            };
            let bindClickListener = function () {
                editor.querySelectorAll('img').forEach((img, i) => { img.onclick = (e) => { if (e.target === img) { clickImage(img); } }; });
            };
            /** 
            let refresh = function () {
                bindClickListener();
                removeResizeFrame();
                if (!currentImage) { return; }
                let img = currentImage;
                let imgHeight = img.offsetHeight;
                let imgWidth = img.offsetWidth;
                let imgPosition = { top: img.offsetTop, left: img.offsetLeft };
                let editorScrollTop = editor.scrollTop;
                let editorScrollLeft = editor.scrollLeft;
                const top = imgPosition.top - editorScrollTop - 1;
                const left = imgPosition.left - editorScrollLeft - 1;
                editor.append(createDOM('span', 'resize-frame', { position: 'absolute', top: (top + imgHeight) + 'px', left: (left + imgWidth) + 'px', border: 'solid 2px red',   width: '6px',           height: '6px', cursor: 'se-resize', zIndex: 1 }));
            //editor.append(createDOM('span', 'reset-class',  { position: 'absolute', top: (top + imgHeight -12) + 'px', left: (left + imgWidth) + 'px', border: 'solid 2px green', width: '6px',           height: '6px', cursor: 'se-resize', zIndex: 1 }));
                editor.append(createDOM('span', 'resizer',      { position: 'absolute', top: (top            ) + 'px', left: (left           ) + 'px', border: 'dashed 1px grey', width: imgWidth + 'px', height: '0px'            }));
                editor.append(createDOM('span', 'resizer',      { position: 'absolute', top: (top            ) + 'px', left: (left + imgWidth) + 'px', border: 'dashed 1px grey', width: '0px',           height: imgHeight + 'px' }));
                editor.append(createDOM('span', 'resizer',      { position: 'absolute', top: (top + imgHeight) + 'px', left: (left           ) + 'px', border: 'dashed 1px grey', width: imgWidth + 'px', height: '0px'            }));
            };
            **/
            let reset = function () {
                if (currentImage != null) {
                    currentImage = null;
                    resizing = false;
                    removeResizeFrame();
                }
                bindClickListener();
            };
            editor.addEventListener('scroll', function () {
                reset();
            }, false); 

            editor.addEventListener('mouseup', function (e) {
                if (!resizing) {
                    const x = (e.x) ? e.x : e.clientX;
                    const y = (e.y) ? e.y : e.clientY;
                    let mouseUpElement = document.elementFromPoint(x, y);
                    if (mouseUpElement) {
                        let matchingElement = null;
                        if (mouseUpElement.tagName === 'IMG') {
                            matchingElement = mouseUpElement;
                        }
                        if (!matchingElement) {
                            reset();
                        } else {
                            clickImage(matchingElement);
                        }
                    }
                }
            });
            
        }  

        edit_visible=true;   // allow cursor keys in edit src

        function onChange(T, rowName, currentROW, rowValue){
            console.log('onchange',T,rowName,currentROW,rowValue);
        }
        /**
        function load_page(module,table,page,parent,row){
            console.log('load_page',module,table,page,parent,row);
        }
        **/

        function onTableEvent(data){
            
            // console.log('onTableEvent',data);

            if        (data.table=='CLI_PAGES_FILES'||data.table==_TB_PREFIX_ + '_' + _TB_NAME_+'_FILES'){ //}          'NOT_NEWS_FILES'||data.table=='BLG_BLOG_FILES'){

                if      (data.event=='update'){

                    let row = data.row;

                    $('#row-id-'+row+' .gallery-desc').text(data.field)  //FIX.highlight();
                    
                    let url =  _MODULE_ + '/ajax/op=getfield/table=' + data.table + '/field=NAME/key=ID/value=';

                    console.log('onTableEvent','UPDATE',url+row);
                    
                    $.get(url+row,function(data){
                        console.log('data',data);
                        if(data.error==0){      
                            setTimeout(function(){
                                $('#row-id-'+row+' .gallery-desc').text(data.field) //FIX.highlight();
                            },300);
                        }else{
                        }
                    },'json')
                    .done(function()     {  })
                    .fail(function(data) {  console.log(data);     })
                    .always(function()   {  });
                    
                    if(data.local_file){
                        let rnd = Math.random() * 10;

                        if(data.ext != data.old.EXT){
                            console.log('TYPE CHANGED', data.old.EXT, data.ext);
                        }   
            
                        if(['jpg','jpeg','png','webp','gif'].indexOf(data.ext) != -1){
                            let img_src = _MEDIA_FILES+'/'+_ID_+'/.tn_'+data.local_file+'?ver='+rnd;
                            console.log('IMG',data.local_file,img_src);
                            $('#row-id-'+row+' img').attr('src',img_src)  //FIX .highlight();                    
                        }
                    
                    }
                        
                }else if(data.event=='insert'){

                    let rnd = Math.random() * 10;
                    let local_file = data.local_file;
                    let the_filename = data.the_filename;
                    let row_id = data.next_insert_id;

                    if(['jpg','jpeg','png','webp','gif'].indexOf(data.ext) != -1){
                                let html_img = `<div class="item" id="row-id-${row_id}">`
                                            + `<a rel="gallery" class="swipebox" href="${_MEDIA_FILES}/${_ID_}/${local_file}?ver=00000000" title=""><img src="${_MEDIA_FILES}/${_ID_}/.tn_${local_file}?ver=${rnd}" alt="${the_filename}"></a>`
                                            + `<span class="gallery-image-desc gallery-desc">${the_filename}</span>`
                                            + `<i data-id="${row_id}" class="edit_page_files fa fa-edit" title="${str_edit_file}" style="color:#007fad;"></i>`
                                            + `<i data-id="${row_id}" class="dele_page_files fa fa-trash-o" title="${str_delete_file}" style="color:#f50a51;"></i>`
                                            + `</div>`;
                                $('#gallery').append(html_img);
                    }else{
                        let html_img = `<li id="row-id-${row_id}">`;
                        if     (data.ext =='pdf')  html_img += `<a data-href="${_MEDIA_FILES}/${_ID_}/${local_file}"  class="pdf open_file_pdf gallery-desc" title="${local_file}">${local_file}</a>`;     
                        else if(data.ext =='epub') html_img += `<a class="file-epub gallery-desc" href="javascript:load_epub('${_MEDIA_FILES}/${_ID_}/${local_file}')" title="${local_file}">${local_file}</a>`;          
                        else                       html_img += `<a class="gallery-desc" href="${_MEDIA_FILES}/${_ID_}/${local_file}" title="${local_file}">${local_file}</a>`
                                                            + ` <i data-id="${row_id}" class="edit_page_files fa fa-edit" title="${str_edit_file}" style="color:#007fad;"></i>`
                                                            + ` <i data-id="${row_id}" class="dele_page_files fa fa-trash-o" title="${str_delete_file}" style="color:#f50a51;"></i>`;
                        html_img += '</li>';
                        $('#file-list ul').append(html_img);
                    }

                    setTimeout(function(){
                        //console.log('highlight','#row-id-'+row_id+' img');
                        //FIX $('#row-id-'+row_id+' img').highlight();                    
                    },300);
                    
                }
            }else if  (data.table=='CLI_PAGES'){
                console.log('data',data);
                location.reload();                //CHECK this
            }
        }

        var fileobj;

        function upload_file(e) {
            e.preventDefault();
            fileobj = e.dataTransfer.files[0];
            ajax_file_upload(fileobj);
        }

        function drag_over(e) {
            //e.preventDefault();
            $('#drag_upload_file').addClass('over_drag'); //.show();
            return false;
        }

        function drag_leave(e) {
            //e.preventDefault();
            $('#drag_upload_file').removeClass('over_drag'); //.show();
            return false;
        }

        function file_explorer() {
            document.getElementById('selectfile').click();
            document.getElementById('selectfile').onchange = function() {
                fileobj = document.getElementById('selectfile').files[0];
                ajax_file_upload(fileobj);
            };
        }
                
        function ajax_file_upload(file_obj) {
            if(file_obj != undefined) {
                var form_data = new FormData();                  
                form_data.append('file', file_obj);
                $.ajax({
                    type: 'POST',
                    url: _URL_AJAX_+'/op=save_file/id='+_ID_,
                    contentType: false,
                    processData: false,
                    dataType: "json",
                    data: form_data,
                    success:function(response) {
                        //console.log(response);
                        //console.log(response.thumb);
                        $('#files-gallery').append(response.html); //'<img src="'+response.url+'">');
                        //$('#files-gallery').append('<img src="'+response.thumb+'">');
                        //console.log(response.msg);
                        showMessageInfo(response.msg);
                        $('#selectfile').val('');
                        $('#drag_upload_file').removeClass('over_drag');
                        setTimeout(function(){
                            $('#files-gallery .gallery-item:last-child') //FIX .highlight();
                        },500);
                    }
                });
            }
        }

        $(document).ready(function() {
            var editor = false;
            var editor_css = false;
            var editor_js = false;
            var editor_code = false;

            //if(_CODE_EDITOR_=='monaco'){
            //    const monaco_editor = new MonacoEditor();
            //}

            $('#edit_page_advanced').click(function(e){

                e.preventDefault()
                var url = _MODULE_ =='page'
                        ? '/control_panel/ajax/op=edit/table=CLI_PAGES/id='+_ID_+'/target=module'
                        : '/' + _MODULE_ + '/ajax/op=edit/table=' + _TB_PREFIX_ + '_' + _TB_NAME_ + '/id=' + _ID_ + '/target=module';
                console.log('URL_AVD_EDIT',url);
                $.modalform({ 'title' : `${str_edit} (${str_row} ${_ID_})`, 'url': url  });
                return false;
            });

            $('body').on('click','#gallery-add-files,#files-add-files',function(e){
                let url = _MODULE_ == 'page'
                        ?'/page/ajax/op=add/table=CLI_PAGES_FILES/parent='+_ID_
                        :'/' + _MODULE_ + '/ajax/op=add/table=' + _TB_PREFIX_ + '_' + _TB_NAME_ + '_FILES/parent='+_ID_;
                console.log(url);
                $.modalform({ 'title' : str_add_files, 'url': url }, function(accept) {  });
            });

            $('body').on('click','.edit_page_files',function(e){
                e.preventDefault();
                let id = $(this).data('id');
                let url = _MODULE_ == 'page'
                        ?'/page/ajax/op=edit/table=CLI_PAGES_FILES/id='+id
                        :'/' + _MODULE_ + '/ajax/op=edit/table=' + _TB_PREFIX_ + '_' + _TB_NAME_ + '_FILES/id='+id;
                console.log('.edit_page_files click',url);
                $.modalform({ 'title' : str_edit, 'url': url });
                return false;
            });

            $('body').on('click','.dele_page_files',function(e){
                let id = $(this).data('id');
                let fname = $(this).closest('.item').find('a').attr('title');//$(this).data('id');
                let url = _MODULE_ == 'page'
                        ?'/page/ajax/op=delete/table=CLI_PAGES_FILES/id='+id
                        :'/' + _MODULE_ + '/ajax/op=delete/table=' + _TB_PREFIX_ + '_' + _TB_NAME_ + '_FILES/id='+id;
                $.modalform({'title' : str_delete_file, 'html':`${str_delete_file} ${id} ${fname}`, 'buttons':'ok cancel'}, function(accept) {
                    if(accept) {
                        //$.modalform({ 'title' : 'Eliminar', 'url': url });
                        $.get(url,function(data){
                            console.log('data',data);
                            if(data.error==0){    
                                showMessageInfo(data.msg);  
                                hideElement($('#row-id-'+id));
                            }else{
                            }
                        },'json')
                        .done(function()     {  })
                        .fail(function(data) {  console.log(data);     })
                        .always(function()   {  });

                    }
                });
            });

            $("#files-button,#files-panel  #files-panel-handle").click(function () {
                var ww = $(window).width();
                var fw = $("#files-panel").outerWidth();
                var pos = $("#files-panel").offset();
                var hidden = pos['left']>ww-1;
                //console.log($(window).width(),$("#files-panel").outerWidth(),pos,hidden);
                if(hidden) {// $("#files-panel").css('right','0px');  else $("#files-panel").css('right','-100px');
                    $("#files-panel")[0].style.left = (ww-fw)+'px';
                    $('#files-panel-handle>div').html('&raquo;');//.css('text-align','right');
                }else{
                    $("#files-panel")[0].style.left = (ww-0)+'px';
                    $('#files-panel-handle>div').html('&laquo;');//.css('text-align','left')
                }
            });

            $('#edit-buttons').draggable();           
            
            //moveElementTo -85 -383 null 35 null 333 50 50
            function moveElementTo(element,bottom=50,right=50) {
                
                var windowHeight = $(window).height();
                var lineHeight = element.height();
                var windowWidth = $(window).width();
                var lineWidth = element.width();
                var desiredBottom = bottom;
                var desiredRight = right;
                
                var newPositionTop = windowHeight - (lineHeight + desiredBottom);
                var newPositionRight = windowWidth - (lineWidth + desiredRight);
                if(console_log) console.log('moveElementTo',newPositionTop,newPositionRight,windowHeight,lineHeight,windowWidth,lineWidth,desiredBottom,desiredRight);
                element.animate({top:newPositionTop,left:newPositionRight,opacity:1},1000,function (){ });
                
            }

            moveElementTo($('#edit-buttons'))
            
            var edit_source = false;
            var prev_text = $('#content_'+_NAME_).html();
            if (_MODULE_=='page') { 
                var prev_text_css  = $('#edit_'+_NAME_+'_css') .data('reset'); // 'item_code_css';
                var prev_text_js   = $('#edit_'+_NAME_+'_js')  .data('reset'); // 'item_code_js';
                var prev_text_code = $('#edit_'+_NAME_+'_code').data('reset'); // 'item_code';
            }

            function IsSafari() {
              var is_safari = /apple/i.test(navigator.vendor);  //navigator.userAgent.toLowerCase().indexOf('safari/') > -1;
              return is_safari;
            }                       //  mozilla/5.0 (windows nt 10.0; win64; x64) applewebkit/537.36 (khtml, like gecko) chrome/90.0.4430.93 safari/537.36

            $( "#cancel_text" ).click(function() {
                page_editing = false;
                $("#save_text" ).hide('fast');
                $("#cancel_text" ).hide('fast');
                $("#edit_page").show('fast');
                $("#edit_page_source").show('fast');
                $("#edit_page_advanced").show('fast');                          //ADVANCED
                $(".not_edit_page" ).show('fast');
                $("#files-panel").hide('fast');  //#drop_file_zone
                //$('.edit-image-link').show();
                if( edit_source){
                    //console.log(prev_text);
                    if(_CODE_EDITOR_=='ace'){
                        editor.setValue(prev_text,-1); 
                        editor.resize();
                    }
                  //editor_css.setValue(prev_text_css,-1); 
                    $('#content_'+_NAME_).show();
                    $('#edit_'+_NAME_).val(prev_text).hide();  //html(contents);
                  //FIX$('#code_'+_NAME_).hide();
                    if (_MODULE_=='page') {
                        if(_CODE_EDITOR_=='ace'){
                            editor_css.setValue( prev_text_css , -1);
                            editor_css.resize();
                            editor_js.setValue( prev_text_js , -1);
                            editor_js.resize();
                            editor_code.setValue( prev_text_code , -1);
                            editor_code.resize();
                        }
                        $('#edit_'+_NAME_+'_css') /*.val(prev_text_css) */.hide();  //html(contents);
                        $('#edit_'+_NAME_+'_js')  /*.val(prev_text_js)  */.hide();  //html(contents);
                        $('#edit_'+_NAME_+'_code')/*.val(prev_text_code)*/.hide();  //html(contents);
                      //FIX$('#code_'+_NAME_+'_css').hide();
                        $('#ftabs-code-tabs').hide();
                    } 
                    edit_source = false;
                }else{
                    $('#content_'+_NAME_).attr('contenteditable', false);    
                    $('#toolbar-buttons').hide('fast');   
                    $('#content_'+_NAME_).html(prev_text);
                }
            });

            // These four functions from Liam  <https://stackoverflow.com/users/3714181/liam>
            // inhttps://stackoverflow.com/questions/6249095/how-to-set-the-caret-cursor-position-in-a-contenteditable-element-div
            function createRange(node, chars, range) {
                if (!range) {
                    range = document.createRange()
                    range.selectNode(node);
                    range.setStart(node, 0);
                }

                if (chars.count === 0) {
                    range.setEnd(node, chars.count);
                } else if (node && chars.count >0) {
                    if (node.nodeType === Node.TEXT_NODE) {
                        if (node.textContent.length < chars.count) {
                            chars.count -= node.textContent.length;
                        } else {
                            range.setEnd(node, chars.count);
                            chars.count = 0;
                        }
                    } else {
                       for (var lp = 0; lp < node.childNodes.length; lp++) {
                            range = createRange(node.childNodes[lp], chars, range);

                            if (chars.count === 0) {
                                break;
                            }
                        }
                    }
                } 

                return range;
            };

            function setCurrentCursorPosition(chars,Id) {
                if (chars >= 0) {
                    var selection = window.getSelection();

                    range = createRange(document.getElementById(Id).parentNode, { count: chars });

                    if (range) {
                        range.collapse(false);
                        selection.removeAllRanges();
                        selection.addRange(range);
                    }
                }
            };

            function isChildOf(node, parentId) {
                while (node !== null) {
                    if (node.id === parentId) {
                        return true;
                    }
                    node = node.parentNode;
                }

                return false;
            };

            function getCurrentCursorPosition(parentId) {
                var selection = window.getSelection(),
                    charCount = -1,
                    node;

                if (selection.focusNode) {
                    if (isChildOf(selection.focusNode, parentId)) {
                        node = selection.focusNode; 
                        charCount = selection.focusOffset;

                        while (node) {
                            if (node.id === parentId) {
                                break;
                            }

                            if (node.previousSibling) {
                                node = node.previousSibling;
                                charCount += node.textContent.length;
                            } else {
                                 node = node.parentNode;
                                 if (node === null) {
                                     break
                                 }
                            }
                       }
                  }
               }

                return charCount;
            };

            function inserHTML(html){
                $('#content_'+_NAME_).focus();
                let pos = getCurrentCursorPosition('content_'+_NAME_);
                let length =$('#content_'+_NAME_).text().length;
                if(pos<1) setCurrentCursorPosition(length,'content_'+_NAME_);
                document.execCommand('insertHTML', false, html);  
            }

            function insertVIDEO(id,provider){
                let html = false;
                if(provider=='youtube')
                   html = '<div class="rvideo"><iframe src="https://www.youtube.com/embed/'+id+'" frameborder="0" allowfullscreen="allowfullscreen"></iframe></div>'; //autoplay=1&controls=0&showinfo=0&autohide=1
                else if(provider=='vimeo')
                   html = '<div class="rvideo"><iframe src="https://player.vimeo.com/video/'+id+'" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe><7div>';
                if(html) inserHTML(html);         
            }

            $( ".not_edit_page" ).click(function() { 
                show_alert( $('#edit-buttons'), str_this_entry_contains_code,10000 );
            });
            
            $( "#edit_page" ).click(function() { 
                //if(IsSafari()){
                //    show_error( $('#edit-buttons'), 'La edición inline está desactivada para Safari. Debe usar Chrome, Firefox, o Edge. Quejas a Steve Jobs.',10000 );
                //    exit;
                //}
                edit_source = false;    
                console.log('EDIT_PAGE');
                $("#save_text" ).show('fast');
                $("#cancel_text" ).show('fast');
                $("#edit_page" ).hide('fast');
                $("#edit_page_source" ).hide('fast');
                $("#edit_page_advanced").hide('fast');                   //ADVANCED
                $(".not_edit_page" ).hide('fast');
                $("#files-panel" ).show('fast');  //#drop_file_zone
                //$('.edit-image-link').hide();
                prev_text = $('#content_'+_NAME_).html();

                var f_url = _MODULE_=='page' 
                          ? '/control_panel/ajax/op=getfield/field=' + _TEXT_FNAME_ + '/field_alt=' + _TEXT_FNAME_A + '/table=CLI_PAGES/key=item_id/value='+_ID_
                          : '/'+_MODULE_ +'/ajax/op=getfield/field=' + _TEXT_FNAME_ + '/field_alt=' + _TEXT_FNAME_A + '/table=' + _TB_PREFIX_ + '_' + _TB_NAME_ + '/key=' + _TB_PREFIX_ + '_ID/value='+_ID_;
                
                $.ajax({
                    url: f_url,
                    data: {'value':_ID_},
                    type:"POST",
                    dataType: "json",
                    beforeSend:function(data){ 
                        $('.ajax-loader').show();
                      //$('#result').html('Obteniendo código ...');   
                    }
                }).done(function(data){

                    if(_SESSION_LANG_!=_DEFAULT_LANG_ && data.error==1){
                        show_alert($('.inner-page'),_MSG_NO_TEXT_,'never');
                    }else {
                        $('#content_'+_NAME_).html(data.field);
                        $('#content_'+_NAME_).attr('contenteditable', true);
                        $('#toolbar-buttons').show('fast').draggable();   
                        $('#toolbar-buttons .btn-editor button').click(function(e) {
                            e.preventDefault();
                            switch($(this).data('role')) {
                               case 'h1':
                               case 'h2':
                               case 'h3':
                               case 'h4':
                               case 'p':
                               case 'pre':
                                   document.execCommand('formatBlock', false, $(this).data('role'));
                                   break;
                               default:
                                   let role = $(this).data('role');
                                   if(role=='insertImage'){
                                       console.log('role',role)
                                       /***********/
                                       var ww = $(window).width();
                                       var fw = $("#files-panel").outerWidth();
                                       var pos = $("#files-panel").offset();
                                       var hidden = pos['left']>ww-1;
                                       if(hidden) {
                                            $("#files-panel")[0].style.left = (ww-fw)+'px';
                                            $('#files-panel-handle>div').html('&raquo;');//.css('text-align','right');
                                       }else{
                                            $("#files-panel")[0].style.left = (ww-0)+'px';
                                            $('#files-panel-handle>div').html('&laquo;');//.css('text-align','left')
                                       }
                                       /*********************/
                                   }else if(role=='insertFile'){
                                       // $('#customer-profile').load('/control_panel/ajax/module=control_panel/op=view/table=CLI_USER/id='+_SESSION_userid_, function() {  

                                       document.execCommand('insertHTML', false, '<span>TEST</span>');    
                                       /**
                                       if($('#page-files').is(':visible')) {
                                           $('#page-files').hide()
                                       }else{
                                           let tbmodule = _MODULE_=='page'?'control_panel':_MODULE_;   
                                           let tbfiles  = _MODULE_=='page'?'CLI_PAGES_FILES':_TB_PREFIX_ + '_' + _TB_NAME_+'_FILES';   
                                           let url = '/'+tbmodule+'/ajax/op=show/table='+tbfiles+'/parent='+_ID_+'/target=inline';
                                           $('#page-files').load(url, function() { 
                                               $('#pager_'+tbfiles).html('JAU');
                                               $('#page-files').show()
                                           });
                                           // $.modalform({ 'title' : 'Archivos', 'url': url  });
                                       }
                                       */
                                   }else if(role=='insertVideo'){
                                       let url = prompt("YouTube/Vimeo url", '');
                                       url.match(/(http:|https:|)\/\/(player.|www.)?(vimeo\.com|youtu(be\.com|\.be|be\.googleapis\.com))\/(video\/|embed\/|watch\?v=|v\/)?([A-Za-z0-9._%-]*)(\&\S+)?/);

                                       if (RegExp.$3.indexOf('youtu') > -1) {
                                          var type = 'youtube';
                                          var id = RegExp.$6;
                                       } else if (RegExp.$3.indexOf('vimeo') > -1) {
                                          var type = 'vimeo';
                                          var id = RegExp.$6;
                                       }
                                    
                                       insertVIDEO(id,type);
                                   }else{
                                       let val = (typeof $(this).data('val') !== "undefined") 
                                              ? prompt("Value for " + $(this).data('role') + "?", $(this).data('val')) 
                                              : "";
                                       document.execCommand($(this).data('role'), false, (val || ""));
                                   }
                                   break;
                            }
                        });            
                        page_editing = true;
                        enableImageResizeInDiv('content_'+_NAME_);
                    } // if(_SESSION_LANG_!=_DEFAULT_LANG_ && data.error==1)

                }).fail(function(data){
                    // $('#result').html('error');
                }).always(function(){
                    $('.ajax-loader').hide(); 
                });
            });

            $( "#edit_page_source" ).click(function() { 
                
                var f_url = _MODULE_=='page' 
                          ? '/control_panel/ajax/op=getfield/field=' + _TEXT_FNAME_ + '/field_alt=' + _TEXT_FNAME_A + '/table=CLI_PAGES/key=item_id/value='+_ID_
                          : '/'+_MODULE_ +'/ajax/op=getfield/field=' + _TEXT_FNAME_ + '/field_alt=' + _TEXT_FNAME_A + '/table=' + _TB_PREFIX_ + '_' + _TB_NAME_ + '/key=' + _TB_PREFIX_ + '_ID/value='+_ID_;
                 
                console.log('URL_SRC_EDIT',f_url);
                //$('.edit-image-link').hide();

                $.ajax({
                    url: f_url,
                    data: {'value':_ID_},
                    type:"POST",
                    dataType: "json",
                    beforeSend:function(data){ 
                        $('.ajax-loader').show();
                      //$('#result').html('Obteniendo código ...');   
                    }
                }).done(function(data){

                    edit_source = true;
                    console.log('EDIT_PAGE_SOURCE');
                    $("#save_text" ).show('fast');
                    $("#cancel_text" ).show('fast');
                    $("#edit_page" ).hide('fast');
                    $("#edit_page_source" ).hide('fast');
                    $("#edit_page_advanced").hide('fast');              //ADVANCED
                    $(".not_edit_page" ).hide('fast');
                    $("#files-panel" ).show('fast');  //#drop_file_zone             
                    $('#content_'+_NAME_).hide();
                    // var url = '/control_panel/ajax/op=getfield/field=item_text/table=CLI_PAGES/key=item_id/value='+_ID_;
                    /**** FIX set url fields to current lang ***********************************************/

                    if(_SESSION_LANG_!=_DEFAULT_LANG_ && data.error==1){
                    //    show_alert($('.inner-page'),_MSG_NO_TEXT_,'never');
                    }
                    if(_CODE_EDITOR_=='ace'){
                        editor = ace.edit('edit_'+_NAME_);
                        editor.setValue(data.field); ///////////////////////////////////////////////////////FIX 
                        editor.setTheme("ace/theme/monokai");  // tomorrow
                        editor.getSession().setMode("ace/mode/html");
                        editor.setOption("wrap", true)
                        $('#edit_'+_NAME_).show();  
                        editor.resize();
                    }else if(_CODE_EDITOR_=='monaco'){
                        console.log('DATA.FIELD',data.field)
                        $('#edit_'+_NAME_).show(); //.val(data.field);  
                        monaco_editor.setValue('edit_'+_NAME_,data.field);
                    }
                    /**/
                    //$('#edit_'+_NAME_).show();  

                    if (_MODULE_=='page') {

                        $('#content_'+_NAME_+'_css').hide();
                        $('#content_'+_NAME_+'_js').hide();
                        $('#content_'+_NAME_+'_code').hide();

                        if(_CODE_EDITOR_=='ace'){
                            editor_css = ace.edit('edit_'+_NAME_+'_css');
                            editor_css.setTheme("ace/theme/monokai");  // tomorrow
                            editor_css.getSession().setMode("ace/mode/css");           //editor_css.setFontSize("13px")
                            editor_css.setOption("wrap", true)
                            editor_css.resize();
                            
                            editor_js = ace.edit('edit_'+_NAME_+'_js');
                            editor_js.setTheme("ace/theme/monokai");  // tomorrow
                            editor_js.getSession().setMode("ace/mode/javascript");           //editor_css.setFontSize("13px")
                            editor_js.setOption("wrap", true)
                            editor_js.resize();
                            
                            editor_code = ace.edit('edit_'+_NAME_+'_code');
                            editor_code.setTheme("ace/theme/monokai");  // tomorrow
                            editor_code.getSession().setMode("ace/mode/html");           //editor_css.setFontSize("13px")
                            editor_code.setOption("wrap", true)
                            editor_code.resize();
                        }
                        $('#edit_'+_NAME_+'_css').show();  //html(contents);
                        $('#edit_'+_NAME_+'_js').show();  //html(contents);
                        $('#edit_'+_NAME_+'_code').show();  //html(contents);
                        

                        $('#ftabs-code-tabs').show();
                    // console.log('editor_css',editor_css);
                    } 


                }).fail(function(data){
                    // $('#result').html('error');
                }).always(function(){
                    $('.ajax-loader').hide(); 
                });


            });

            if(_MODULE_=='page') { 
                $( "#clone_page" ).click(function() {
                    var clonepage_form = _LANG_ == 'es'
                    ?['<div style="text-align:left;margin:30px;">',
                    '<p>Va a crear una nueva página idéntica a esta, con el mismo contenido, que podrá editar después.<br />Necesita proporcionar un título distinto para la nueva.<br><br></p>',
                      '<label for="form_title">Título</label> <input type="text" size="40" name="form_title" id="form_title" placeholder="Título de la página"><br />',
                      '<div style="text-align:right;max-width:350px;margin:10px;">',
                      '<label for="form_addmenu">Añadir al menú</label> <input type="checkbox" name="form_addmenu" id="form_addmenu" checked><br />',
                      '<label for="form_submenu"> ... como subpágina</label> <input type="checkbox" name="form_submenu" id="form_submenu"><br />',
                      '</div>',
                      '<p>Deje marcado "Añadir al menú" si desea crear una entrada en el menú principal para acceder a ésta nueva página.<br />Marque "como subpágina" para que dicha entrada sea \'hija\' de la actual.</p>',
                    '</div>'].join('')
                    :['<div style="text-align:left;margin:30px;">',
                    '<p>You are about to create a new page identical to this one, with the same content, which you can edit later.<br />You need to provide a different title for the new page.<br><br></p>',
                    '<label for="form_title">Title</label> <input type="text" size="40" name="form_title" id="form_title" placeholder="Page title"><br />',
                    '<div style="text-align:right;max-width:350px;margin:10px;">',
                    '<label for="form_addmenu">Add to menu</label> <input type="checkbox" name="form_addmenu" id="form_addmenu" checked><br />',
                    '<label for="form_submenu"> ... as a subpage</label> <input type="checkbox" name="form_submenu" id="form_submenu"><br />',
                    '</div>',
                    '<p>Leave "Add to menu" checked if you want to create a main menu entry to access this new page.<br />Check "as a subpage" to make this entry a child of the current page.</p>',
                    '</div>'].join('');
                    
                    $.modalform({'title' : str_clone_page, 'html':clonepage_form, 'buttons':'ok cancel'}, function(accept) {
                        if(accept) {
                            let title =  $('#form_title').val();
                            let addmenu = $('#form_addmenu').is(":checked")?'1':'0';
                            let aschild = $('#form_submenu').is(":checked")?'1':'0';
                            if( title == ''){
                                showMessageError('¿Y el título?');
                            }else{
                                $.modalform({ 'html':str_are_you_sure, 'buttons':'ok cancel'}, function(accept) {
                                    if(accept) { 
                                        //showMessageInfo('Creando página '+title);
                                        $.ajax({
                                            url: _URL_AJAX_,
                                            data: {'op':'clone_page', id:_ID_,'title':title,'addmenu':addmenu,'aschild':aschild },
                                            type:"POST",
                                            dataType: "json",
                                            beforeSend:function(data){ 
                                                $('.ajax-loader').show();
                                                $('#result').html(str_cloning_page+' ...');   
                                            }
                                        }).done(function(data){
                                            // console.log('done',data);
                                            if(data.error==1){
                                                showMessageError(data.msg);
                                            }else{
                                                showMessageInfo(data.msg);
                                            } 
                                        }).fail(function(data){
                                            // console.log('fail',data);
                                            $('#result').html('ERROR: '+data.msg);
                                        }).always(function(){
                                            $('.ajax-loader').hide(); 
                                        });

                                    }else{
                                      showMessageError(str_bye);                                   
                                    }
                                });
                            }
                        } else { 
                            showMessageError(str_maybe_later);
                        }
                    });
                });
            }

            $( "#save_text" ).click(function() {
                page_editing = false;
                removeResizeFrame();
                //let editor_value = ;
                
                let editor_value = _CODE_EDITOR_=='monaco' ? monaco_editor.getValue('edit_'+_NAME_) : editor.getValue();

                console.log('_URL_AJAX_',_URL_AJAX_)
                console.log('_ID_', _ID_)
                console.log('_NAME_', _NAME_)
                //console.log('EDITOR_VALUE', editor_value)
                console.log('_SESSION_LANG_',_SESSION_LANG_)
                
                //return false;

                if( edit_source) var str = str2crypt( editor_value, _TOKEN_ );
                           else  var str = str2crypt( $('#content_'+_NAME_).html() , _TOKEN_ );
                let mydata = {'op':'save_page', id:_ID_,text:str };
                if (_MODULE_=='page') { 
                    if( edit_source){

                        let editor_value_css  = _CODE_EDITOR_=='monaco' ? monaco_editor.getValue('edit_'+_NAME_+'_css')  : editor_css .getValue() ;
                        let editor_value_js   = _CODE_EDITOR_=='monaco' ? monaco_editor.getValue('edit_'+_NAME_+'_js')   : editor_js  .getValue();
                        let editor_value_code = _CODE_EDITOR_=='monaco' ? monaco_editor.getValue('edit_'+_NAME_+'_code') : editor_code.getValue();

                        mydata.css  = str2crypt( editor_value_css,  _TOKEN_ );
                        mydata.js   = str2crypt( editor_value_js,   _TOKEN_ );
                        mydata.code = str2crypt( editor_value_code, _TOKEN_ );
                    }
                } 
                $.ajax({
                    url: _URL_AJAX_,
                    data: mydata,
                    type:"POST",
                    dataType: "json",
                    beforeSend:function(data){ 
                        $('.ajax-loader').show();
                        $('#result').html(str_saving+' ...');   
                    }
                }).done(function(data){
                    $('#result').html('');
                    $("#edit_page").show('fast');
                    $("#edit_page_source").show('fast');
                    $("#edit_page_advanced").show('fast'); //ADVANCED
                    $(".not_edit_page" ).show('fast');


                    var f_url = _MODULE_=='page' 
                              ? '/control_panel/ajax/op=getfield/field=' + _TEXT_FNAME_ + '/table=CLI_PAGES/key=item_id/value='+_ID_
                              : '/'+_MODULE_ +'/ajax/op=getfield/field=' + _TEXT_FNAME_ + '/table=' + _TB_PREFIX_ + '_' + _TB_NAME_ + '/key=' + _TB_PREFIX_ + '_ID/value='+_ID_;

                    let shortcoded_content = '';
                    $.ajax({
                        url: f_url+'/shortcodes=y',
                        data: {'value':_ID_},
                        type:"POST",
                        dataType: "json",
                        beforeSend:function(data){ 
                            $('.ajax-loader').show();
                          //$('#result').html(str_getting_code+' ...');   
                        }
                    }).done(function(data){
                        shortcoded_content = data.field
                        console.log('SHORTCODED.CONTENT',data.field);

                        $('#content_'+_NAME_).html( shortcoded_content  );

                        if( edit_source){

                            console.log('LOAD SHORTCODED SOURCE');
                            //$('#edit_'+_NAME_).hide();
                            
                        }else{
                        
                            console.log('LOAD SHORTCODED CONTENT ');
                            //$('#edit_'+_NAME_).val( shortcoded_content );
                            //$('#ftabs-code-tabs').hide();
                        }

                        ImageEditor.editable_images('.editable-image','/page/ajax/id='+_ID_+'/op=image-crop');  
                        if(_HIGHLIGHT_CODE_) {
                            //console.log('HIGHLIGHT CODE ENABLED');
                            Prism.highlightAll(); //CHECK 
                        }

                    }).fail(function(data){
                        // $('#result').html('error');
                    }).always(function(){
                        $('.ajax-loader').hide(); 
                        if (_MODULE_=='page') $('#ftabs-code-tabs').hide();
                                         else $('#edit_'+_NAME_).hide();
                        //$('.edit-image-link').show();

                    });
                    /*****
                    if( edit_source){
                        console.log('LOAD SHORTCODED SOURCE');
                        $('#content_'+_NAME_).html( 
                                                    _CODE_EDITOR_=='monaco' ? monaco_editor.getValue('edit_'+_NAME_) 
                                                                            : editor.getValue()
                                                  );

                        $('#edit_'+_NAME_).hide();
                      //FIX$('#code_'+_NAME_).hide();
                        if (_MODULE_=='page') { 
                            //$('#edit_'+_NAME_+'_css') .hide();  //html(contents);
                            //$('#edit_'+_NAME_+'_js')  .hide();  //html(contents);
                            //$('#edit_'+_NAME_+'_code').hide();  //html(contents);
                            $('#ftabs-code-tabs').hide();
                        } 
                    }else{
                    
                        console.log('LOAD SHORTCODED CONTENT ');

                        $('#edit_'+_NAME_).val( 
                            
                            $('#content_'+_NAME_).html() 

                        );
                    
                    }
                    ****/
                    $('#content_'+_NAME_).show();
                    if(data.error>0) showMessageError(data.msg);
                                else showMessageInfo(data.msg);
                    //console.log('DATA.MSG',data.msg);

                }).fail(function(data){
                    $('#result').html('error');
                    //console.log(data);
                }).always(function(data){
                    //console.log(data);
                    if( edit_source){
                    }else{
                        $('#content_'+_NAME_).attr('contenteditable', false);
                        $('#toolbar-buttons').hide('fast');
                    }
                    $("#save_text" ).hide('fast');
                    $("#cancel_text" ).hide('fast');
                    $("#files-panel" ).hide('fast');  //#drop_file_zone
                    $('.ajax-loader').hide(); 
                    edit_source = false;    
                });
            });

            $('#files-gallery img').click(function(){
                console.log('SRC',$(this).attr('src'))
            });

            //$('#files-gallery .gallery-item button').click(function(){
            $('#files-gallery').on('click','.gallery-item button',function(){

                let op     = $(this).data('op');
                let thumb  = $(this).closest('.gallery-item').data('thumb');
                let src    = $(this).closest('.gallery-item').data('src');
                let desc   = $(this).closest('.gallery-item').data('desc');
                let ext    = $(this).closest('.gallery-item').data('ext');
                let parent = $(this).closest('.gallery-item').data('parent');
                let id     = $(this).closest('.gallery-item').data('id');
                let html   = '';
                if(!desc) desc = 'description';
              //if     (op=='image') html = '<spanclass="image"><img src="'+src+'"><span class="image-description">'+desc+'</span></span><br />';
                if     (op=='image') html = `<img class="editable-image open_file_image" data-parent="${parent}" data-id="${id}" title="${desc}" src="${src}"   alt="${thumb}">`;
                else if(op=='thumb') html = `<img class="editable-image open_file_image"  data-href="${src}" data-parent="${parent}" data-id="${id}" title="${desc}" src="${thumb}" alt="${thumb}">`;
                else if(op=='link')  {
                    if      (ext=='image') html =`<a class="open_file_image" href="${src}" title="${desc}"><img class="editable-image" data-parent="${parent}" data-id="${id}" src="${thumb}" alt="${thumb}"></a>`;
                    else if (ext=='pdf')   html =`<a class="open_file_pdf"  data-href="${src}" title="${desc}"><i class="fa fa-file-pdf-o"></i> ${desc}</a></br />`;
                    else                   html =`<a href="${src}" title="${desc}">${desc}</a><br />`;
                }
                //console.log(html);
                if( edit_source){
                   //editor.session.insert(editor.getCursorPosition(), html)
                    monaco_editor.insertHtml('edit_'+_NAME_,html)
                }else{
                    inserHTML(html);
                }
            });

        });         