export function basicFormattingPlugin(editor) {
  // Se obtiene el contenedor de la barra de herramientas del editor
  const toolbar = editor.toolbar;

  // Función auxiliar para crear un botón
  function createButton(label, command) {
    const btn = document.createElement('button');
    btn.innerHTML = label;
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      document.execCommand(command, false);
      // Actualizar el estado de los botones tras la acción
      editor.updatePluginStates();
      editor.editorDiv.focus();
    });
    return btn;
  }

  // Crear botones para negrita, cursiva y subrayado
  const boldBtn = createButton('B', 'bold');
  boldBtn.style.fontWeight = 'bold';

  const italicBtn = createButton('I', 'italic');
  italicBtn.style.fontStyle = 'italic';

  const underlineBtn = createButton('U', 'underline');
  underlineBtn.style.textDecoration = 'underline';

  // Añadir los botones a la barra de herramientas
  toolbar.appendChild(boldBtn);
  toolbar.appendChild(italicBtn);
  toolbar.appendChild(underlineBtn);

  // Función para actualizar el estado de los botones según la selección actual
  function updateButtonStates() {
    if (document.queryCommandState('bold')) {
      //console.log('BOLD OK')
      boldBtn.classList.add('active');
    } else {
      //console.log('BOLD KO')
      boldBtn.classList.remove('active');
    }
    if (document.queryCommandState('italic')) {
      //console.log('ITALIC OK')
      italicBtn.classList.add('active');
    } else {
      //console.log('ITALIC KO')
      italicBtn.classList.remove('active');
    }
    if (document.queryCommandState('underline')) {
      //console.log('UNDERLINE OK')
      underlineBtn.classList.add('active');
    } else {
      //console.log('UNDERLINE KO')
      underlineBtn.classList.remove('active');
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
