(function () {
    'use strict';

    function getImages(gallery) {
        try {
            var images = JSON.parse(gallery.getAttribute('data-images') || '[]');
            return Array.isArray(images) ? images : [];
        } catch (error) {
            return [];
        }
    }

    function updateGallery(gallery, index) {
        var images = getImages(gallery);
        var track = gallery.querySelector('.shop-image-track');
        var previous = gallery.querySelector('.shop-image-prev');
        var next = gallery.querySelector('.shop-image-next');

        // Con una sola imagen (o sin data-images valido) no hay nada que navegar.
        if (previous) previous.hidden = images.length < 2;
        if (next) next.hidden = images.length < 2;

        if (!track || images.length < 1) return;

        index = ((index % images.length) + images.length) % images.length;
        gallery.setAttribute('data-image-index', index);

        var galleryImages = track.querySelectorAll('img');
        track.style.width = '100%';
        galleryImages.forEach(function (image, imageIndex) {
            image.classList.toggle('item_thumb', imageIndex === index);
            image.style.flex = '0 0 100%';
        });
        track.style.transform = 'translateX(-' + (index * 100) + '%)';
    }

    function buildGallery(gallery) {
        var images = getImages(gallery);
        var track = gallery.querySelector('.shop-image-track');
        var firstImage = track && track.querySelector('img');
        if (!track || !firstImage || images.length < 1) return;

        if (track.querySelectorAll('img').length < images.length) {
            var alt = firstImage.getAttribute('alt') || '';
            images.slice(1).forEach(function (src) {
                var image = document.createElement('img');
                image.className = 'shop-gallery-image thumb';
                image.src = src;
                image.alt = alt;
                image.draggable = false;
                track.appendChild(image);
            });
        }
    }

    function initialiseGallery(gallery) {
        buildGallery(gallery);
        updateGallery(gallery, 0);
    }

    function initialiseGalleries(root) {
        (root || document).querySelectorAll('.shop-image-gallery').forEach(initialiseGallery);
    }

    // El grid de CLI_PRODUCTS se repinta por AJAX (paginado, filtros, busqueda).
    // Sin esto las cards nuevas nunca pasaban por updateGallery y sus flechas
    // quedaban visibles aunque el producto tuviera una sola imagen.
    function watchForGalleries() {
        if (!window.MutationObserver || !document.body) return;

        new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                Array.prototype.forEach.call(mutation.addedNodes, function (node) {
                    if (node.nodeType !== 1) return;
                    if (node.classList.contains('shop-image-gallery')) initialiseGallery(node);
                    else initialiseGalleries(node);
                });
            });
        }).observe(document.body, { childList: true, subtree: true });
    }

    document.addEventListener('click', function (event) {
        var button = event.target.closest('.shop-image-prev, .shop-image-next');
        if (!button) return;

        event.preventDefault();
        event.stopPropagation();

        var gallery = button.closest('.shop-image-gallery');
        if (!gallery) return;

        // CLI_PRODUCTS may be injected after DOMContentLoaded by the table
        // loader, so initialise this gallery lazily when it is first used.
        buildGallery(gallery);

        var current = parseInt(gallery.getAttribute('data-image-index') || '0', 10) || 0;
        updateGallery(gallery, current + (button.classList.contains('shop-image-next') ? 1 : -1));
    });

    function start() {
        initialiseGalleries();
        watchForGalleries();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();
