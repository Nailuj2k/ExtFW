            /////////$('#epub-reader .fa-font' ).click(function(){$('#select-font-family').toggle('fast');});

            function validISBN10(isbn) {
              if (isbn == null) {
                return false;
              }
              // Elimina cualquier guión
              isbn = isbn.replaceAll("-", "");
              // Debe ser un número ISBN-10 de 10 dígitos
              if (isbn.length != 10) {
                return false;
              }
              try {
                let tot = 0;
                for (let i = 0; i < 9; i++) {
                  let digit = parseInt(isbn.substring(i, i + 1));
                  tot += ((10 - i) * digit);
                }
                let checksum = parseInt((11 - (tot % 11)) % 11);
                if (checksum == 10) {
                  checksum = "X";
                }
                return checksum == isbn.substring(9);
              } catch /* (NumberFormatException nfe)*/ {
                // Para capturar ISBN inválidos que tienen caracteres no numéricos
                return false;
              }
            }


            function validISBN13(isbn) {
              // Eliminar guiones y espacios en blanco del ISBN
              isbn = isbn.replace(/[-\s]/g, '');

              // Verificar la longitud del ISBN
              if (isbn.length !== 13) {
                return false;
              }

              // Verificar que todos los caracteres sean dígitos numéricos
              if (!/^\d+$/.test(isbn)) {
                return false;
              }

              // Calcular el dígito de verificación
              let suma = 0;
              for (let i = 0; i < 12; i++) {
                suma += parseInt(isbn.charAt(i)) * (i % 2 === 0 ? 1 : 3);
              }
              let digitoVerificador = (10 - (suma % 10)) % 10;

              // Comparar el dígito de verificación calculado con el dígito de verificación proporcionado
              return digitoVerificador === parseInt(isbn.charAt(12));
            }

            var epub_initialized = false;
            var rendition,ebook,view_mode;
            function load_epub(epubfile){
                proccess_keydown = false; // not proccess keydown in other places
                $('.tb_id,#datatable-body,#datatable-footer,#footer,.main-nav').hide();
                console.log('EPUB',epubfile);
                const default_fontsize='22';  // 16px
                const fontsize_unit = 'px';
                let fontsize=default_fontsize;  // 16px
                let fontfamily='IM Fell English'; 
                let justify_mode = false;
                let ih = Math.min(710,(window.innerHeight-120))+'px';
                let key = cyrb53(epubfile.split('/').slice(-1)[0]);  // slice(-2,-1)[0];  //  use book.key() ??
                console.log('KEY',key);
                let displayed;
                const stored_cfi = localStorage.getItem(key+'-cfi');
                const stored_fontsize = localStorage.getItem(key+'-fontsize');
                const stored_fontfamily = localStorage.getItem(key+'-fontfamily')||'IM Fell English';

                $('#toc').empty();
                $('#epub-reader').fadeIn(); 
                console.log('epubfile',epubfile);
                book = ePub(epubfile, { openAs: "epub" });
                rendition = book.renderTo("area", {
                    width: '100%',  //  width: "100% - 106px",
                    height: ih,     //  height: this.calculateReaderHeight()
                   // stylesheet: epub_stylesheet, 
                    stylesheet: '/_lib_/epub/epub.css?ver=1.3.2',
                    flow: 'paginated',
                    manager: 'continuous',
                    spread: 'always'
                });

                console.log('STORED_CFI',stored_cfi);
                if(stored_cfi!=null) displayed = rendition.display(stored_cfi);
                                else displayed = rendition.display();

                book.ready.then(function() {     // Generate location and pagination           
                    if(stored_fontsize!=null){fontsize = stored_fontsize;if(fontsize<=5)fontsize=5.5;if(fontsize>=30)fontsize=30;}else fontsize = default_fontsize;
                    if(stored_fontfamily!=null){fontfamily = stored_fontfamily;$('#select-font-family' ).val(fontfamily);}else fontfamily = 'serif';
                    rendition.themes.default({'h1,h2,h3,h4,h5,p,div,span,.paragraph': {'font-family': `${fontfamily}`},'body' : {'font-size': `${fontsize}${fontsize_unit}`}});

                    const stored = localStorage.getItem(key + '-locations');
                    if (stored) return book.locations.load(stored);
                           else return book.locations.generate(1024); 

                }).then(function(location) { 
                    localStorage.setItem(key + '-locations', book.locations.save());
                });

                // METADATA cover, title, author, etc.
                book.loaded.metadata.then(function(meta){
                    var $title  = document.getElementById("epub-title-title");
                    var $author = document.getElementById("author");
                    var $isbn   = document.getElementById("isbn");
                    var $cover  = document.getElementById("epub-cover-img");    // var $cover = $('#epub-cover-img');  //con el puto jQuery
                    var isbn13  = meta.identifier && validISBN13(meta.identifier) ? meta.identifier : ''
                    console.log('BOOK.META',meta);
                    $title.textContent = meta.title;
                    $author.textContent = meta.creator;
                    $isbn.textContent = isbn13;
                    console.log('BOOK.ISBN13', book.isbn13);
                    console.log('BOOK.COVER', book.cover);
                    if (book.archive && book.cover) {
                        book.archive.createUrl(book.cover)
                          .then(function (url) {$cover.src = url; })            // $cover.attr('src',  url); //+'?ver=3');   // .... jQuery                         
                    } else  if(validISBN13(meta.identifier)){
                        $cover.src = 'https://pictures.abebooks.com/isbn/'+meta.identifier+'-es.jpg';
                    } else {
                        if(meta.identifier)
                        $cover.src = book.cover;
                    }
               });

                // TOC
                book.loaded.navigation.then(function(toc){                      // Code from de https://codepen.io/CutePixel/pen/xxZvZOP
                    let $nav = document.getElementById("toc"),
                        docfrag = document.createDocumentFragment();
                    let addTocItems = function (parent, tocItems) {
                        let $ul = document.createElement("ul");
                        tocItems.forEach(function(chapter) {
                            let item = document.createElement("li");
                            let link = document.createElement("a");
                            link.textContent = chapter.label;
                            link.href = chapter.href;
                            item.appendChild(link);
                            if (chapter.subitems) addTocItems(item, chapter.subitems)
                            link.onclick = function(){
                                let url = link.getAttribute("href");
                                rendition.display(url);
                                $('#toc').slideToggle(250);
                                return false;
                            };
                            $ul.appendChild(item);
                        });
                        parent.appendChild($ul);
                    };
                    addTocItems(docfrag, toc);
                    $nav.appendChild(docfrag);
                });
                
                // PAGINATION INFO
                rendition.on('relocated', function(locations) {
                    progress = book.locations.percentageFromCfi(locations.start.cfi);
                    $('#epub-page-number').html( book.locations.locationFromCfi(locations.start.cfi) );
                    $('#epub-total-pages').html( book.locations.total );
                    $('#epub-progress')   .html( Math.round((progress*100))+'%' ); // The % of how far along in the book you are
                });

                function savePos(){
                    let location = rendition.currentLocation();
                    if(typeof location.start !== 'undefined'){
                        let key = cyrb53(epubfile.split('/').slice(-1)[0]);  //slice(-2,-1)[0];
                        let cfiString = location.start.cfi; // this is what you want to save
                        localStorage.setItem(key+'-cfi', cfiString)    
                    }
                }
 
               // BUTTONS BAR  
                if (!epub_initialized){  // pevent repetitions
                    epub_initialized = true;
                    $('#epub-reader .fa-list').click(function(e){e.preventDefault();$("#toc").slideToggle(250);}); 
                    $('#epub-reader #toggle-mode').click(function(){toggleMode()});
                    $('#epub-reader #prev').click(function(e){e.preventDefault();rendition.prev();savePos();}); 
                    $('#epub-reader #next').click(function(e){e.preventDefault();rendition.next();savePos();}); 
                }
                $('#epub-reader .fa-square-o' ).click(function(){fontsize=default_fontsize;setFontSize()}); //TODO check repetitions
                $('#epub-reader .fa-plus' ).click(function(){if(isNaN(fontsize))fontsize=default_fontsize;++fontsize; setFontSize();  });
                $('#epub-reader .fa-minus').click(function(){if(isNaN(fontsize))fontsize=default_fontsize;--fontsize; setFontSize();  });
                $('#epub-reader #toggle-justify').click(function(){toggleJustify()});

                // FONT SIZE
                function setFontSize(){console.log('fontsize',fontsize);if(fontsize<=5)fontsize=5;if(fontsize>=30)fontsize=30;rendition.themes.default({"body":{"font-size": `${fontsize}${fontsize_unit} !important`}});}  
               
                // MODE
                var fontcolor = 'black'; 
                function toggleMode(){
                    if (view_mode==5){
                        $('#toggle-mode').removeClass('fa-moon-o').removeClass('fa-sun-o').addClass('fa-certificate'); 
                        $('#reader').removeClass('paper').removeClass('auto').removeClass('paper0').removeClass('auto0').addClass('dark');
                        fontcolor= 'white';
                    }else if (view_mode==4){
                        $('#toggle-mode').removeClass('fa-sun-o').removeClass('fa-certificate').addClass('fa-moon-o');
                        $('#reader').removeClass('paper').removeClass('paper0').removeClass('auto0').removeClass('dark').addClass('auto'); 
                        fontcolor= '#444';
                    }else if (view_mode==3){
                        $('#toggle-mode').removeClass('fa-sun-o').removeClass('fa-certificate').addClass('fa-moon-o');
                        $('#reader').removeClass('paper').removeClass('paper0').removeClass('auto').removeClass('dark').addClass('auto0'); 
                        fontcolor= '#444';
                    }else if (view_mode==2){                            
                        $('#toggle-mode').removeClass('fa-moon-o').removeClass('fa-certificate').addClass('fa-sun-o');  // Paper backgrounds search 'carta antigua plantilla'
                        $('#reader').removeClass('dark').removeClass('paper0').removeClass('auto').removeClass('auto0').addClass('paper');
                        fontcolor= 'black';
                    }else {                            
                        $('#toggle-mode').removeClass('fa-moon-o').removeClass('fa-certificate').addClass('fa-sun-o'); 
                        $('#reader').removeClass('dark').removeClass('auto').removeClass('paper').removeClass('auto0').addClass('paper0');
                        fontcolor= 'black';

                    }
                    rendition.themes.default({ 'h1,h2,h3,h4,h5,p,div,span,.paragraph': {'font-family': `${fontfamily}`},
                                               'body' : {'font-size': `${fontsize}${fontsize_unit}`},
                                               '*':{'color': `${fontcolor} !important`}    });

                    view_mode++;if(view_mode>5) view_mode=1;
                } 
                view_mode=1;  // reset view mode
                toggleMode();

                // JUSTIFIY
                function toggleJustify(){
                    justify_mode = !justify_mode;
                    if(justify_mode) { rendition.themes.default({"p,div":{"text-align": 'justify !important'}}); $('#toggle-justify').removeClass('fa-align-left').addClass('fa-align-justify'); }
                               else { rendition.themes.default({"p,div":{"text-align": 'unset !important'}});   $('#toggle-justify').removeClass('fa-align-justify').addClass('fa-align-left'); }
                }
 
                $('#epub-reader .fa-font' ).click(function(){$('#select-font-family').toggle('fast');});

                // FONT FAMILY
                $('#select-font-family').click(function(e){
                    e.preventDefault();
                    fontfamily = $(this).val();
                    //$(this).css('font-family',`'${fontfamily}'`);
                    rendition.themes.default({ "body,h1,h2,h3,h4,h5,p,div,span,.paragraph": { "font-family":`'${fontfamily}','serif' !important;`}});
                    $(this).hide();
                    return false;
                }).hide();

                // CLOSE 
                $('body').on('click','#epub_viewer_close',function(e){
                    $('.tb_id,#datatable-body,#datatable-footer,#footer,.main-nav').show();  
                    proccess_keydown=true;
                 //   savePos();
                    localStorage.setItem(key+'-fontsize',fontsize);
                    localStorage.setItem(key+'-fontfamily',fontfamily);
                    rendition.destroy(); 
                    book.destroy(); 
                    $('#epub-reader').fadeOut();
                });

                // KEYBOARD NAVIGATION
                $('body').on('keydown',null,function(e){
                    let epub_visible=$('#epub-reader').is(':visible');
                    if(!epub_visible) return true;
                    if     (e.which == 37 || e.which == 33) { e.preventDefault();rendition.prev(); }
                    else if(e.which == 39 || e.which == 34) { e.preventDefault();rendition.next(); }    
                    // else if HOME goto first page                    
                }); 
                
            }