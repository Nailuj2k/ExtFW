/**
 * Tooltip Library - Vanilla JavaScript
 * Uso: tooltip(selector, options)
 */

const tooltip = (function() {
    // Contenedor único para todos los tooltips
    let tooltipEl = null;
    
    // Estilos CSS que se inyectan automáticamente
    /*
    const styles = `
        .tooltip-container {
            position: fixed;
            background: #333;
            color: #fff;
            padding: 6px 10px;
            border-radius: 4px;
            font-size: 13px;
            pointer-events: none;
            z-index: 9999;
            opacity: 0;
            transition: opacity 0.2s ease;
            white-space: nowrap;
            max-width: 300px;
        }
        
        .tooltip-container::after {
            content: '';
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            border: 5px solid transparent;
        }
        
        .tooltip-container.position-top::after { bottom: -10px; border-top-color: inherit; }
        .tooltip-container.position-bottom::after { top: -10px; border-bottom-color: inherit; }
        .tooltip-container.show { opacity: 1; }
        
        .tooltip-container.theme-default {background: #333333; color: #fff; border-color: #333;}
        .tooltip-container.theme-light   {background: #ffffff; color: #333; box-shadow: 0 2px 8px rgba(0,0,0,0.15); border: 1px solid #ddd; border-color: #fff;}
        .tooltip-container.theme-light::after {filter: drop-shadow(0 2px 2px rgba(0,0,0,0.1));}
        .tooltip-container.theme-primary {background: #007bff; color: #fff; border-color: #007bff;}
        .tooltip-container.theme-success {background: #28a745; color: #fff; border-color: #28a745;}
        .tooltip-container.theme-danger  {background: #dc3545; color: #fff; border-color: #dc3545;}
        .tooltip-container.theme-tomato  {background: #ff6347; color: yellow; border-color: #ff6347;} 
    `;
    */
    // Inyectar estilos en el documento
    /*
    function injectStyles() {
        if (!document.getElementById('tooltip-styles')) {
            const styleTag = document.createElement('style');
            styleTag.id = 'tooltip-styles';
            styleTag.textContent = styles;
            document.head.appendChild(styleTag);
        }
    }
    */
    // Crear el elemento tooltip si no existe
    function createTooltipElement() {
        if (!tooltipEl) {
            tooltipEl = document.createElement('div');
            tooltipEl.className = 'tooltip-container';
            document.body.appendChild(tooltipEl);
        }
        return tooltipEl;
    }
    
    // Calcular posición del tooltip
    function positionTooltip(target, tooltip) {
        const rect = target.getBoundingClientRect();
        const tooltipRect = tooltip.getBoundingClientRect();
        
        let left = rect.left + (rect.width / 2) - (tooltipRect.width / 2);
        let top = rect.top - tooltipRect.height - 8;
        let position = 'top';
        
        // Ajustar si se sale por la izquierda
        if (left < 5) left = 5;
        
        // Ajustar si se sale por la derecha
        if (left + tooltipRect.width > window.innerWidth - 5) {
            left = window.innerWidth - tooltipRect.width - 5;
        }
        
        // Si se sale por arriba, mostrar abajo
        if (top < 5) {
            top = rect.bottom + 8;
            position = 'bottom';
        }
        
        // Actualizar clase de posición para la flecha
        tooltip.classList.remove('position-top', 'position-bottom');
        tooltip.classList.add(`position-${position}`);
        
        tooltip.style.left = left + 'px';
        tooltip.style.top = top + 'px';
    }
    
    // Mostrar tooltip
    function showTooltip(e, text, theme) {
        const tooltip = createTooltipElement();
        tooltip.textContent = text;
        tooltip.className = `tooltip-container theme-${theme}`;
        
        // Forzar reflow para que la transición funcione
        tooltip.offsetHeight;
        
        positionTooltip(e.currentTarget, tooltip);
        tooltip.classList.add('show');
    }
    
    // Ocultar tooltip
    function hideTooltip() {
        if (tooltipEl) {
            tooltipEl.classList.remove('show');
        }
    }
    
    // Función principal
    return function(selector, options = {}) {
        // Opciones por defecto
        const config = {
            theme: options.theme || 'default',
            attribute: options.attribute || 'title',
            delay: options.delay || 0
        };
        
        // Inyectar estilos
        ///////injectStyles();
        
        // Seleccionar elementos
        const elements = document.querySelectorAll(selector);
        
        elements.forEach(el => {
            let timeoutId = null;
            
            // Evento mouseenter
            el.addEventListener('mouseenter', function(e) {
                const text = this.getAttribute(config.attribute);
                const element = this; // Guardar referencia al elemento
                
                if (text) {
                    if (config.delay > 0) {
                        timeoutId = setTimeout(() => {
                            // Crear un objeto de evento simulado con currentTarget
                            const fakeEvent = { currentTarget: element };
                            showTooltip(fakeEvent, text, config.theme);
                        }, config.delay);
                    } else {
                        showTooltip(e, text, config.theme);
                    }
                }
            });
            
            // Evento mouseleave
            el.addEventListener('mouseleave', function() {
                if (timeoutId) {
                    clearTimeout(timeoutId);
                    timeoutId = null;
                }
                hideTooltip();
            });
            
            // Actualizar posición al hacer scroll
            el.addEventListener('mousemove', function(e) {
                if (tooltipEl && tooltipEl.classList.contains('show')) {
                    positionTooltip(this, tooltipEl);
                }
            });
        });
    };
})();

// Ejemplo de uso:
// tooltip('#toolbar .btn', null);
// tooltip('.info-icon', { theme: 'light', attribute: 'data-tooltip' });
// tooltip('.warning', { theme: 'danger', attribute: 'aria-label', delay: 500 });
