export function imagePlugin(editor) {
  // URL de la imagen por defecto
  const defaultImageSrc = '/media/links/logos/10.jpg';

  // Crear el botón en la toolbar
  const btn = document.createElement('button');

  //const iconUrl = 'url(/_images_/feather/image.svg)'; 
  //btn.style.backgroundImage = iconUrl;
  btn.classList.add('btn-icon');
  btn.classList.add('btn-image');

  editor.toolbar.appendChild(btn);

    // Variables para almacenar la imagen a editar y la última imagen clicada en el editor
  let targetImage = null;
  let lastClickedImg = null;

  // Listener para capturar clicks sobre imágenes dentro del editor
  editor.editorDiv.addEventListener('click', (e) => {
    if (e.target && e.target.tagName && e.target.tagName.toLowerCase() === 'img') {
      lastClickedImg = e.target;
    }
  });

  // Crear el popup y agregarlo al wrapper para posicionarlo de forma relativa
  const popup = document.createElement('div');
  popup.style.position = 'absolute';
  popup.style.background = '#fff';
  popup.style.border = '1px solid #ccc';
  popup.style.padding = '10px';
  popup.style.zIndex = '10000';
  popup.style.display = 'none';
  // Agregar el popup al wrapper en lugar de al body
  editor.wrapper.appendChild(popup);

  // Crear el elemento <img> que se mostrará en el popup
  const popupImg = document.createElement('img');
  popupImg.id = 'image-popup';
  popupImg.style.maxWidth = '300px';
  popupImg.style.maxHeight = '200px';
  popupImg.style.display = 'block';
  popupImg.style.marginBottom = '10px';

  // Botón Guardar
  const saveBtn = document.createElement('button');
  saveBtn.textContent = 'Guardar';
  // Botón Cancelar
  const cancelBtn = document.createElement('button');
  cancelBtn.textContent = 'Cancelar';
  cancelBtn.style.marginLeft = '10px';

  // Agregar elementos al popup
  popup.appendChild(popupImg);
  popup.appendChild(saveBtn);
  popup.appendChild(cancelBtn);

  // Función para mostrar el popup usando offset del botón respecto al wrapper
  function showPopup() {
    popup.style.top = (btn.offsetTop + btn.offsetHeight + 5) + 'px';
    popup.style.left = btn.offsetLeft + 'px';
    popup.style.display = 'block';
  }

  function hidePopup() {
    popup.style.display = 'none';
    // Limpiamos targetImage para que en la siguiente acción se use la imagen por defecto
    targetImage = null;
  }
  
  //  <img      id = "img-test-1"                       
  //         style = "width:350px;height:auto;display:inline-block;" 
  //           src = "1729180006.jpg?ver=<?=$ver?>" 
  //           alt = "Preview" 
  //     data-dest = "dest-img1" 
  //  data-ajaxurl = "ajax.php?op=image-crop">
  
  // Al pulsar el botón del plugin
  btn.addEventListener('click', (e) => {
    e.preventDefault();
    e.stopPropagation();

    // Reiniciamos la variable targetImage
    targetImage = null;

    // Intentamos obtener la imagen de la selección actual
    const sel = window.getSelection();
    if (sel.rangeCount > 0) {
      let node = sel.getRangeAt(0).commonAncestorContainer;
      if (node.nodeType === Node.TEXT_NODE) {
        node = node.parentNode;
      }
      if (node && node.tagName && node.tagName.toLowerCase() === 'img') {
        targetImage = node;
      }
    }
    // Si no se detectó en la selección, usar la última imagen clicada en el editor
    if (!targetImage && lastClickedImg) {
      targetImage = lastClickedImg;
    }

    // Si targetImage existe, usar su src; de lo contrario, usar la imagen por defecto
    popupImg.src = targetImage ? targetImage.getAttribute('src') : defaultImageSrc;

    // Mostrar el popup, posicionándolo según el botón dentro del wrapper
    showPopup();
     
  });

  // Al pulsar "Guardar" en el popup:
  // - Si targetImage existe, se actualiza su src.
  // - En caso contrario, se inserta un nuevo <img> en la posición del cursor.
  saveBtn.addEventListener('click', (e) => {
    e.preventDefault();
    e.stopPropagation();
    const newSrc = popupImg.src;
    if (targetImage) {
      targetImage.setAttribute('src', newSrc);
    } else {
      // Asegurarse de que el editor tenga foco para insertar la imagen
      editor.editorDiv.focus();
      document.execCommand('insertImage', false, newSrc);
    }
    hidePopup();
  });

  // Al pulsar "Cancelar", se cierra el popup sin hacer cambios.
  cancelBtn.addEventListener('click', (e) => {
    e.preventDefault();
    e.stopPropagation();
    hidePopup();
  });

  // Si se hace click fuera del popup (dentro del wrapper), se oculta
  editor.wrapper.addEventListener('click', (e) => {
    if (!popup.contains(e.target) && e.target !== btn) {
      hidePopup();
    }
  });

  return {
    updateState: () => {}
  };
}