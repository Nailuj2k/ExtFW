    // Slider Logic
    const originalSlides = document.querySelectorAll('.hero-slide');
    const indicators = document.querySelectorAll('.indicator');
    const track = document.querySelector('.hero-track');
    
    if (originalSlides.length > 1 && track) {
        // Clonar la primera diapositiva pero SIN los vídeos para evitar recargas o desincronizaciones
        const firstClone = originalSlides[0].cloneNode(true);
        
        // Si el clon tiene un vídeo, lo quitamos y dejamos sólo el poster (imagen)
        const cloneVideo = firstClone.querySelector('video');
        if (cloneVideo) {
            const poster = cloneVideo.getAttribute('poster');
            const img = document.createElement('img');
            img.src = poster || '';
            img.className = cloneVideo.className;
            cloneVideo.parentNode.replaceChild(img, cloneVideo);
        }
        
        // Si hay un iframe (youtube), lo quitamos para que no cargue dos veces
        const cloneIframe = firstClone.querySelector('iframe');
        if (cloneIframe) {
            const div = document.createElement('div');
            div.className = cloneIframe.className;
            div.style.backgroundColor = '#000';
            cloneIframe.parentNode.replaceChild(div, cloneIframe);
        }

        track.appendChild(firstClone);

        let currentSlide = 0;
        const slideInterval = 5000; // 5 seconds per slide
        let timer;
        const totalSlides = originalSlides.length;
        let isTransitioning = false;

        function updateIndicators(index) {
            indicators.forEach(ind => ind.classList.remove('active'));
            const activeIndex = index % totalSlides;
            if (indicators[activeIndex]) indicators[activeIndex].classList.add('active');
        }

        function restartFirstVideo() {
            const firstVideo = originalSlides[0].querySelector('video');
            if (firstVideo) {
                firstVideo.currentTime = 0;
                firstVideo.play().catch(()=>{});
            }
        }

        function goToSlide(index) {
            if (isTransitioning) return;
            currentSlide = index;
            track.style.transition = 'transform 0.8s cubic-bezier(0.25, 1, 0.5, 1)';
            track.style.transform = `translateX(-${currentSlide * 100}%)`;
            updateIndicators(currentSlide);
            if (currentSlide === 0) {
                restartFirstVideo();
            }
        }

        function nextSlide() {
            if (isTransitioning) return;
            isTransitioning = true;
            currentSlide++;
            track.style.transition = 'transform 0.8s cubic-bezier(0.25, 1, 0.5, 1)';
            track.style.transform = `translateX(-${currentSlide * 100}%)`;
            updateIndicators(currentSlide);
        }

        track.addEventListener('transitionend', () => {
            isTransitioning = false;
            // Si llegamos al clon
            if (currentSlide >= totalSlides) {
                track.style.transition = 'none';
                currentSlide = 0;
                track.style.transform = `translateX(0%)`;
                void track.offsetHeight; // Forzar reflow
                restartFirstVideo(); // Reiniciar el vídeo original para que empiece de 0 sin parpadear
            }
        });

        function startSlider() {
            document.getElementById('slider').style.display = 'block';
            timer = setInterval(nextSlide, slideInterval);
        }

        function resetSlider() {
            clearInterval(timer);
            startSlider();
        }

        // Indicator click events
        indicators.forEach((indicator, index) => {
            indicator.addEventListener('click', () => {
                if (currentSlide === totalSlides - 1 && index === 0) {
                    nextSlide();
                } else {
                    goToSlide(index);
                }
                resetSlider();
            });
        });

         // Arrow click events
         const leftArrow = document.getElementById('slide-arrow-left');
         const rightArrow = document.getElementById('slide-arrow-right');
         if (leftArrow) {
             leftArrow.addEventListener('click', () => {
                 if (isTransitioning) return;
                 isTransitioning = true;
                 currentSlide--;
                 if (currentSlide < 0) {
                     track.style.transition = 'none';
                     currentSlide = totalSlides - 1;
                     track.style.transform = `translateX(-${currentSlide * 100}%)`;
                     void track.offsetHeight; // Forzar reflow
                     isTransitioning = false;
                 } else {
                     track.style.transition = 'transform 0.8s cubic-bezier(0.25, 1, 0.5, 1)';
                     track.style.transform = `translateX(-${currentSlide * 100}%)`;
                 }
                 updateIndicators(currentSlide);
                 resetSlider();
             });
         }

         if (rightArrow) {
             rightArrow.addEventListener('click', () => {
                 nextSlide();
                 resetSlider();
             });
         }  

        // Start auto-play
        startSlider();
    }