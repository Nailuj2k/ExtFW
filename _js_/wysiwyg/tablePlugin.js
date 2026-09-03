export function tablePlugin(editor) {
  const toolbar = editor.toolbar;

  // Botón para insertar una tabla
  const btn = document.createElement('button');

  //const iconUrl = 'url(/_images_/feather/table.svg)'; 
  //btn.style.backgroundImage = iconUrl;
  btn.classList.add('btn-icon');
  btn.classList.add('btn-table');
  
  btn.addEventListener('click', (e) => {
    e.preventDefault();
    insertDefaultTable();
  });
  toolbar.appendChild(btn);

  // Inserta una tabla por defecto (2x2)
  function insertDefaultTable() {
    const table = document.createElement('table');
    table.border = 1;
    table.style.borderCollapse = "collapse";
    const tbody = document.createElement('tbody');
    for (let i = 0; i < 2; i++) {
      const tr = document.createElement('tr');
      for (let j = 0; j < 2; j++) {
        const td = document.createElement('td');
        td.style.padding = "4px";
        td.innerHTML = "&nbsp;";
        tr.appendChild(td);
      }
      tbody.appendChild(tr);
    }
    table.appendChild(tbody);
    // Inserta la tabla en la posición del cursor
    editor.editorDiv.focus();
    document.execCommand('insertHTML', false, table.outerHTML);
  }

  // Al hacer clic en el área editable, si se hace click sobre una tabla se muestra el popup
  editor.editorDiv.addEventListener('click', function(e) {
    const target = e.target;
    const tableElement = target.closest('table');
    if (tableElement) {
      e.stopPropagation();
      showTablePopup(tableElement);
    } else {
      // Si se hace click fuera de una tabla, se elimina cualquier popup existente
      removeExistingPopup();
    }
  });

  // Elimina el popup existente (si lo hubiera)
  function removeExistingPopup() {
    const existingPopup = document.querySelector('.table-popup');
    if (existingPopup) {
      existingPopup.remove();
    }
  }

  // Muestra un popup de edición de tabla con opciones: añadir/quitar fila y columna, y cerrar.
  function showTablePopup(tableElement) {
    removeExistingPopup();

    const popup = document.createElement('div');
    popup.className = 'table-popup';
    // Estilos básicos (se pueden extraer a un CSS propio)
    popup.style.position = 'absolute';
    popup.style.background = '#fff';
    popup.style.border = '1px solid #ccc';
    popup.style.padding = '5px';
    popup.style.zIndex = 1000;
    popup.style.display = 'flex';
    popup.style.gap = '5px';

    // Botón "Añadir fila"
    const addRowBtn = document.createElement('button');
    addRowBtn.title = 'Añadir fila';
    addRowBtn.style.backgroundImage = 'url(/_images_/feather/table-add-row.svg)';

    addRowBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      addRow(tableElement);
    });
    popup.appendChild(addRowBtn);

    // Botón "Quitar fila"
    const removeRowBtn = document.createElement('button');
    removeRowBtn.style.backgroundImage = 'url(/_images_/feather/table-delete-row.svg)';
    removeRowBtn.title = 'Quitar fila';

    removeRowBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      removeRow(tableElement);
    });
    popup.appendChild(removeRowBtn);

    // Botón "Añadir columna"
    const addColBtn = document.createElement('button');
    addColBtn.title = 'Añadir columna';
    addColBtn.style.backgroundImage = 'url(/_images_/feather/table-add-col.svg)';

    addColBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      addColumn(tableElement);
    });
    popup.appendChild(addColBtn);

    // Botón "Quitar columna"
    const removeColBtn = document.createElement('button');
    removeColBtn.title = 'Quitar columna';
    removeColBtn.style.backgroundImage = 'url(/_images_/feather/table-delete-col.svg)';

    removeColBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      removeColumn(tableElement);
    });
    popup.appendChild(removeColBtn);

    // Botón "Cerrar"
    const closeBtn = document.createElement('button');
    closeBtn.title = 'Cerrar';
    closeBtn.style.backgroundImage = 'url(/_images_/feather/x.svg)';

    closeBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      popup.remove();
    });
    popup.appendChild(closeBtn);

    // Posicionar el popup cerca de la tabla (por ejemplo, a la derecha y arriba)
    const rect = tableElement.getBoundingClientRect();
    popup.style.top = (window.scrollY + rect.top - popup.offsetHeight - 10) + 'px';
    popup.style.left = (window.scrollX + rect.right + 10) + 'px';

    document.body.appendChild(popup);
  }

  // Función para añadir una fila a la tabla (al final del tbody)
  function addRow(table) {
    const tbody = table.querySelector('tbody');
    if (!tbody) return;
    const rows = tbody.querySelectorAll('tr');
    const newRow = document.createElement('tr');
    if (rows.length > 0) {
      const lastRow = rows[rows.length - 1];
      const cols = lastRow.querySelectorAll('td, th');
      cols.forEach(() => {
        const td = document.createElement('td');
        td.style.padding = "4px";
        td.innerHTML = "&nbsp;";
        newRow.appendChild(td);
      });
    } else {
      // Caso raro: si no hay filas, se crea una con una celda
      const td = document.createElement('td');
      td.style.padding = "4px";
      td.innerHTML = "&nbsp;";
      newRow.appendChild(td);
    }
    tbody.appendChild(newRow);
  }

  // Función para quitar la última fila (dejando al menos una)
  function removeRow(table) {
    const tbody = table.querySelector('tbody');
    if (!tbody) return;
    const rows = tbody.querySelectorAll('tr');
    if (rows.length > 1) {
      rows[rows.length - 1].remove();
    } else {
      alert("No se puede eliminar la única fila.");
    }
  }

  // Función para añadir una columna: agrega una celda al final de cada fila
  function addColumn(table) {
    const rows = table.querySelectorAll('tr');
    rows.forEach(row => {
      const cellTag = row.firstElementChild ? row.firstElementChild.tagName.toLowerCase() : 'td';
      const newCell = document.createElement(cellTag);
      newCell.style.padding = "4px";
      newCell.innerHTML = "&nbsp;";
      row.appendChild(newCell);
    });
  }

  // Función para quitar la última columna de cada fila (dejando al menos una celda)
  function removeColumn(table) {
    const rows = table.querySelectorAll('tr');
    let canRemove = true;
    rows.forEach(row => {
      if (row.children.length <= 1) {
        canRemove = false;
      }
    });
    if (!canRemove) {
      alert("No se puede eliminar la única columna.");
      return;
    }
    rows.forEach(row => {
      row.lastElementChild.remove();
    });
  }

  // El plugin retorna un objeto con updateState (no usado en este caso)
  return {
    updateState: () => {}
  };
}