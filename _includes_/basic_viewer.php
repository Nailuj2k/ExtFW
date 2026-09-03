<?php
/**
 * SimpleImageViewer - Un visor de imágenes básico que no depende de bibliotecas externas
 * Versión: 1.0.0
 * Fecha: 2025-04-28
 * 
 * Este visor simple reemplaza la funcionalidad básica de un lightbox
 * sin depender de bibliotecas externas que puedan causar errores.
 */

// Evitamos la ejecución directa
if (!defined('SCRIPT_DIR_INCLUDES')) {
    die('No direct script access allowed');
}

// Incluimos el CSS inline para el visor
function basic_viewer_css() {
    echo '<style type="text/css">
    #simple-image-viewer {
        display: none;
        position: fixed;
        z-index: 999999;
        padding-top: 30px;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.9);
    }
    
    #simple-image-viewer .viewer-content {
        margin: auto;
        display: block;
        max-width: 90%;
        max-height: 80%;
    }
    
    #simple-image-viewer .viewer-caption {
        margin: auto;
        display: block;
        width: 80%;
        max-width: 700px;
        text-align: center;
        color: #ccc;
        padding: 10px 0;
        height: 50px;
    }
    
    #simple-image-viewer .viewer-content, 
    #simple-image-viewer .viewer-caption {
        animation-name: zoom;
        animation-duration: 0.3s;
    }
    
    @keyframes zoom {
        from {transform:scale(0.1)} 
        to {transform:scale(1)}
    }
    
    #simple-image-viewer .viewer-close {
        position: absolute;
        top: 15px;
        right: 35px;
        color: #f1f1f1;
        font-size: 40px;
        font-weight: bold;
        transition: 0.3s;
    }
    
    #simple-image-viewer .viewer-close:hover,
    #simple-image-viewer .viewer-close:focus {
        color: #bbb;
        text-decoration: none;
        cursor: pointer;
    }
    
    #simple-image-viewer .viewer-nav {
        color: white;
        font-size: 60px;
        font-weight: bold;
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        user-select: none;
        -webkit-user-select: none;
    }
    
    #simple-image-viewer .viewer-prev {
        left: 15px;
    }
    
    #simple-image-viewer .viewer-next {
        right: 15px;
    }
    </style>';
}

