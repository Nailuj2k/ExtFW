(function($) {
 
    $.fn.dialog = function(options) {
        const defaults = {
            title: "Diálogo",
            type: 'default', // default, image, iframe, ajax
            defaultType: 'default', // Tipo de contenido por defecto
            prefix: "wq-dialog",
            id : null,
            class : '',
            modal:true,
            fullscreen: false, // true, false 
            footerFixed: false,
            width: 'auto', //"700px",
            height: 'auto',
            draggable: true,
            buttons: [], // Ejemplo: [{ text: "Aceptar", action: function() { alert("Aceptar"); } }]
            content: './media/images/logo.png', //null, // Puede ser una URL o un selector de un elemento existente
            errorMessage: "No se pudo cargar el contenido. Inténtalo de nuevo más tarde.",
            showFrom: 'default', // top, bottom, left, right, defaul
            params: null, // params to pass to onLoad
            onBeforeLoad: null, // callback function called before show dialog
            onLoad: null, // callback function called after show dialog
            onClose: null,
            //onLoaded: null, //callback funcion called only when ajax json resolve
            timeout: false, // Auto-close timeout in milliseconds (false = no auto-close)
            slideAnimation: false, // true, false
            closeAnimation: false, // Animación de cierre: 'none', 'fade', 'zoom', 'slide-left', 'slide-right', 'slide-up', 'bounce', 'shake'
            openAnimation: false, // Animación de apertura: 'none', 'fade', 'zoom', 'bounce', 'slide-left', 'slide-right', 'slide-up', 'slide-down'
            // if showFrom is default todo queda como ahora. no changes.
            // if showFrom is left dialogOverlay is created with position:fixed and left=-width and screen and right=width of screen
            // then when show it is animated to left=0 and right=width of screen
            // if showFrom is right dialogOverlay is created with position:fixed and left=width of screen and right=-width of screen
            // then when show it is animated to left=0 and right=width of screen
        };

        const settings = Object.assign({}, defaults, options);

        if (settings.slideAnimation===false)  settings.showFrom='default';
        
        // Si openAnimation está configurada y slideAnimation es false, usar openAnimation en lugar de slideAnimation
        if (settings.openAnimation && settings.openAnimation !== 'none' && settings.slideAnimation===false) {
            settings.showFrom = 'default'; // Asegurar que no interfiera con slideAnimation
        }

        function getTypeOfContent(content){

            if(typeof content  == 'array' && content.length > 0){
                settings = content
            }
            /*
            if(typeof content  == 'undefined' ) {       
                $('.wq-dialog-overlay').remove();
                return false;
            }
            */
            if(typeof content  == 'object'){
                settings.content = content
                return 'form'
            } else if (typeof content === "function") {
                return 'html'
            } else if (content instanceof HTMLElement) {
                return 'html'
            } else if (typeof content === "object") {
                //settings.content = content
                return 'html'
            }else{

                     if (settings.type=='pdf'     || /(\/|\.pdf)$/i.test(content)                       )   return 'pdf';
                else if (settings.type=='iframe'                                                        )   return 'iframe';
                else if (settings.type=='youtube' || /youtube|youtu\.be/i.test(content)                 )   return 'video';
                else if (settings.type=='vimeo'   || /vimeo|vimeopro/i.test(content)                    )   return 'video';
                else if (settings.type=='dmotion' || /dailymotion/i.test(content)                       )   return 'video';
                else if (settings.type=='video'   || /content\.(mp4|mkv|ogg|avi|webm)$/i.test(content)  )   return 'video';
                else if (settings.type=='image'   || /content\.(jpg|jpeg|png|gif|webp)$/i.test(content) )   return 'image';
                else if (settings.type=='txt'     || /(\/|\.txt)$/i.test(content)                       )   return 'txt';
                else if (settings.type=='md'      || /(\/|\.md)$/i.test(content)                        )   return 'md';       //TEST
                else if (settings.type=='epub'    || content.endsWith(".epub")                          )   return 'epub';
                else if (/*settings.type=='epub'  || */content.startsWith("#")                          )   return 'html';
                else if (settings.type=='ajax'                                                          )   return 'ajax';
                else                                                                                        return settings.defaultType;
                /**/
            }
        }

        // Función mejorada para cargar imágenes y manejar sus dimensiones de forma asíncrona
        function loadImageWithDimensions(url, callback) {
            const img = new Image();
            img.onload = function() {
                // Una vez cargada la imagen, llamamos al callback con sus dimensiones
                callback({
                    width: img.width,
                    height: img.height,
                    element: img
                });
            };
            img.onerror = function() {
                // En caso de error, llamamos al callback con dimensiones predeterminadas
                callback({
                    width: 300,
                    height: 200,
                    element: img,
                    error: true
                });
            };
            img.src = url;
        }

        return this.each(function() {
            let isMaximized = false; // Estado del diálogo (maximizado o no)
            
            if(settings.content==null || settings.content==undefined || settings.content=='undefined' ) 
                return false; //settings.content = './media/images/logo.png'; 

            let contentType = getTypeOfContent(settings.content)
            
            settings.type = contentType;

            const originalPosition = {}; // Guardar posición y tamaño originales

            // Crear el contenedor del diálogo
            const dialogOverlay = document.createElement("div"); 
            
            dialogOverlay.className = settings.prefix+'-overlay '+settings.prefix+'-'+contentType+' '+settings.class+' '+(settings.fullscreen ? 'dialog-fullscreen' : '') ;
            if(settings.id) dialogOverlay.id = settings.id;
            
            // Crear objeto dialog con métodos útiles
            const dialogInstance = {
                overlay: dialogOverlay,
                close: function() {
                    if (dialogOverlay && dialogOverlay.parentNode) {
                        // Aplicar animación de cierre si está configurada
                        if (settings.closeAnimation && settings.closeAnimation !== 'none') {
                            console.log('Aplicando animación de cierre:', settings.closeAnimation);
                            
                            // Force a reflow before applying animation to ensure initial state is rendered
                            dialogOverlay.offsetHeight;
                            
                            dialogOverlay.classList.add('closing-' + settings.closeAnimation);
                            
                            // Log para debug - verificar si las clases se aplicaron
                            console.log('Clases aplicadas:', dialogOverlay.className);
                            console.log('Transform inicial del contenido:', getComputedStyle(dialogContent).transform);
                            
                            // Force another reflow to ensure animation class is applied
                            dialogOverlay.offsetHeight;
                            
                            // Calcular duración basada en el tipo de animación
                            let animationDuration;
                            if (['shake', 'bounce'].includes(settings.closeAnimation)) {
                                animationDuration = 600; // keyframe animations
                            } else {
                                animationDuration = 500; // transition-based animations
                            }
                            
                            // Para todas las animaciones, comportamiento estándar
                            setTimeout(() => {
                                if (dialogOverlay && dialogOverlay.parentNode) {
                                    document.body.removeChild(dialogOverlay);
                                    if (typeof settings.onClose === "function") {
                                        settings.onClose();
                                    }
                                }
                            }, animationDuration);
                        } else {
                            // Cierre inmediato sin animación
                            document.body.removeChild(dialogOverlay);
                            if (typeof settings.onClose === "function") {
                                settings.onClose();
                            }
                        }
                        // Remover event listeners
                        document.removeEventListener("keydown", escKeyHandler);
                    }
                },
                maximize: function() {
                    if (!isMaximized) {
                        maximizeButton.click();
                    }
                },
                minimize: function() {
                    if (isMaximized) {
                        maximizeButton.click();
                    }
                }
            };
            
            // Crear el contenido del diálogo
            const dialogContent = document.createElement("div");
            dialogContent.className = settings.prefix+"-content";
            //dialogContent.style.width  = settings.type=='pdf' ? '90%' : settings.width;
            //dialogContent.style.height = settings.type=='pdf' ? '90%' : settings.height;
            dialogContent.style.width  = settings.fullscreen ? '100%' : (contentType=='pdf' ? '90%' : settings.width);
            dialogContent.style.height = settings.fullscreen ? '100%' : (contentType=='pdf' ? '90%' : settings.height);
            dialogContent.setAttribute("role", "dialog");
            dialogContent.setAttribute("aria-labelledby", "dialog-title");
            dialogContent.setAttribute("aria-describedby", "dialog-body");

            // Mover el foco al diálogo al abrir
            dialogContent.setAttribute("tabindex", "-1");
            dialogContent.focus();

            // Crear la barra de título
            const dialogTitleBar = document.createElement("div");
            dialogTitleBar.className = settings.prefix+"-titlebar";

            const dialogTitle = document.createElement("span");
            dialogTitle.className = settings.prefix+"-title";
            dialogTitle.innerHTML = settings.title;
            dialogTitle.id = "dialog-title";

            // Botones de la barra de título
            const maximizeButton = document.createElement("button");
            maximizeButton.className = settings.prefix+"-maximize";

            const titlebarCloseButton = document.createElement("button");
            titlebarCloseButton.className = settings.prefix+"-close";
            titlebarCloseButton.setAttribute("aria-label", "Cerrar diálogo");

            dialogTitleBar.appendChild(dialogTitle);
            dialogTitleBar.appendChild(maximizeButton);
            dialogTitleBar.appendChild(titlebarCloseButton);

            // Contenedor para el contenido del diálogo
            const dialogBody = document.createElement("div");
            dialogBody.className = settings.prefix+"-body";
            //////////////////////////////////////////////////////////////////////////////dialogBody.id = "dialog-body";

            // Barra inferior para botones
            const dialogFooter = document.createElement("div");
            dialogFooter.className = settings.prefix+"-footer";

            settings.buttons.forEach(button => {
                const footerButton = document.createElement("button");
                footerButton.innerHTML = button.text;
                footerButton.className = button.class;

                // Pasar referencia al diálogo al callback
                footerButton.addEventListener("click", function(event) {
                    button.action.call(dialogContent, event, dialogOverlay);
                });

                dialogFooter.appendChild(footerButton);
            });

            // Ensamblar el diálogo
            dialogContent.appendChild(dialogTitleBar);
            dialogContent.appendChild(dialogBody);

            if(settings.fullscreen || settings.footerFixed){
                dialogFooter.classList.add(settings.prefix+'-footer-middle')
                dialogOverlay.appendChild(dialogFooter);
            }else{
                dialogFooter.classList.add(settings.prefix+'-footer-bottom')
                dialogContent.appendChild(dialogFooter);
            }
            
            dialogOverlay.appendChild(dialogContent);


            document.body.appendChild(dialogOverlay);

            if (settings.onBeforeLoad)
                setTimeout(() => settings.onBeforeLoad.call(dialogInstance, settings.params),100);

            // Store dialog instance reference for external button access
            dialogContent._dialogInstance = dialogInstance;

            // Guardar posición y tamaño originales
            function saveOriginalPosition() {
                originalPosition.width = dialogContent.style.width;
                originalPosition.height = dialogContent.style.height;
                originalPosition.top = dialogContent.style.top;
                originalPosition.left = dialogContent.style.left;
                originalPosition.position = dialogContent.style.position;
            }

            // Restaurar posición y tamaño originales
            function restoreOriginalPosition() {
                dialogContent.style.width = originalPosition.width;
                dialogContent.style.height = originalPosition.height;
                dialogContent.style.top = originalPosition.top;
                dialogContent.style.left = originalPosition.left;
                dialogContent.style.position = originalPosition.position;
            }

            // Crear una función nombrada para poder eliminarla después
            function escKeyHandler(e) {
                if (e.key === "Escape") {
                    dialogInstance.close();
                }
            }                                                                     
            
            // Añadir el event listener
            document.addEventListener("keydown", escKeyHandler);

            // Eventos de los botones
            titlebarCloseButton.addEventListener("click", function() {
                dialogInstance.close();
            });

            // Cerrar al pulsar en el overlay (fuera del dialog), respetando dialogs anidados
            dialogOverlay.addEventListener("click", function(e) {
                if (e.target === dialogOverlay) {
                    dialogInstance.close();
                }
            });

            maximizeButton.addEventListener("click", function() {
                if (isMaximized) {     // Restaurar tamaño original               
                    restoreOriginalPosition();
                    dialogContent.classList.remove(settings.prefix+"-maximized");
                    isMaximized = false;
                    this.classList.add(settings.prefix+"-maximize");
                    this.classList.remove(settings.prefix+"-minimize");
                } else {   // Maximizar                
                    saveOriginalPosition();    // Guardar posición y tamaño originales
                    dialogContent.style.position = "fixed";
                    dialogContent.style.top = "0";
                    dialogContent.style.left = "0";
                    dialogContent.style.right = "0";
                    dialogContent.style.bottom = "0";
                    dialogContent.style.width = "100%";
                    dialogContent.style.height = "100%";
                    dialogContent.classList.add(settings.prefix+"-maximized");
                    this.classList.remove(settings.prefix+"-maximize");
                    this.classList.add(settings.prefix+"-minimize");
                    isMaximized = true;
                }
            });

            // Hacer el diálogo movible con jQuery
            // if (typeof $.fn.draggable === "function") {
            //    $(dialogContent).draggable({ handle: "."+settings.prefix+"-titlebar" });
            // }

            if(settings.draggable === true) {    // Hacer el diálogo movible (Vanilla JS replacement for jQuery draggable)
                let isDragging = false;
                let dragOffset = { x: 0, y: 0 };                
                dialogTitleBar.addEventListener('mousedown', function(e) {
                    dialogContent.style.transition = 'unset';    // Quitar transition para que el arrastre no sea extremadamente lento
                    isDragging = true;
                    dragOffset.x = e.clientX - dialogContent.offsetLeft;
                    dragOffset.y = e.clientY - dialogContent.offsetTop;
                    dialogContent.style.cursor = 'move';
                    e.preventDefault();
                });                
                document.addEventListener('mousemove', function(e) {
                    if (isDragging) {
                        dialogContent.style.left = (e.clientX - dragOffset.x) + 'px';
                        dialogContent.style.top = (e.clientY - dragOffset.y) + 'px';
                        dialogContent.style.position = 'fixed';
                    }
                });                
                document.addEventListener('mouseup', function() {
                    if (isDragging) {
                        isDragging = false;
                        dialogContent.style.cursor = 'default';
                    }
                });
            } // End draggable option      
        
            if (settings.content) {    // Cargar contenido dinámico en el cuerpo del diálogo
                if (typeof settings.content === "string") {
                          if (settings.type=='pdf'    || settings.content.endsWith(".pdf")   ||settings.content.endsWith("/pdf")) {
                        dialogBody.innerHTML = `<iframe src="${settings.content}" style="width: 100%; height: 100%; border: none;"></iframe>`;
                    }else if (settings.type=='iframe') {
                        dialogBody.innerHTML = `<iframe src="${settings.content}" scrolling="no" style="width: 100%; height: 100%; border: none;"></iframe>`;
                    }else if (settings.type=='youtube'|| settings.content.includes("youtube")||settings.content.includes("youtu.be")) {                    
                        let videoId = settings.content.split('v=')[1] || settings.content.split('/').pop();
                        let ampersandPosition = videoId.indexOf('&');
                        if (ampersandPosition !== -1) {
                            videoId = videoId.substring(0, ampersandPosition);
                        }
                        dialogBody.innerHTML = `<iframe style="min-width:801px;min-height:450px;margin-bottom:-4px;" src="https://www.youtube.com/embed/${videoId}?autoplay=1&rel=0" frameborder="0" allow="autoplay;encrypted-media;" allowfullscreen></iframe>`;
                    }else if (settings.type=='dailymotion'|| settings.content.includes("dailymotion")) {                    
                        let videoId = settings.content.split('/').pop();
                        dialogBody.innerHTML = `<iframe style="min-width:801px;min-height:450px;margin-bottom:-4px;" src="https://geo.dailymotion.com/player.html?video=${videoId}" frameborder="0" allow="autoplay;encrypted-media;" allowfullscreen></iframe>`;
                    }else if (settings.type=='epub'   || settings.content.endsWith(".epub")) {
                        //load_epub(settings.content) 
                        dialogBody.innerHTML = `<p>Documento EPUB:  ${settings.content}</p>`;
                    }else if (settings.type=='video'  || settings.content.endsWith(".mp4")) {
                          dialogBody.innerHTML = '<video id="video" controls controlslist="nodownload" style="display:block;max-width:100%;height:auto;" noposter="media/images/home.jpg" autoplay noloop nomuted playsinline><source src="'+settings.content+'" type="video/mp4">Tu navegador no soporta el elemento de video.</video>';
                    }else if (settings.type=='image'  || settings.content.endsWith(".jpg")   ||/*  settings.content.includes(".jpg?")||*/ settings.content.endsWith(".jpeg")||settings.content.endsWith(".png")||settings.content.endsWith(".gif")||settings.content.endsWith(".webp")) {
                        if(settings.fullscreen){
                            loadImageWithDimensions(settings.content, function(dimensions) {
                                let style = dimensions.width > dimensions.height ? 'width:-webkit-fill-available; height:fit-content;' : 'width:fit-content;height:-webkit-fill-available;';
                                //console.log('IMAGE', settings.content, dimensions.width + '<' + dimensions.height);
                                dialogBody.innerHTML = `<img src="${settings.content}" alt="Imagen" style="${style}">`;
                            });
                        }else{
                            dialogBody.innerHTML = `<img src="${settings.content}" alt="Imagen">`;
                        }                        
                    }else if (settings.type=='txt'    || settings.content.endsWith(".txt")   || settings.content.endsWith("/txt")   ) {                    
                        dialogBody.innerHTML = `<p class="txt">Cargando ${settings.content}</p>`;
                        fetch(settings.content)
                            .then(response => {
                                if (!response.ok) throw new Error("1.Error al cargar el contenido: "+settings.content);
                                return response.text();
                            })
                            .then(text   => {                                                        
                                dialogBody.innerHTML = '';              // vaciamos el contenedor                           
                                const p = document.createElement('p');  // creamos el <p class="txt">
                                p.className = 'txt';                           
                                p.innerHTML = text;                     // insertamos el HTML que viene por fetch                            
                                dialogBody.appendChild(p);              // lo añadimos al dialogBody                                
                            })
                            .catch(error => { dialogBody.innerHTML = `<p class="txt">2.Error al cargar el contenido: ${settings.content}</p>`; });
                    }else if (settings.type=='md'     || settings.content.endsWith(".md")    || settings.content.endsWith("/md")   ) {                    
                        dialogBody.innerHTML = `<p class="txt">Cargando ${settings.content}</p>`;
                        fetch(settings.content)
                            .then(response => {
                                if (!response.ok) throw new Error("1.Error al cargar el contenido: "+settings.content);
                                return response.text();
                            })
                            .then(text   => {                                                        
                                dialogBody.innerHTML = '';              // vaciamos el contenedor                           
                                const p = document.createElement('pre');  // creamos el <p class="txt">
                                p.className = 'md';                           
                                p.style='margin:20px';
                                p.innerHTML = '<code style="font-size:11px;">'+text+'<br><br><br><br></code>';    // insertamos el HTML que viene por fetch  
                                dialogBody.appendChild(p);              // lo añadimos al dialogBody                                
                            })
                            .catch(error => { dialogBody.innerHTML = `<p class="txt">2.Error al cargar el contenido: ${settings.content}</p>`; });
                    }else if (settings.content.startsWith("http")||settings.content.endsWith("/html")||settings.content.indexOf("/ajax/") > -1) {
                        
                        if(settings.type=='ajax' && typeof Load == 'function') 
                            Load(dialogBody,settings.content);
                        else
                            dialogBody.innerHTML = "<p>Cargando...</p>";
                        //console.log('CONTENT',settings.content);

                        fetch(settings.content)
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error("3.Error al cargar el contenido: "+settings.content +"");  //FIX escape settings.content when contain html code
                                }
                                return response.text();
                            })
                            .then(html => {

                                //console.log('THEN.1',html);


                                //if (settings.onLoaded)  setTimeout(() => settings.onLoaded.call(dialogInstance, html),100);
                                if(settings.type=='ajax' && typeof Loaded == 'function') 
                                    Loaded(dialogBody,html);
                                else
                                    dialogBody.innerHTML = html;

                            })
                            .then(html=>{
                               
                                let tabElements = dialogBody.querySelectorAll('[data-simpletabs]');
                                tabElements.forEach(el => new SimpleTabs(el));                            
                                const scripts = dialogBody.querySelectorAll("script");    // Manejar scripts de manera segura sin usar appendChild para scripts
                                scripts.forEach(script => {
                                    if (script.type === "module") {    // Comprobar si es un script módulo
                                        // Crear un nuevo script con type="module" para manejar imports ES6
                                        const newScript = document.createElement("script");
                                        newScript.type = "module";
                                        newScript.textContent = script.textContent;
                                        // Añadir al head en lugar del body del diálogo para mejor compatibilidad
                                        document.head.appendChild(newScript);
                                    } else {  // Para scripts normales, evaluar el código
                                        try {    // Usar Function para crear un ámbito aislado                                        
                                            new Function(script.textContent)();
                                            //console.log('THEN.2',script.textContent);
                                        } catch (e) {
                                            console.error("Error ejecutando script:", e);
                                        }
                                    }
                                });
                                // move element in dialogBody .dialog-buttons to settings.prefix+'-content' '.'+settings.prefix+'-footer'
                                let buttons = dialogBody.querySelectorAll('.dialog-buttons button');
                                if (buttons.length > 0) {
                                    buttons.forEach(button => {
                                        let footer = dialogFooter; //BUTTONS
                                        footer.prepend(button);
                                    }); 
                                }                                
                            })
                            .catch(error => {
                                dialogBody.innerHTML = "<p>4.Error al cargar el contenido: "+settings.content +"</p>";
                            });
                    }else if (settings.content.startsWith("#")){
                        //console.log('ELEMENT',settings.content)
                        const element = document.querySelector(settings.content);
                        if (element) {
                            dialogBody.innerHTML = element.innerHTML;
                        } else {
                            dialogBody.innerHTML = `<p>Elemento no encontrado:${settings.content}</p>`;
                        }
                    }else {                    
                        dialogBody.innerHTML = settings.content;
                    }
                } else if (settings.content instanceof HTMLElement) {
                    dialogBody.appendChild(settings.content);
                } else if (typeof settings.content === "function") {
                    const result = settings.content();
                    if (result instanceof HTMLElement) {
                        dialogBody.appendChild(result);
                    } else if (typeof result === "string") {
                        const tempDiv = document.createElement("div");
                        tempDiv.innerHTML = result;
                        dialogBody.appendChild(tempDiv);
                    }
                } else if (typeof settings.content === "object") {
                    dialogBody.innerHTML = settings.content.html();
                }else{
                    console.log('UNKNOWN ELEMENT',typeof settings.content)
                }
            }

            //console.log('settings.showFrom',settings.showFrom)

            if (settings.showFrom=='left') {
                dialogContent.style.left = "-100%";
                dialogContent.style.right = "auto";
            } else if (settings.showFrom=='right') {
                dialogContent.style.left = "100%";
                dialogContent.style.right = "auto";
            } else if (settings.showFrom=='top') {
                dialogContent.style.top = "-100%";
                dialogContent.style.bottom = "auto";
            } else if (settings.showFrom=='bottom') {
                dialogContent.style.top = "auto";
                dialogContent.style.bottom = "-100%";         
            }

            // Añadir la clase show después de un pequeño retraso para permitir que el navegador procese
            // Si openAnimation está configurada y no hay slideAnimation, aplicar animación de apertura
            if (settings.openAnimation && settings.openAnimation !== 'none' && settings.slideAnimation===false) {
                // Aplicar clase de animación de apertura inicial (estado oculto)
                dialogOverlay.classList.add('opening-' + settings.openAnimation);               
                setTimeout(() => {
                    dialogOverlay.classList.add("show");

                    // Remover la clase de animación inicial para permitir la transición
                    if (     settings.openAnimation === 'slide-down'
                         ||  settings.openAnimation === 'slide-up' 
                         ||  settings.openAnimation === 'bounce')  
                    {
                       // setTimeout(() => { dialogOverlay.classList.remove('opening-' + settings.openAnimation); }, 500);
                    }else{
                        setTimeout(() => { dialogOverlay.classList.remove('opening-' + settings.openAnimation); }, 50);
                    }

                }, 10);
            } else {
                setTimeout(() => dialogOverlay.classList.add("show"), 10);
            }

            // Si se especifica una dirección de entrada, aplicar una transición CSS
            if (settings.showFrom) {    // Primero establecemos la transición            
                dialogContent.style.transition = "left 1s ease, right 1s ease, top 1s ease, bottom 1s ease";
                setTimeout(() => {     // Luego, en el siguiente ciclo, cambiamos la posición para activar la transición
                    if (settings.showFrom=='left' || settings.showFrom=='right') {
                        dialogContent.style.left = "0";
                        dialogContent.style.right = "0";
                    } else if (settings.showFrom=='top' || settings.showFrom=='bottom') {
                        dialogContent.style.top = "0";
                        dialogContent.style.bottom = "0";
                    }  
                }, 20);

                setTimeout(() => {
                   dialogOverlay.classList.add('background')
                },  settings.slideAnimation? 500 : 0);
            }
            
            if (settings.onLoad)
                setTimeout(() => settings.onLoad.call(dialogInstance, dialogInstance),500);
                   
            if (settings.timeout && settings.timeout > 0) {     // Auto-close timeout functionality

                const countdownSpan = document.createElement("span");     // add span to show a countdown
                countdownSpan.className = settings.prefix+"-countdown";
                /*countdownSpan.style.color = "white";*/
                countdownSpan.style.position = "absolute";
                countdownSpan.style.left = "10px";
                countdownSpan.style.paddingTop = "5px";
                countdownSpan.textContent = `Cerrando en ${settings.timeout / 1000} segundos...`;
                dialogFooter.appendChild(countdownSpan);
                let countdown = settings.timeout / 1000; // Convertir a segundos
                const countdownInterval = setInterval(() => {
                    countdown--;
                    countdownSpan.textContent = `Cerrando en ${countdown} segundos...`;
                    if (countdown <= 0) {
                        clearInterval(countdownInterval);
                        dialogInstance.close();
                    }
                }
                , 1000);

            }
                
            const actionClose = function(event, overlay) {    // Define button actions with access to dialogInstance
                console.log('ACTIONCLOSE')
                dialogInstance.close(); 
            };

            const closeButton = {    // Define buttons with proper scope access
                text: "Cerrar",
                action: actionClose
            };

            const okButton = {
                text: "Aceptar", 
                action: actionClose
            };

            const cancelButton = {
                text: "Cancelar",
                action: actionClose
            };

            return dialogInstance;    // Retornar el objeto dialog instance para permitir acceso a sus métodos
        });
    };

    // Add button definitions as static properties of the dialog function for external access
    $.fn.dialog.closeButton = {
        text: "Cerrar",
        action: function(event, overlay) {    // Find the dialog instance and call its close method properly        
            if (overlay && overlay.parentNode) {    // Try to find and call the proper close method if available
                const dialogContent = overlay.querySelector('.wq-dialog-content');
                if (dialogContent && dialogContent._dialogInstance) {
                    dialogContent._dialogInstance.close();
                } else {      // Fallback to direct removal if instance not found
                    document.body.removeChild(overlay);
                }
            }
        }
    };

    $.fn.dialog.removeAll = function(selector = '') {
        let _selector = '.wq-dialog-overlay';
        if (selector!=='') _selector =  _selector+selector;
        console.log('REMOVEALL',_selector);   
        document.querySelectorAll(_selector).forEach(overlay => {
            const dialogContent = overlay.querySelector('.wq-dialog-content');

            if (dialogContent && dialogContent._dialogInstance) {
                dialogContent._dialogInstance.close();
            } else if (overlay.parentNode) {
                overlay.parentNode.removeChild(overlay);
            }
        });
    };

    $.fn.dialog.okButton = {
        text: "Aceptar",
        action: function(event, overlay) {
            if (overlay && overlay.parentNode) {
                const dialogContent = overlay.querySelector('.wq-dialog-content');
                if (dialogContent && dialogContent._dialogInstance) {
                    dialogContent._dialogInstance.close();
                } else {
                    document.body.removeChild(overlay);
                }
            }
        }
    };

    $.fn.dialog.cancelButton = {
        text: "Cancelar", 
        action: function(event, overlay) {
            if (overlay && overlay.parentNode) {
                const dialogContent = overlay.querySelector('.wq-dialog-content');
                if (dialogContent && dialogContent._dialogInstance) {
                    dialogContent._dialogInstance.close();
                } else {
                    document.body.removeChild(overlay);
                }
            }
        }
    };

    // Also make them available directly on $ for convenience
    $.dialog = $.dialog || {};
    $.dialog.closeButton = $.fn.dialog.closeButton;
    $.dialog.okButton = $.fn.dialog.okButton;
    $.dialog.cancelButton = $.fn.dialog.cancelButton;
    $.dialog.removeAll = $.fn.dialog.removeAll;


    $('body').on('click','.open_ajax',function(e){
        e.preventDefault();
        let data_href = $(this).attr('href') || $(this).data('href');
        let data_title =  $(this).attr('title') || $(this).data('title') || $(this).attr('alt') || data_href;
        let href = data_href.indexOf("/ajax/") > -1 ? data_href : data_href+'/html';
        let w=$(this).data('width') ?? '80%';
        let h=$(this).data('height') ?? '80%';
       // let loaded = data_href.indexOf("/ajax/") > -1 ? function(json) {console.log('LOADED',json);console.log($(this)) /*this.dialogBody.innerHTML ='LOADED';*/} : null;
        $("body").dialog({
            title:  data_title,
            width: w,
            height: h,
            type: 'ajax',
            content: href,
            buttons: [$.dialog.closeButton],
            onLoad: function() { console.log('ONLOAD');  console.log($(this))/*dialogBody.innerHTML ='LOADING';*/ },               // Al cargar
            //onLoaded: loaded
        });
    });

    $('body').on('click','.open_href',function(e){
        e.preventDefault();
        let data_href = $(this).attr('href') || $(this).data('href');
        let data_title =  $(this).attr('title') || $(this).data('title') || $(this).attr('alt') || data_href;
        let w=$(this).data('width') ?? '80%';
        let h=$(this).data('height') ?? '80%';
        let t= $(this).data('type') ?? 'auto';
       // let loaded = data_href.indexOf("/ajax/") > -1 ? function(json) {console.log('LOADED',json);console.log($(this)) /*this.dialogBody.innerHTML ='LOADED';*/} : null;
        $("body").dialog({
            title:  data_title,
            width: w,
            height: h,
            type: t,
            content: data_href,
            buttons: [$.dialog.closeButton]//,
            //onLoad: function() { console.log('ONLOAD');  console.log($(this))/*dialogBody.innerHTML ='LOADING';*/ },               // Al cargar
            //onLoaded: loaded
        });
    });

    $('body').on('click','.open_file_pdf',function(e){
        e.preventDefault();
        let file_pdf = $(this).data('href');
        let file_pdf_title =  $(this).attr('title') || $(this).data('title') || $(this).attr('alt') || file_pdf;
        
        $("body").dialog({
            title: `<span class="file-pdf">${file_pdf_title}</span>`,
            width: "80%",
            height: "80%",
            type: 'pdf',
            content: file_pdf,
            buttons: [$.dialog.closeButton]
        });
    });

    $('body').on('click','.open_file_txt',function(e){
        e.preventDefault();
        let file_txt = $(this).data('href');
        let file_txt_title = $(this).data('title');
        let file_ext = $(this).data('ext') ?? 'txt';
        $("body").dialog({
            title:  file_txt_title,
            width: 'auto',
            height: 'auto',
            type: file_ext,
            content: file_txt,
            buttons: [$.dialog.closeButton]
        });
    });    

      //  $('body').on('click','.open_file_image:not([rel="g2"])',function(e){
    $('body').on('click','.open_file_image:not([rel="g2"]',function(e){
        //    $('body').on('click','.open_file_image',function(e){
        e.preventDefault();
        //e.stopPropagation();
        
        let file_img = $(this).attr('href') || $(this).data('href') || $(this).attr('src');
        let file_img_title = $(this).attr('title') || $(this).data('title') || $(this).attr('alt')||file_img;
        $("body").dialog({
            title: file_img_title,
            type:'image',
            width: "auto",
            height: "auto",
            buttons: [$.dialog.closeButton],
            content: file_img
        });
    });
    

    $('body').on('click','.open_file_video',function(e){    // Handle .open_file_video clicks
        e.preventDefault();
        let video_url = $(this).attr('href')||$(this).data('href');
        let file_video_title = $(this).attr('title') || $(this).data('title') || $(this).attr('alt')||video_url;

        // Twitter/X CDN videos → open tweet in new tab (X blocks all iframes)
        var tweetIdMatch = video_url && video_url.match(/(?:amplify_video|ext_tw_video|tweet_video)\/(\d+)\//);
        if (tweetIdMatch) {
            window.open('https://x.com/i/web/status/' + tweetIdMatch[1], '_blank', 'noopener');
            return;
        }

        $("body").dialog({
            title: `<span class="file-video">${file_video_title}</span>`,
            type: 'video',
            content: video_url,
            buttons: [$.dialog.closeButton]
        });
    });

})($);
