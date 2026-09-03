export function htmlViewPlugin(editor) {
  // Botón para ver/editar el HTML
  const btn = document.createElement('button');
  
  
  //const iconUrl = 'url(/_images_/feather/code.svg)'; 
  //btn.style.backgroundImage = iconUrl;
  btn.classList.add('btn-icon');
  btn.classList.add('btn-code');

  editor.toolbar.appendChild(btn);

  // Crear el modal (popup) para mostrar el código HTML
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
  container.style.maxWidth = '600px';
  container.style.maxHeight = '80%';
  container.style.overflow = 'auto';

  // Textarea para mostrar y editar el HTML
  const textarea = document.createElement('textarea');
  textarea.style.width = '100%';
  textarea.style.height = '300px';
  textarea.style.boxSizing = 'border-box';
  container.appendChild(textarea);

  // Botones Guardar y Cancelar
  const saveBtn = document.createElement('button');
  saveBtn.textContent = 'Guardar';
  const cancelBtn = document.createElement('button');
  cancelBtn.textContent = 'Cancelar';
  cancelBtn.style.marginLeft = '10px';
  container.appendChild(saveBtn);
  container.appendChild(cancelBtn);

  modal.appendChild(container);
  document.body.appendChild(modal);

  // Mostrar el modal con el HTML actual del editor
  btn.addEventListener('click', (e) => {
    e.preventDefault();
    e.stopPropagation();
    textarea.value = editor.getContent(); // o editor.editorDiv.innerHTML
    modal.style.display = 'block';
  });

  // Guardar: actualizar el contenido del editor
  saveBtn.addEventListener('click', (e) => {
    e.preventDefault();
    e.stopPropagation();
    editor.setContent(textarea.value);
    modal.style.display = 'none';
  });

  // Cancelar: cerrar el modal sin guardar cambios
  cancelBtn.addEventListener('click', (e) => {
    e.preventDefault();
    e.stopPropagation();
    modal.style.display = 'none';
  });

  // Cerrar el modal al hacer click fuera del contenedor
  modal.addEventListener('click', (e) => {
    if (e.target === modal) {
      modal.style.display = 'none';
    }
  });

  return {
    updateState: () => {}
  };
}