// Incluimos el JavaScript inline para el visor
function basic_viewer_js() {
    echo '<script type="text/javascript">
    document.addEventListener("DOMContentLoaded", function() {
        // Crear el contenedor del visor si no existe
        if (!document.getElementById("simple-image-viewer")) {
            const viewer = document.createElement("div");
            viewer.id = "simple-image-viewer";
            viewer.innerHTML = `
                <span class="viewer-close">&times;</span>
                <img class="viewer-content" id="viewer-img">
                <div class="viewer-caption" id="viewer-caption"></div>
                <a class="viewer-nav viewer-prev">&#10094;</a>
                <a class="viewer-nav viewer-next">&#10095;</a>
            `;
            document.body.appendChild(viewer);
            
            // Cerrar el visor al hacer clic en el botón de cierre
            document.querySelector(".viewer-close").addEventListener("click", closeViewer);
            
            // Cerrar el visor al hacer clic fuera de la imagen
            viewer.addEventListener("click", function(e) {
                if (e.target === this) {
                    closeViewer();
                }
            });
            
            // Navegación con teclas
            document.addEventListener("keydown", function(e) {
                if (viewer.style.display === "block") {
                    if (e.key === "Escape") {
                        closeViewer();
                    } else if (e.key === "ArrowLeft") {
                        showPrevImage();
                    } else if (e.key === "ArrowRight") {
                        showNextImage();
                    }
                }
            });
            
            // Navegación con botones
            document.querySelector(".viewer-prev").addEventListener("click", showPrevImage);
            document.querySelector(".viewer-next").addEventListener("click", showNextImage);
        }
        
        // Buscar todas las imágenes habilitadas para el visor
        const images = document.querySelectorAll(".simple-viewer");
        let currentIndex = 0;
        let imagesList = [];
        
        // Configurar el evento de clic para cada imagen
        images.forEach((img, index) => {
            img.style.cursor = "pointer";
            img.addEventListener("click", function() {
                openViewer(index);
            });
            imagesList.push({
                src: img.getAttribute("data-full") || img.src,
                alt: img.alt || ""
            });
        });
        
        // Abrir el visor con la imagen seleccionada
        function openViewer(index) {
            const viewer = document.getElementById("simple-image-viewer");
            const img = document.getElementById("viewer-img");
            const caption = document.getElementById("viewer-caption");
            
            if (imagesList.length > 0) {
                currentIndex = index;
                img.src = imagesList[currentIndex].src;
                caption.innerHTML = imagesList[currentIndex].alt;
                viewer.style.display = "block";
                
                // Mostrar/ocultar botones de navegación
                toggleNavButtons();
            }
        }
        
        // Cerrar el visor
        function closeViewer() {
            document.getElementById("simple-image-viewer").style.display = "none";
        }
        
        // Mostrar imagen anterior
        function showPrevImage() {
            if (currentIndex > 0) {
                currentIndex--;
                updateViewerImage();
            }
        }
        
        // Mostrar siguiente imagen
        function showNextImage() {
            if (currentIndex < imagesList.length - 1) {
                currentIndex++;
                updateViewerImage();
            }
        }
        
        // Actualizar la imagen mostrada
        function updateViewerImage() {
            const img = document.getElementById("viewer-img");
            const caption = document.getElementById("viewer-caption");
            
            img.src = imagesList[currentIndex].src;
            caption.innerHTML = imagesList[currentIndex].alt;
            
            // Mostrar/ocultar botones de navegación
            toggleNavButtons();
        }
        
        // Mostrar/ocultar botones de navegación
        function toggleNavButtons() {
            const prevBtn = document.querySelector(".viewer-prev");
            const nextBtn = document.querySelector(".viewer-next");
            
            prevBtn.style.display = currentIndex > 0 ? "block" : "none";
            nextBtn.style.display = currentIndex < imagesList.length - 1 ? "block" : "none";
        }
    });
    </script>';
}

// Función para convertir elementos con clase swipebox a nuestro visor
function convert_swipebox_to_simple_viewer() {
    echo '<script type="text/javascript">
    document.addEventListener("DOMContentLoaded", function() {
        // Convertir elementos swipebox a simple-viewer
        document.querySelectorAll(".swipebox").forEach(function(el) {
            el.classList.add("simple-viewer");
            el.classList.remove("swipebox");
            
            // Si tiene un atributo href, usarlo como la imagen a tamaño completo
            if (el.hasAttribute("href")) {
                el.setAttribute("data-full", el.getAttribute("href"));
            }
        });
    });
    </script>';
}

// Incluir automáticamente el CSS y JavaScript necesarios
basic_viewer_css();
basic_viewer_js();
convert_swipebox_to_simple_viewer();

/**
 * Función helper para generar una galería de imágenes
 * 
 * @param array $images Array de imágenes [['url' => '...', 'thumb' => '...', 'title' => '...'], ...]
 * @param array $options Opciones adicionales
 * @return string HTML de la galería
 */
function basic_gallery($images, $options = []) {
    $html = '<div class="basic-gallery">';
    
    foreach ($images as $image) {
        $url = isset($image['url']) ? $image['url'] : '';
        $thumb = isset($image['thumb']) ? $image['thumb'] : $url;
        $title = isset($image['title']) ? $image['title'] : '';
        
        $html .= '<a href="' . htmlspecialchars($url) . '" class="basic-viewer" title="' . htmlspecialchars($title) . '">';
        $html .= '<img src="' . htmlspecialchars($thumb) . '" alt="' . htmlspecialchars($title) . '">';
        $html .= '</a>';
    }
    
    $html .= '</div>';
    return $html;
}
?>