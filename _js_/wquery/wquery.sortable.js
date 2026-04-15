/**
 * wQuery Sortable Plugin - Implementación basada en eventos de mouse
 * 
 * Versión: 1.3.1
 * Fecha: 21 de abril de 2025
 * 
 * Una implementación ligera de sortable utilizando eventos de mouse directos
 * para mayor compatibilidad y confiabilidad.
 */

(function($) {
  $.fn.sortable = function(options) {
    // Opciones predeterminadas
    options = $.extend({
      items: '> *',
      handle: null,
      placeholder: 'sortable-placeholder',
      forcePlaceholderSize: true,
      helper: null,
      revert: false,
      axis: false,
      connectWith: false,
      start: null,
      sort: null,
      change: null,
      update: null,
      stop: null,
      cancel: 'input, textarea, button, select, option, a', // Elementos que cancelan el inicio del arrastre
      // Callbacks adicionales para compatibilidad total con jQuery UI sortable
      beforeStop: null,
      over: null,
      out: null,
      receive: null
    }, options || {});

    // Variables globales para estado durante el arrastre
    let activeItem = null;     // Elemento que se está arrastrando
    let placeholder = null;    // Elemento placeholder
    let startPos = null;       // Posición inicial del mouse
    let offsetX = 0, offsetY = 0; // Offset relativo al elemento
    let startParent = null;    // Contenedor inicial
    let startIndex = -1;       // Índice inicial
    let sortableIn = 0;        // Variable para controlar el estado over/out

    // Variable para tracking de movimiento entre contenedores
    let currentContainer = null;

    // Helper para obtener posición del mouse
    function getMousePosition(e) {
      return {
        x: e.clientX,
        y: e.clientY
      };
    }

    // Función para verificar si un elemento o sus padres tienen una clase
    function hasClassOrParent(element, className) {
      while (element && element.nodeType === 1) {
        if (element.classList.contains(className)) return true;
        element = element.parentNode;
      }
      return false;
    }

    // Función para verificar si el arrastre debe cancelarse
    function shouldCancel(e) {
      if (!options.cancel) return false;
      
      const target = e.target;
      
      // Verificar si el clic fue en un enlace o elemento cancelable
      if (target.tagName === 'A' || target.closest('a')) {
        return true; // Siempre cancelar el drag si se hizo clic en un enlace
      }
      
      return target.matches(options.cancel) || hasClassOrParent(target, 'ui-resizable-handle');
    }

    // Función para obtener el elemento más cercano bajo un punto
    function getElementUnderPoint(x, y, container, exclude) {
      // Obtener todos los elementos en esa posición
      const elements = document.elementsFromPoint(x, y);
      
      // Filtrar por elementos que pertenezcan al contenedor actual
      for (let i = 0; i < elements.length; i++) {
        const el = elements[i];
        if (el !== exclude && el !== placeholder && container.contains(el)) {
          // Encontrar el elemento sortable padre (puede ser un hijo de un item sortable)
          let sortableParent = el;
          while (sortableParent && sortableParent.parentNode !== container) {
            sortableParent = sortableParent.parentNode;
            if (!sortableParent || sortableParent === document.body) return null;
          }
          return sortableParent;
        }
      }
      return null;
    }

    return this.each(function() {
      const container = this;
      
      // Implementar método toArray para compatibilidad
      this.sortable = function(method) {
        if (method === 'toArray') {
          // Devolver array de IDs como lo hace jQuery UI sortable
          if (!container || !container.children) {
            console.warn('El contenedor no tiene children');
            return [];
          }
          
          try {
            // Asegurar que devolvemos un array JavaScript real
            const result = [];
            for (let i = 0; i < container.children.length; i++) {
              const child = container.children[i];
              if (child && child.id) {
                result.push(child.id);
              }
            }
            return result;
          } catch (e) {
            console.error('Error en toArray:', e);
            return [];
          }
        }
        return null;
      };
      
      // Obtener los elementos ordenables
      const getItems = function() {
        if (options.items === '> *') {
          return Array.from(container.children);
        } else if (options.items.indexOf('>') === 0) {
          // Para selectores que comienzan con '>'
          const cleanSelector = options.items.replace(/^\s*>\s*/, '');
          return Array.from(container.children).filter(child => {
            return !cleanSelector || child.matches(cleanSelector);
          });
        } else {
          return Array.from(container.querySelectorAll(options.items));
        }
      };
      
      // Limpiar manejadores anteriores si existen
      getItems().forEach(item => {
        if (item._sortableHandlers) {
          const handlers = item._sortableHandlers;
          if (handlers.mousedown) {
            item.removeEventListener('mousedown', handlers.mousedown);
          }
          delete item._sortableHandlers;
        }
      });
      
      // Handler para mouse down: inicio del arrastre
      function handleMouseDown(e) {
        if (e.button !== 0) return; // Solo permitir clic izquierdo
        if (shouldCancel(e)) return; // Cancelar si es elemento no arrastrable
        
        const item = this;
        const handle = options.handle ? item.querySelector(options.handle) : item;
        
        // Solo iniciar si el clic está en el handle
        if (options.handle && !e.target.closest(options.handle)) return;
        
        e.preventDefault();
        e.stopPropagation();
        
        // Guardar estado inicial
        activeItem = item;
        startParent = container;
        startIndex = Array.from(container.children).indexOf(item);
        currentContainer = container;
        sortableIn = 0;

        // Crear placeholder
        placeholder = document.createElement('div');
        placeholder.className = options.placeholder;
        if (options.forcePlaceholderSize) {
          const rect = item.getBoundingClientRect();
          placeholder.style.height = rect.height + 'px';
          placeholder.style.width = rect.width + 'px';
          placeholder.style.margin = window.getComputedStyle(item).margin;
        }
        
        // Obtener posición inicial del mouse y offset
        startPos = getMousePosition(e);
        const rect = item.getBoundingClientRect();
        offsetX = startPos.x - rect.left;
        offsetY = startPos.y - rect.top;
        
        // Clonar item para usar como "ghost" durante el arrastre
        const ghost = item.cloneNode(true);
        ghost.style.position = 'fixed';
        ghost.style.zIndex = '1000';
        ghost.style.width = rect.width + 'px';
        ghost.style.height = rect.height + 'px';
        ghost.style.opacity = '0.8';
        ghost.style.pointerEvents = 'none';
        ghost.style.boxShadow = '0 0 10px rgba(0,0,0,0.3)';
        
        // Agregar una clase especial para permitir estilos adicionales vía CSS
        ghost.classList.add('ui-sortable-helper');

        document.body.appendChild(ghost);
        
        // Posicionar el ghost en la posición del mouse
        ghost.style.left = (startPos.x - offsetX) + 'px';
        ghost.style.top = (startPos.y - offsetY) + 'px';
        
        // Poner placeholder donde estaba el item
        container.insertBefore(placeholder, item);
        
        // Ocultar el item original durante el arrastre
        const originalDisplay = item.style.display;
        item.style.display = 'none';
        
        // Crear objeto UI similar al de jQuery UI
        const ui = {
          item: $(item),
          placeholder: $(placeholder),
          helper: $(ghost),
          position: { top: startPos.y, left: startPos.x },
          originalPosition: { top: rect.top, left: rect.left },
          offset: { top: rect.top, left: rect.left }
        };
        
        // Llamar al callback de inicio
        if (typeof options.start === 'function') {
          options.start.call(container, e, ui);
        }
        
        // Handler para mover durante el arrastre
        function handleMouseMove(e) {
          if (!activeItem) return;
          
          e.preventDefault();
          
          // Calcular nueva posición
          const pos = getMousePosition(e);
          
          // Restricción por eje si está configurada
          let newX = pos.x - offsetX;
          let newY = pos.y - offsetY;
          
          if (options.axis === 'x') {
            newY = rect.top;
            pos.y = startPos.y;
          } else if (options.axis === 'y') {
            newX = rect.left;
            pos.x = startPos.x;
          }
          
          // Mover el ghost
          ghost.style.left = newX + 'px';
          ghost.style.top = newY + 'px';
          
          // Actualizar el objeto UI
          ui.position = { top: pos.y, left: pos.x };
          ui.offset = { top: newY, left: newX };
          
          // Encontrar el elemento debajo del cursor
          const pointElement = getElementUnderPoint(pos.x, pos.y, currentContainer, ghost);
          
          if (pointElement) {
            // Determinar si colocar antes o después
            const pointRect = pointElement.getBoundingClientRect();
            const isBefore = (options.axis === 'x') ? 
                             (pos.x < pointRect.left + pointRect.width / 2) : 
                             (pos.y < pointRect.top + pointRect.height / 2);
            
            if (isBefore) {
              if (pointElement.previousSibling !== placeholder) {
                currentContainer.insertBefore(placeholder, pointElement);
              }
            } else {
              if (pointElement.nextSibling !== placeholder) {
                currentContainer.insertBefore(placeholder, pointElement.nextSibling);
              }
            }
          }
          
          // Verificar conectividad con otros contenedores
          if (options.connectWith) {
            const connectTargets = $(options.connectWith);
            
            connectTargets.each(function() {
              if (this === currentContainer) return; // Saltar el contenedor actual
              
              const targetRect = this.getBoundingClientRect();
              
              // Verificar si el mouse está sobre este contenedor
              if (pos.x >= targetRect.left && pos.x <= targetRect.right &&
                  pos.y >= targetRect.top && pos.y <= targetRect.bottom) {
                
                if (sortableIn === 0 && typeof options.over === 'function') {
                  options.over.call(this, e, ui);
                  sortableIn = 1;
                }
                
                // Cambiar al nuevo contenedor
                const oldContainer = currentContainer;
                currentContainer = this;
                
                // Si el placeholder no está en este contenedor, agregarlo
                if (placeholder.parentNode !== currentContainer) {
                  currentContainer.appendChild(placeholder);
                  
                  // Llamar al callback receive
                  if (typeof options.receive === 'function') {
                    options.receive.call(currentContainer, e, ui);
                  }
                }
                
                // Buscar elemento para posicionar el placeholder
                const targetElement = getElementUnderPoint(pos.x, pos.y, currentContainer, ghost);
                
                if (targetElement) {
                  const targetRect = targetElement.getBoundingClientRect();
                  const isBefore = (pos.y < targetRect.top + targetRect.height / 2);
                  
                  if (isBefore) {
                    currentContainer.insertBefore(placeholder, targetElement);
                  } else {
                    currentContainer.insertBefore(placeholder, targetElement.nextSibling);
                  }
                } else {
                  // Si no hay elementos, agregar al final
                  currentContainer.appendChild(placeholder);
                }
                
                return false; // Salir del each
              } else if (sortableIn === 1 && typeof options.out === 'function') {
                options.out.call(this, e, ui);
                sortableIn = 0;
              }
            });
          }
          
          // Llamar al callback de sort si existe
          if (typeof options.sort === 'function') {
            options.sort.call(container, e, ui);
          }
        }
        
        // Handler para soltar
        function handleMouseUp(e) {
          if (!activeItem) return;
          
          e.preventDefault();
          
          // Limpiar
          document.removeEventListener('mousemove', handleMouseMove);
          document.removeEventListener('mouseup', handleMouseUp);
          
          // Llamar a beforeStop si existe
          if (typeof options.beforeStop === 'function') {
            options.beforeStop.call(container, e, ui);
          }
          
          // Restaurar el elemento original
          item.style.display = originalDisplay;
          
          // Eliminar el ghost
          document.body.removeChild(ghost);
          
          // Mover el elemento a su posición final
          if (placeholder && placeholder.parentNode) {
            placeholder.parentNode.insertBefore(item, placeholder);
            placeholder.parentNode.removeChild(placeholder);
          }
          
          // Verificar si cambió de posición
          const finalContainer = item.parentNode;
          const finalIndex = Array.from(finalContainer.children).indexOf(item);
          const positionChanged = startParent !== finalContainer || startIndex !== finalIndex;
          
          // Llamar al callback de update si cambió la posición
          if (positionChanged && typeof options.update === 'function') {
            options.update.call(finalContainer, e, ui);
          }
          
          // Llamar al callback de stop
          if (typeof options.stop === 'function') {
            options.stop.call(container, e, ui);
          }
          
          // Limpiar variables globales
          activeItem = null;
          placeholder = null;
          startPos = null;
          startParent = null;
          startIndex = -1;
          currentContainer = null;
        }
        
        // Agregar handlers globales
        document.addEventListener('mousemove', handleMouseMove);
        document.addEventListener('mouseup', handleMouseUp);
      }
      
      // Configurar elementos sortables
      getItems().forEach(item => {
        // Guardar referencia al handler para limpieza
        item._sortableHandlers = {
          mousedown: handleMouseDown
        };
        
        // Asignar handler de mousedown
        item.addEventListener('mousedown', handleMouseDown);
        
        // Aplicar cursor
        if (!options.handle) {
          item.style.cursor = 'move';
        } else {
          const handles = item.querySelectorAll(options.handle);
          handles.forEach(handle => {
            handle.style.cursor = 'move';
          });
        }
      });
    });
  };
})($);
