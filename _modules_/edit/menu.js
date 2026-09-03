class ContextMenu {

    constructor(className, menuItems, onShow) {
        this.className = className;
        this.menuItems = menuItems;
        this.menuElement = null;
        this.targetElement = null;
        this.onShowMenu = onShow
        this.init();
    }

    init() {

        // Crear el elemento del menú
        this.createMenuElement();
        
        // Agregar listeners para elementos con la clase específica
        document.addEventListener('contextmenu', this.handleContextMenu.bind(this));
        
        // Cerrar el menú al hacer click fuera
        document.addEventListener('click', this.closeMenu.bind(this));
        
        // Cerrar el menú al presionar ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') this.closeMenu();
        });
    }

    createMenuElement() {
        this.menuElement = document.createElement('div');
        this.menuElement.className = 'context-menu';
        this.menuElement.style.cssText = `
            position: fixed;
            background: white;
            border: 1px solid #ccc;
            border-radius: 4px;
            padding: 5px 0;
            min-width: 150px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            display: none;
            z-index: 1000;
        `;

        // Crear elementos del menú
        this.menuItems.forEach(item => {
       
            const menuItem = document.createElement('div');
            menuItem.className = 'context-menu-item';
            menuItem.textContent = item.text;
            menuItem.id = item.id;
            menuItem.style.cssText = `
                padding: 8px 15px;
                cursor: pointer;
                user-select: none;
            `;

            // Hover effect
            menuItem.addEventListener('mouseover', () => {
                menuItem.style.backgroundColor = '#f0f0f0';
            });
            menuItem.addEventListener('mouseout', () => {
                menuItem.style.backgroundColor = 'transparent';
            });

            // Click handler
            menuItem.addEventListener('click', (e) => {

                //console.log('MENUITEM',e.target.dataset.filename);

                e.stopPropagation();
                if (item.handler && this.targetElement) {
                    item.handler(this.targetElement, item);
                }
                this.closeMenu();
            });

            this.menuElement.appendChild(menuItem);


        });

        document.body.appendChild(this.menuElement);
    }

    handleContextMenu(e) {
        // Verificar si el elemento o alguno de sus padres tiene la clase especificada
        const targetElement = e.target.closest(`.${this.className}`);
        //console.log('HANDLE.CONTEXT.MENU',e.target.dataset.filename)
        if (targetElement) {
            e.preventDefault();
            this.targetElement = targetElement;
            
            // Posicionar el menú
            const x = e.clientX;
            const y = e.clientY;
            this.showMenu(x, y);
        } else {
            this.closeMenu();
        }
    }

    showMenu(x, y) {
        this.menuElement.style.display = 'block';
        
        // Ajustar posición si el menú se sale de la ventana
        const menuRect = this.menuElement.getBoundingClientRect();
        const windowWidth = window.innerWidth;
        const windowHeight = window.innerHeight;

        if (x + menuRect.width > windowWidth) {
            x = windowWidth - menuRect.width;
        }
        
        if (y + menuRect.height > windowHeight) {
            y = windowHeight - menuRect.height;
        }

        this.menuElement.style.left = `${x}px`;
        this.menuElement.style.top = `${y}px`;

        if(this.onShowMenu) this.onShowMenu(this)
    }

    closeMenu() {
        if (this.menuElement) {
            this.menuElement.style.display = 'none';
            this.targetElement = null;
        }
    }
}


