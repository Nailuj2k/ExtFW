        function flyTo(element, target, options = {}) {
            // Validar opciones
            if (typeof options !== 'object') {
                console.error('flyTo: options must be an object');
                return null;
            }
            
            // Validar elemento
            if (!element) {
                console.error('flyTo: Element is required');
                return null;
            }
            
            // Convertir a elemento DOM si es un selector o un objeto wQuery/jQuery
            if (typeof element === 'string') {
                element = document.querySelector(element);
            } else if (element instanceof $ && element.length > 0) {
                element = element[0];
            }
            
            if (!element || !element.nodeType) {
                console.error('flyTo: Invalid element');
                return null;
            }

            // Configuración por defecto
            const defaults = {
                duration: 600,
                easing: 'ease',
                arc: 150,
                remove: false,
                hideOnComplete: false,
                complete: function() {}
            };

            // Extender opciones con defaults
            options = Object.assign({}, defaults, options);
                
            // Verificar si el elemento está en el DOM
            if (!element.isConnected) {
                console.error('flyTo: Element is not in the DOM');
                return null;
            }

            // Guardar posición original
            const originalPosition = window.getComputedStyle(element).position;
            const originalLeft = element.style.left;
            const originalTop = element.style.top;

            // Asegurar posición absoluta o fixed
            if (!['absolute', 'fixed'].includes(originalPosition)) {
                element.style.position = 'fixed';
            }

            // Obtener coordenadas iniciales
            const start = element.getBoundingClientRect();
            let end;

            // Determinar coordenadas finales
            if (typeof target === 'string') {
                const targetElement = document.querySelector(target);
                if (!targetElement) {
                    console.error('flyTo: Target element not found:', target);
                    return null;
                }
                end = targetElement.getBoundingClientRect();
            } else if (target instanceof $ && target.length > 0) {
                end = target[0].getBoundingClientRect();
            } else if (target.nodeType) {
                end = target.getBoundingClientRect();
            } else if (typeof target === 'object' && 
                      typeof target.left === 'number' && 
                      typeof target.top === 'number') {
                end = target;
            } else {
                console.error('flyTo: Invalid target type');
                return null;
            }

            // Calcular vectores de movimiento
            const dx = end.left - start.left;
            const dy = end.top - start.top;
            const distance = Math.sqrt(dx * dx + dy * dy);

            // Función de easing
            const ease = {
                ease: function(t) { return t; },
                easeIn: function(t) { return t * t; },
                easeOut: function(t) { return t * (2 - t); },
                easeInOut: function(t) { return t < 0.5 ? 2 * t * t : -1 + (4 - 2 * t) * t; }
            }[options.easing] || ease.ease;

            // Iniciar animación
            let startTime = null;
            let isAnimating = true;

            function animate(currentTime) {
                if (!isAnimating) return;
                
                if (!startTime) startTime = currentTime;
                const elapsedTime = currentTime - startTime;
                const progress = Math.min(elapsedTime / options.duration, 1);
                const easedProgress = ease(progress);

                // Calcular posición
                const x = start.left + dx * easedProgress;
                const y = start.top + dy * easedProgress - Math.sin(easedProgress * Math.PI) * options.arc;

                // Actualizar posición
                element.style.left = `${x}px`;
                element.style.top = `${y}px`;

                // Completar animación
                if (progress >= 1) {
                    isAnimating = false;
                    
                    // Restaurar posición original o aplicar comportamiento final
                    if (options.remove) {
                        element.remove();
                    } else if (options.hideOnComplete) {
                        element.style.display = 'none';
                    } else {
                        element.style.position = originalPosition;
                        element.style.left = originalLeft;
                        element.style.top = originalTop;
                    }

                    // Ejecutar callback
                    options.complete.call(element);
                    return;
                }

                requestAnimationFrame(animate);
            }

            // Iniciar animación
            requestAnimationFrame(animate);
            
            // Devolver el elemento para permitir encadenamiento
            return element;
        }