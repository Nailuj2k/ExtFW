/**
 * wQuery - Una mini biblioteca para reemplazar jQuery con funcionalidades básicas
 * 
 * Versión: 1.0.1
 * Fecha: 2025-04-16
 * 
 * Autores:
 *  JTS 
 *  Copilot Claude 3.7 Sonnet (versión 1.0.0 2024-04-06)
 *  ChatGPT   (refactorización versión 1.0.1 2025-04-16)
 * 
 * Esta biblioteca implementa un subconjunto mínimo de la funcionalidad de jQuery
 * enfocada en los métodos más comúnmente utilizados en script.js y dejanddo de lado
 * la compatibilidad con navegadores antiguos. (los modernos son gratis).
 * 
 * Minify: cd _js_\wquery && npx terser wquery.js -o wquery.min.js --compress --mangle
 * 
 */

(function(window) {
    'use strict';
  
  // ——— Monkey‑patch global para que querySelectorAll acepte #ids que empiecen por dígito ———

  // Guardamos las implementaciones nativas
  const _docQSA = Document.prototype.querySelectorAll;
  const _elQSA  = Element.prototype.querySelectorAll;

  // Función helper que intercepta todas las llamadas
  function selectAll(context, selector) {
    // Caso “#foo” puro (incluso empezando por número)
    if (/^#[^ .,:>+~\[\]]+$/.test(selector)) {
      const id = selector.slice(1);
      const el = context.nodeType === 9
        ? context.getElementById(id)
        : // en elementos no‑documento, buscamos por atributo para aceptar dígitos
          context.querySelector(`[id="${id}"]`);
      return el ? [el] : [];
    }
    // Para los demás selectores, escapamos cualquier #123abc → [id="123abc"]
    const safeSel = selector.replace(/#(\d[\w-]*)/g, '[id="$1"]');
    // Delegamos al QSA original según sea Document o Element
    const fn = context.nodeType === 9 ? _docQSA : _elQSA;
    return fn.call(context, safeSel);
  }

  // Sobreescribimos los nativos para que usen siempre selectAll
  Document.prototype.querySelectorAll = function(sel) {
    return selectAll(this, sel);
  };
  Element.prototype.querySelectorAll = function(sel) {
    return selectAll(this, sel);
  };

  // ——————————————————————————————————————————————————————————————————————

    // Función interna para crear un objeto wQuery a partir de un array de elementos
    function createResult(arr) {
      const result = new wQuery();
      arr.forEach((el, i) => {
        result[i] = el;
      });
      result.length = arr.length;
      return result;
    }
  
    // Constructor principal
    function wQuery(selector, context) {
      return new wQuery.fn.init(selector, context);
    }
  
    // Constructor Event para emular jQuery.Event
    wQuery.Event = function(src, props) {
      // Permitir crear un evento sin 'new'
      if (!(this instanceof wQuery.Event)) {
        return new wQuery.Event(src, props);
      }
      
      // Manejar el caso cuando src es un evento DOM existente
      if (src && src.type) {
        this.originalEvent = src;
        this.type = src.type;
        
        // Los eventos que provienen del DOM ya tienen defaultPrevented calculado
        this.isDefaultPrevented = src.defaultPrevented ? 
          function() { return true; } : 
          function() { return false; };
          
        // Copiar propiedades del evento original
        const properties = ['altKey', 'bubbles', 'cancelable', 'ctrlKey', 'currentTarget',
                           'detail', 'eventPhase', 'metaKey', 'relatedTarget', 'shiftKey',
                           'target', 'timeStamp', 'view', 'which', 'char', 'code', 'charCode',
                           'key', 'keyCode', 'button', 'buttons', 'clientX', 'clientY', 'offsetX',
                           'offsetY', 'pageX', 'pageY', 'screenX', 'screenY', 'touches', 'changedTouches'];
        
        for (let i = 0; i < properties.length; i++) {
          const prop = properties[i];
          if (src[prop] !== undefined) {
            this[prop] = src[prop];
          }
        }
      } else {
        // Si src es una cadena, crear un evento personalizado
        this.type = typeof src === 'string' ? src : '';
        this.timeStamp = Date.now();
      }
      
      // Combinar con propiedades adicionales si se proporcionan
      if (props) {
        for (let prop in props) {
          this[prop] = props[prop];
        }
      }
      
      // Establecer estado inicial de métodos de prevención
      this.isDefaultPrevented = this.isPropagationStopped = this.isImmediatePropagationStopped = function() {
        return false;
      };
    };
    
    // Métodos del prototipo de Event
    wQuery.Event.prototype = {
      constructor: wQuery.Event,
      
      // Métodos para manipular el evento
      preventDefault: function() {
        this.isDefaultPrevented = function() { return true; };
        if (this.originalEvent && this.originalEvent.preventDefault) {
          this.originalEvent.preventDefault();
        }
        return this;
      },
      
      stopPropagation: function() {
        this.isPropagationStopped = function() { return true; };
        if (this.originalEvent && this.originalEvent.stopPropagation) {
          this.originalEvent.stopPropagation();
        }
        return this;
      },
      
      stopImmediatePropagation: function() {
        this.isImmediatePropagationStopped = function() { return true; };
        this.stopPropagation();
        if (this.originalEvent && this.originalEvent.stopImmediatePropagation) {
          this.originalEvent.stopImmediatePropagation();
        }
        return this;
      }
    };
  
    // Definición del prototipo
    wQuery.fn = wQuery.prototype = {
      constructor: wQuery,
      length: 0,
  
      init: function(selector, context) {
        if (!selector) return this;
        if (selector === window) {
          this[0] = window;
          this.length = 1;
          return this;
        }
        if (selector.nodeType) {
          this[0] = selector;
          this.length = 1;
          return this;
        }
        if (typeof selector === "string") {
          if (selector.startsWith("<") && selector.endsWith(">") && selector.length >= 3) {
            // Crear elementos desde HTML
            const div = document.createElement("div");
            div.innerHTML = selector;
            Array.from(div.children).forEach((el, i) => {
              this[i] = el;
            });
            this.length = div.children.length;
          } else {
            // Selección de elementos mediante selector
            const contextNode = (context instanceof wQuery ? context[0] : (context || document));
            if (!contextNode) return this;
            const hasBareFirst = /:first(?![\w-])/.test(selector);
            const hasBareLast = /:last(?![\w-])/.test(selector);

            if (hasBareFirst) {
              const baseSelector = selector.replace(/:first(?![\w-])/g, '');
              const elements = contextNode.querySelectorAll(baseSelector);
              if (elements.length > 0) {
                this[0] = elements[0];
                this.length = 1;
              }
            } else if (hasBareLast) {
              const baseSelector = selector.replace(/:last(?![\w-])/g, '');
              const elements = contextNode.querySelectorAll(baseSelector);
              if (elements.length > 0) {
                this[0] = elements[elements.length - 1];
                this.length = 1;
              }
            } else {
              const elements = contextNode.querySelectorAll(selector);
              Array.from(elements).forEach((el, i) => {
                this[i] = el;
              });
              this.length = elements.length;
            }
          }
          return this;
        }
        if (typeof selector === "function") {
          if (document.readyState === "complete") {
            selector();
          } else {
            document.addEventListener("DOMContentLoaded", selector);
          }
          return this;
        }
        return this;
      },
  
      each: function(callback) {
        for (let i = 0; i < this.length; i++) {
          if (callback.call(this[i], i, this[i]) === false) break;
        }
        return this;
      },
  
      get: function(index) {
        if (index === undefined) {
          return Array.from({ length: this.length }, (_, i) => this[i]);
        }
        if (index < 0) index = this.length + index;
        return this[index];
      },
  
      first: function() {
        return this.length > 0 ? wQuery(this[0]) : wQuery();
      },
  
      last: function() {
        return this.length > 0 ? wQuery(this[this.length - 1]) : wQuery();
      },
  
      // MÉTODOS DE ACCESO AL DOM
  
      find: function(selector) {
        let result = [];
        if (selector.includes(':first')) {
          const baseSelector = selector.replace(':first', '');
          this.each(function() {
            const elements = this.querySelectorAll(baseSelector);
            if (elements.length > 0) result.push(elements[0]);
          });
        } else if (selector.includes(':last')) {
          const baseSelector = selector.replace(':last', '');
          this.each(function() {
            const elements = this.querySelectorAll(baseSelector);
            if (elements.length > 0) result.push(elements[elements.length - 1]);
          });
        } else {
          this.each(function() {
            Array.from(this.querySelectorAll(selector)).forEach(el => result.push(el));
          });
        }
        return createResult(result);
      },
  
      not: function(selector) {
        const result = [];
        this.each(function(i) {
          if (typeof selector === "function") {
            if (!selector.call(this, i, this)) result.push(this);
          } else if (typeof selector === "string") {
            if (!this.matches(selector)) result.push(this);
          } else {
            const compareWith = selector.nodeType ? [selector] : (selector instanceof wQuery ? selector : wQuery(selector));
            let matched = false;
            for (let elem of compareWith) {
              if (this === elem) {
                matched = true;
                break;
              }
            }
            if (!matched) result.push(this);
          }
        });
        return createResult(result);
      },
  
      parent: function() {
        const result = [];
        this.each(function() {
          if (this.parentNode && !result.includes(this.parentNode))
            result.push(this.parentNode);
        });
        return createResult(result);
      },
  
      parents: function(selector) {
        const result = [];
        this.each(function() {
          let parent = this.parentNode;
          while (parent && parent !== document) {
            if (!selector || parent.matches(selector)) {
              if (!result.includes(parent)) result.push(parent);
            }
            parent = parent.parentNode;
          }
        });
        return createResult(result);
      },
  
      prevAll: function(selector) {
        const result = [];
        this.each(function() {
          let prev = this.previousElementSibling;
          while (prev) {
            if (!selector || prev.matches(selector)) {
              if (!result.includes(prev)) result.push(prev);
            }
            prev = prev.previousElementSibling;
          }
        });
        return createResult(result);
      },
  
      next: function() {
        const result = [];
        this.each(function() {
          const nextEl = this.nextElementSibling;
          if (nextEl && !result.includes(nextEl))
            result.push(nextEl);
        });
        return createResult(result);
      },
  
      closest: function(selector) {
        const result = [];
        this.each(function() {
          let current = this;
          while (current && current !== document) {
            if (current.matches(selector)) {
              result.push(current);
              break;
            }
            current = current.parentNode;
          }
        });
        return createResult(result);
      },
  
      eq: function(index) {
        if (index < 0) index = this.length + index;
        return wQuery(this.get(index));
      },
      /* 
      clone: function(withDataAndEvents) {
        const result = [];
        this.each(function() {
          const cloned = this.cloneNode(true);
          if (withDataAndEvents) {
            if (this._Data) {
              cloned._Data = (typeof structuredClone === 'function')
                ? structuredClone(this._Data)
                : JSON.parse(JSON.stringify(this._Data));
            }
            const events = this._Events;
            if (events) {
              cloned._Events = {};
              for (let type in events) {
                if (events.hasOwnProperty(type)) {
                  cloned._Events[type] = [];
                  events[type].forEach(handler => {
                    cloned.addEventListener(type, handler);
                    cloned._Events[type].push(handler);
                  });
                }
              }
            }
          }
          result.push(cloned);
        });
        return wQuery(result);
      },
      */


    // Método clone mejorado y compatible con jQuery
    clone: function(withDataAndEvents) {
        const result = [];
        
        this.each(function() {
        // Clonar el nodo con todas sus propiedades
        const cloned = this.cloneNode(true);
        
        // Si se solicitó clonar datos y eventos
        if (withDataAndEvents) {
            // Copiar datos almacenados en _Data
            if (this._Data) {  //CHECK rename _Data if error
            cloned._Data = JSON.parse(JSON.stringify(this._Data));
            }
            
            // Copiar posición (para compatibilidad con jQuery)
            cloned.position = function() {
            const rect = this.getBoundingClientRect();
            const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            return {
                top: rect.top + scrollTop,
                left: rect.left + scrollLeft
            };
            };
            
            // Copiar eventos (usamos el sistema de eventos de la biblioteca)
            if (this._Events) {      //CHECK rename _Events if error
            cloned._Events = {};
            
            // Copiar todos los eventos registrados
            for (const eventType in this._Events) {
                cloned._Events[eventType] = [...this._Events[eventType]];
                
                // Añadir listeners para cada evento
                this._Events[eventType].forEach(handler => {
                cloned.addEventListener(eventType, handler);
                });
            }
            }
        }
        
        result.push(cloned);
        });
        
        return createResult(result);
    },


      children: function(selector) {
        const result = [];
        this.each(function() {
          const childArray = Array.from(this.children);
          if (!selector) {
            childArray.forEach(child => {
              if (!result.includes(child)) result.push(child);
            });
          } else {
            if (selector.indexOf(':') !== -1) {
              let [baseSelector, pseudoSelector] = selector.split(':');
              let filtered = childArray.filter(child => !baseSelector || child.matches(baseSelector));
              if (pseudoSelector === 'first' && filtered.length)
                result.push(filtered[0]);
              else if (pseudoSelector === 'last' && filtered.length)
                result.push(filtered[filtered.length - 1]);
              else if (pseudoSelector.startsWith('eq(') && pseudoSelector.endsWith(')')) {
                const index = parseInt(pseudoSelector.slice(3, -1));
                if (!isNaN(index) && index < filtered.length)
                  result.push(filtered[index]);
              }
            } else {
              childArray.forEach(child => {
                if (child.matches(selector) && !result.includes(child))
                  result.push(child);
              });
            }
          }
        });
        return createResult(result);
      },
  
      prev: function() {
        const result = [];
        this.each(function() {
          const prevEl = this.previousElementSibling;
          if (prevEl && !result.includes(prevEl)) result.push(prevEl);
        });
        return createResult(result);
      },
  
      prevAll: function(selector) {
        const result = [];
        this.each(function() {
          let prev = this.previousElementSibling;
          while (prev) {
            if (!selector || prev.matches(selector)) {
              if (!result.includes(prev)) result.push(prev);
            }
            prev = prev.previousElementSibling;
          }
        });
        return createResult(result);
      },
  
      siblings: function(selector) {
        const result = [];
        this.each(function() {
          const parent = this.parentNode;
          if (parent) {
            Array.from(parent.children).forEach(child => {
              if (child !== this && (!selector || child.matches(selector)) && !result.includes(child))
                result.push(child);
            });
          }
        });
        return createResult(result);
      },
  
      nextAll: function(selector) {
        const result = [];
        this.each(function() {
          let next = this.nextElementSibling;
          while (next) {
            if (!selector || next.matches(selector)) {
              if (!result.includes(next)) result.push(next);
            }
            next = next.nextElementSibling;
          }
        });
        return createResult(result);
      },
  
      nextUntil: function(selector) {
        const result = [];
        this.each(function() {
          let next = this.nextElementSibling;
          while (next && (!selector || !next.matches(selector))) {
            if (!result.includes(next)) result.push(next);
            next = next.nextElementSibling;
          }
        });
        return createResult(result);
      },
  
      prevUntil: function(selector) {
        const result = [];
        this.each(function() {
          let prev = this.previousElementSibling;
          while (prev && (!selector || !prev.matches(selector))) {
            if (!result.includes(prev)) result.push(prev);
            prev = prev.previousElementSibling;
          }
        });
        return createResult(result);
      },
  
      // MANIPULACIÓN DE ATRIBUTOS Y PROPIEDADES
  
      attr: function(name, value) {
        if (value === undefined) {
          if (this.length === 0) return undefined;
          return this[0].getAttribute(name);
        }
        return this.each(function() {
          if (this.nodeType === 1) {
            (value === null) ? this.removeAttribute(name) : this.setAttribute(name, value);
          }
        });
      },
  
      data: function(key, value) {
        if (value === undefined) {
          if (this.length === 0) return undefined;
          // Primero intentamos obtener del sistema _Data interno
          if (this[0]._Data && this[0]._Data[key] !== undefined) {
            return this[0]._Data[key];
          }
          // Si no hay datos en _Data, intentamos leer del atributo data-*
          const dataAttr = this[0].getAttribute('data-' + key);
          if (dataAttr !== null) {
            try {
              return JSON.parse(dataAttr);
            } catch(e) {
              return dataAttr;
            }
          }
          return undefined;
        }
        return this.each(function() {
          if (!this._Data) this._Data = {};
          this._Data[key] = value;
          // También actualizamos el atributo data-* en el DOM para mantener consistencia
          this.setAttribute('data-' + key, typeof value === 'object' ? JSON.stringify(value) : value);
        });
      },
  
      val: function(value) {
        if (value === undefined)
          return this[0] ? this[0].value : null;
        return this.each(function() { this.value = value; });
      },
  
      text: function(text) {
        if (text === undefined) {
          let result = "";
          this.each(function() { result += this.textContent; });
          return result;
        }
        return this.each(function() { this.textContent = text; });
      },
  
      html: function(content) {
        if (content === undefined)
          return this[0] ? this[0].innerHTML : null;
        return this.each(function() {
          this.innerHTML = content;
          Array.from(this.getElementsByTagName('script')).forEach(script => {
            const newScript = document.createElement('script');
            Array.from(script.attributes).forEach(attr => {
              newScript.setAttribute(attr.name, attr.value);
            });
            newScript.innerHTML = script.innerHTML;
            script.parentNode.replaceChild(newScript, script);
          });
        });
      },
  
      // MANIPULACIÓN DE CLASES
  
      hasClass: function(className) {
        return this[0] ? this[0].classList.contains(className) : false;
      },
  
      addClass: function(className) {
        return this.each(function() {
          if (className) {
            className.split(" ").forEach(cls => {
              if (cls) this.classList.add(cls);
            }, this);
          }
        });
      },
  
      removeClass: function(className) {
        return this.each(function() {
          if (className) {
            className.split(" ").forEach(cls => {
              if (cls) this.classList.remove(cls);
            }, this);
          } else {
            this.className = "";
          }
        });
      },
  
      toggleClass: function(className) {
        return this.each(function() {
          className.split(/\s+/).forEach(cls => {
            this.classList.toggle(cls);
          }, this);
        });
      },
  
      is: function(selector) {
        if (!this[0]) return false;
        if (typeof selector === "string") {
          if (selector === ":hidden")
            return this[0].offsetWidth === 0 && this[0].offsetHeight === 0;
          if (selector === ":visible")
            return this[0].offsetWidth > 0 || this[0].offsetHeight > 0;
          return this[0].matches(selector);
        } else if (typeof selector === "function") {
          return selector.call(this[0], 0, this[0]);
        } else if (selector.nodeType) {
          return this[0] === selector;
        }
        return false;
      },
  
      // MANIPULACIÓN DEL DOM
  
      append: function(content) {
        return this.each(function() {
          if (typeof content === "string") {
            this.insertAdjacentHTML("beforeend", content);
          } else if (content instanceof wQuery) {
            Array.from(content).forEach(el => this.appendChild(el));
          } else if (content.nodeType) {
            this.appendChild(content);
          }
        });
      },
  
      appendTo: function(target) {
        // Convertir el target a un array de elementos DOM
        let targets;
        
        if (typeof target === "string") {
          // Si es un selector, obtener elementos que coincidan
          targets = Array.from(document.querySelectorAll(target));
        } else if (target instanceof wQuery) {
          // Si es objeto wQuery, obtener sus elementos
          targets = Array.from(target);
        } else if (target.nodeType) {
          // Si es un nodo DOM único, ponerlo en un array
          targets = [target];
        } else if (target.length) {
          // Si es una colección como NodeList o HTMLCollection
          targets = Array.from(target);
        } else {
          // Fallback si no es ninguno de los anteriores
          return this;
        }
        
        // Si no hay targets válidos, retornar sin hacer nada
        if (targets.length === 0) return this;
        
        // Clonar y añadir nodos
        const nodes = Array.from(this);
        
        targets.forEach((targ, i) => {
          // Verificar que targ es un elemento DOM válido
          if (targ && targ.nodeType === 1) {
            nodes.forEach(node => {
              // Para el último target, mover el nodo original; para los demás, usar clones
              targ.appendChild(i === targets.length - 1 ? node : node.cloneNode(true));
            });
          }
        });
        
        return this;
      },
  
      empty: function() {
        return this.each(function() {
          while (this.firstChild) this.removeChild(this.firstChild);
        });
      },
  
      remove: function() {
        return this.each(function() {
          if (this.parentNode) this.parentNode.removeChild(this);
        });
      },
  
      before: function(content) {
        return this.each(function() {
          if (typeof content === "string") {
            this.insertAdjacentHTML("beforebegin", content);
          } else if (content instanceof wQuery) {
            Array.from(content).forEach(el => this.parentNode.insertBefore(el, this));
          } else if (content.nodeType) {
            this.parentNode.insertBefore(content, this);
          }
        });
      },
  
      after: function(content) {
        return this.each(function() {
          if (typeof content === "string") {
            this.insertAdjacentHTML("afterend", content);
          } else if (content instanceof wQuery) {
            Array.from(content).forEach(el => {
              if (this.nextSibling)
                this.parentNode.insertBefore(el, this.nextSibling);
              else
                this.parentNode.appendChild(el);
            });
          } else if (content.nodeType) {
            if (this.nextSibling)
              this.parentNode.insertBefore(content, this.nextSibling);
            else
              this.parentNode.appendChild(content);
          }
        });
      },
  
      prepend: function(content) {
        return this.each(function() {
          if (typeof content === "string") {
            this.insertAdjacentHTML("afterbegin", content);
          } else if (content instanceof wQuery) {
            for (let i = content.length - 1; i >= 0; i--) {
              this.firstChild
                ? this.insertBefore(content[i], this.firstChild)
                : this.appendChild(content[i]);
            }
          } else if (content.nodeType) {
            this.firstChild
              ? this.insertBefore(content, this.firstChild)
              : this.appendChild(content);
          }
        });
      },
  
      prependTo: function(target) {
        let targets = typeof target === "string" ? document.querySelectorAll(target) : target;
        if (!targets.length) targets = [targets];
        const nodes = Array.from(this);
        Array.from(targets).forEach((targ, i) => {
          nodes.forEach(node => {
            if (i === targets.length - 1) {
              targ.firstChild ? targ.insertBefore(node, targ.firstChild) : targ.appendChild(node);
            } else {
              const clone = node.cloneNode(true);
              targ.firstChild ? targ.insertBefore(clone, targ.firstChild) : targ.appendChild(clone);
            }
          });
        });
        return this;
      },
  
      insertBefore: function(target) {
        let targets = typeof target === "string" ? document.querySelectorAll(target) :
                      (target instanceof wQuery ? target : target.nodeType ? [target] : []);
        if (!targets.length) return this;
        const nodes = Array.from(this);
        Array.from(targets).forEach((targ, i) => {
          const parent = targ.parentNode;
          if (parent) {
            if (i === targets.length - 1) {
              const fragment = document.createDocumentFragment();
              nodes.forEach(node => fragment.appendChild(node));
              parent.insertBefore(fragment, targ);
            } else {
              const fragment = document.createDocumentFragment();
              nodes.forEach(node => fragment.appendChild(node.cloneNode(true)));
              parent.insertBefore(fragment, targ);
            }
          }
        });
        return this;
      },
  
      insertAfter: function(target) {
        let targets = typeof target === "string" ? document.querySelectorAll(target) :
                      (target instanceof wQuery ? target : target.nodeType ? [target] : []);
        if (!targets.length) return this;
        const nodes = Array.from(this);
        Array.from(targets).forEach((targ, i) => {
          const parent = targ.parentNode;
          if (parent) {
            const nextSibling = targ.nextSibling;
            if (i === targets.length - 1) {
              const fragment = document.createDocumentFragment();
              nodes.forEach(node => fragment.appendChild(node));
              parent.insertBefore(fragment, nextSibling);
            } else {
              const fragment = document.createDocumentFragment();
              nodes.forEach(node => fragment.appendChild(node.cloneNode(true)));
              parent.insertBefore(fragment, nextSibling);
            }
          }
        });
        return this;
      },
  
      // CSS Y DIMENSIONES
  
      css: function(property, value) {
        if (typeof property === "object") {
          return this.each(function() {
            Object.keys(property).forEach(key => {
              this.style[key] = property[key];
            });
          });
        }
        if (value === undefined) {
          if (!this[0]) return null;
          return getComputedStyle(this[0])[property];
        }
        return this.each(function() {
          this.style[property] = value;
        });
      },
  
      width: function(value) {
        if (this.length === 0) return null;
        if (this[0] === window || this[0] === Window.prototype) return window.innerWidth;
        if (this[0] === document) return document.documentElement.clientWidth;
        if (value === undefined) return this[0].offsetWidth;
        return this.each(function() {
          this.style.width = typeof value === "number" ? value + "px" : value;
        });
      },
  
      height: function(value) {
        if (this.length === 0) return null;
        if (this[0] === window || this[0] === Window.prototype) return window.innerHeight;
        if (this[0] === document) return document.documentElement.clientHeight;
        if (value === undefined) return this[0].offsetHeight;
        return this.each(function() {
          this.style.height = typeof value === "number" ? value + "px" : value;
        });
      },
  
      outerHeight: function(includeMargin) {
        if (this.length === 0) return null;
        const element = this[0];
        let height = element.offsetHeight;
        if (includeMargin === true) {
          const style = getComputedStyle(element);
          height += parseInt(style.marginTop) + parseInt(style.marginBottom);
        }
        return height;
      },
  
      outerWidth: function(includeMargin) {
        if (this.length === 0) return null;
        const element = this[0];
        let width = element.offsetWidth;
        if (includeMargin === true) {
          const style = getComputedStyle(element);
          width += parseInt(style.marginLeft) + parseInt(style.marginRight);
        }
        return width;
      },
  
      offset: function(coordinates) {
        if (coordinates === undefined) {
          if (this.length === 0) return null;
          const rect = this[0].getBoundingClientRect();
          return {
            top: rect.top + window.pageYOffset,
            left: rect.left + window.pageXOffset
          };
        }
        return this.each(function() {
          const $el = wQuery(this);
          if ($el.css('position') === 'static') $el.css('position', 'relative');
          const curOffset = $el.offset();
          const curTop = parseFloat($el.css('top')) || 0;
          const curLeft = parseFloat($el.css('left')) || 0;
          $el.css({
            top: curTop + (coordinates.top - curOffset.top) + 'px',
            left: curLeft + (coordinates.left - curOffset.left) + 'px'
          });
        });
      },
  
      // DESPLAZAMIENTO (SCROLL)

      scroll: function(handler) {
        if (handler) {
          // Si se proporciona un handler, añadir listener al evento scroll
          return this.on('scroll', handler);
        }
        // Si no hay handler, disparar el evento scroll
        return this.each(function() {
          const event = new Event('scroll', {
            bubbles: true,
            cancelable: true
          });
          this.dispatchEvent(event);
        });
      },

      scrollTop: function(value) {
        if (value === undefined) {
          // Getter: obtener la posición de scroll vertical
          if (this.length === 0) return null;
          
          const element = this[0];
          
          // Para window/document
          if (element === window || element === document || element === document.documentElement) {
            return window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0;
          }
          
          // Para elementos normales
          return element.scrollTop;
        }
        
        // Setter: establecer la posición de scroll vertical
        return this.each(function() {
          const element = this;
          
          // Para window/document
          if (element === window || element === document || element === document.documentElement) {
            window.scrollTo(window.pageXOffset || document.documentElement.scrollLeft || document.body.scrollLeft || 0, value);
          } else {
            // Para elementos normales
            element.scrollTop = value;
          }
        });
      },

      scrollLeft: function(value) {
        if (value === undefined) {
          // Getter: obtener la posición de scroll horizontal
          if (this.length === 0) return null;
          
          const element = this[0];
          
          // Para window/document
          if (element === window || element === document || element === document.documentElement) {
            return window.pageXOffset || document.documentElement.scrollLeft || document.body.scrollLeft || 0;
          }
          
          // Para elementos normales
          return element.scrollLeft;
        }
        
        // Setter: establecer la posición de scroll horizontal
        return this.each(function() {
          const element = this;
          
          // Para window/document
          if (element === window || element === document || element === document.documentElement) {
            window.scrollTo(value, window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0);
          } else {
            // Para elementos normales
            element.scrollLeft = value;
          }
        });
      },
  
      // VISIBILIDAD
  
      hide: function() {
        return this.each(function() {
          this.style.display = "none";
        });
      },
  
      show: function(speed, callback) {
        if (speed === undefined || speed === 0 || speed === null || typeof speed === 'object') {
          return this.each(function() {
            this.style.display = "";
            if (getComputedStyle(this).display === "none") this.style.display = "block";
            if (typeof callback === 'function') callback.call(this);
          });
        }
        const duration = typeof speed === 'number' ? speed : (speed === 'slow' ? 600 : (speed === 'fast' ? 200 : 400));
        return this.each(function() {
          const el = this;
          el.style.display = "";
          if (getComputedStyle(el).display === "none") el.style.display = "block";
          if (el.style.display !== "none" && typeof callback === "function") {
            setTimeout(() => callback.call(el), duration);
          }
        });
      },
  
      toggle: function() {
        return this.each(function() {
          const computedStyle = getComputedStyle(this);
          if (computedStyle.display === "none") {
            // Element is hidden, show it
            // Restore original display style if stored, otherwise let browser default (empty string)
            this.style.display = this._originalDisplay || ''; 
            // If setting to '' still results in 'none' (e.g. for an element styled display:none in CSS),
            // then explicitly set to 'block' as a common visible default.
            if (getComputedStyle(this).display === 'none') {
              this.style.display = 'block';
            }
          } else {
            // Element is visible, hide it
            // Store current display style before hiding
            this._originalDisplay = computedStyle.display; 
            this.style.display = "none";
          }
        });
      },
  
      // EVENTOS
  
      on: function(events, selector, data, handler, eventOptions) {
        // Compatibilidad total con jQuery: on(events [, selector] [, data], handler [, eventOptions])
        
        // Manejar diferentes combinaciones de parámetros como en jQuery
        if (typeof selector === "function") {
          // on(events, handler)
          handler = selector;
          selector = null;
          data = null;
        } else if (typeof data === "function") {
          // on(events, selector, handler) 
          handler = data;
          data = null;
        } else if (typeof selector === "object" && selector !== null && typeof data === "function") {
          // on(events, data, handler) - selector es en realidad data
          handler = data;
          data = selector;
          selector = null;
        }
        
        if (!handler) return this;
        
        // Dividir múltiples eventos separados por espacios (como en jQuery)
        const eventList = events.trim().split(/\s+/);
        
        return this.each(function() {
          const element = this;
          if (!element._Events) element._Events = {};
          
          // Procesar cada evento individualmente
          eventList.forEach(function(eventName) {
            if (!element._Events[eventName]) element._Events[eventName] = [];
            
            if (selector) {
              // Event delegation
              const listener = function(e) {
                let target = e.target;
                while (target && target !== element) {
                  if (target.matches && target.matches(selector)) {
                    e.data = data;
                    e.delegateTarget = element;
                    handler.call(target, e);
                    return;
                  }
                  target = target.parentElement;
                }
              };
              element._Events[eventName].push(listener);
              element.addEventListener(eventName, listener, eventOptions);
            } else {
              // Direct event binding
              const listener = function(e) {
                e.data = data;
                handler.call(element, e);
              };
              element._Events[eventName].push(listener);
              element.addEventListener(eventName, listener, eventOptions);
            }
          });
        });
      },
  
      bind: function(eventName, handler) {
        return this.on(eventName, handler);
      },
  
      delegate: function(selector, eventName, handler) {
        return this.on(eventName, function(event) {
          let currentNode = event.target;
          while (currentNode && currentNode !== this) {
            if (currentNode.matches && currentNode.matches(selector)) {
              event.delegateTarget = currentNode;
              handler.call(currentNode, event);
              return;
            }
            currentNode = currentNode.parentNode;
          }
        });
      },
  
      off: function(eventName, handler) {
        return this.each(function() {
          if (eventName) {
            if (handler) {
              this.removeEventListener(eventName, handler);
            }
          }
        });
      },
  
      click: function(handler) {
        if (handler) {
          // Registrar evento click para dispositivos de escritorio
          this.on("click", handler);
          
          // Agregar soporte para eventos táctiles en dispositivos móviles con passive: true
          let touchStarted = false;
          let touchMoved = false;
          let touchTarget = null;
          
          // Detectar inicio de toque - AHORA CON PASSIVE
          this.on("touchstart", function(e) {
            touchStarted = true;
            touchMoved = false;
            touchTarget = e.target;
          }, null, null, { passive: true });
          
          // Detectar si el usuario mueve el dedo - AHORA CON PASSIVE
          this.on("touchmove", function() {
            touchMoved = true;
          }, null, null, { passive: true });
          
          // Al finalizar el toque - AHORA CON PASSIVE
          this.on("touchend", function(e) {
            if (touchStarted && !touchMoved && e.target === touchTarget) {
              // Simular evento click con los mismos datos
              const clickEvent = new Event('click', {
                bubbles: true,
                cancelable: true
              });
              
              // Conservar propiedades importantes del evento original
              clickEvent.pageX = e.changedTouches ? e.changedTouches[0].pageX : 0;
              clickEvent.pageY = e.changedTouches ? e.changedTouches[0].pageY : 0;
              
              e.target.dispatchEvent(clickEvent);
              
              // Prevenir doble ejecución en algunos navegadores
              e.preventDefault();
            }
            
            // Resetear estado
            touchStarted = false;
            touchMoved = false;
            touchTarget = null;
          }, null, null, { passive: false }); // touchend necesita poder llamar preventDefault
    
          return this;
        }
        
        // Si no hay handler, disparar el evento click en todos los elementos
        return this.each(function() { 
          this.click(); 
        });
      },
  
      focus: function(handler) {
        if (handler) return this.on("focus", handler);
        return this.each(function() { this.focus(); });
      },
  
      blur: function(handler) {
        if (handler) return this.on("blur", handler);
        return this.each(function() { this.blur(); });
      },
  
      keydown: function(handler) {
        return this.on("keydown", handler);
      },
  
      keyup: function(handler) {
        return this.on("keyup", handler);
      },
  
      keypress: function(handler) {
        return this.on("keypress", handler);
      },
  
      change: function(handler) {
        return this.on("change", handler);
      },

      // Eventos de ratón
      mousedown: function(handler) {
        return this.on("mousedown", handler);
      },
      
      mouseup: function(handler) {
        return this.on("mouseup", handler);
      },
      
      mousemove: function(handler) {
        return this.on("mousemove", handler);
      },
      
      mouseover: function(handler) {
        return this.on("mouseover", handler);
      },
      
      mouseout: function(handler) {
        return this.on("mouseout", handler);
      },
      
      mouseenter: function(handler) {
        return this.on("mouseenter", handler);
      },
      
      mouseleave: function(handler) {
        return this.on("mouseleave", handler);
      },
      
      hover: function(fnOver, fnOut) {
        return this.mouseenter(fnOver).mouseleave(fnOut || fnOver);
      },

    // Método submit para formularios
    submit: function(handler) {
        if (typeof handler === 'function') {
        // Si se proporciona un handler, es para agregar un listener al evento submit
        return this.on('submit', handler);
        } else {
        // Si no hay handler, disparar el evento submit
        return this.each(function() {
            if (this.tagName && this.tagName.toLowerCase() === 'form') {
            // Crear y disparar un evento submit nativo
            const event = new Event('submit', {
                bubbles: true,
                cancelable: true
            });
            this.dispatchEvent(event);
            
            // Si el evento no fue cancelado (preventDefault), enviar el formulario
            if (!event.defaultPrevented) {
                this.submit();
            }
            }
        });
        }
    },

    // Método reset para formularios
    reset: function(handler) {
        if (typeof handler === 'function') {
        // Si se proporciona un handler, es para agregar un listener al evento reset
        return this.on('reset', handler);
        } else {
        // Si no hay handler, disparar el evento reset
        return this.each(function() {
            if (this.tagName && this.tagName.toLowerCase() === 'form') {
            // Crear y disparar un evento reset nativo
            const event = new Event('reset', {
                bubbles: true,
                cancelable: true
            });
            this.dispatchEvent(event);
            
            // Si el evento no fue cancelado (preventDefault), resetear el formulario
            if (!event.defaultPrevented) {
                this.reset();
            }
            }
        });
        }
    },

      // Devuelve posición relativa al offsetParent (como en jQuery)
      position: function() {
        const el = this[0];
        if (!el) return null;
        // Top/left respecto a su offsetParent
        return {
          top: el.offsetTop,
          left: el.offsetLeft
        };
      },

      trigger: function(eventName, data) {
        let event;
        if (typeof eventName === 'object' && eventName instanceof Event) {
          // Crear un nuevo evento del mismo tipo en lugar de reutilizar el original
          // (reutilizarlo causa InvalidStateError si ya está siendo despachado)
          event = document.createEvent('HTMLEvents');
          event.initEvent(eventName.type, true, true);
        } else {
          event = document.createEvent('HTMLEvents');
          // Si necesitamos crear un evento
          if (typeof eventName === 'string') {
            event.initEvent(eventName, true, true);
            if (data) {
              event.data = data;
              Object.keys(data).forEach(key => { event[key] = data[key]; });
            }
          }
        }
        
        return this.each(function() {
          if (!this._Events) this._Events = {};
          if (this._Events[event.type]) {
            this._Events[event.type].forEach(fn => fn.call(this, event, data));
          }
          if (typeof this.dispatchEvent === 'function')
            this.dispatchEvent(event);
        });
      },
  
      fadeOut: function(speed, callback) {
        const duration = typeof speed === 'number' ? speed : (speed === 'slow' ? 600 : (speed === 'fast' ? 200 : 400));
        return this.each(function() {
          const el = this;
          
          // Si ya está oculto, ejecutar callback inmediatamente
          if (window.getComputedStyle(el).display === 'none') {
            if (typeof callback === 'function') {
              Promise.resolve().then(() => callback.call(el));
            }
            return;
          }
          
          // Crear una Promise para manejar la animación
          const animationPromise = new Promise((resolve) => {
            const startOpacity = parseFloat(window.getComputedStyle(el).opacity);
            
            // Establecer opacidad inicial si no está definida
            if (isNaN(startOpacity) || startOpacity === 0) {
              el.style.opacity = '1';
            }
            
            el.style.transition = `opacity ${duration}ms ease`;
            
            // Forzar repintado antes de cambiar opacidad
            el.offsetHeight;
            
            const onComplete = () => {
              el.removeEventListener('transitionend', onComplete);
              el.style.display = 'none';
              el.style.transition = '';
              resolve();
            };
            
            // Usar transitionend como método principal
            el.addEventListener('transitionend', onComplete, { once: true });
            
            // Fallback con setTimeout solo en caso de que transitionend falle
            const fallbackTimer = setTimeout(onComplete, duration + 100);
            
            // Limpiar el fallback si transitionend se ejecuta
            el.addEventListener('transitionend', () => clearTimeout(fallbackTimer), { once: true });
            
            // Iniciar la transición
            setTimeout(() => {
              el.style.opacity = '0';
            }, 10);
          });
          
          // Ejecutar callback cuando la Promise se resuelva
          if (typeof callback === 'function') {
            animationPromise.then(() => callback.call(el));
          }
        });
      },
  
      fadeIn: function(speed, callback) {
        const duration = typeof speed === 'number' ? speed : (speed === 'slow' ? 600 : (speed === 'fast' ? 200 : 400));
        return this.each(function() {
          const el = this;
          const originalDisplay = el.getAttribute('data-display') ||
            (el.tagName.toLowerCase() === 'span' ? 'inline' :
            (el.tagName.toLowerCase() === 'table' ? 'table' : 'block'));
          
          // Si ya está visible, ejecutar callback inmediatamente
          if (window.getComputedStyle(el).display !== 'none' && parseFloat(window.getComputedStyle(el).opacity) === 1) {
            if (typeof callback === 'function') {
              Promise.resolve().then(() => callback.call(el));
            }
            return;
          }
          
          // Crear una Promise para manejar la animación
          const animationPromise = new Promise((resolve) => {
            el.style.opacity = '0';
            el.style.display = originalDisplay;
            el.style.transition = `opacity ${duration}ms ease`;
            
            // Forzar repintado antes de cambiar opacidad
            el.offsetHeight;
            
            const onComplete = () => {
              el.removeEventListener('transitionend', onComplete);
              el.style.transition = '';
              resolve();
            };
            
            // Usar transitionend como método principal
            el.addEventListener('transitionend', onComplete, { once: true });
            
            // Fallback con setTimeout solo en caso de que transitionend falle
            const fallbackTimer = setTimeout(onComplete, duration + 100);
            
            // Limpiar el fallback si transitionend se ejecuta
            el.addEventListener('transitionend', () => clearTimeout(fallbackTimer), { once: true });
            
            // Iniciar la transición
            setTimeout(() => {
              el.style.opacity = '1';
            }, 10);
          });
          
          // Ejecutar callback cuando la Promise se resuelva
          if (typeof callback === 'function') {
            animationPromise.then(() => callback.call(el));
          }
        });
      },
  
      slideToggle: function(speed, callback) {
        const duration = typeof speed === 'number' ? speed : (speed === 'slow' ? 600 : (speed === 'fast' ? 200 : 400));
        return this.each(function() {
          const el = this;
          const isHidden = getComputedStyle(el).display === "none";
          
          if (isHidden) {
            // Usar slideDown
            $(el).slideDown(duration, callback);
          } else {
            // Usar slideUp
            $(el).slideUp(duration, callback);
          }
        });
      },

slideUp: function(speed, callback) {
  const duration = typeof speed === 'number' ? speed : (speed === 'slow' ? 600 : (speed === 'fast' ? 200 : 400));
  const frames = Math.round(duration / 16); // Aproximadamente 60fps
  let frame = 0;

  return this.each(function() {
    const el = this;

    // Si ya está oculto, no hacemos nada (o llamamos al callback)
    if (getComputedStyle(el).display === 'none') {
      if (typeof callback === 'function') callback.call(el);
      return;
    }

    // 1. Preparar el elemento:
    // Asegurarnos de que el overflow esté oculto para la animación
    el.style.overflow = 'hidden';
    
    // 2. Calcular la altura inicial (currentHeight)
    // Establecer la altura actual para que la animación empiece desde ahí
    const initialHeight = el.offsetHeight;
    el.style.height = `${initialHeight}px`;

    // 3. Iniciar la animación
    const animate = () => {
      frame++;
      // Calculamos el porcentaje inverso para ir de 1 a 0
      const percentage = 1 - (frame / frames); 
      const currentHeight = initialHeight * percentage;

      el.style.height = `${currentHeight}px`;

      if (frame < frames) {
        requestAnimationFrame(animate);
      } else {
        // Limpieza al finalizar:
        el.style.display = 'none'; // Ocultar el elemento completamente
        el.style.height = ''; // Quitar la altura fija
        el.style.overflow = ''; // Permitir overflow si lo había

        if (typeof callback === 'function') callback.call(el);
      }
    };

    requestAnimationFrame(animate);
  });
},

slideDown: function(speed, callback) {
  const duration = typeof speed === 'number' ? speed : (speed === 'slow' ? 600 : (speed === 'fast' ? 200 : 400));
  const frames = Math.round(duration / 16); // Aproximadamente 60fps
  let frame = 0;

  return this.each(function() {
    const el = this;

    // Si ya está visible, no hacemos nada (o llamamos al callback)
    if (getComputedStyle(el).display !== 'none') {
      if (typeof callback === 'function') callback.call(el);
      return;
    }

    // 1. Preparar el elemento:
    // Poner el display original (o 'block') y overflow hidden
    // para calcular la altura natural.
    const originalDisplay = el.getAttribute('data-display') || 'block';
    el.style.display = originalDisplay;
    el.style.overflow = 'hidden';
    
    // 2. Calcular la altura final (targetHeight)
    // Guardar la altura 'auto' y luego establecerla a 0.
    const targetHeight = el.offsetHeight; // Altura real con padding/margin
    el.style.height = '0px';

    // 3. Iniciar la animación
    const animate = () => {
      frame++;
      const percentage = frame / frames;
      const currentHeight = targetHeight * percentage;

      el.style.height = `${currentHeight}px`;

      if (frame < frames) {
        requestAnimationFrame(animate);
      } else {
        // Limpieza al finalizar:
        el.style.height = ''; // Quitar la altura fija para que vuelva a 'auto' o CSS
        el.style.overflow = ''; // Permitir overflow si lo había
        if (typeof callback === 'function') callback.call(el);
      }
    };

    requestAnimationFrame(animate);
  });
},
     
     animate: function(properties, duration, easing, callback) {
        if (typeof duration === "function") {
          callback = duration;
          duration = 400;
          easing = "swing";
        } else if (typeof easing === "function") {
          callback = easing;
          easing = "swing";
        }
        duration = duration || 400;
        easing = easing || "swing";
        const easingFn = wQuery.easing[easing] || wQuery.easing.swing;
        return this.each(function() {
          const element = this;
          if (element._animationTimer) cancelAnimationFrame(element._animationTimer);
          const start = {}, target = {}, unit = {};
          Object.keys(properties).forEach(prop => {
            const cssProp = prop.replace(/([A-Z])/g, "-$1").toLowerCase();
            let computedStyle = getComputedStyle(element)[cssProp];
            let currentValue;
            if (prop === "opacity")
              currentValue = parseFloat(computedStyle);
            else if (["width", "height", "top", "left", "right", "bottom", "margin", "padding"].some(p => prop.indexOf(p) === 0))
              currentValue = parseFloat(computedStyle) || 0;
            else
              currentValue = computedStyle;
            
            let targetValue = properties[prop];
            let targetNumeric;
            let isRelative = false;
            
            // Manejar valores relativos (+=, -=)
            if (typeof targetValue === "string") {
              if (targetValue.startsWith('+=')) {
                isRelative = true;
                targetNumeric = currentValue + parseFloat(targetValue.substring(2));
              } else if (targetValue.startsWith('-=')) {
                isRelative = true;
                targetNumeric = currentValue - parseFloat(targetValue.substring(2));
              } else {
                targetNumeric = parseFloat(targetValue);
              }
            } else {
              targetNumeric = parseFloat(targetValue);
            }
            
            let targetUnit = "";
            if (typeof targetValue === "string" && !isRelative) {
              const match = targetValue.match(/^[+-]?\d+(?:\.\d+)?(.*)$/);
              if (match && match[1]) targetUnit = match[1];
            }
            if (!targetUnit && typeof computedStyle === "string") {
              const match = computedStyle.match(/^[+-]?\d+(?:\.\d+)?(.*)$/);
              if (match && match[1]) targetUnit = match[1];
            }
            if (prop === "opacity") targetUnit = "";
            else if (!targetUnit && prop !== "opacity") targetUnit = "px";
            
            start[prop] = (typeof currentValue === "number") ? currentValue : parseFloat(currentValue) || 0;
            target[prop] = targetNumeric;
            unit[prop] = targetUnit;
          });
          const startTime = performance.now();
          function animateFrame(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const easedProgress = easingFn(progress);
            Object.keys(properties).forEach(prop => {
              const value = start[prop] + (target[prop] - start[prop]) * easedProgress;
              if (prop === "opacity")
                element.style.opacity = value;
              else if (prop === "scrollTop")
                element.scrollTop = value;
              else if (prop === "scrollLeft")
                element.scrollLeft = value;
              else {
                const cssProp = prop.replace(/([A-Z])/g, "-$1").toLowerCase();
                element.style[cssProp] = value + unit[prop];
              }
            });
            if (progress < 1) {
              element._animationTimer = requestAnimationFrame(animateFrame);
            } else {
              delete element._animationTimer;
              if (typeof callback === "function") callback.call(element);
            }
          }
          element._animationTimer = requestAnimationFrame(animateFrame);
        });
      },

      BADanimate: function(properties, duration, easing, callback) {
        if (typeof duration === "function") {
          callback = duration;
          duration = 400;
          easing = "swing";
        } else if (typeof easing === "function") {
          callback = easing;
          easing = "swing";
        }
        duration = duration || 400;
        easing = easing || "swing";
        const easingFn = wQuery.easing[easing] || wQuery.easing.swing;
        return this.each(function() {
          const element = this;
          if (element._animationTimer) cancelAnimationFrame(element._animationTimer);
          const start = {}, target = {}, unit = {};
          Object.keys(properties).forEach(prop => {
            const cssProp = prop.replace(/([A-Z])/g, "-$1").toLowerCase();
            let computedStyle = getComputedStyle(element)[cssProp];
            let currentValue;
            if (prop === "opacity")
              currentValue = parseFloat(computedStyle);
            else if (["width", "height", "top", "left", "right", "bottom", "margin", "padding"].some(p => prop.indexOf(p) === 0))
              currentValue = parseFloat(computedStyle) || 0;
            else
              currentValue = computedStyle;
            let targetValue = properties[prop];
            let targetNumeric = parseFloat(targetValue);
            let targetUnit = "";
            if (typeof targetValue === "string") {
              const match = targetValue.match(/^[+-]?\d+(?:\.\d+)?(.*)$/);
              if (match && match[1]) targetUnit = match[1];
            }
            if (!targetUnit && typeof computedStyle === "string") {
              const match = computedStyle.match(/^[+-]?\d+(?:\.\d+)?(.*)$/);
              if (match && match[1]) targetUnit = match[1];
            }
            if (prop === "opacity") targetUnit = "";
            else if (!targetUnit && prop !== "opacity") targetUnit = "px";
            start[prop] = (typeof currentValue === "number") ? currentValue : parseFloat(currentValue) || 0;
            target[prop] = targetNumeric;
            unit[prop] = targetUnit;
          });
          const startTime = performance.now();
          function animateFrame(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const easedProgress = easingFn(progress);
            Object.keys(properties).forEach(prop => {
              const value = start[prop] + (target[prop] - start[prop]) * easedProgress;
              if (prop === "opacity")
                element.style.opacity = value;
              else if (prop === "scrollTop")
                element.scrollTop = value;
              else if (prop === "scrollLeft")
                element.scrollLeft = value;
              else {
                const cssProp = prop.replace(/([A-Z])/g, "-$1").toLowerCase();
                element.style[cssProp] = value + unit[prop];
              }
            });
            if (progress < 1) {
              element._animationTimer = requestAnimationFrame(animateFrame);
            } else {
              delete element._animationTimer;
              if (typeof callback === "function") callback.call(element);
            }
          }
          element._animationTimer = requestAnimationFrame(animateFrame);
        });
      },
  
      effect: function(effect, options, duration, callback) {
        if (typeof options === "function") { callback = options; options = {}; duration = 400; }
        else if (typeof duration === "function") { callback = duration; duration = 400; }
        options = options || {};
        duration = duration || 400;
        if (effect === "highlight") {
          const backgroundColor = options.color || "#ffff99";
          return this.each(function() {
            const el = this;
            const originalBg = getComputedStyle(el).backgroundColor;
            el.style.backgroundColor = backgroundColor;
            setTimeout(() => {
              wQuery(el).animate({ backgroundColor: originalBg }, duration, function() {
                if (typeof callback === "function") callback.call(el);
              });
            }, 200);
          });
        }
        return this;
      },
  
      flyTo: function(target, options = {}) {
        const defaults = {
            duration: 600,
            easing: 'ease',
            arc: 150,
            removeCloneOnComplete: true, // Behavior depends on directFly
            hideOriginalOnStart: false,   // Only if directFly = false
            hideOriginalOnComplete: false, // Only if directFly = false
            placeOriginalAtTarget: false, // Only if directFly = false
            directFly: false,             // If true, 'this' flies directly
            complete: function() {}
        };
        options = Object.assign({}, defaults, options);

        return this.each(function() {
            const elementProvided = this;

            if (!elementProvided.isConnected) {
                console.error('flyTo: Provided element is not in the DOM');
                if (typeof options.complete === 'function') options.complete.call(elementProvided);
                return;
            }

            let flyingElement;
            let originalElementForIndirect = null; // Stores elementProvided if not directFly

            // Styles of the element that will be physically moved/animated BEFORE flyTo changes them to fixed.
            // These are used for restoration if the flyingElement is not removed.
            const preFlyStyles = {
                position: elementProvided.style.position,
                left: elementProvided.style.left,
                top: elementProvided.style.top,
                zIndex: elementProvided.style.zIndex,
                display: getComputedStyle(elementProvided).display
            };

            if (options.directFly) {
                flyingElement = elementProvided;
                // Assume flyingElement is already in the DOM and positioned as desired by the caller.
            } else {
                originalElementForIndirect = elementProvided;
                flyingElement = originalElementForIndirect.cloneNode(true);
                
                originalElementForIndirect._originalDisplayForFlyTo = getComputedStyle(originalElementForIndirect).display;

                const originalRect = originalElementForIndirect.getBoundingClientRect();
                flyingElement.style.position = 'fixed'; // Clone always starts fixed for animation
                flyingElement.style.left = `${originalRect.left}px`;
                flyingElement.style.top = `${originalRect.top}px`;
                flyingElement.style.width = `${originalRect.width}px`;
                flyingElement.style.height = `${originalRect.height}px`;
                flyingElement.style.margin = '0';
                flyingElement.style.zIndex = '999999';
                flyingElement.style.display = originalElementForIndirect._originalDisplayForFlyTo === 'none' ? 'block' : originalElementForIndirect._originalDisplayForFlyTo;
                
                document.body.appendChild(flyingElement);

                if (options.hideOriginalOnStart) {
                    originalElementForIndirect.style.display = 'none';
                }
            }
            
            const startRect = flyingElement.getBoundingClientRect(); // Current visual position of the element that will fly

            // Ensure flyingElement is styled for fixed animation path
            flyingElement.style.position = 'fixed';
            flyingElement.style.left = `${startRect.left}px`;
            flyingElement.style.top = `${startRect.top}px`;
            flyingElement.style.zIndex = '999999';
            if (getComputedStyle(flyingElement).display === 'none') {
                 flyingElement.style.display = preFlyStyles.display !== 'none' ? preFlyStyles.display : 'block';
            }

            let endRect;
            if (typeof target === 'string') {
                const targetElement = document.querySelector(target);
                if (!targetElement) {
                    console.error('flyTo: Target element not found:', target);
                    if (!options.directFly) { // Cleanup for indirect fly
                        if (flyingElement.parentNode) flyingElement.parentNode.removeChild(flyingElement);
                        if (options.hideOriginalOnStart && originalElementForIndirect) {
                             originalElementForIndirect.style.display = originalElementForIndirect._originalDisplayForFlyTo;
                        }
                        if (originalElementForIndirect) delete originalElementForIndirect._originalDisplayForFlyTo;
                    } else { // Restore preFlyStyles for direct fly if target fails
                        flyingElement.style.position = preFlyStyles.position;
                        flyingElement.style.left = preFlyStyles.left;
                        flyingElement.style.top = preFlyStyles.top;
                        flyingElement.style.zIndex = preFlyStyles.zIndex;
                        flyingElement.style.display = preFlyStyles.display;
                    }
                    if (typeof options.complete === 'function') options.complete.call(elementProvided);
                    return;
                }
                endRect = targetElement.getBoundingClientRect();
            } else if (target instanceof wQuery && target.length > 0) {
                endRect = target[0].getBoundingClientRect();
            } else if (target && target.nodeType) {
                endRect = target.getBoundingClientRect();
            } else if (typeof target === 'object' && typeof target.left === 'number' && typeof target.top === 'number') {
                endRect = { left: target.left, top: target.top, width: (target.width || 0), height: (target.height || 0) };
            } else {
                console.error('flyTo: Invalid target type');
                 if (!options.directFly) { /* Similar cleanup as target not found */ 
                    if (flyingElement.parentNode) flyingElement.parentNode.removeChild(flyingElement);
                    if (options.hideOriginalOnStart && originalElementForIndirect) originalElementForIndirect.style.display = originalElementForIndirect._originalDisplayForFlyTo;
                    if (originalElementForIndirect) delete originalElementForIndirect._originalDisplayForFlyTo;
                 } else { /* Similar restoration as target not found */
                    flyingElement.style.position = preFlyStyles.position;
                    flyingElement.style.left = preFlyStyles.left;
                    flyingElement.style.top = preFlyStyles.top;
                    flyingElement.style.zIndex = preFlyStyles.zIndex;
                    flyingElement.style.display = preFlyStyles.display;
                 }
                if (typeof options.complete === 'function') options.complete.call(elementProvided);
                return;
            }

            const dx = endRect.left - startRect.left;
            const dy = endRect.top - startRect.top;

            const easeFunctions = {
                ease: function(t) { return t; }, linear: function(t) { return t; },
                easeIn: function(t) { return t * t; }, easeOut: function(t) { return t * (2 - t); },
                easeInOut: function(t) { return t < 0.5 ? 2 * t * t : -1 + (4 - 2 * t) * t; }
            };
            const ease = easeFunctions[options.easing] || easeFunctions.linear; // Default to linear
            let startTime = null;

            function animateFrame(currentTime) {
                if (!flyingElement.isConnected) {
                    if (!options.directFly && originalElementForIndirect) {
                        if (options.hideOriginalOnStart && !options.hideOriginalOnComplete && !options.placeOriginalAtTarget) {
                          originalElementForIndirect.style.display = originalElementForIndirect._originalDisplayForFlyTo;
                        }
                        delete originalElementForIndirect._originalDisplayForFlyTo;
                    }
                    if (typeof options.complete === 'function') options.complete.call(elementProvided);
                    return;
                }

                if (!startTime) startTime = currentTime;
                const elapsedTime = currentTime - startTime;
                const progress = Math.min(elapsedTime / options.duration, 1);
                const easedProgress = ease(progress);

                const currentFixedX = startRect.left + dx * easedProgress;
                const currentFixedY = startRect.top + dy * easedProgress - Math.sin(easedProgress * Math.PI) * options.arc;

                flyingElement.style.left = `${currentFixedX}px`;
                flyingElement.style.top = `${currentFixedY}px`;

                if (progress >= 1) {
                    if (options.directFly) {
                        if (options.removeCloneOnComplete) {
                            if (flyingElement.parentNode) flyingElement.parentNode.removeChild(flyingElement);
                        } else {
                            // Si es directFly y no se remueve, lo dejamos en su posición final (fixed)
                            // El callback 'complete' se encargará de su destino final.
                            // No restauramos preFlyStyles aquí para darle control total al callback.
                        }
                    } else { // Indirect fly (originalElementForIndirect exists)
                        if (options.removeCloneOnComplete) { // removeCloneOnComplete se refiere al clon (flyingElement)
                            if (flyingElement.parentNode) flyingElement.parentNode.removeChild(flyingElement);
                        } else {
                            // Si el clon (flyingElement) no se remueve, restaurar sus preFlyStyles y posicionar
                            const finalVisualLeft = parseFloat(flyingElement.style.left);
                            const finalVisualTop = parseFloat(flyingElement.style.top);

                            flyingElement.style.position = preFlyStyles.position; // Esto es para el clon
                            flyingElement.style.zIndex = preFlyStyles.zIndex;
                            // flyingElement.style.display = preFlyStyles.display; // Display podría haber cambiado

                            if (preFlyStyles.position === 'absolute' || preFlyStyles.position === 'relative') {
                                const offsetParent = flyingElement.offsetParent || document.body;
                                const parentRect = offsetParent.getBoundingClientRect();
                                flyingElement.style.left = (finalVisualLeft - parentRect.left) + 'px';
                                flyingElement.style.top = (finalVisualTop - parentRect.top) + 'px';
                            } else if (preFlyStyles.position === 'fixed') {
                                flyingElement.style.left = finalVisualLeft + 'px';
                                flyingElement.style.top = finalVisualTop + 'px';
                            } else { 
                                flyingElement.style.left = preFlyStyles.left;
                                flyingElement.style.top = preFlyStyles.top;
                            }
                        }

                        // Manejo del originalElementForIndirect
                        if (originalElementForIndirect) {
                          let finalDisplayOriginal = originalElementForIndirect._originalDisplayForFlyTo;
                          if (options.placeOriginalAtTarget) {
                            const origComputedStyle = getComputedStyle(originalElementForIndirect);
                            const origPos = origComputedStyle.position;
                            if (origPos === 'static') originalElementForIndirect.style.position = 'relative';
                            
                            const offsetParent = originalElementForIndirect.offsetParent || document.body;
                            const parentRect = offsetParent.getBoundingClientRect();
                            originalElementForIndirect.style.left = (endRect.left - parentRect.left) + 'px';
                            originalElementForIndirect.style.top = (endRect.top - parentRect.top) + 'px';
                            finalDisplayOriginal = finalDisplayOriginal === 'none' ? 'block' : finalDisplayOriginal;
                          }
                          if (options.hideOriginalOnComplete) {
                            originalElementForIndirect.style.display = 'none';
                          } else {
                            originalElementForIndirect.style.display = finalDisplayOriginal;
                          }
                          delete originalElementForIndirect._originalDisplayForFlyTo;
                        }
                    }
                    
                    if (typeof options.complete === 'function') {
                        options.complete.call(elementProvided); // elementProvided es el 'this' original de la llamada a flyTo
                    }
                    return;
                }
                requestAnimationFrame(animateFrame);
            }
            requestAnimationFrame(animateFrame);
        });
    },

    load: function(url, data, callback) {
        if (typeof data === "function") {
          callback = data;
          data = null;
        }
        const self = this;
        wQuery.ajax({
          url: url,
          method: 'GET',
          data: data,
          dataType: 'html',
          success: function(response) {
            self.each(function() {
              this.innerHTML = response;
              Array.from(this.getElementsByTagName('script')).forEach(script => {
                const newScript = document.createElement('script');
                Array.from(script.attributes).forEach(attr => {
                  newScript.setAttribute(attr.name, attr.value);
                });
                newScript.innerHTML = script.innerHTML;
                script.parentNode.replaceChild(newScript, script);
              });
              if (typeof callback === "function")
                callback.call(this, response);
            });
          },
          error: function() {
            if (typeof callback === "function")
              callback.call(self[0], "", "error");
          }
        });
        return this;
      },
      
      // Método seguro para manipular strings
      safeString: function(val) {
        if (val === null || val === undefined) return '';
        return (typeof val === 'string') ? val : (val.toString ? val.toString() : String(val));
      },

      // Método para monkeypatching seguro
      monkeyPatch: function() {
        // Prevenir errores comunes como val.replace en no-strings
        try {
          // Proteger String.prototype.replace
          const originalReplace = String.prototype.replace;
          String.prototype.replace = function() {
            return originalReplace.apply(this, arguments);
          };
          
          // Proteger llamadas a Array/Object desde valores potencialmente nulos
          ['map', 'forEach', 'filter', 'reduce', 'some', 'every'].forEach(method => {
            const originalArrayMethod = Array.prototype[method];
            Array.prototype[method] = function() {
              return originalArrayMethod.apply(this, arguments);
            };
          });
        } catch (e) {
          console.warn('No se pudo aplicar monkey patching:', e);
        }
        return this;
      },
  
      ready: function(fn) {
        if (document.readyState === "complete" ||
            (document.readyState !== "loading" && !document.documentElement.doScroll)) {
          setTimeout(fn, 1);
        } else {
          document.addEventListener("DOMContentLoaded", fn);
        }
        return this;
      },
  
      unbind: function(eventName, handler) {
        if (typeof handler === 'undefined') {
          return this.each(function() {
            if (!this._Events) return;
            if (eventName) {
              if (this._Events[eventName]) {
                this._Events[eventName].forEach(fn => this.removeEventListener(eventName, fn));
                delete this._Events[eventName];
              }
            } else {
              Object.keys(this._Events).forEach(type => {
                this._Events[type].forEach(fn => this.removeEventListener(type, fn));
              });
              this._Events = {};
            }
          });
        }
        return this.each(function() {
          if (!this._Events) return;
          if (this._Events[eventName]) {
            const index = this._Events[eventName].indexOf(handler);
            if (index !== -1) {
              this.removeEventListener(eventName, handler);
              this._Events[eventName].splice(index, 1);
            }
          }
        });
      },
  
      removeAttr: function(name) {
        return this.each(function() {
          if (this.nodeType === 1) this.removeAttribute(name);
        });
      },
  
      prop: function(name, value) {
        if (value === undefined) {
          if (this.length === 0) return undefined;
          return this[0][name];
        }
        return this.each(function() {
          this[name] = value;
        });
      },

      // Serializa un formulario en un array de objetos con name y value
      serializeArray: function() {
        const result = [];
        
        this.each(function() {
          // Solo procesar elementos form
          if (this.tagName && this.tagName.toLowerCase() !== 'form') return;
          
          // Obtener todos los elementos del formulario que pueden enviarse
          const elements = this.elements;
          
          Array.from(elements).forEach(element => {
            // Filtrar por elementos que tengan name y no estén deshabilitados
            const name = element.name;
            const type = element.type;
            
            if (!name || element.disabled) return;
            
            // Manejar diferentes tipos de inputs
            if (type === 'checkbox' || type === 'radio') {
              if (element.checked) {
                result.push({ name, value: element.value });
              }
            } else if (element.tagName === 'SELECT') {
              Array.from(element.options).forEach(option => {
                if (option.selected) {
                  result.push({ name, value: option.value || option.text });
                }
              });
            } else if (type !== 'button' && type !== 'reset' && type !== 'submit' && type !== 'file') {
              result.push({ name, value: element.value });
            }
          });
        });
        
        return result;
      },
      
      // Serializa un formulario en una cadena de texto URL-encoded
      serialize: function() {
        return this.serializeArray()
          .map(item => encodeURIComponent(item.name) + '=' + encodeURIComponent(item.value))
          .join('&');
      },

      // Implementación de map para compatibilidad con jQuery
      map: function(callback) {
        const results = [];
        this.each(function(index) {
          const result = callback.call(this, index, this);
          if (result != null) {
            results.push(result);
          }
        });
        return results;
      },

      stop: function(clearQueue, jumpToEnd) {
        return this.each(function() {
          // Si hay un temporizador de animación activo, cancelarlo
          if (this._animationTimer) {
            cancelAnimationFrame(this._animationTimer);
            delete this._animationTimer;
          }
          
          // Eliminar transiciones CSS
          this.style.transition = '';
          
          // Si jumpToEnd es true, completar inmediatamente la animación
          if (jumpToEnd) {
            // Esto es una implementación simplificada
            // Para una implementación completa, tendríamos que conocer el estado final deseado
            if (this.style.opacity === '0') {
              this.style.display = 'none';
            }
          }
        });
      },

      replaceWith: function(newContent) {
        const originalElements = this.get(); // Get a static array of elements from the current wQuery object.

        // Iterate over each element that was originally selected to be replaced.
        for (let i = 0; i < originalElements.length; i++) {
          const elementToReplace = originalElements[i];
          const parent = elementToReplace.parentNode;

          if (!parent) {
            // If the element is not in the DOM, it cannot be replaced in the traditional sense.
            // We simply skip DOM manipulation for it.
            continue;
          }

          let resolvedNewContent;
          if (typeof newContent === 'function') {
            // `this` inside the function refers to elementToReplace.
            // The function is called with (index, oldHtml).
            resolvedNewContent = newContent.call(elementToReplace, i, elementToReplace.outerHTML);
          } else {
            resolvedNewContent = newContent;
          }

          let nodesToInsert = [];
          if (typeof resolvedNewContent === 'string') {
            // If newContent is an HTML string, create new DOM elements.
            const tempContainer = document.createElement('div');
            tempContainer.innerHTML = resolvedNewContent;
            nodesToInsert = Array.from(tempContainer.childNodes);
          } else if (resolvedNewContent && resolvedNewContent.nodeType) { // Single DOM node (Element, Text, Comment, etc.)
            // If newContent is a DOM node:
            // - Use the original node if this is the first element being replaced (i === 0).

            // - Clone the node for subsequent elements.
            nodesToInsert = [i === 0 ? resolvedNewContent : resolvedNewContent.cloneNode(true)];
          } else if (resolvedNewContent instanceof wQuery) { // wQuery object
            // If newContent is a wQuery object:
            // - Get its underlying DOM elements.
            // - For each node, use the original if i === 0, otherwise clone.
            nodesToInsert = resolvedNewContent.get().map(node => (i === 0 ? node : node.cloneNode(true)));
          } else if (resolvedNewContent && typeof resolvedNewContent.length === 'number' &&
                     resolvedNewContent !== window && // Exclude window object
                     !(resolvedNewContent.nodeType) && // Exclude single DOM nodes (already handled)
                     typeof resolvedNewContent !== 'string') { // Exclude strings (already handled)
            // Handle array-like objects of nodes (e.g., NodeList, array of DOM elements)
            nodesToInsert = Array.from(resolvedNewContent).map(node => {
              if (node && node.nodeType) { // Process only actual nodes
                return i === 0 ? node : node.cloneNode(true);
              }
              return null;
            }).filter(Boolean); // Remove nulls if any non-node items were in the array-like
          }
          // Other types for resolvedNewContent (e.g., number, boolean) will result in empty nodesToInsert.

          // Insert the new nodes before the elementToReplace.
          nodesToInsert.forEach(node => {
            parent.insertBefore(node, elementToReplace);
          });

          // Remove the original elementToReplace from the DOM.
          parent.removeChild(elementToReplace);
        }

        // Return a new wQuery object containing the original set of elements that were removed.
        // The createResult function is expected to be defined in the same scope.
        return createResult(originalElements);
      }
    };
  
    // Asignar el prototype de init
    wQuery.fn.init.prototype = wQuery.fn;
  
    // Método de utilidad para obtener información sobre la biblioteca
    wQuery.fn.wQuery = function() {
      return {
        name: 'wQuery',
        version: '1.0.1',
        date: '2025-04-16',
        authors: ['JTS', 'Claude', 'ChatGPT'],
        description: 'Una mini biblioteca para reemplazar jQuery con funcionalidades básicas',
        methods: Object.keys(wQuery.fn).filter(key => typeof wQuery.fn[key] === 'function'),
        properties: Object.keys(wQuery.fn).filter(key => typeof wQuery.fn[key] !== 'function')
      };
    };
  
    // MÉTODOS ESTÁTICOS
  
    // Implementación mínima de $.extend para copiar propiedades
    wQuery.extend = function() {
      let target = arguments[0] || {},
          i = 1,
          length = arguments.length,
          deep = false;
  
      if (typeof target === "boolean") {
        deep = target;
        target = arguments[i] || {};
        i++;
      }
  
      if (typeof target !== "object" && typeof target !== "function")
        target = {};
  
      for (; i < length; i++) {
        if (arguments[i] != null) {
          for (let name in arguments[i]) {
            if (arguments[i].hasOwnProperty(name)) {
              let src = target[name],
                  copy = arguments[i][name];
  
              if (target === copy)
                continue;
  
              if (deep && copy && (wQuery.isPlainObject(copy) ||
                Array.isArray(copy))) {
                let clone;
                if (Array.isArray(copy)) {
                  clone = Array.isArray(src) ? src : [];
                } else {
                  clone = wQuery.isPlainObject(src) ? src : {};
                }
                target[name] = wQuery.extend(deep, clone, copy);
              } else if (copy !== undefined) {
                target[name] = copy;
              }
            }
          }
        }
      }
      return target;
    };

       // Method to extend wQuery.fn (the prototype)
       wQuery.fn.extend = function() {
        const args = Array.prototype.slice.call(arguments);
        args.unshift(this); // Add the prototype as first argument
        return wQuery.extend.apply(wQuery, args);
      };
  
    // Método auxiliar para comprobar si un objeto es "plain" (literal)
    wQuery.isPlainObject = function(obj) {
      return Object.prototype.toString.call(obj) === "[object Object]";
    };
  
    // Implementación de $.proxy para enlazar contextos
    wQuery.proxy = function(fn, context) {
      if (typeof context === "string") {
        let tmp = fn[context];
        context = fn;
        fn = tmp;
      }
      if (typeof fn !== "function") {
        return undefined;
      }
      return function() {
        return fn.apply(context, arguments);
      };
    };
  
    wQuery.easing = {
        // Standard easing functions
        linear: function(p) {
          return p;
        },
        swing: function(p) {
          return 0.5 - Math.cos(p * Math.PI) / 2;
        },
        // Additional easing functions commonly used
        easeInQuad: function(p) {
          return p * p;
        },
        easeOutQuad: function(p) {
          return 1 - (1 - p) * (1 - p);
        },
        easeInOutQuad: function(p) {
          return p < 0.5 ? 2 * p * p : 1 - Math.pow(-2 * p + 2, 2) / 2;
        }
      };
  

          // Create animation step function expected by jQuery.easing
    wQuery.fx = {
        step: {},
        interval: 13,
        start: function() {
          // Minimal implementation to support jQuery.easing
        },
        stop: function() {
          // Minimal implementation to support jQuery.easing
        }
      };
    
    wQuery.ajax = function(options) {
      const settings = {
        url: options.url || "",
        method: options.method || options.type || "GET",
        data: options.data || null,
        dataType: options.dataType || "json",
        headers: options.headers || {},
        success: options.success || function() {},
        error: options.error || function() {},
        complete: options.complete || function() {},
        // jQuery compatibility options for FormData
        processData: options.processData !== false,
        contentType: options.contentType !== false ? (options.contentType || "application/x-www-form-urlencoded") : false
      };

      // ============================================
      // CSRF Token automático en POST
      // ============================================
      // Añadir token CSRF automáticamente a todos los POST
      // El token está disponible globalmente como _TOKEN_
      if (settings.method === "POST" && typeof _TOKEN_ !== 'undefined') {
        // Si data es FormData, añadir el token
        if (settings.data instanceof FormData) {
          if (!settings.data.has('token') && !settings.data.has('csrf_token')) {
            settings.data.append('token', _TOKEN_);
          }
        }
        // Si data es un objeto, añadir el token
        else if (typeof settings.data === 'object' && settings.data !== null) {
          if (!settings.data.token && !settings.data.csrf_token) {
            settings.data.token = _TOKEN_;
          }
        }
        // Si data es string, añadir el token
        else if (typeof settings.data === 'string') {
          if (settings.data.indexOf('token=') === -1 && settings.data.indexOf('csrf_token=') === -1) {
            settings.data += (settings.data ? '&' : '') + 'token=' + encodeURIComponent(_TOKEN_);
          }
        }
        // Si data es null o undefined, crear objeto con token
        else if (!settings.data) {
          settings.data = { token: _TOKEN_ };
        }
      }

      const fetchOptions = {
        method: settings.method,
        headers: { ...settings.headers }
      };
  
      if (settings.method === "POST" && settings.data) {
        // Detectar si data es FormData
        if (settings.data instanceof FormData) {
          // Para FormData, no establecer Content-Type (fetch lo hace automáticamente)
          // No procesar los datos, enviar FormData directamente
          fetchOptions.body = settings.data;
        } else if (settings.contentType === false) {
          // Si contentType es explícitamente false, no procesar datos
          fetchOptions.body = settings.data;
        } else {
          // Comportamiento normal para datos que no son FormData
          fetchOptions.headers["Content-Type"] = settings.contentType;
          fetchOptions.body = typeof settings.data === "string" ? settings.data : wQuery.param(settings.data);
        }
      }
  
      let url = settings.url;
      if (settings.method === "GET" && settings.data) {
        const queryString = typeof settings.data === "string" ? settings.data : wQuery.param(settings.data);
        if (queryString) url += (url.indexOf('?') >= 0 ? '&' : '?') + queryString;
      }
  
      const promise = {
        doneCallbacks: [settings.success],
        failCallbacks: [settings.error],
        alwaysCallbacks: [settings.complete],
        done: function(callback) {
          if (typeof callback === 'function') this.doneCallbacks.push(callback);
          return this;
        },
        fail: function(callback) {
          if (typeof callback === 'function') this.failCallbacks.push(callback);
          return this;
        },
        always: function(callback) {
          if (typeof callback === 'function') this.alwaysCallbacks.push(callback);
          return this;
        }
      };
  
      fetch(url, fetchOptions)
        .then(response => {
          const contentType = response.headers.get('content-type');
          const xhrLike = {
            responseText: null,
            status: response.status,
            statusText: response.statusText,
            getAllResponseHeaders: function() {
              let headers = '';
              response.headers.forEach((value, name) => {
                headers += name + ': ' + value + '\r\n';
              });
              return headers;
            },
            getResponseHeader: function(name) {
              return response.headers.get(name);
            }
          };
          const responseClone = response.clone();
          if (response.ok) {
            let dataPromise;
            
            if (settings.dataType === 'json' && contentType && contentType.includes('application/json')) {
              dataPromise = response.json();
            } else if (settings.dataType === 'xml' && contentType && contentType.includes('application/xml')) {
              dataPromise = response.text().then(text => {
                const parser = new DOMParser();
                return parser.parseFromString(text, 'application/xml');
              });
            } else {
              dataPromise = response.text();
            }
            
            return dataPromise.then(data => {
              xhrLike.responseText = typeof data === 'string' ? data : JSON.stringify(data);
              promise.doneCallbacks.forEach(fn => fn(data, 'success', xhrLike));
              promise.alwaysCallbacks.forEach(fn => fn(xhrLike, 'success'));
              return data;
            });
          } else {
            return responseClone.text().then(errorText => {
              throw new Error(`HTTP error! status: ${response.status}, message: ${errorText}`);
            });
          }
        })
        .catch(error => {
          const xhrLike = {
            status: 0,
            statusText: error.message,
            responseText: error.toString()
          };
          promise.failCallbacks.forEach(fn => fn(xhrLike, 'error', error));
          promise.alwaysCallbacks.forEach(fn => fn(xhrLike, 'error'));
        });
  
      return promise;
    };
  
    wQuery.get = function(url, data, success, dataType) {
      if (typeof data === "function") {
        dataType = success;
        success = data;
        data = null;
      }
      return wQuery.ajax({
        url: url,
        method: "GET",
        data: data,
        success: success,
        dataType: dataType || 'html'
      });
    };
  
    wQuery.post = function(url, data, success, dataType) {
      if (typeof data === "function") {
        dataType = success;
        success = data;
        data = null;
      }
      return wQuery.ajax({
        url: url,
        method: "POST",
        data: data,
        success: success,
        dataType: dataType
      });
    };
  
    wQuery.data = function(element, key, value) {
      if (!element) return undefined;
      
      // Si el elemento es un objeto File u otro objeto no-DOM sin _Data
      if (typeof element === 'object' && !element._Data && !(element instanceof Element)) {
        // Comprobar si ya hemos guardado datos para este objeto en nuestro mapa interno
        if (!wQuery._dataMap) {
          wQuery._dataMap = new WeakMap();
        }
        
        // Obtener o crear el objeto de datos para este elemento
        let dataObj = wQuery._dataMap.get(element);
        if (!dataObj) {
          // Crear un objeto que simule la interfaz jQuery para compatibilidad
          dataObj = {
            _element: element,
            _classes: [],
            
            // Métodos de manipulación de DOM que podrían ser llamados en $.data(file)
            find: function(selector) {
              // Si hay un elemento HTML asociado, buscar en él
              if (this._html) {
                return wQuery(this._html).find(selector);
              }
              return wQuery(); // Devolver colección vacía
            },
            
            // Métodos de clase
            addClass: function(className) {
              if (!this._html) return this;
              wQuery(this._html).addClass(className);
              if (className && !this._classes.includes(className)) {
                this._classes.push(className);
              }
              return this;
            },
            
            removeClass: function(className) {
              if (!this._html) return this;
              wQuery(this._html).removeClass(className);
              const index = this._classes.indexOf(className);
              if (index !== -1) {
                this._classes.splice(index, 1);
              }
              return this;
            },
            
            hasClass: function(className) {
              if (!this._html) return false;
              return wQuery(this._html).hasClass(className);
            }
          };
          
          wQuery._dataMap.set(element, dataObj);
        }
        
        if (arguments.length === 3) {
          dataObj[key] = value;
          return value;
        }
        if (arguments.length === 2) {
          return dataObj[key];
        }
        return dataObj;
      }
      
      // Comportamiento original para elementos DOM
      if (!element._Data) element._Data = {};
      if (arguments.length === 3) {
        element._Data[key] = value;
        return value;
      }
      if (arguments.length === 2) {
        return element._Data[key];
      }
      return element._Data;
    };
  
    // Implementación de $.isFunction para comprobar si un objeto es una función
    wQuery.isFunction = function(obj) {
      return typeof obj === "function" && typeof obj.nodeType !== "number";
    };

    wQuery.isArray = function(obj) {
      return Array.isArray(obj);
    }
  
    wQuery.each = function(obj, callback) {
      if (Array.isArray(obj) || obj instanceof NodeList) {
        for (let i = 0; i < obj.length; i++) {
          if (callback.call(obj[i], i, obj[i]) === false) break;
        }
      } else if (typeof obj === "object") {
        for (let key in obj) {
          if (obj.hasOwnProperty(key)) {
            if (callback.call(obj[key], key, obj[key]) === false) break;
          }
        }
      }
      return obj;
    };
    
    // Implementación de $.map (método estático)
    wQuery.map = function(array, callback) {
      const results = [];
      if (Array.isArray(array) || array instanceof NodeList || array instanceof HTMLCollection) {
        for (let i = 0; i < array.length; i++) {
          const result = callback.call(array[i], array[i], i);
          if (result != null) {
            if (Array.isArray(result)) {
              // Si el resultado es un array, se aplana (como en jQuery)
              Array.prototype.push.apply(results, result);
            } else {
              results.push(result);
            }
          }
        }
      } else if (typeof array === "object" && array !== null) {
        for (const key in array) {
          if (array.hasOwnProperty(key)) {
            const result = callback.call(array[key], array[key], key);
            if (result != null) {
              if (Array.isArray(result)) {
                Array.prototype.push.apply(results, result);
              } else {
                results.push(result);
              }
            }
          }
        }
      }
      return results;
    };
  
    wQuery.param = function(obj) {
      const params = new URLSearchParams();
      
      // Caso especial para el objeto con 'form' como array de objetos (serializeArray)
      if (obj && obj.form && Array.isArray(obj.form)) {
        // Comprobar si es un array resultado de serializeArray()
        if (obj.form.length > 0 && typeof obj.form[0] === 'object' && 'name' in obj.form[0] && 'value' in obj.form[0]) {
          // Usar formato PHP-compatible para arrays: form[0][name], form[0][value]
          obj.form.forEach((item, index) => {
            params.append(`form[${index}][name]`, item.name || '');
            params.append(`form[${index}][value]`, item.value == null ? '' : item.value);
          });
          return params.toString();
        }
      }
      
      // Comportamiento original para otros objetos
      Object.keys(obj).forEach(key => {
        params.append(key, obj[key]);
      });
      return params.toString();
    };
  
    // Se elimina el método modalform de la librería, ya que se manejará en un .js aparte.
  
  
    //window.wQuery = window.J = wQuery;  
    //window.jQuery = window.$ = wQuery;

    window.wQuery = window.jQuery = window.$ = wQuery;

  })(window);
