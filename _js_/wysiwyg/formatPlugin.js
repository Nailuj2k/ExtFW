export function formatPlugin(editor) {
  // Crear el botón "Formato"
  const btn = document.createElement('button');
  btn.textContent = 'Formato';

  const iconDown = 'url(/_images_/feather/chevron-down.svg)'; 
  const iconUp = 'url(/_images_/feather/chevron-up.svg)'; 
  btn.style.backgroundImage = iconDown;  
  btn.style.backgroundPosition = 'right 4px';
  btn.style.paddingRight = '16px';

  editor.toolbar.appendChild(btn);


  // Crear el contenedor desplegable para las opciones de formato
  const dropdown = document.createElement('div');
  dropdown.style.position = 'absolute';
  dropdown.style.background = '#fff';
  dropdown.style.border = '1px solid #ccc';
  dropdown.style.padding = '5px';
  dropdown.style.display = 'none';
  dropdown.style.zIndex = 1000;

  // Evitar que el dropdown robe el foco (y así se mantiene la selección)
  dropdown.addEventListener('mousedown', (e) => {
    e.preventDefault();
  });

  // Opciones disponibles: se usan los tags con ángulos (lo que execCommand espera)
  const options = [
    { label: 'Párrafo', tag: '<p>' },
    { label: 'H1', tag: '<h1>' },
    { label: 'H2', tag: '<h2>' },
    { label: 'H3', tag: '<h3>' },
    { label: 'H4', tag: '<h4>' },
    { label: 'H5', tag: '<h5>' },
    { label: 'H6', tag: '<h6>' },
    { label: 'Code', tag: '<pre>' }
  ];

  // Crear cada opción en el dropdown
  options.forEach(option => {
    const opt = document.createElement('div');
    opt.textContent = option.label;
    opt.style.cursor = 'pointer';
    opt.style.padding = '2px 5px';
    opt.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      // Asegurarse de que el editor mantenga el foco (la selección se preserva)
      editor.editorDiv.focus();
      // Ejecutar el comando de formato
      document.execCommand('formatBlock', false, option.tag);
      dropdown.style.display = 'none';
      updateButtonLabel();
    });
    dropdown.appendChild(opt);
  });
  document.body.appendChild(dropdown);

  // Mostrar/Ocultar el dropdown al pulsar el botón "Formato"
  btn.addEventListener('click', (e) => {
    e.preventDefault();
    e.stopPropagation();
    if (dropdown.style.display === 'none') {
      const rect = btn.getBoundingClientRect();
      dropdown.style.top = (window.scrollY + rect.bottom) + 'px';
      dropdown.style.left = (window.scrollX + rect.left) + 'px';
      dropdown.style.display = 'block';
      btn.style.backgroundImage = iconUp;  
    } else {
      dropdown.style.display = 'none';
      btn.style.backgroundImage = iconDown;  
    }
  });

  // Ocultar el dropdown si se hace clic fuera
  document.addEventListener('click', (e) => {
    if (!btn.contains(e.target) && !dropdown.contains(e.target)) {
      dropdown.style.display = 'none';
    }
  });

  // Función para actualizar el label del botón según el bloque actual donde está el cursor
  function updateButtonLabel() {
    const selection = window.getSelection();
    if (!selection.rangeCount) {
      btn.textContent = 'Formato';
      return;
    }
    let node = selection.getRangeAt(0).commonAncestorContainer;
    if (node.nodeType === Node.TEXT_NODE) {
      node = node.parentNode;
    }
    if (node && node.tagName) {
      const tag = node.tagName.toLowerCase();
      if (['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'pre'].includes(tag)) {
        btn.textContent = tag.toUpperCase();
        return;
      }
    }
    btn.textContent = 'Formato';
  }

  // Actualizar el label cada vez que cambia la selección y el editor tiene foco
  document.addEventListener('selectionchange', () => {
    if (document.activeElement === editor.editorDiv) {
      updateButtonLabel();
    }
  });

  return {
    updateState: updateButtonLabel
  };
}
