/**
 * ImageGallery - Una librería simple para crear galerías de imágenes y contenido
 * Version: 1.0.1
 * 
 * Permite crear galerías de imágenes, videos de YouTube y otros contenidos
 * Funciona con wQuery Dialog`
 */

// Namespace para ImageGallery
const ImageGallery = (function() {
    "use strict";
    
    // Constructor de la galería
    function Gallery(images, options) {
        this.images = Array.isArray(images) ? images : [];
        this.currentIndex = 0;
        this.showFrom = 'left'; // Dirección de la animación
        // Opciones por defecto
        this.options = {
            fullscreen: false,       // Mostrar en pantalla completa
            defaultType: 'default',          // Tipo de contenido (image, video, etc.)
            footerFixed: true,      // Pie de página fijo
            //showTitle: true,        // Mostrar título
            showCounter: true,      // Mostrar contador de imágenes
            class: '',               // Clases CSS adicionales
            slideAnimation: true    // Habilitar animación de deslizamiento
        };
        
        // Fusionar opciones proporcionadas con las predeterminadas
        if (options && typeof options === 'object') {
            for (let key in options) {
                if (options.hasOwnProperty(key)) {
                    this.options[key] = options[key];
                }
            }
        }


    }
   
    // Métodos del prototipo
    Gallery.prototype = {
        // Obtener imagen actual
        getCurrentImage: function() {
            return this.images[this.currentIndex];
        },

        // Obtener siguiente imagen
        nextImage: function() {
            this.showFrom = 'left'; 
            this.currentIndex = (this.currentIndex + 1) % this.images.length;
            return this.getCurrentImage();
        },
        
        // Obtener imagen anterior
        prevImage: function() {
            this.showFrom = 'right'; 
            this.currentIndex = (this.currentIndex - 1 + this.images.length) % this.images.length;
            return this.getCurrentImage();
        },
        
        // Mostrar galería comenzando por una imagen específica o la actual
        show: function(startImageIndex/*, direction*/) {
            // Si se proporciona un índice válido, establecerlo como actual
            if (typeof startImageIndex === 'number' && startImageIndex >= 0 && startImageIndex < this.images.length) {
                this.currentIndex = startImageIndex;
            }
            
            // Referencia al objeto gallery para usar en callbacks
            const gallery = this;
            
            // Obtener el elemento actual (ahora es un objeto)
            const currentItem = this.getCurrentImage();
            
            // Preparar el título con contador si está habilitado
            let title = '';
            //if (this.options.showTitle) {
                // Usar el título del elemento actual si está disponible
                if (currentItem && currentItem.title) {
                    title = currentItem.title;
                }
                
                // Añadir contador si está habilitado
                if (this.options.showCounter) {
                    title += (title ? ' - ' : '') + 'Elemento ' + (this.currentIndex + 1) + ' de ' + this.images.length;
                }
            //}
            
            // Mostrar el diálogo con el contenido actual
            // Usar la función global del plugin sin jQuery directamente
            $("body").dialog({
                title: title,
                fullscreen: this.options.fullscreen,
                // Usar el tipo del elemento actual o el tipo por defecto
                type: currentItem && currentItem.type || this.options.defaultType,
                footerFixed: this.options.footerFixed,
                class: this.options.class,
                // Usar el contenido del elemento actual
                content: currentItem &&currentItem.content,
                showFrom: this.showFrom, // Pasar la dirección de la animación
                slideAnimation: this.options.slideAnimation,
                buttons: [
                    { 
                        text: '',  // Símbolo flecha izquierda
                        class: 'btn-prev', 
                        action: function(event, overlay) { 

                            let dc = document.querySelector('.wq-dialog-content');
                            dc.style.transition = "left 1s ease, right 1s ease, top 1s ease, bottom 1s ease";
                            dc.style.left = '-100%';
                
                            setTimeout(() => document.body.removeChild(overlay) , 500);
                            gallery.prevImage();
                            gallery.show(null/*, 'prev'*/);
                        } 
                    },
                    { 
                        text: '',  // Símbolo flecha derecha
                        class: 'btn-next', 
                        action: function(event, overlay) { 

                            let dc = document.querySelector('.wq-dialog-content');
                            dc.style.transition = "left 1s ease, right 1s ease, top 1s ease, bottom 1s ease";
                            dc.style.left = '100%';

                            setTimeout(() => document.body.removeChild(overlay) , 500);
                            gallery.nextImage();
                            gallery.show(null/*, 'next'*/);
                        } 
                    },
                    { 
                        text: '', // Símbolo X
                        class: 'btn-close', 
                        action: function(event, overlay) { 
                            document.body.removeChild(overlay); 
                        } 
                    }
                ]
            });
        }
    };    
  
    // Función factory para crear nuevas instancias de galería
    return {
        // Crear una nueva galería y mostrarla
        create: function(startImage, images, options) {
            // Si el primer parámetro es un array, asumimos que son las imágenes
            // y no se ha proporcionado una imagen de inicio específica
            if (Array.isArray(startImage)) {
                options = images;
                images = startImage;
                startImage = null;
            }
            
            // Convertir imágenes simples (strings) a objetos con propiedades
            const processedImages = Array.isArray(images) ? images.map(item => {
                // Si ya es un objeto con las propiedades necesarias, devolverlo tal cual
                if (typeof item === 'object' && item !== null && 'content' in item) {
                    return item;
                }
                // Si es una string, convertirla a objeto
                if (typeof item === 'string') {
                    let type = 'default';
                    // Determinar el tipo basado en la URL
                    if (item.match(/\.(jpg|jpeg|png|gif|webp)$/i) || item.includes(".jpg?") || item.includes(".jpeg?") || item.includes(".png?") || item.includes(".gif?") || item.includes(".webp?")) {
                        type = 'image';
                    } else if (item.match(/\.(pdf)$/i) || item.endsWith('/pdf')) {
                        type = 'pdf';
                    } else if (item.match(/\.(txt)$/i) || item.endsWith('/txt')) {
                        type = 'txt';
                    } else if (item.indexOf('youtube.com') !== -1 || item.indexOf('youtu.be') !== -1) {
                        type = 'youtube';
                    } else if (item.endsWith('/html')) {
                        type = 'html';
                    }
                    
                    return {
                        type: type,
                        content: item,
                        title: ''
                    };
                }
                // En caso de no ser ni objeto ni string, devolver un objeto con contenido vacío
                return {
                    type: 'default',
                    content: '',
                    title: ''
                };
            }) : [];
            
            const gallery = new Gallery(processedImages, options);
            
            if (processedImages && processedImages.length > 0) {
                let startIndex = 0;
                
                // Si se proporciona una imagen específica como string, buscarla en el array
                if (startImage && typeof startImage === 'string') {
                    for (let i = 0; i < processedImages.length; i++) {
                        if (processedImages[i].content === startImage) {
                            startIndex = i;
                            break;
                        }
                    }
                } else if (typeof startImage === 'number') {
                    // Si es un número, usarlo directamente como índice
                    startIndex = startImage;
                }
                
                gallery.show(startIndex);
            }
            
            return gallery;
        },
        
        // Crear una galería sin mostrarla inmediatamente
        createGallery: function(images, options) {
            // Convertir imágenes simples (strings) a objetos si es necesario
            const processedImages = Array.isArray(images) ? images.map(item => {
                // Mismo procesamiento que en create
                if (typeof item === 'object' && item !== null && 'content' in item) {
                    return item;
                }
                if (typeof item === 'string') {
                    let type = 'default';
                    if (item.match(/\.(jpg|jpeg|png|gif|webp)$/i) || item.includes(".jpg?") || item.includes(".jpeg?") || item.includes(".png?") || item.includes(".gif?") || item.includes(".webp?")) {
                        type = 'image';
                    } else if (item.match(/\.(pdf)$/i) || item.endsWith('/pdf')) {
                        type = 'pdf';
                    } else if (item.match(/\.(txt)$/i) || item.endsWith('/txt')) {
                        type = 'txt';
                    } else if (item.indexOf('youtube.com') !== -1 || item.indexOf('youtu.be') !== -1) {
                        type = 'youtube';
                    } else if (item.endsWith('/html')) {
                        type = 'html';
                    }
                    
                    return {
                        type: type,
                        content: item,
                        title: ''
                    };
                }
                return {
                    type: 'default',
                    content: '',
                    title: ''
                };
            }) : [];
            
            return new Gallery(processedImages, options);
        }
    };
    
})();

