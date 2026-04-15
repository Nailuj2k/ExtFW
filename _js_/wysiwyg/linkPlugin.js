export function linkPlugin(editor) {
  const toolbar = editor.toolbar;

  // Botón "Link" en la toolbar
  const btn = document.createElement('button');

  //const iconUrl = 'url(/_images_/feather/link.svg)';
  //btn.style.backgroundImage = iconUrl;
  btn.classList.add('btn-icon');
  btn.classList.add('btn-link');

  toolbar.appendChild(btn);

  // Crear el modal para editar atributos del enlace
  const modal = document.createElement('div');
  modal.style.position = 'fixed';
  modal.style.top = '0';
  modal.style.left = '0';
  modal.style.width = '100%';
  modal.style.height = '100%';
  modal.style.backgroundColor = 'rgba(0,0,0,0.5)';
  modal.style.display = 'none';
  modal.style.zIndex = '10000';

  // Contenedor central del modal
  const container = document.createElement('div');
  container.style.position = 'absolute';
  container.style.top = '50%';
  container.style.left = '50%';
  container.style.transform = 'translate(-50%, -50%)';
  container.style.background = '#fff';
  container.style.padding = '20px';
  container.style.boxShadow = '0 2px 10px rgba(0,0,0,0.5)';
  container.style.width = '80%';
  container.style.maxWidth = '400px';
  container.style.boxSizing = 'border-box';

  // Formulario para editar atributos (incluye el desplegable para clases)
  container.innerHTML = `
    <div style="margin-bottom:10px;">
      <label style="display:block; font-weight:bold;">URL:</label>
      <input type="text" name="url" style="width:100%;" placeholder="http://">
    </div>
    <div style="margin-bottom:10px;">
      <label style="display:block; font-weight:bold;">Target:</label>
      <select name="target" style="width:100%;">
        <option value="">Default</option>
        <option value="_blank">_blank (Nueva pestaña)</option>
        <option value="_self">_self (Misma pestaña)</option>
        <option value="_parent">_parent</option>
        <option value="_top">_top</option>
      </select>
    </div>
    <div style="margin-bottom:10px;">
      <label style="display:block; font-weight:bold;">Title:</label>
      <input type="text" name="title" style="width:100%;">
    </div>
    <div style="margin-bottom:10px;">
      <label style="display:block; font-weight:bold;">Alt:</label>
      <input type="text" name="alt" style="width:100%;">
    </div>
    <div style="margin-bottom:10px;">
      <label style="display:block; font-weight:bold;">Clases:</label>
      <select name="classes" style="width:100%;" multiple>
        <option value="btn">btn</option>
        <option value="link">link</option>
        <option value="highlight">highlight</option>
      </select>
    </div>
    <div style="text-align:right;">
      <button type="submit">Guardar</button>
      <button type="button" id="cancelLink" style="margin-left:10px;">Cancelar</button>
    </div>
  `;
  modal.appendChild(container);
  document.body.appendChild(modal);

  // Variable para almacenar el enlace que se está editando (si lo hay)
  let targetLink = null;

  // Función para abrir el modal y precargar los valores si se está editando un enlace
  function openModal() {
    targetLink = null;
    const sel = window.getSelection();
    if (sel.rangeCount > 0) {
      let node = sel.getRangeAt(0).commonAncestorContainer;
      if (node.nodeType === Node.TEXT_NODE) {
        node = node.parentNode;
      }
      if (node && node.tagName && node.tagName.toLowerCase() === 'a') {
        targetLink = node;
      }
    }
    // Referencias a los inputs
    const urlInput = container.querySelector('input[name="url"]');
    const targetSelect = container.querySelector('select[name="target"]');
    const titleInput = container.querySelector('input[name="title"]');
    const altInput = container.querySelector('input[name="alt"]');
    const classesSelect = container.querySelector('select[name="classes"]');

    if (targetLink) {
      urlInput.value = targetLink.getAttribute('href') || '';
      targetSelect.value = targetLink.getAttribute('target') || '';
      titleInput.value = targetLink.getAttribute('title') || '';
      altInput.value = targetLink.getAttribute('alt') || '';
      // Marcar las opciones del desplegable según las clases actuales del enlace
      const currentClasses = targetLink.getAttribute('class') || '';
      const classList = currentClasses.split(/\s+/);
      Array.from(classesSelect.options).forEach(opt => {
        opt.selected = classList.includes(opt.value);
      });
    } else {
      urlInput.value = '';
      targetSelect.value = '';
      titleInput.value = '';
      altInput.value = '';
      // Ninguna opción seleccionada
      Array.from(classesSelect.options).forEach(opt => {
        opt.selected = false;
      });
    }
    modal.style.display = 'block';
  }

  // Función para cerrar el modal
  function closeModal() {
    modal.style.display = 'none';
  }

  // Al pulsar el botón "Link" se abre el modal
  btn.addEventListener('click', (e) => {
    e.preventDefault();
    e.stopPropagation();
    openModal();
  });

  // Función para guardar los cambios
  function saveLink() {
    const url = container.querySelector('input[name="url"]').value.trim();
    const targetVal = container.querySelector('select[name="target"]').value.trim();
    const title = container.querySelector('input[name="title"]').value.trim();
    const alt = container.querySelector('input[name="alt"]').value.trim();
    const classesSelect = container.querySelector('select[name="classes"]');
    // Recopilar las opciones seleccionadas y unirlas en un string separado por espacios
    const selectedClasses = Array.from(classesSelect.selectedOptions)
      .map(opt => opt.value)
      .join(' ');

    if (url === '') {
      // Si se deja vacío el URL, eliminamos el enlace si existe
      if (targetLink) {
        unwrapLink(targetLink);
      }
    } else {
      if (targetLink) {
        // Actualizamos el enlace existente
        targetLink.setAttribute('href', url);
        targetLink.setAttribute('target', targetVal);
        targetLink.setAttribute('title', title);
        targetLink.setAttribute('alt', alt);
        targetLink.setAttribute('class', selectedClasses);
      } else {
        // Insertamos un nuevo enlace:
        // Si hay selección (texto) no colapsada, se envuelve con <a>
        const sel = window.getSelection();
        if (!sel.isCollapsed) {
          editor.editorDiv.focus();
          document.execCommand('createLink', false, url);
          // Intentar obtener el nuevo enlace y actualizar sus atributos
          let node = sel.getRangeAt(0).commonAncestorContainer;
          if (node.nodeType === Node.TEXT_NODE) {
            node = node.parentNode;
          }
          if (node && node.tagName && node.tagName.toLowerCase() === 'a') {
            targetLink = node;
            targetLink.setAttribute('target', targetVal);
            targetLink.setAttribute('title', title);
            targetLink.setAttribute('alt', alt);
            targetLink.setAttribute('class', selectedClasses);
          }
        } else {
          // Si la selección está colapsada, insertamos un enlace en la posición del cursor
          editor.editorDiv.focus();
          const linkHTML = `<a href="${url}" target="${targetVal}" title="${title}" alt="${alt}" class="${selectedClasses}">${url}</a>`;
          document.execCommand('insertHTML', false, linkHTML);
        }
      }
    }
    closeModal();
    editor.editorDiv.focus();
    updateButtonState();
  }

  // Función para eliminar un enlace manteniendo su contenido
  function unwrapLink(linkElement) {
    const parent = linkElement.parentNode;
    while (linkElement.firstChild) {
      parent.insertBefore(linkElement.firstChild, linkElement);
    }
    parent.removeChild(linkElement);
  }

  // Actualiza el estado del botón (lo resalta si la selección está sobre un enlace)
  function updateButtonState() {
    const sel = window.getSelection();
    if (sel.rangeCount > 0) {
      let node = sel.getRangeAt(0).commonAncestorContainer;
      if (node.nodeType === Node.TEXT_NODE) {
        node = node.parentNode;
      }
      if (node && node.tagName && node.tagName.toLowerCase() === 'a') {
        btn.classList.add('active');
      } else {
        btn.classList.remove('active');
      }
    }
  }

  // Listeners para los botones del modal
  container.querySelector('button[type="submit"]').addEventListener('click', (e) => {
    e.preventDefault();
    saveLink();
  });

  container.querySelector('#cancelLink')?.addEventListener('click', (e) => {
    e.preventDefault();
    closeModal();
  });

  // Cerrar el modal al hacer click fuera del contenedor
  modal.addEventListener('click', (e) => {
    if (e.target === modal) {
      closeModal();
    }
  });

  // Actualizar estado del botón al cambiar la selección
  document.addEventListener('selectionchange', () => {
    if (document.activeElement === editor.editorDiv) {
      updateButtonState();
    }
  });

  return {
    updateState: updateButtonState
  };
}
