       // BEGIN MODAL DIALOG
       const DLG_overlay = document.querySelector("#modal-overlay");
       const DLG_window  = DLG_overlay.querySelector('#modal-window');
       const DLG_handle  = DLG_window.querySelector('#modal-header');
       const DLG_cancel  = DLG_window.querySelector('#modal-btn-cancel');
       const DLG_create  = DLG_window.querySelector('#modal-btn-create');
       const DLG_close   = DLG_handle.querySelector('#modal-close');
       const DLG_title   = DLG_handle.querySelector('#modal-title');
       let isDragging = false;
       let startX;
       let startY;
       let startLeft;
       let startTop;
       DLG_close  .onclick     = function(e) { close_modal(e) }
       DLG_cancel .onclick     = function(e) { close_modal(e) }
     //DLG_create .onclick     = function(e) { create_file(e) }
       DLG_overlay.onclick     = function(e) { close_modal(e) }
       DLG_handle .onmousedown = function(e) { dragStart(e)   }
       document.addEventListener("mousemove", drag);
       document.addEventListener("mouseup", dragEnd);

       close_modal = (e) => { 
           if([DLG_overlay,DLG_close,DLG_cancel,DLG_create].includes(e.target))  DLG_overlay.style.display = "none"; 
       }       

       show_modal = (type) => {           
           DLG_window.style.top = '50%';
           DLG_window.style.left = '50%';
           DLG_overlay.style.display = "block";
           DLG_title.innerHTML = type;
       }
       
       function dragStart(e) {
           if (e.target !== DLG_handle) return;
           isDragging = true;
           startX = e.clientX;
           startY = e.clientY;
           startLeft = DLG_window.offsetLeft;
           startTop  = DLG_window.offsetTop;
           DLG_window.style.cursor = 'grabbing';
       }

       function drag(e) {
           if (!isDragging) return;
           e.preventDefault();
           DLG_window.style.left = `${startLeft + e.clientX - startX}px`;
           DLG_window.style.top  = `${startTop  + e.clientY - startY}px`;
       }
       
       function dragEnd(e) {
           if (e.target !== DLG_handle) return;
           isDragging = false;
           DLG_window.style.cursor = 'grab';
       }
       // END MODAL DIALOG
       
    