function getImageIndexInImagesArray(e, images){
    let index = 0;
    for (let i = 0; i < images.length; i++) {
        if (images[i].content === e) {
            index = i;
            break;
        }
    }
    return index;
}

function galleryFromSelector(selector) {

    const elements = document.querySelectorAll(selector);
    
    // Convertir los elementos seleccionados en un array de objetos con type, content y title
    var images = Array.from(elements).map(element => {
        const href = element.getAttribute('href') || '';
        const title = element.getAttribute('title') || element.getAttribute('alt') || element.getAttribute('data-title') || '';
        
        // Determinar el tipo de contenido basado en la URL
        let type = 'image';
        if (href.match(/\.(jpg|jpeg|png|gif|webp)$/i)) {
            type = 'image';
        } else if (href.match(/\.(pdf)$/i) || href.endsWith('/pdf')) {
            type = 'pdf';
        } else if (href.match(/\.(txt)$/i) || href.endsWith('/txt')) {
            type = 'txt';
        } else if (href.indexOf('youtube.com') !== -1 || href.indexOf('youtu.be') !== -1) {
            type = 'youtube';
        } else if (href.endsWith('/html')) {
            type = 'html';
        }
        
        return {
            type: type,
            content: href,
            title: title
        };
    });
    
    // Crear galería con opciones
    const gallery = ImageGallery.createGallery(images, {
        //showTitle: true,
        showCounter: true,
        defaultType: 'image',  // Tipo por defecto
        fullscreen: false,
        slideAnimation: true
    });

    // ANTES  (jQuery)
    /*
    $('body').on('click', selector, function(e) {
        e.preventDefault();
        let imageIndex = getImageIndexInImagesArray($(this).attr('href'), images);
        gallery.show(imageIndex);
    });
    */
    
    // AHORA (vanilla JS)
    // Manejar clicks en los elementos
    document.addEventListener('click', function(e) {
        // Verificar si el elemento clickeado coincide con el selector
        if (e.target.matches(selector) || e.target.closest(selector)) {
            const element = e.target.matches(selector) ? e.target : e.target.closest(selector);
            e.preventDefault();
            let imageIndex = getImageIndexInImagesArray(element.getAttribute('href'), images);
            gallery.show(imageIndex);
        }
    });
    
    return images;
}



/*
// Ejemplos de uso:

// 1. Usando la nueva API con opciones:
ImageGallery.create([
    '/media/page/files/5/angel_nieto_1977_venezuela.jpg',
    '/media/page/files/5/249.jpg'
], {
    fullscreen: true,
    showTitle: false
});

// 2. Comenzando desde una imagen específica:
ImageGallery.create('/media/page/files/5/249.jpg', [
    '/media/page/files/5/angel_nieto_1977_venezuela.jpg',
    '/media/page/files/5/249.jpg'
]);

// 3. Crea la galería pero no la muestra inmediatamente
const gallery = ImageGallery.createGallery([
    '/media/page/files/5/angel_nieto_1977_venezuela.jpg',
    '/media/page/files/5/249.jpg'
], {
    fullscreen: false
});
// Más tarde...
gallery.show();
*/


