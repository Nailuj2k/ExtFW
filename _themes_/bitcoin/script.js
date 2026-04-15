document.addEventListener("DOMContentLoaded", function(){
    // Configuración: 'click' o 'mouseover'
    const submenuTrigger = 'mouseover'; 

    // Elementos del DOM
    const menuToggle = document.querySelector('.menu-toggle');
    const menu = document.querySelector('.menu');

    // Toggle del menú principal
    menuToggle.addEventListener('click', () => {
        menu.classList.toggle('active');
        menuToggle.classList.toggle('active');
    });

    // Función para abrir/cerrar un submenú
    function toggleSubmenu(item) {
        const submenu = item.querySelector('.submenu');
        if (submenu) {
            const isActive = submenu.classList.toggle('active');
            // Actualiza el atributo aria-expanded según el estado
            item.setAttribute('aria-expanded', isActive ? 'true' : 'false');

            // Cerrar submenús hermanos
            const siblings = Array.from(item.parentNode.children).filter(child => child !== item);
            siblings.forEach(sibling => {
            const siblingSubmenu = sibling.querySelector('.submenu');
            if (siblingSubmenu) {
                siblingSubmenu.classList.remove('active');
                sibling.setAttribute('aria-expanded', 'false');
            }
            });
        }
    }
    /*
    // Asignar eventos a los elementos con submenú según la configuración
    document.querySelectorAll('.has-submenu').forEach(item => {
        if (submenuTrigger === 'click') {
            item.addEventListener('click', (e) => {
                if (item.querySelector('.submenu')) {
                    e.preventDefault();
                    e.stopPropagation(); // Evita que el click se propague al elemento padre
                    toggleSubmenu(item);
                }
            });
        } else if (submenuTrigger === 'mouseover') {
            item.addEventListener('mouseenter', () => {
                toggleSubmenu(item);
            });
        }
    });
    */

    // Cerrar submenús al hacer click fuera del menú (esto reemplaza el mouseleave)
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.has-childs')) {
            document.querySelectorAll('.submenu.active').forEach(sub => {
                sub.classList.remove('active');
            });
        }
    });



    // Asignar comportamiento a cada elemento que tenga submenú
    document.querySelectorAll('.has-childs').forEach(item => {
        if (submenuTrigger === 'mouseover') {
            
            // Al pasar el mouse por encima, se muestra el submenú
            item.addEventListener('mouseenter', (e) => {
                // Oculta los submenús de los hermanos en el mismo nivel
                const siblings = Array.from(item.parentNode.children).filter(child => child !== item);
                siblings.forEach(sibling => {
                    const siblingSubmenu = sibling.querySelector('.submenu');
                    if (siblingSubmenu) {
                        siblingSubmenu.classList.remove('active');
                    }
                });
                // Muestra el submenú actual
                const submenu = item.querySelector('.submenu');
                if (submenu) {
                    submenu.classList.add('active');
                }
            });

            // Al salir con el mouse del elemento padre, se oculta el submenú
            item.addEventListener('mouseleave', (e) => {
                const submenu = item.querySelector('.submenu');
                if (submenu) {
                    submenu.classList.remove('active');
                }
            });

            // Opcional: si notas que el submenú no se oculta al pasar fuera de él,
            // añade también el listener directamente sobre el submenú
            const submenu = item.querySelector('.submenu');
            if (submenu) {
                submenu.addEventListener('mouseleave', (e) => {
                    submenu.classList.remove('active');
                });
            }
        }
    });

    document.querySelectorAll('.has-childs > a').forEach(link => {
        link.addEventListener('keydown', (e) => {
            // Al presionar Enter, activa o desactiva el submenú
            if (e.key === 'Enter') {
            e.preventDefault();
            const parentItem = link.parentElement;
            toggleSubmenu(parentItem);
            }
            // Puedes agregar lógica adicional para flechas arriba/abajo
        });
    });

    document.querySelectorAll('.copy-link').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const url = link.getAttribute('href');
            navigator.clipboard.writeText(url).then(() => {
                alert('Tor URL copied to clipboard!');
            }).catch(err => {
                console.error('Failed to copy: ', err);
            });
        });

    });



});
