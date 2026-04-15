export function codePlugin(editor) {
  // Se obtiene el contenedor de la barra de herramientas del editor
  const toolbar = editor.toolbar;

  // Función auxiliar para crear un botón
  function createButton(label, command) {
    const btn = document.createElement('button');
    btn.innerHTML = label;
    btn.addEventListener('click', (e) => {
      /*
      e.preventDefault();
      document.execCommand(command, false);
      // Actualizar el estado de los botones tras la acción
      editor.updatePluginStates();
      editor.editorDiv.focus();
      */


      e.preventDefault();
      e.stopPropagation();
      // Asegurarse de que el editor mantenga el foco (la selección se preserva)
      editor.editorDiv.focus();
      // Ejecutar el comando de formato
      document.execCommand('formatBlock', false, 'pre');
      
      //updateButtonLabel();
      editor.updatePluginStates();



    });
    return btn;
  }

  // Crear botones para negrita, cursiva y subrayado
  const codeBtn = createButton('pre', 'pre');
  
    // Añadir los botones a la barra de herramientas
  toolbar.appendChild(codeBtn);
  
  // Función para actualizar el estado de los botones según la selección actual
  function updateButtonStates() {
    if (document.queryCommandState('pre')) {
      //console.log('PRE OK')
      codeBtn.classList.add('active');
    } else {
      //console.log('PRE KO')
      codeBtn.classList.remove('active');
    }
  }


  
  // Actualizar el estado de los botones al cambiar la selección
  document.addEventListener('selectionchange', () => {
    if (document.activeElement === editor.editorDiv) {
      updateButtonStates();
    }
  });

  // Devolver un objeto con el método updateState para que el editor pueda actualizar la UI del plugin
  return {
    updateState: updateButtonStates
  };

}
