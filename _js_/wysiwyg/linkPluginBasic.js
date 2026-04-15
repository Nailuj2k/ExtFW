export function linkPlugin(editor) {
  const toolbar = editor.toolbar;

  // Crear el botón de enlace
  const linkBtn = document.createElement('button');
  linkBtn.innerHTML = 'Link';
  linkBtn.addEventListener('click', (e) => {
    e.preventDefault();
    const selection = window.getSelection();
    if (selection.rangeCount > 0) {
      const range = selection.getRangeAt(0);
      let node = range.commonAncestorContainer;
      if (node.nodeType === Node.TEXT_NODE) {
        node = node.parentNode;
      }
      // Si el nodo es un enlace, se edita (o elimina si se deja vacío)
      if (node && node.tagName && node.tagName.toLowerCase() === 'a') {
        const currentURL = node.getAttribute('href') || '';
        const newURL = prompt("Editar enlace (deja vacío para eliminar):", currentURL);
        if (newURL === null) {
          // Cancelado: no hacer nada
        } else if (newURL === "") {
          // Eliminar el enlace: quitar la etiqueta <a>
          unwrapLink(node);
        } else {
          // Actualizar el atributo href
          node.setAttribute('href', newURL);
        }
      } else {
        // Si no hay enlace, se crea uno nuevo usando el comando 'createLink'
        const url = prompt("Inserta URL:", "http://");
        if (url) {
          document.execCommand('createLink', false, url);
        }
      }
    }
    editor.editorDiv.focus();
    updateButtonStates();
  });
  toolbar.appendChild(linkBtn);

  // Función para "desenvolver" un enlace (quitar la etiqueta <a>)
  function unwrapLink(linkElement) {
    const parent = linkElement.parentNode;
    while (linkElement.firstChild) {
      parent.insertBefore(linkElement.firstChild, linkElement);
    }
    parent.removeChild(linkElement);
  }

  // Función para actualizar el estado (resaltar el botón) si la selección está en un enlace
  function updateButtonStates() {
    const selection = window.getSelection();
    if (selection.rangeCount > 0) {
      const range = selection.getRangeAt(0);
      let node = range.commonAncestorContainer;
      if (node.nodeType === Node.TEXT_NODE) {
        node = node.parentNode;
      }
      if (node && node.tagName && node.tagName.toLowerCase() === 'a') {
        linkBtn.classList.add('active');
      } else {
        linkBtn.classList.remove('active');
      }
    }
  }

  // Actualizar el estado al cambiar la selección
  document.addEventListener('selectionchange', () => {
    if (document.activeElement === editor.editorDiv) {
      updateButtonStates();
    }
  });

  return {
    updateState: updateButtonStates
  };
}