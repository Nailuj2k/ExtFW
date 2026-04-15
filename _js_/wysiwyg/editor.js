export class WysiwygEditor {
  constructor(textarea, options = {}) {
    // Si se pasa un selector, se obtiene el elemento textarea
    if (typeof textarea === 'string') {
      textarea = document.querySelector(textarea);
    }
    if (!textarea) {
      throw new Error('No se encontró el elemento textarea');
    }
    this.textarea = textarea;
    this.options =  {
            height: '290px',
            minHeight: '150px',
            maxHeight: '600px',
            ...options  // Sobrescribir con las opciones del usuario
        };

    // Crear el div editable y asignarle la clase correspondiente
    this.editorDiv = document.createElement('div');
    this.editorDiv.contentEditable = true;
    this.editorDiv.className = 'wysiwyg-editor';
    this.editorDiv.innerHTML = this.textarea.value;
    this.editorDiv.height = '100px';

    // Crear el contenedor del editor con una barra de herramientas
    this.wrapper = document.createElement('div');
    this.wrapper.className = 'wysiwyg-wrapper';
    this.toolbar = document.createElement('div');
    this.toolbar.className = 'wysiwyg-toolbar';

    // Añadir la barra de herramientas y el área editable al contenedor
    this.wrapper.appendChild(this.toolbar);
    this.wrapper.appendChild(this.editorDiv);

    // Copiar estilos clave del textarea al contenedor del editor
    this.copyStyles(textarea, this.wrapper, ['width', 'height', 'display']);
    //this.wrapper.style.height='290px';

        this.wrapper.style.height = this.options.height;
        if (this.options.minHeight) {
            this.wrapper.style.minHeight = this.options.minHeight;
        }
        if (this.options.maxHeight) {
            this.wrapper.style.maxHeight = this.options.maxHeight;
        }


    this.wrapper.style.width='100%';

    // Insertar el contenedor en el DOM y ocultar el textarea original
    this.textarea.parentNode.insertBefore(this.wrapper, this.textarea);
    this.textarea.style.display = 'none';

    // Vincular eventos para sincronizar el contenido
    this.bindEvents();

    // Array para almacenar los plugins registrados
    this.plugins = [];
  }

  // Función para copiar propiedades de estilo específicas del elemento source al target
  copyStyles(source, target, properties) {
    const computed = window.getComputedStyle(source);
    properties.forEach(prop => {
        //if (prop =='height'){
        //    target.style[prop] = '100px';
        //}else{
            target.style[prop] = computed[prop];
        //}
    });
  }

  bindEvents() {
    // Sincroniza el contenido en cada cambio
    this.editorDiv.addEventListener('input', () => {
      this.sync();
    });
    this.editorDiv.addEventListener('blur', () => {
      this.sync();
    });
    // Actualizar el estado de los plugins en eventos de teclado y ratón
    this.editorDiv.addEventListener('keyup', (e) => {
      //  e.stopPropagation();
      this.sync();
      this.updatePluginStates();
    });
    this.editorDiv.addEventListener('mouseup', () => {
      this.updatePluginStates();
    });



  }

  // Sincroniza el contenido del editor con el textarea
  sync() {
    this.textarea.value = this.editorDiv.innerHTML;
  }

  // Retorna el contenido del editor
  getContent() {
    return this.editorDiv.innerHTML;
  }

  // Establece el contenido del editor y actualiza el textarea
  setContent(content) {
    this.editorDiv.innerHTML = content;
    this.sync();
  }

  // Método para registrar un plugin. Se espera que el plugin sea una función que reciba el editor.
  registerPlugin(pluginFunction) {
    const plugin = pluginFunction(this);
    this.plugins.push(plugin);
  }

  // Llama al método updateState de cada plugin (si lo tiene) para actualizar su estado
  updatePluginStates() {
    this.plugins.forEach(plugin => {
      if (typeof plugin.updateState === 'function') {
        plugin.updateState();
      }
    });
  }
}
