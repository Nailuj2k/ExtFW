/**
 * Solución unificada para interceptar formularios en modalform
 * Funciona tanto con jQuery como con wQuery
 */
(function() {
  'use strict';
  
  // Esta función es la que realmente maneja el envío del formulario
  function handleFormSubmit(form, event) {
    if (event) event.preventDefault();
    
    // Usar getAttribute para evitar que un input con name/id="id" sobrescriba la propiedad
    var rawId = form.getAttribute('id') || '';    
    console.log('Manejando envío de formulario:', rawId || 'sin ID');
    
    // Obtener el ID del formulario
    var formId = rawId.replace('form_', '');
    
    // Variables para el progreso de archivo
    var showfileprogress = false;
    var bar, percent, fstatus;
    
    // Comprobar si tenemos elementos para mostrar el progreso de subida
    if (document.querySelector('.fileprogress_bar')) {
      showfileprogress = true;
      bar = document.querySelector('.fileprogress_bar');
      percent = document.querySelector('.fileprogress_percent');
      fstatus = document.getElementById('fileprogress_status');
      
      // Mostrar la barra de progreso
      if (document.querySelector('.fileprogress_progress')) {
        document.querySelector('.fileprogress_progress').style.display = 'block';
      }
      
      // Resetear la barra de progreso
      if (fstatus) fstatus.innerHTML = '';
      if (bar) bar.style.width = '0%';
      if (percent) percent.innerHTML = '0%';
    }
    
    // Crear un FormData con los datos del formulario
    var formData = new FormData(form);
    
    //console.log('action', form.action);
    //console.log('formData',formData);
    formData.set('token', _TOKEN_); // Asegurar que el token CSRF siempre se envía
    
    // Procesar datos de textarea si es necesario
    if (typeof FORMS !== 'undefined' && typeof str2crypt !== 'undefined') {
      // console.log('Procesando campos textarea para encriptación si es necesario');  
      for (var pair of formData.entries()) {
        var input = form.querySelector('textarea[name="' + pair[0] + '"]');

        if (input && FORMS['ID_' + formId + '_token']) {

          // Si es un campo de tipo textarea, encriptarlo
          var value = pair[1];
          
          // Si estamos usando TinyMCE, obtener el contenido del editor
          if (typeof tinymce !== 'undefined' && tinymce.get(pair[0])) {
            value = tinymce.get(pair[0]).getContent();
          }
          
          // Encriptar el valor
          formData.set(pair[0], str2crypt(value, FORMS['ID_' + formId + '_token']));
        }
      }
    }
    
    // Realizar la petición AJAX
    var xhr = new XMLHttpRequest();
    
    // Configurar la barra de progreso para archivos
    if (showfileprogress && xhr.upload) {
      xhr.upload.addEventListener('progress', function(event) {
        if (event.lengthComputable) {
          var percentComplete = Math.round((event.loaded / event.total) * 100);
          var percentVal = percentComplete + '%';
          
          if (bar) bar.style.width = percentVal;
          if (percent) percent.innerHTML = percentVal;
        }
      }, false);
    }
    
    xhr.open('POST', form.action, true);
    
    // Configurar callbacks
    xhr.onreadystatechange = function() {
      if (xhr.readyState === 4) {
        
        /////// console.log('FormSubmit Response - Status:', xhr.status, 'ResponseText:', xhr.responseText);
        
        // Completado - actualizar la barra de progreso al 100%
        if (showfileprogress) {
          if (bar) bar.style.width = '100%';
          if (percent) percent.innerHTML = '100%';
          if (fstatus) fstatus.innerHTML = xhr.responseText;
          
          // Ocultar la barra de progreso después de completar
          setTimeout(function() {
            if (document.querySelector('.fileprogress_progress')) {
              document.querySelector('.fileprogress_progress').style.display = 'none';
            }
          }, 1000);
        }
        
        var response;
        try {
          response = JSON.parse(xhr.responseText);
        } catch (err) {
          console.error('Error parseando JSON:', err);

          console.log('XHR response content',xhr.responseText)

          response = {
            error: 1,
            msg: 'Error en la respuesta del servidor'
          };
        }
        
        if (xhr.status >= 200 && xhr.status < 300) {
          /////////////console.log('Status exitoso. Response object:', response);
          
          // Ejecutar cualquier JavaScript que venga en la respuesta
          var scriptMatch = xhr.responseText.match(/<script[^>]*>(.*?)<\/script>/s);
          if (scriptMatch && scriptMatch[1]) {
            try {
              eval(scriptMatch[1]);
              ////console.log('JavaScript de respuesta ejecutado');
            } catch (scriptErr) {
              console.log('Error ejecutando JavaScript de respuesta:', scriptErr);
            }
          }
          
          // Éxito
          if (response.error === 0 || response.error === '0' || response.error === 4) {

            // Cerrar modal
            var modal = form.closest('.wq-dialog-overlay');
            if (modal) {
              modal.style.display = 'none';
              setTimeout(function() {
                modal.remove();
              }, 300);
            }
          
            if (typeof module_name !== 'undefined' && typeof reloadGrid === 'function') { // Recargar grid si es necesario
              reloadGrid(module_name, response);
            }//else 
            if (typeof onTableEvent === 'function') {  // Llamar a onTableEvent si existe
              onTableEvent(response);
            }//else
            if (typeof onCommentEvent === 'function') {   // Llamar a onCommentEvent si existe
              onCommentEvent(response);
            }//else 
            if (typeof showMessageInfo === 'function' && response.msg) { // Mostrar mensaje de éxito
              showMessageInfo(response.msg);
            }
            
            // Remover clase modalopen
            document.body.classList.remove('modalopen');

          } else {
 
            // Error
            let showNotifyError = true;
            
            if (typeof onErrorEvent === 'function') {   // Llamar a onError si existe
               showNotifyError = onErrorEvent(response);
            }else if (typeof $ !== 'undefined') {
               if (response.html)
                 $.modalform({ 'title': 'Error', 'html': response.html, 'buttons': 'close' });
            }

            if(showNotifyError && typeof showMessageError === 'function' && response.msg) {
              showMessageError(response.msg);
            }


          }
        } else {
          // Error
          if (typeof showMessageError === 'function') {
            showMessageError('Error en la solicitud: ' + xhr.status);
          }
        }
      }
    };
    
    // Enviar formulario
    xhr.send(formData);
  }

  // Función de inicialización que se ejecuta cuando el DOM está listo
  function init() {
    // Si jQuery está disponible, interceptarlo directamente en jQuery
    if (typeof $ !== 'undefined' && typeof $.fn !== 'undefined') {
      // Interceptar eventos submit en formularios usando jQuery
      $(document).on('submit', 'form:not(.no-ajax)', function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('Interceptando formulario con jQuery:', this.getAttribute('id') || 'sin ID');
        handleFormSubmit(this, e);
        return false;
      });
      
      // Si $.modalform existe, modificarlo para marcar formularios después de crear modales
      if (typeof $.modalform === 'function') {
        var originalModalForm = $.modalform;
        $.modalform = function(options) {
          var result = originalModalForm.apply(this, arguments);
          
          // Después de crear el modalform, marcar los formularios
          setTimeout(function() {
            $('form:not(.no-ajax)').each(function() {
              if (!this.dataset.wQqueryForm) {
                this.dataset.wQueryForm = 'true';
                console.log('Formulario en modal marcado para interceptación:', this.getAttribute('id') || 'sin ID');
              }
            });
          }, 100);
          
          return result;
        };
      }
    }
    /*********************************************************************************
    // Interceptar envíos de formularios a nivel de documento (para wQuery)
    // Este listener solo se activará si el evento no fue detenido por wQuery
    document.body.addEventListener('submit', function(e) {
      if (e.target.tagName === 'FORM' && !e.target.classList.contains('no-ajax')) {
        e.preventDefault();
        console.log('Interceptando formulario con vanilla JS:', e.target.id || 'sin ID');
        handleFormSubmit(e.target, e);
      }
    });
    **********************************************************************************/
  }
  
  // Inicializar cuando el DOM esté listo
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
  
  // Exportar función para reinicializar explícitamente
  window.reinitFormInterceptor = function(container) {
    console.log('Reinicializando interceptor de formularios');
    
    var forms = container 
      ? (typeof $ !== 'undefined' ? $(container).find('form:not(.no-ajax)') : container.querySelectorAll('form:not(.no-ajax)'))
      : (typeof $ !== 'undefined' ? $('form:not(.no-ajax)') : document.querySelectorAll('form:not(.no-ajax)'));
    /*  
    if (typeof $ !== 'undefined') {
      forms.each(function() {
        if (!this.dataset.wQueryForm) {
          $(this).ajaxForm();
        }
      });
    } else {
    */
      forms.forEach(function(form) {
        if (!form.dataset.wQueryForm) {
          form.dataset.wQueryForm = 'true';
        }
      });
    //}
  };
})();


