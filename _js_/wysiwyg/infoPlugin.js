export function infoPlugin(editor) {



    // Botón para ver/editar el HTML
    const btn = document.createElement('button');
    
    
    //const iconUrl = 'url(/_images_/feather/code.svg)'; 
    //btn.style.backgroundImage = iconUrl;
    btn.classList.add('btn-icon');
    btn.classList.add('btn-info');

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
    const contentInfo = document.createElement('div');
    contentInfo.style.width = '100%';
    contentInfo.style.height = '300px';
    contentInfo.style.boxSizing = 'border-box';
    container.appendChild(contentInfo);

  
    
    // Botones Guardar y Cancelar
    const okiBtn = document.createElement('button');
    okiBtn.textContent = 'Okis';
    okiBtn.style.marginLeft = '10px';

    container.appendChild(okiBtn);

    modal.appendChild(container);
    document.body.appendChild(modal);

    // Mostrar el modal con el HTML actual del editor
    btn.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        contentInfo.innerHTML = `Information<br>${_MODULE_}<br>${_ID_}<br>${_TB_NAME_}<br>`;
        modal.style.display = 'block';
    });

  
    // Cancelar: cerrar el modal sin guardar cambios
    okiBtn.addEventListener('click', (e) => {
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
        updateState: ()=>{ return false}
    };

}