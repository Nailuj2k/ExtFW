export function fullscreenPlugin(editor) {
  // Botón para activar/desactivar fullscreen
  const btn = document.createElement('button');


  //const compressIconUrl = 'url(/_images_/feather/minimize.svg)'; // Icono para activar fullscreen
  //const expandIconUrl = 'url(/_images_/feather/maximize.svg)'; // Icono para salir de fullscreen
  //btn.style.backgroundImage = expandIconUrl;
  btn.classList.add('btn-icon');
  btn.classList.add('btn-maximize');

  editor.toolbar.appendChild(btn);

  // Bandera para el estado fullscreen
  let isFullscreen = false;
  
  // Guardamos algunos estilos originales del wrapper para poder restaurarlos
  const originalStyles = {
    position: editor.wrapper.style.position || '',
    top: editor.wrapper.style.top || '',
    left: editor.wrapper.style.left || '',
    width: editor.wrapper.style.width || '',
    height: editor.wrapper.style.height || '',
    zIndex: editor.wrapper.style.zIndex || ''
  };

  function toggleFullscreen() {
    if (!isFullscreen) {
      // Activar fullscreen: se configura el wrapper en fixed para ocupar toda la ventana
      editor.wrapper.style.position = 'fixed';
      editor.wrapper.style.top = '26px';
      editor.wrapper.style.left = '0';
      editor.wrapper.style.width = '100vw';
      editor.wrapper.style.height = '100vh';
      editor.wrapper.style.zIndex = '9999';
      //btn.style.backgroundImage = compressIconUrl;
      btn.classList.remove('btn-maximize');
      btn.classList.add('btn-minimize');     
      isFullscreen = true;
    } else {
      // Restaurar estilos originales
      editor.wrapper.style.position = originalStyles.position;
      editor.wrapper.style.top = originalStyles.top;
      editor.wrapper.style.left = originalStyles.left;
      editor.wrapper.style.width = originalStyles.width;
      editor.wrapper.style.height = originalStyles.height;
      editor.wrapper.style.zIndex = originalStyles.zIndex;
      //btn.style.backgroundImage = expandIconUrl;
      btn.classList.remove('btn-minimize');
      btn.classList.add('btn-maximize');
      isFullscreen = false;
    }
  }

  // Al pulsar el botón se alterna el modo fullscreen
  btn.addEventListener('click', (e) => {
    e.preventDefault();
    e.stopPropagation();
    toggleFullscreen();
  });

  // Permite salir del modo fullscreen con la tecla Escape
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && isFullscreen) {
      toggleFullscreen();
    }
  });

  return {
    updateState: () => {}
  };
}
