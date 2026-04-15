// Sobrescribir alert()
    window.alert = function(message, timeout = false) {
        $("body").dialog({
            title: "Alerta!",
            width: "400px",
            //class: 'alert',
            //draggable:false,
            height: "auto",
            defaultType: "form",
            buttons: [ $.dialog.okButton ],
            content: `<p style="padding: 0 20px;">${message}</p>`,
            timeout: timeout
        });
    };
  
    function stackFixedElements(selector, animated = true, position = 'top-right') {
        /**
         * position can be: 'top-right', 'top-left', 'bottom-right', 'bottom-left'
         * 
         */
    

        //if position is not top-right or top-left or bottom-right or bottom-left then position = top-right
        if (!['top-right', 'top-left', 'bottom-right', 'bottom-left'].includes(position)) {
            position = 'top-right';
        }   

        const elements = document.querySelectorAll(selector+'.pos-'+position);
    
        if (elements.length === 0) {
            // No es un warning, puede que no haya elementos que apilar temporalmente
            return;
        }
    
        const spacing = -5; // Espaciado entre elementos
        const edgeOffset = '5px'; // Distancia desde el borde de la pantalla
    
        // Separar la posición en vertical y horizontal
        const [vertical, horizontal] = position.split('-');
    
        // Invertir el array para que el último elemento añadido aparezca en la parte superior de la pila
        const elementsArray = Array.from(elements).reverse();
    
        let currentOffset = spacing;
    
        elementsArray.forEach((element) => {
            // Resetear posiciones para evitar conflictos
            element.style.top = 'auto';
            element.style.bottom = 'auto';
            element.style.left = 'auto';
            element.style.right = 'auto';
    
            // Aplicar posición fija
            element.style.position = 'fixed';
    
            // Configurar la transición
            if (animated) {
                element.style.transition = `${vertical} 0.3s ease-out`;
            } else {
                element.style.transition = 'none';
            }
    
            // Posicionamiento horizontal
            element.style[horizontal] = edgeOffset;
    
            // Posicionamiento vertical
            element.style[vertical] = `${currentOffset}px`;
    
            // Actualizar el offset para el siguiente elemento
            currentOffset += element.offsetHeight + spacing;
        });
    }



    moveElementToTarget = function(element, target) {

                    // Validar que target exists y no es null
                    if (!target) {
                        console.warn('moveElementToTarget: target is null or undefined');
                        return;
                    }
                    
                    // Guardar referencia al target en el overlay para futuras comprobaciones
                    element.overlay.dataset.targetElement = target.id || target.className || 'custom-target';
                    
                    // Obtener la posición del elemento target
                    const targetRect = target.getBoundingClientRect();
                    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                    const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;
                    
                    // Buscar todas las notificaciones existentes para el mismo target
                    const existingNotifications = Array.from(document.querySelectorAll('.wq-dialog-overlay.notify.target'))
                        .filter(overlay => overlay !== element.overlay && overlay.dataset.targetElement === element.overlay.dataset.targetElement);
                    
                    // Calcular el offset vertical basado en las notificaciones existentes
                    let verticalOffset = 0;
                    const spacing = 5; // Espaciado entre notificaciones
                    
                    existingNotifications.forEach(notification => {
                        const notificationRect = notification.getBoundingClientRect();
                        verticalOffset += notificationRect.height + spacing;
                    });
                    
                    // Posicionar el dialog sobre el elemento (primera notificación en el mismo top + margen)
                    // Las siguientes se apilan debajo
                    element.overlay.style.position = 'absolute';
                    element.overlay.style.top = (targetRect.top + scrollTop + 5 + verticalOffset) + 'px';
                    element.overlay.style.left = (targetRect.left + scrollLeft + 5) + 'px';
                    element.overlay.style.right = 'auto'; // Resetear right
                    element.overlay.style.width = (targetRect.width - 10) + 'px'; // Ajustar el ancho al del target menos los márgenes
                    element.overlay.style.zIndex = 1000 + existingNotifications.length; // z-index incremental
                    element.overlay.style.opacity = 1; // Asegurar que esté visible
    };






    const message_icons = [];
    message_icons['error']   ='<i class="dialog-icon fa fa-bug"></i>';
    message_icons['danger']  ='<i class="dialog-icon fa fa-bug"></i>';
    message_icons['info']    ='<i class="dialog-icon fa fa-info-circle"></i>';
    message_icons['alert']   ='<i class="dialog-icon fa fa-exclamation-triangle"></i>';
    message_icons['warning'] ='<i class="dialog-icon fa fa-exclamation-triangle"></i>';
    message_icons['success'] ='<i class="dialog-icon fa fa-check-square-o"></i>';

    window.notify = function( message, type='info', timeout = null, target=null, onShow=null, onClose=null, position='bottom-right') {

        /////// console.log('TARGET',target,typeof target)
        
        if (typeof target == 'string')
            target = document.querySelector(target);

        let classes = target && typeof target == 'object' ? 'notify target' : 'notify stacked';
        classes += ' dialog-' + type;
        classes += ' pos-'+position;
        message = message_icons[type]+message;


        $("body").dialog({
            title: "Alerta",
            //width: "400px",
            class: classes,
            draggable:false,
            height: "auto",
            defaultType: "form",
            //buttons: [ $.dialog.okButton ],
            content: `<p>${message}</p>`,
            timeout: timeout,
            onBeforeLoad: function(dialogInstance) { if (!target) stackFixedElements('.wq-dialog-overlay.notify.stacked',true,position);   },
            onClose: function(dialogInstance)      { 
                if (!target) {
                    stackFixedElements('.wq-dialog-overlay.notify.stacked',true,position);
                }
                if (typeof onClose=='function') onClose($(dialogInstance.overlay));
            },
            onLoad: function(dialogInstance)       { if (target && typeof target == 'object')  moveElementToTarget(dialogInstance, target); if (typeof onShow=='function')   onShow($(dialogInstance.overlay))}
        });
    };


    // Sobrescribir confirm()
    window.confirm = function(message, timeout = false) {
        return new Promise((resolve) => {
            $("body").dialog({
                title: "Confirmar",
                width: "400px",
                height: "auto",
                defaultType: "form",
                buttons: [
                    {
                        text: "Aceptar",
                        action: function(event, overlay) {
                            document.body.removeChild(overlay); // Cerrar el diálogo
                            resolve(true); // Confirmado
                        }
                    },
                    {
                      text: "Cancelar",
                      action: function(event, overlay) {
                          document.body.removeChild(overlay); // Cerrar el diálogo
                          resolve(false); // Cancelado
                      }
                    }
                ],
                content: `<p style="padding: 0 20px;">${message}</p>`,
                timeout: timeout,
                onClose: function() {
                    // Si se cierra por timeout, resolveremos como false
                    resolve(false);
                }
            });
        });
    };
  
    // Sobrescribir prompt()
    window.prompt = function(message, defaultValue = "", timeout = false) {
        return new Promise((resolve) => {
            // Crear el input para el prompt
            const input = document.createElement("input");
            input.type = "text";
            input.value = defaultValue;
            input.style.width = "90%";
            input.style.padding = "8px";
            input.style.margin = "10px 20px";
            input.style.boxSizing = "border-box";

            // Crear el contenido del diálogo
            const content = document.createElement("div");
            content.innerHTML = `<p style="padding: 0 20px;">${message}</p>`;
            content.appendChild(input);

            $("body").dialog({
                title: "Prompt",
                width: "400px",
                height: "auto",
                defaultType: "form",
                buttons: [
                    {
                    text: "Aceptar",
                    action: function(event, overlay) {
                            document.body.removeChild(overlay); // Cerrar el diálogo
                            resolve(input.value); // Resolver con el valor introducido
                        }
                    },
                    {
                        text: "Cancelar",
                        action: function(event, overlay) {
                            document.body.removeChild(overlay); // Cerrar el diálogo
                            resolve(null); // Resolver con null (cancelado)
                        }
                    }
                ],
                content: content,
                timeout: timeout,
                onClose: function() {
                    // Si se cierra por timeout, resolveremos como null
                    resolve(null);
                },
                onLoad: function() {
                    // Enfocar el input automáticamente
                    setTimeout(() => input.focus(), 100);
                }
                //.innerHTML // Usar el contenido HTML creado
            });
        });
    };

    window.image = function(img, title = false, timeout = false) {
        $("body").dialog({
            title: title||"Imagen "+img,
            type:'image',
            width: "auto",
            height: "auto",
            buttons: [ $.dialog.closeButton ],
            content: img,
            timeout: timeout || 5000, // Tiempo de cierre automático por defecto
        });
    };

    // Función para generar el template del diálogo
    const getDialogTemplate = (msg, dialog_icon, color, bgcolor, type = "Error") => {
        return `<div style="display: flex; align-items: center; padding: 20px 20px 20px 20px; color: ${color}; background-color: ${bgcolor};">
            <div style="font-size: 24px; margin-right: 12px; color:${color};">
                ${dialog_icon}
            </div>
            <div style="flex: 1;">
                <!--<div style="font-weight: 600; margin-bottom: 4px; font-size: 16px;">
                    ${type}
                </div>-->
                <div style="font-size: 14px; line-height: 1.4;">
                    ${msg}
                </div>
            </div>
        </div>`;
    };

    window.error = function(msg,title=false,timeout=false) {

        const dialog_icon = "⚠️";
        const dialog = $("body").dialog({
            title: title||"Error",
            width: "auto",
            //class: 'opening-slide-left', 
            height: "auto",
            buttons: [ $.dialog.closeButton ],
            content: getDialogTemplate(msg, dialog_icon, '#d32f2f','#ffebee', "Error"),
            timeout: timeout || 5000, // Fix: usar timeout pasado o 5 segundos por defecto
            closeAnimation: 'shake', // Animación específica para errores
            openAnimation: 'slide-down' // Animación al abrir el diálogo
        });
        
        // Retornar el objeto dialog para permitir control externo
        return dialog;
    };


    // Funciones de utilidad adicionales que aprovechan la nueva funcionalidad de timeout

    window.success = function(msg, title = false, timeout = false) {
        const dialog_icon = "✅";
        const dialog = $("body").dialog({
            title: title||"Éxito",
            width: "auto",
            height: "auto",
            buttons: [ $.dialog.closeButton ],
            content: getDialogTemplate(msg, dialog_icon, '#2e7d32','#e8f5e8', "Éxito"),
            //     timeout: timeout || 3000,
            openAnimation: 'fade',
            closeAnimation: 'fade'
        });
        
        return dialog;
    };

    
    window.danger = function(msg, title = false, timeout = false) {
        const dialog_icon = "☢️";
        const dialog = $("body").dialog({
            title: title||"Peligro",
            width: "auto",  
            height: "auto",
            buttons: [ $.dialog.closeButton ],
            content: getDialogTemplate(msg, dialog_icon, '#ee0b44ff','#fde3e9ff', "Peligro"),
            //    timeout: timeout || 6000,
            openAnimation: 'bounce',
            closeAnimation: 'zoom'
        });
        
        return dialog;
    };

    window.warning = function(msg, title = false, timeout = false) {
        const dialog_icon = "⚠️";
        const dialog = $("body").dialog({
            title: title||"Advertencia",
            width: "auto",
            height: "auto",
            buttons: [ $.dialog.closeButton ],
            content: getDialogTemplate(msg, dialog_icon, '#f57c00','#fff3e0', "Advertencia"),
            //     timeout: timeout || 4000,
            openAnimation: 'slide-up',
            closeAnimation: 'slide-up'
        });
        
        return dialog;
    };

    window.info = function(msg, title = false, timeout = false) {
        const dialog_icon = "ℹ️";
        const dialog = $("body").dialog({
            title: title||"Información",
            width: "auto",
            height: "auto",
            buttons: [ $.dialog.closeButton ],
            content: getDialogTemplate(msg, dialog_icon, '#1976d2','#e3f2fd', "Información"),
            //    timeout: timeout || 6000,
            openAnimation: 'bounce',
            closeAnimation: 'zoom'
        });
        
        return dialog;
    };


    /*

    // Ejemplos con nueva funcionalidad de timeout

    // Diálogos básicos
    alert("Este es un mensaje de alerta.");
    alert("Mensaje que se cierra en 3 segundos", 3000);

    confirm("¿Estás seguro?").then(result => {
        if (result) {
            console.log("Confirmado");
        } else {
            console.log("Cancelado");
        }
    });

    // Confirm con timeout (se resuelve como false si expira)
    confirm("¿Continuar? (se cancela en 5 segundos)", 5000).then(result => {
          console.log("Resultado:", result);
    });

    prompt("¿Cuál es tu nombre?", "John Doe").then(value => {
        if (value !== null) {
            console.log("Valor ingresado:", value);
        } else {
            console.log("Cancelado");
        }
    });

    // Nuevas funciones de utilidad
    error("Error crítico del sistema", "Error", 5000);
    success("Operación completada exitosamente"); // Auto-close en 3 segundos por defecto
    warning("Esta acción no se puede deshacer");
    info("Nueva actualización disponible", "Información", 10000);

    // Diálogos personalizados con timeout
    $("body").dialog({
        title: "Mi Diálogo",
        content: "<p>Este diálogo se cierra automáticamente</p>",
        timeout: 4000,
        buttons: [$.dialog.closeButton]
    });

    // Imagen con auto-close
    image("ruta/imagen.jpg", "Mi Imagen", 8000);

    */