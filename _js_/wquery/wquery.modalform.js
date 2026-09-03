(function($) {

    'use strict';
  
    // Preserve the compatibility with $.modalform
    $.modalform = function(params, callback) {

        // Convert modalform params to dialog format
        let buttons = [];
        /*
        buttons.push({
            text: "Test",
            action:  function(event, overlay) {
                alert("Este es un mensaje de alerta.");
            }
        });
        */

        // Handle button configurations
        if (params.buttons) {

            if (params.buttons.indexOf('ok') > -1) {
                buttons.push({
                    text: "Aceptar",
                    action: function(e, overlay) {
                        if (callback) callback(true);
                        $(overlay).remove();
                    }
                });
            }

            if (params.buttons.indexOf('cancel') > -1) {
                buttons.push({
                    text: "Cancelar",
                    class : "btn btn-danger",
                    action: function(e, overlay) {
                        if (callback) callback(false);
                        $(overlay).remove();
                    }
                });
            }

        }

        $("body").dialog({
            title:  params.title ? params.title : 'Cargando...',
            width: "850px",
            height: "auto",
            defaultType:'form',
            content:  params.url ? params.url : params.html,
            buttons
        });
        
    };
  
    // Helper function to show a message dialog (replacing the global messageDialog function)
    window.messageDialog = function(msg, callback) {
       return $.modalform({'title': '', 'html': msg, 'buttons': 'ok cancel'}, callback);
    };
  
    // Helper function to close all modals (replacing cancelModal)
    window.cancelModal = function() {
        const overlays = document.querySelectorAll('.wq-dialog-overlay');
        overlays.forEach(overlay => {
            overlay.remove();
        });
        return false;
    };
  
    // Helper function to close a specific modal (replacing modalClose)
    window.modalClose = function(element) {
        const overlay = element.closest('.wq-dialog-overlay');
        if (overlay) {
            overlay.remove();
        }
        document.body.classList.remove('modalopen');
        return false;
    };

})($);
/*
function showModalFormWithAjax(url) {

    $("body").dialog({
        title: "Contenido desde URL",
        width: "80%",
        height: "80%",
        content:  url,
        buttons: [
            {
                text: "Abrir Diálogo Anidado",
                action: function() {
                   $("body").dialog({
                        title: "Diálogo Anidado",
                        width: "400px",
                        height: "200px",
                        buttons: [
                            {
                                text: "Cerrar",
                                action: function(event, overlay) {
                                    document.body.removeChild(overlay);
                                }
                            }
                        ]
                    });
                }
            },    
            {
                text: "Alert",
                action: function(event, overlay) {
                    alert("Este es un mensaje de alerta.");
                }
            },
            {
                text: "Confirm",
                action: function(event) {
                    confirm("¿Estás seguro?").then(result => {
                        if (result) {
                            alert("Confirmado");
                        } else {
                            alert("Cancelado");
                        }
                    });
                }
            },
            {
                text: "Prompt",
                action: function(event) {
                    prompt("¿Cuál es tu nombre?", "John Doe").then(value => {
                        if (value !== null) {
                            alert("Valor ingresado:"+ value);
                        } else {
                            alert("Cancelado");
                        }
                    });
                }
            }
        ]
    });

}

*/