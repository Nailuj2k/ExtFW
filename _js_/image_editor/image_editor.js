async function getCameraList(camSelect) {
      const devices = await navigator.mediaDevices.enumerateDevices();
      const videoDevices = devices.filter(device => device.kind === 'videoinput');
      camSelect.innerHTML = '';
      videoDevices.forEach(device => {
          const option = document.createElement('option');
          option.value = device.deviceId;
          option.text = device.label || `Cámara ${camSelect.length + 1}`;
          camSelect.appendChild(option);
      });
  }    


  class ImageEditor {
      
      static editable_image_list = [];
      static image_index = 1;
      constructor(options) {
        this.imageId = options.imageId;
        this.ajaxUrl = options.ajaxUrl;
        this.cropper = null;
        this.containerDiv = null;
        this.loaderDiv = null;
        this.originalImg = null;
        this.originalWidth = 0;
        this.originalHeight = 0;        
        this.originalClass = '';  // value of class attribute
        this.originalStyle = '';  // value of class attribute
        //OLD this.resizeHandle = null;  //DEL ?
        this.fileInput = null;   
        this.img = document.getElementById(this.imageId);
        //OLD this.img.addEventListener('dblclick', this.execute.bind(this), false);
        this.saveAs = options.saveAs ? options.saveAs : false;
        this.destImg = options.destImg ? document.querySelector(options.destImg) : false;
        // Add support for image format - defaults to 'jpeg' for backward compatibility
        this.imageFormat = options.imageFormat ? options.imageFormat.toLowerCase() : 'jpeg';
        //LD this.overlay = null;
        this.rotation = 0; // Nueva propiedad para la rotación
        this.originalSrc = this.img.getAttribute('src')
        this.filterControls = document.createElement('div');
        this.filters = {
            brightness: 100,
            contrast: 100,
            grayscale: 0
        };
        //NEW
        this.lastAnimationFrame = null;
        this.OnUploadSuccessCallback = options.OnUploadSuccessCallback || null;        
        console.log('VERSION','1.1.1')
    
        // Event listener para manejar eventos de doble clic en la imagen con el ID especificado
        this.img.addEventListener('dblclick', (e) => {
            e.preventDefault(); // Evita el comportamiento predeterminado del doble clic
            e.stopPropagation(); // Detiene la propagación del evento
            console.log('DATASET',this.img.dataset.dest);
            //this.destImg = this.img.dataset.dest ? document.querySelector(this.img.dataset.dest) : false;
            //this.imageId = this.img.id; // ? this.img.dataset.ajxurl : (options.ajaxUrl ? options.ajaxUrl : false);
            //this.ajaxUrl = this.img.dataset.ajaxurl ? this.img.dataset.ajxurl : (options.ajaxUrl ? options.ajaxUrl : false);
            //this.destImg = this.img.dataset.dest ? document.querySelector('#'+this.img.dataset.dest) : (options.destImg ? document.querySelector(options.destImg) : false);
            this.execute();            
        });
        /***
        // Captura el evento en el elemento padre (<a>)
        const parent = this.img.parentElement; // Obtiene el elemento padre
        if (parent && parent.tagName === 'A') {
           parent.removeAttribute('href');
        }        
        ***/
        /*            
        // Event listener para manejar eventos de doble clic en la imagen con el ID especificado
        parent.addEventListener('dblclick', (e) => {
          e.preventDefault(); // Evita el comportamiento predeterminado del doble clic
          e.stopPropagation(); // Detiene la propagación del evento
        });
        */ 
        // Estados posibles del editor  //OLD??
        this.states = {
            IDLE: 'idle',
            CROPPING: 'cropping',
            LOADING: 'loading',
            READY_TO_SAVE: 'ready_to_save'
        };
        this.currentState = this.states.IDLE;
       
        this.execute();

      }

      setState(newState) {
         this.currentState = newState;
         ImageEditor.showMessage(`Estado cambiado a: ${this.currentState}`,this.containerDiv,2000);
      }
      
      // Función modular para crear un control de filtro
      createFilterControl(labelText, filterProperty, min, max) {
        const input = document.createElement('input');
        input.type = 'range';
        input.min = min;
        input.max = max;
        input.value = this.filters[filterProperty];
        input.title = `${labelText}: ${input.value}%`;
        input.addEventListener('input', (e) => {
            this.filters[filterProperty] = e.target.value;
            input.title = `${labelText}: ${e.target.value}%`;
            this.applyFilters();
        });
        input.classList.add('image-editor-filter-input');
        return input;
      }

      applyFilters() {
        if (this.lastAnimationFrame) cancelAnimationFrame(this.lastAnimationFrame);//NEW
        
        this.lastAnimationFrame = requestAnimationFrame(() => {//NEW
          const { brightness, contrast, grayscale } = this.filters;
          this.img.style.filter = `brightness(${brightness}%) contrast(${contrast}%) grayscale(${grayscale}%)`;
        });  //NEW

      }   

      createFilterControls() {

        if (this.filterControls && this.filterControls.childElementCount > 0) {
          //this.filterControls.style.display = 'block';
          console.warn('CREATED createFilterControls')
          return;
        }

        this.filterControls.classList.add('image-editor-filters');

        this.filterControls.appendChild(this.createFilterControl('Brillo', 'brightness', 0, 200));
        this.filterControls.appendChild(this.createFilterControl('Contraste', 'contrast', 0, 200));
        this.filterControls.appendChild(this.createFilterControl('Escala de grises', 'grayscale', 0, 100));

        this.containerDiv.appendChild(this.filterControls);
      }

      execute(e) {
        
        if (this.currentState !== this.states.IDLE) {
            ImageEditor.showWarning('No se puede ejecutar mientras el editor no esté en estado IDLE',this.containerDiv);
            return;
        }        
        
        this.originalImg = document.getElementById(this.imageId);
        if (!this.originalImg) {
          ImageEditor.showError(`No se encontró la imagen con id ${this.imageId}`,this.containerDiv);
          return;
        }
        
        this.originalWidth = this.originalImg.width;
        this.originalHeight = this.originalImg.height;
        this.originalClass = this.originalImg.getAttribute("class");
        this.originalStyle = this.originalImg.getAttribute("style");

        this.containerDiv = this.createContainerDiv();
        this.fileInput = this.createFileInput();
        this.containerDiv.appendChild(this.fileInput);
  
        this.loaderDiv = document.createElement('div');
        this.loaderDiv.classList.add('loader');
        this.containerDiv.appendChild(this.loaderDiv);

        const newImg = this.createImageElement();
        this.containerDiv.appendChild(newImg);
        this.createControls();
  
        this.originalImg.parentNode.replaceChild(this.containerDiv, this.originalImg);
        this.img = document.getElementById(this.imageId);
  
        this.addDragAndDropListeners();

        this.setState(this.states.READY_TO_SAVE);
        this.createFilterControls();
        //this.filterControls.style.display = 'none';
        if(this.originalWidth<300) this.containerDiv.classList.add('small');
  
      }
      
      async reloadImg(url) {
        await fetch(url, { cache: 'reload', mode: 'no-cors' })
        document.body.querySelectorAll(`img[src='${url}']`)
          .forEach(img => img.src = url)
      }
      
      cancelEdition(){
          this.containerDiv.outerHTML = `<img id="${this.imageId}" class="${this.originalClass}" style="${this.originalStyle}"  src="${this.originalSrc}">`; // + '?ver=' + timestamp+'">`;
          this.reloadImg(this.originalSrc)
          console.log('CANCEL.EDITION','#'+this.imageId,this.ajaxUrl)
          
          let id  = '#'+this.imageId
          let url = this.ajaxUrl
  
          setTimeout(function(){
              console.log('EDITABLE.IMAGE',id,url);  //,src)
              ImageEditor.editable_images(id,url);  
          },2000);

      }

      createContainerDiv() {
        const containerDiv = document.createElement('div');
        containerDiv.setAttribute('class','image-editor-container '+this.originalClass)
        containerDiv.setAttribute('style',this.originalStyle)
        /** */
        containerDiv.style.border   = this.originalImg.style.border   ? this.originalImg.style.border   : 'none';
        containerDiv.style.display  = this.originalImg.style.display  ? this.originalImg.style.display  : 'inline-block';
        containerDiv.style.width    = this.originalWidth  + 'px';
        containerDiv.style.height   = this.originalHeight + 'px'; 
        /**/
        containerDiv.style.position = this.originalImg.style.position ? this.originalImg.style.position : 'relative';
        return containerDiv;
      }
  
      createFileInput() {
        const fileInput = document.createElement('input');
        fileInput.type = "file";
        fileInput.style.display = "none";
        fileInput.addEventListener('change', (e) => {
            this.setState(this.states.LOADING);
            this.handleFile(e.target.files[0]);
        });        
        return fileInput;
      }
  
      createImageElement() {
        const newImg = document.createElement('img');
        newImg.src = this.originalImg.src;
        newImg.id = this.imageId;
        newImg.style.maxWidth = '100%';
        newImg.style.maxHeight = '100%';
        newImg.style.height = '100%';
        newImg.style.objectFit = 'contain';
        return newImg;
      }
  
      addDragAndDropListeners() {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => { this.containerDiv.addEventListener(eventName, ImageEditor.preventDefaults,       false);  });
        ['dragenter', 'dragover'                     ].forEach(eventName => { this.containerDiv.addEventListener(eventName, ImageEditor.xhighlight.bind(this),  false);  });
        ['dragleave', 'drop'                         ].forEach(eventName => { this.containerDiv.addEventListener(eventName, ImageEditor.xunhighlight.bind(this),false);  });
        ['drop'                                      ].forEach(eventName => { this.containerDiv.addEventListener(eventName, this.handleDrop.bind(this),        false);  });
      }

      static preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
      }
      
      static xhighlight(e)   { this.containerDiv.classList.add   ('image-editor-highlight') }
      static xunhighlight(e) { this.containerDiv.classList.remove('image-editor-highlight') }
      /*
      static highlight(elements) {
        elements.forEach(function(el) {  
          el.style.opacity = 1;
          / *
          var fadeEffect = setInterval(function() {
            if (el.style.opacity<=0.3) {
              el.style.opacity = 1;
            }
            if (el.style.opacity > 0.3) {
              el.style.opacity -= 0.01;
            } else {
              clearInterval(fadeEffect);
            }
          }, 10);
          * /
        });
      }
      */
      /**
      static highlight(elements) {
        if (NodeList.prototype.isPrototypeOf(elements) || Array.isArray(elements)) {
          elements.forEach(function(el) {  
            el.style.opacity = 1;
          });
        } else if (elements instanceof Element) {
          elements.style.opacity = 1;
        } else {
          console.warn('ImageEditor.highlight: elements no es iterable ni un elemento válido.', elements);
        }
      }
      ******/
      //NEW Función modular para crear iconos
      createIcon(iconText, title) {
          const icon = document.createElement('span');
          icon.classList.add('image-editor-icon');
          icon.title = title;
          icon.textContent =`${iconText}`;
          icon.style.transition = 'all 0.3s ease';
          return icon;
      }

      createControls() {
        const controls = document.createElement('div');   //controls.className = 'image-editor-controls';
        controls.classList.add('image-editor-controls');

        controls.appendChild(this.createIcon('✂️', 'Recortar'));
        controls.appendChild(this.createIcon('🔄', 'Rotar'));
        controls.appendChild(this.createIcon('📷', 'Cámara'));
        if(this.imageFormat=='jpeg')
        controls.appendChild(this.createIcon('🎨', 'Filtros')); // Nuevo botón
        controls.appendChild(this.createIcon('💾', 'Guardar'));
        controls.appendChild(this.createIcon('📤', 'Load'));
        controls.appendChild(this.createIcon('❌', 'Cancel'));
        controls.appendChild(this.createIcon('❓', 'Help'));
        
        controls.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            if (e.target.classList.contains('image-editor-icon')) {
              const action = e.target.title;
              switch (action) {
                case 'Recortar'  :  this.startCropping();  break;
                case 'Rotar'     :  this.rotateImage();    break;                  
                case 'Cámara'    :  this.openCamera();     break;
                case 'Filtros'   :  this.toggleFilters();  break; // Nueva acción
                case 'Guardar'   :  this.saveImage();      break;
                case 'Load'      :  this.loadFile();       break;
                case 'Cancel'    :  this.cancelEdition();  break;                
                case 'Help'      :  this.help();           break;
                }
            }
        })

        this.containerDiv.appendChild(controls);
      }

      toggleFilters() {
        if (!this.filterControls.childElementCount) {
            this.createFilterControls();
        }
        const filterIcon = Array.from(this.containerDiv.querySelectorAll('.image-editor-icon'))
                               .find(icon => icon.title === 'Filtros');
        
        if (filterIcon) {
            filterIcon.style.backgroundColor = this.filterControls.classList.contains('visible') 
                ? 'revert' 
                : 'rgba(76, 175, 80, 0.3)';
        }
        this.filterControls.classList.toggle('visible');
      }

      rotateImage() {
        if (this.cropper && typeof this.cropper !== 'undefined') {
            this.cropper.rotate(90); // rota la imagen del cropper 
        } else{
            this.rotation = (this.rotation + 90) % 360; // rota la imagen original
            this.img.style.transform = `rotate(${this.rotation}deg)`; 
        }
      }      
      
      loadFile() { this.fileInput.click() }
      
      help() {
        const message = 'Double Click in cropper or outside to change crop/move function'
        ImageEditor.showMessage(message,this.containerDiv,20000)
      }

      async readFileAsync(file) {
        return new Promise((resolve, reject) => {
          const reader = new FileReader();
          reader.onload = () => resolve(reader.result);
          reader.onerror = () => reject(reader.error);
          reader.readAsDataURL(file);
        });
      }
     
      async handleFile(file) {   
        if (this.cropper)  this.cropper.destroy();
        if (file && file.type.startsWith('image/')) {
            try {
                // Auto-detect and set format based on loaded file type
                if (file.type === 'image/png') {
                    this.imageFormat = 'png';
                } else if (file.type === 'image/webp') {
                    this.imageFormat = 'webp';
                } else {
                    this.imageFormat = 'jpeg';
                }
                
                const result = await this.readFileAsync(file); // console.log('IMG', this.imageId, this.img);
                this.img.src = result;
                this.setState(this.states.READY_TO_SAVE);
            } catch (error) {
                ImageEditor.showError(`Error al leer el archivo: ${error}`,this.containerDiv);
            }
        } else {
          ImageEditor.showError('Por favor, selecciona un archivo de imagen válido.',this.containerDiv);
          this.setState(this.states.IDLE);
        }
      }
    
      handleDrop(e) {
        const dt = e.dataTransfer;
        const file = dt.files[0];
        this.setState(this.states.LOADING);
        this.handleFile(file)
      }
        
      startCropping() {
        if (this.cropper) {
          this.resetCropperState();
          this.filterControls.style.display = 'block';
          //ilterControls.classList.remove('hidden')
        } else {
          this.initCropper();
          //this.filterControls.style.display = 'none';
          //this.filterControls.classList.add('hidden')
        }
      }

      resetCropperState() {
          if (this.cropper) {
              this.cropper.destroy();
              this.cropper = null;
              this.setState(this.states.IDLE);
          }
      }

      initCropper() {
          const aspectRatio = NaN;  //this.originalWidth / this.originalHeight;
          const options = this.createCropperOptions(aspectRatio);
          this.cropper = new Cropper(this.img, options);
          this.setState(this.states.CROPPING);
      }

      createCropperOptions(aspectRatio) {
          return {
              aspectRatio: aspectRatio,
              viewMode: 1,
              autoCropArea: 0.9,
              //responsive: true,
              //background: false,
              //scalable: true,
              dragMode: 'crop',
              restore: false,
              modal: true,
              guides: true,
              highlight: true,
              cropBoxMovable: true,
              cropBoxResizable: true,
              movable:true,
              zoomable:true,
              zoomOnWheel:true,
              toggleDragModeOnDblclick:true            
          };
      }      
    
      closeModal() {
        document.body.removeChild(this.overlay);
      }
  
      createModal() {
        this.overlay = document.createElement('div');
        const cameraDiv = document.createElement('div');
        const closeButton = document.createElement('span');
        const dragHandle = document.createElement('div');
        closeButton.textContent = '❌';
        cameraDiv.appendChild(dragHandle);
        cameraDiv.appendChild(closeButton);     
        this.overlay.appendChild(cameraDiv);
        document.body.appendChild(this.overlay);
        
        dragHandle.innerText = 'Captura cámara';       
        
        this.overlay.classList.add('image-editor-overlay');
        cameraDiv.classList.add('image-editor-camera-modal');
        closeButton.classList.add('image-editor-close-button');
        dragHandle.classList.add('image-editor-drag-handle');

        closeButton.addEventListener('click', () => this.closeModal(this.overlay));       
     
        let isDragging = false;
        let startX;
        let startY;
        let startLeft;
        let startTop;

            
        let dragStart = (e) => {
            isDragging = true;
            startX = e.clientX;
            startY = e.clientY;
            startLeft = cameraDiv.offsetLeft;
            startTop = cameraDiv.offsetTop;
            cameraDiv.style.cursor = 'grabbing';
        }
  
        let drag = (e) => {
            if (!isDragging) return;
            e.preventDefault();
            const dx = e.clientX - startX;
            const dy = e.clientY - startY;
            cameraDiv.style.left = `${startLeft + dx}px`;
            cameraDiv.style.top = `${startTop + dy}px`;
        }
  
        let dragEnd = (e) => {
            isDragging = false;
            cameraDiv.style.cursor = 'grab';
        }
   
        dragHandle.addEventListener("mousedown", dragStart);
        document.addEventListener("mousemove", drag);
        document.addEventListener("mouseup", dragEnd);

        this.overlay.addEventListener('click', (e) => { if (e.target === this.overlay) this.closeModal(); });      
  
        return cameraDiv;
        
      }
      
      openCamera() {      
          let activeFilters = [];
    
          const modal = this.createModal();
    
          const video = document.createElement('video');
          const topBar = document.createElement('div');
          const bottomBar = document.createElement('div');
          const camSelect = document.createElement('select');
          const captureButton = document.createElement('button');
          const playPauseBtn = document.createElement('button');
          const grayscaleBtn = document.createElement('button');
          const sepiaBtn = document.createElement('button');
          const invertBtn = document.createElement('button');     


          topBar.classList.add('image-editor-top-bar');
          video.classList.add('image-editor-video');
          bottomBar.classList.add('image-editor-bottom-bar');

          playPauseBtn.textContent = 'Pause';
          playPauseBtn.style.marginRight = '40px';
          grayscaleBtn.textContent = 'Grayscale';
          sepiaBtn.textContent = 'Sepia';
          invertBtn.textContent = 'Invert';
          captureButton.textContent = 'Capturar';
          captureButton.style.marginLeft = '40px';
          captureButton.disabled = true;
          playPauseBtn.disabled = true;
    
          topBar.appendChild(camSelect)
          bottomBar.appendChild(playPauseBtn);
          bottomBar.appendChild(grayscaleBtn);
          bottomBar.appendChild(sepiaBtn);
          bottomBar.appendChild(invertBtn);
          bottomBar.appendChild(captureButton);
          modal.appendChild(topBar); 
          modal.appendChild(video);
          modal.appendChild(bottomBar);
    
          getCameraList(camSelect);      
    
          let applyFilters = () => {
            video.style.filter = activeFilters.join(' ');
          }
    
          let toggleFilter = (filterName, button) => {
            const index = activeFilters.indexOf(filterName);
            if (index > -1) {
                activeFilters.splice(index, 1);
                button.style.backgroundColor = 'revert';
                button.style.color = 'revert';
                
              } else {
                activeFilters.push(filterName);
                button.style.backgroundColor = '#4CAF50';
                button.style.color = 'white';
            }
            applyFilters();
          }
    
          let initializeCamera = (deviceId) => {
            
            const constraints = {
              video: {deviceId: deviceId ? {exact: deviceId} : undefined}
            };
            
            navigator.mediaDevices.getUserMedia(constraints) 
            .then(stream => {
              video.srcObject = stream;
              video.play();      //console.log('INFO','Cámara inicializada: ' + deviceId)
              captureButton.disabled = false;
              playPauseBtn.disabled = false;
              playPauseBtn.textContent = 'Pausar';
            })
            .catch(err => {
              captureButton.disabled = true;
              playPauseBtn.disabled = true;
              ImageEditor.showError('Error al acceder a la cámara: ' +  deviceId + ': '+ err.message,modal);
              video.srcObject = null
            });       
            
          }
          
          camSelect.addEventListener('change', (event) => {
            initializeCamera(event.target.value);
          });      
          
          grayscaleBtn.addEventListener('click', () => toggleFilter('grayscale(100%)', grayscaleBtn));
          sepiaBtn    .addEventListener('click', () => toggleFilter(    'sepia(100%)', sepiaBtn    ));
          invertBtn   .addEventListener('click', () => toggleFilter(   'invert(100%)', invertBtn   ));
    
          setTimeout(function(){ initializeCamera(camSelect.options[0].value) },1000)
    
          playPauseBtn.addEventListener('click', () => {
              if (video.paused) {
                  video.play();
                  playPauseBtn.textContent = 'Pausar';
              } else {
                  video.pause();
                  playPauseBtn.textContent = 'Reproducir';
              }
          });          
          
          captureButton.addEventListener('click', () => {
            if (this.cropper && typeof this.cropper !== 'undefined' && this.cropper.ready) this.cropper.destroy();
            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            // Enable alpha channel for PNG and WebP camera captures
            const ctx = canvas.getContext('2d', { alpha: (this.imageFormat === 'png' || this.imageFormat === 'webp') });
            
            // For PNG and WebP, don't fill background to preserve transparency
            // For JPEG, fill with white to avoid black background
            if (this.imageFormat !== 'png' && this.imageFormat !== 'webp') {
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(0, 0, canvas.width, canvas.height);
            }
            
            ctx.filter = activeFilters.join(' ');
            ctx.drawImage(video, 0, 0);
            
            // Use the selected format
            let cameraFormat, cameraQuality;
            if (this.imageFormat === 'png') {
                cameraFormat = 'image/png';
                cameraQuality = 1.0;
            } else if (this.imageFormat === 'webp') {
                cameraFormat = 'image/webp';
                cameraQuality = 0.9;
            } else {
                cameraFormat = 'image/jpeg';
                cameraQuality = 0.9;
            }
            
            this.img.src = canvas.toDataURL(cameraFormat, cameraQuality);
            this.closeModal();
          });
      }
     
      saveImage() {
        let d = new Date();
        let t = d.getTime();

        this.loaderDiv.style.display = 'block';

        // Auto-detect format from current image source to preserve transparency
        const currentSrc = this.img.src;
        if (currentSrc.startsWith('data:image/png') || currentSrc.includes('.png')) {
            this.imageFormat = 'png';
        } else if (currentSrc.startsWith('data:image/webp') || currentSrc.includes('.webp')) {
            this.imageFormat = 'webp';
        }

        let imageData = this.getImageData();
    
        // le añado esto para que a la vuelta del ajax se vea la nueva imagen y ya va bien:
        if(!this.destImg) this.img.style.transform = `rotate(0deg)`;   
    
        this.uploadImage(imageData, t);
      }
      
      getImageData() {
        let imageData;
        // Get format and quality based on image format setting
        let imageFormat, imageQuality, fileExtension;
        
        if (this.imageFormat === 'png') {
            imageFormat = 'image/png';
            imageQuality = 1.0; // PNG doesn't use quality, but set to max
            fileExtension = 'png';
        } else if (this.imageFormat === 'webp') {
            imageFormat = 'image/webp';
            imageQuality = 0.9; // WebP supports quality and transparency
            fileExtension = 'webp';
        } else {
            // Default to JPEG for backward compatibility
            imageFormat = 'image/jpeg';
            imageQuality = 0.85;
            fileExtension = 'jpg';
        }
        
        if (this.cropper && typeof this.cropper !== 'undefined' && this.cropper.ready) {
            // Configure cropper to preserve transparency for PNG and WebP
            const cropperOptions = {};
            if (this.imageFormat === 'png' || this.imageFormat === 'webp') {
                cropperOptions.fillColor = 'transparent';
                cropperOptions.imageSmoothingEnabled = false;
            } else {
                cropperOptions.fillColor = '#ffffff';
            }
            
            const croppedCanvas = this.cropper.getCroppedCanvas(cropperOptions);
            imageData = croppedCanvas.toDataURL(imageFormat, imageQuality);
            this.cropper.destroy();
            this.cropper = null;
        } else {
            // Crear un canvas para aplicar la rotación si es necesario
            const canvas = document.createElement('canvas');
            // Enable alpha channel for PNG and WebP formats
            const context = canvas.getContext('2d', { alpha: (this.imageFormat === 'png' || this.imageFormat === 'webp') });
            const imgWidth = this.img.naturalWidth;
            const imgHeight = this.img.naturalHeight;
    
            // Ajustar tamaño del canvas según la rotación
            if (this.rotation % 180 === 0) {
                canvas.width = imgWidth;
                canvas.height = imgHeight;
            } else {
                canvas.width = imgHeight;
                canvas.height = imgWidth;
            }
    
            // For PNG and WebP, clear the canvas to ensure transparency
            // For JPEG, fill with white background
            if (this.imageFormat === 'png' || this.imageFormat === 'webp') {
                context.clearRect(0, 0, canvas.width, canvas.height);
            } else {
                context.fillStyle = '#ffffff';
                context.fillRect(0, 0, canvas.width, canvas.height);
            }
    
            // Aplicar rotación al contexto del canvas
            context.translate(canvas.width / 2, canvas.height / 2);
            context.rotate((this.rotation * Math.PI) / 180);
            // Aplicar los filtros al contexto del canvas
            context.filter = `brightness(${this.filters.brightness}%) contrast(${this.filters.contrast}%) grayscale(${this.filters.grayscale}%)`;          
            context.drawImage(this.img, -imgWidth / 2, -imgHeight / 2);
            imageData = canvas.toDataURL(imageFormat, imageQuality);
        }
        return imageData;
      }

      uploadImage(imageData, timestamp) {
        this.convertToBlob(imageData)
            .then(blob => this.prepareFormData(blob))
            .then(formData => this.sendImage(formData, timestamp))     // quitar timestamp
            .then(data => this.handleUploadSuccess(data, timestamp))
            .catch(error => {
               this.loaderDiv.style.display = 'block';
               ImageEditor.showError('Error al guardar la imagen: ' + error, this.containerDiv)
            });
      }

      async convertToBlob(imageData) {
          const res = await fetch(imageData);
          return await res.blob();
      }
      
      prepareFormData(blob) {
          // Check file size before uploading
          const fileSizeMB = blob.size / (1024 * 1024);
          console.log('File size before upload:', fileSizeMB.toFixed(2), 'MB');
          
          // If file is still too large (over 4MB), show a warning
          if (fileSizeMB > 4) {
              ImageEditor.showWarning(`La imagen es bastante grande (${fileSizeMB.toFixed(2)}MB), lo que podría causar problemas de carga.`, this.containerDiv);
          }
          
          // Determine file extension based on format
          const fileExtension = this.imageFormat === 'png' ? 'png' : 'jpg';

          console.log('CROPPEDIMAGE', this);


          const [fileName, ext] = (this.originalSrc.split("?")[0].split("/").pop().match(/(.+)\.([^.]+)$/).slice(1));

          // cropped-image

          const formData = new FormData();
          formData.append('croppedImage', blob, `${fileName}.${fileExtension}`);
          formData.append('src', this.originalSrc);
          if (this.saveAs) formData.append('saveas', this.saveAs);
        //if (this.saveAs) formData.append('src', this.saveAs);
        //            else formData.append('src', this.originalSrc);
          return formData;
      }

      async sendImage(formData, timestamp) {  // quitar timestamp
          console.log('AJAX.URL', this.ajaxUrl);
          try {
              const response = await fetch(this.ajaxUrl, {
                  method: 'POST',
                  body: formData
              });
              
              // Check if response is ok
              if (!response.ok) {
                  throw new Error(`Server returned ${response.status} ${response.statusText}`);
              }
              
              // Try to parse JSON response
              try {
                  return await response.json();
              } catch (jsonError) {
                  // If not JSON, try to get text response for more information
                  const textResponse = await response.text();
                  throw new Error(`Failed to parse server response as JSON. Server returned: ${textResponse.substring(0, 100)}...`);
              }
          } catch (error) {
              console.error('Error uploading image:', error);
              throw error;
          }
      }

      handleUploadSuccess(data, timestamp) {
        console.table(/*'handleUploadSuccess',*/data)
        let loader =  this.loaderDiv;

        //filterControls.classList.remove('hidden')
        this.filterControls.style.display = 'block';
        this.setState(this.states.READY_TO_SAVE);

        if (this.destImg) {
          this.destImg.src = data.src + '?ver=' + timestamp;
          loader.style.display = 'none';
        }else{
          //OLD this.img.src = data.src + '?ver=' + timestamp;

          this.containerDiv.outerHTML = `<img id="${this.imageId}" class="${this.originalClass}" style="${this.originalStyle}"  src="${data.src}">`; // + '?ver=' + timestamp+'">`;
          this.reloadImg(data.src)
          console.log('CANCEL.EDITION','#'+this.imageId,this.ajaxUrl)
          
          let id  = '#'+this.imageId
          let url = this.ajaxUrl

          setTimeout(function(){
              loader.style.display = 'none';
              console.log('EDITABLE.IMAGE',id,url);  //,src)
              ImageEditor.editable_images(id,url);  
          },1000);

        }
        ImageEditor.showMessage('Imagen guardada con éxito: ' + data.src, this.containerDiv);

        if(this.OnUploadSuccessCallback && typeof this.OnUploadSuccessCallback === 'function') {
            this.OnUploadSuccessCallback(data.src, this.imageId); //, this.destImg, this.containerDiv, timestamp);
        }

      }

      static showMessage(message,element,duration=5000) {
          if (typeof show_info === "function")
            //show_info('top-right',message,duration,function(e){e.animate({'top':'+=50'});});
            notify(message,'info',duration);
          else
            console.log(message);
      }
    
      static showError(message,element,duration=5000) {
        if (typeof show_error === "function")
            show_error('top-right',message,duration,function(e){e.animate({'top':'+=50'});});
        else
        //    console.error(message);
            $('#error').append('<pre>'+message+'</pre>');

      }
  
      static showWarning(message,element,duration=5000) {
        if (typeof show_error === "function")
            show_warning('top-right',message,duration,function(e){e.animate({'top':'+=50'});});
        else
            console.warn(message);
      }
      
      static editable_images(selector,ajax_url,onUpload=null,image_format='jpeg'){
        console_log('EDITABLE_IMAGES',selector,ajax_url,image_format)
        
        // Default to 'jpeg' for backward compatibility if no format specified
        const outputFormat = image_format ? image_format.toLowerCase() : 'jpeg';
          
        document.querySelectorAll(selector).forEach(image => {
            console_log('EDITABLE_IMAGES.IMAGE',image)
        
            let image_id =  image.id ?? false;
          
            if(!image_id) {
              image.id='image_'+(this.image_index++);
              image_id =  image.id
            }

            let image_dest = image.dataset.dest
                           ? image.dataset.dest
                           : false; //options.destImg ? document.querySelector(options.destImg) : false;

            console_log('IMAGE_ID',image_id)
            console_log('SAVEAS',image.src);
            /*
            this.editable_image_list[image_id] = new ImageEditor({
                imageId: image_id,
                ajaxUrl: ajax_url,  //'ajax.php?op=image-crop'//,
                saveAs : image.src,
                destImg : image_dest
            })
            */

            // Crear el enlace "Editar imagen"
            const editLink = document.createElement('a');
            editLink.textContent = '✏️'; //'Editar imagen '+image.src;
            editLink.title = 'Editar imagen '+image.src;
            editLink.classList.add('edit-image-link');

            // 8. Añadir eventos para mostrar/ocultar el botón al pasar el ratón
            /*
            image.addEventListener('mouseenter', function() { ImageEditor.highlight([editLink]); });
            image.addEventListener('mouseleave', function() { ImageEditor.highlight([editLink]); });
            */
            // Contenedor relativo para el editLink
            const wrapper = document.createElement('div');
            wrapper.style.position = 'relative';
            wrapper.style.display = image.style.display || 'inline-block';
            wrapper.style.width = 'fit-content';
            
            // Reposicionar el editLink relativamente al wrapper
            editLink.style.position = 'absolute';
            editLink.style.top = '10px';
            editLink.style.right = '10px';
            
            // Envolver la imagen con el wrapper y añadir el editLink
            image.parentNode.insertBefore(wrapper, image);
            wrapper.appendChild(image);
            wrapper.appendChild(editLink);

            // Heredar clases y estilos computados de la imagen
            const computedStyle = window.getComputedStyle(image);
            wrapper.style.float = computedStyle.float;
            wrapper.style.margin = computedStyle.margin;
            //wrapper.style.display = computedStyle.display;
            
            // Heredar las clases excepto 'editable-image'
            const classes = image.className.split(' ').filter(c => c !== 'editable-image');
            wrapper.className = classes.join(' ');
            
            image.style.margin = '0'; 
            image.style.float = 'none';

            // Manejar el evento de clic en el enlace "Editar imagen"
            editLink.addEventListener('click', (e) => {

                e.preventDefault();
                e.stopPropagation();

                // Eliminar el atributo href si la imagen está dentro de un enlace <a>

                let a = image.closest('a');
                if(a) {
                  this.originalHref = a.getAttribute('href');
                  a.removeAttribute('href');
                }
                /*
                if (image.parentElement && image.parentElement.tagName === 'A') {
                    this.originalHref = image.parentElement.getAttribute('href');
                    image.parentElement.removeAttribute('href');
                }
                */                // Inicializar el ImageEditor
                this.editable_image_list[image_id] = new ImageEditor({
                    imageId: image_id,
                    ajaxUrl: ajax_url,
                    saveAs: image.src,
                    destImg: image_dest,
                    imageFormat: outputFormat,
                    OnUploadSuccessCallback: onUpload // Callback para manejar el éxito de la carga
                });

                // Ocultar el enlace "Editar imagen" después de inicializar el editor
                editLink.style.display = 'none';
                
                return false;

            });
        
        });
      }


}