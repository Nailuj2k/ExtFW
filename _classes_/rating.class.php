<?php

class Rating extends DbConnection{

    //private $module      = 1;   // 1=PAGE 2= NEWS, 3 = BLOG, etc.
    public static $module      = 1;   // 1=PAGE 2= NEWS, 3 = BLOG, etc.
    public static $url         = 'comments/ajax';
    public static $theme = 'default';
    public static $shape = 'star';
    public static $size = [40,40];

    private $post        = 0;

    private $containerId;
    private $initialRating = 0;
    private $maxStars = null;  // Se inicializará desde Karma::MAX_STARS
    private $readonly = false;
    
    private $ajaxurl = 'news/ajax';
    private $debug = false;
   // private $width = 40;
   // private $height = 40;
    private $showZeroOption = true;
    //private $themePath = './themes/';
    
    private $totalVotes = 0;
    //private $rating = 0;

    private static $cssIncluded = false;
    private static $jsIncluded = false;
    //private static $loadedThemes = [];
    //private static $instanceCount = 0;
    
    public function __construct($containerId, $post_id = 0) {

        $this->post = $post_id;
        $this->containerId = $containerId;
        $this->ajaxurl = self::$url;
        
        // Usar $maxStars de Karma si está disponible (ahora es propiedad estática, no constante)
        $this->maxStars = class_exists('Karma') ? Karma::$maxStars : 5;
    }
    
    // Setters para las propiedades públicas
    public function __set($property, $value) {
        if (property_exists($this, $property)) {
            $this->$property = $value;
        }
    }
    
    // Getters para las propiedades públicas
    public function __get($property) {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
        return null;
    }
    /*
    public function setInitialRating($rating) {
        $this->initialRating = $rating;
        return $this;
    }
    */
    
    public function setMaxStars($stars) {
        $this->maxStars = $stars;
        return $this;
    }
    
    public function setReadonly($readonly = true) {
        $this->readonly = $readonly;
        return $this;
    }
    /*
    public function setAjaxUrl($url) {
        $this->ajaxurl = self::$url;
        return $this;
    }
    */
    public function setDebug($debug = true) {
        $this->debug = $debug;
        return $this;
    }
    /*
    public function setSize($width, $height = null) {
        $this->width = $width;
        $this->height = $height ?: $width;
        return $this;
    }
    */
    public function setShowZeroOption($show = true) {
        $this->showZeroOption = $show;
        return $this;
    }
    /*
    public function setTheme($theme) {
        $this->theme = $theme;
        return $this;
    } 
    */
    /*   
    public function setShape($shape) {
        $this->shape = $shape;
        return $this;
    }
    */
    
    /*
    public function setThemePath($path) {
        $this->themePath = rtrim($path, '/') . '/';
        return $this;
    }
    */
    public function setTotalVotes($votes) {
        $this->totalVotes = $votes;
        return $this;
    }
    
    private function renderBaseCSS() {
        if (self::$cssIncluded) return '';
        self::$cssIncluded = true;
        
        ?>
        <style>
            /* CSS Base con SVG - Solo lo esencial */
            .star-rating {
                /* Variables por defecto - theme básico */
                --star-empty-fill: #ddd;
                --star-fill-color: #ffd700;
                --star-hover-color: #ffed4e;
                --star-stroke-width: 0;
                --star-stroke-color: transparent;
                --zero-inactive-color: #ccc;
                --zero-active-color: #ff4444;
                --zero-line-color: white;
                
                display:inline-block;
                font-size: 0;
                margin: 0px 0;
                /*padding-left: 100px;*/
                /*text-align: right;*/
            }

            .star-rating.shape-heart  {
                 --star-fill-color: #fe1a78;
                 --star-hover-color: #ff0990ff;
            }

            .star-rating.shape-diamond  {
                 --star-fill-color: #98e0f7ff;
                 --star-hover-color: #67dafdff;
            }
            
            .star-rating.shape-thumbs  {
                 --star-fill-color: #c63535;
                 --star-hover-color: #e75050ff;
            }
            
            .star, .zero-option {
                position: relative;
                display: inline-block;
                cursor: pointer;
                margin-right: 0px;
            }
            
            .zero-option {
                /*margin-right: 8px;*/
                transition: all 0.2s ease;
            }
            
            .zero-svg {
                width: 100%;
                height: 100%;
                display: block;
            }
            
            .zero-circle {
                fill: var(--zero-inactive-color);
                transition: fill 0.2s ease;
            }
            
            .zero-line {
                stroke: var(--zero-line-color);
                stroke-width: 4;
                stroke-linecap: round;
            }
            
            .zero-option:hover .zero-circle {
                fill: var(--zero-active-color);
            }
            
            .zero-option.active .zero-circle {
                fill: var(--zero-active-color);
            }

            .star-svg {
                width: 100%;
                height: 100%;
                display: block;
            }

            .star-shape-bg {
                fill: var(--star-empty-fill);
                stroke: var(--star-stroke-color);
                stroke-width: var(--star-stroke-width);
                transition: all 0.2s ease;
            }

            .star-shape-fill {
                fill: var(--star-fill-color);
                stroke: var(--star-stroke-color);
                stroke-width: var(--star-stroke-width);
                transition: all 0.2s ease;
            }

            .star:hover .star-shape-bg {
                fill: var(--star-hover-color);
            }

            .star.hover .star-shape-fill {
                fill: var(--star-hover-color);
            }

            .star .star-fill {
                position: absolute;
                top: 0;
                left: 0;
                width: 0%;
                height: 100%;
                overflow: hidden;
                transition: width 0.2s ease;
            }

            .star-rating.readonly .star,
            .star-rating.readonly .zero-option {
                pointer-events: none;
                cursor: default;
            }
            
            .rating-loading {
                opacity: 0.6;
                pointer-events: none;
            }

            /* Tooltip para mostrar puntuación y votos */
            .star-rating {
                position: relative;
            }

            .rating-tooltip {
                position: absolute;
                bottom: 100%;
                left: 50%;
                transform: translateX(-50%);
                background: rgba(0, 0, 0, 0.9);
                color: white;
                padding: 8px 12px;
                border-radius: 6px;
                font-size: 12px;
                font-family: Arial, sans-serif;
                white-space: nowrap;
                opacity: 0;
                visibility: hidden;
                transition: all 0.2s ease;
                pointer-events: none;
                z-index: 1000;
                margin-bottom: 5px;
            }

            .rating-tooltip::after {
                content: '';
                position: absolute;
                top: 100%;
                left: 50%;
                transform: translateX(-50%);
                border: 5px solid transparent;
                border-top-color: rgba(0, 0, 0, 0.9);
            }

            .star-rating:hover .rating-tooltip {
                opacity: 1;
                visibility: visible;
            }

            .rating-tooltip.show {
                opacity: 1;
                visibility: visible;
            }
        </style>
        <?php
    }
    
    private function NOloadThemeCSS($theme) {
        if ($theme === 'default' || in_array($theme, self::$loadedThemes)) {
            return '';
        }
        /*
        $themeFile = $this->themePath . $theme . '.css';
        
        if (file_exists($themeFile)) {
            self::$loadedThemes[] = $theme;
            return '<link rel="stylesheet" href="' . $themeFile . '">';
        }
        */
        return '';
    }
    
    private function renderJS() {
        if (self::$jsIncluded) return '';  // Descomentar esta línea
        self::$jsIncluded = true;
        
        ?>
        <script>
            // Translated strings for rating JS


            class StarRating {
                constructor(container, options = {}) {
                    this.container = typeof container === "string" ? document.getElementById(container) : container;
                    this.maxStars = options.maxStars || 5;
                    this.initialRating = options.initialRating || 0;
                    this.readonly = options.readonly || false;
                    this.onRate = options.onRate || null;
                    this.debug = options.debug || false;
                    this.ajaxurl = options.ajaxurl || "";
                    this.width = options.width || 40;
                    this.height = options.height || 40;
                    this.showZeroOption = options.showZeroOption !== false;
                    this.theme = options.theme || "default";
                    this.shape = options.shape || "star";
                    this.totalVotes = options.totalVotes || 0;
                    
                    this.currentRating =  this.formatRating(this.initialRating);
                    this.hoverRating = -1;
                    
                    this.init();
                }
                
                formatRating(rating) {
                    // Redondear a 0.5 y formatear: quitar decimales si es .0
                    let formattedRating = rating;
                    // Redondear a múltiplos de 0.5
                    formattedRating = Math.round(formattedRating * 2) / 2;
                    // Si es número entero, mostrarlo sin decimales
                    formattedRating = formattedRating % 1 === 0 ? Math.round(formattedRating) : formattedRating;
                    return formattedRating;
                }

                getShapeForTheme(theme) {
                    const themeShapes = {
                        heart: "heart",
                        diamond: "diamond", 
                        thumbs: "thumbs",
                        btc: "btc"              
                    };
                    return themeShapes[theme] || this.shape;
                }
                
                getSVGPath(shape) {
                    const paths = {
                        star: "M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z",
                        heart: "M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z",
                        diamond: "M6,2L18,2L22,10L12,22L2,10L6,2Z",
                        thumbs: "M5,9V21H1V9H5M9,21A2,2 0 0,1 7,19V9C7,8.45 7.22,7.95 7.59,7.59L14.17,1L15.23,2.06C15.5,2.33 15.67,2.7 15.67,3.11L15.64,3.43L14.69,8H21C22.11,8 23,8.9 23,10V12C23,12.26 22.95,12.5 22.86,12.73L19.84,19.78C19.54,20.5 18.83,21 18,21H9M9,19H18.03L21,12V10H12.21L13.34,4.68L9,9.03V19Z",
                        btc:'M12,2 C6.477,2 2,6.477 2,12 C2,17.523 6.477,22 12,22 C17.523,22 22,17.523 22,12 C22,6.477 17.523,2 12,2 Z M17.29,10.29 C17.53,8.69 16.31,7.84 14.65,7.26 L15.19,5.1 L13.87,4.77 L13.35,6.88 C13,6.79 12.65,6.71 12.3,6.63 L12.83,4.51 L11.51,4.18 L10.97,6.34 C10.68,6.27 10.4,6.21 10.13,6.14 L10.13,6.13 L8.31,5.68 L7.96,7.09 C7.96,7.09 8.94,7.31 8.92,7.33 C9.45,7.46 9.55,7.82 9.53,8.1 L8.92,10.56 C8.96,10.57 9,10.58 9.06,10.6 C9.02,10.59 8.97,10.58 8.92,10.57 L8.06,14.02 C7.99,14.18 7.83,14.42 7.46,14.33 C7.47,14.35 6.5,14.09 6.5,14.09 L5.85,15.6 L7.56,16.03 C7.88,16.11 8.19,16.19 8.5,16.27 L7.95,18.46 L9.27,18.79 L9.81,16.63 C10.17,16.73 10.52,16.82 10.86,16.9 L10.32,19.05 L11.64,19.38 L12.19,17.2 C14.44,17.62 16.12,17.45 16.83,15.42 C17.4,13.79 16.8,12.84 15.62,12.23 C16.48,12.03 17.13,11.47 17.3,10.3 L17.3,10.3 Z M14.28,14.51 C13.87,16.14 11.12,15.26 10.23,15.04 L10.95,12.14 C11.84,12.36 14.98,12.8 14.28,14.51 Z M14.69,10.27 C14.32,11.76 12.03,11 11.28,10.82 L11.94,8.19 C12.68,8.38 15.08,8.72 14.69,10.27 L14.69,10.27 Z'
                    };
                    return paths[shape] || paths.star;
                }
                
                createZeroSVG() {
                    const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
                    svg.setAttribute("class", "zero-svg");
                    svg.setAttribute("viewBox", "0 0 24 24");      //0 0 24 24 2 2 20 20 "0 0 4091.27 4091.73"
                    
                    // Círculo
                    const circle = document.createElementNS("http://www.w3.org/2000/svg", "circle");
                    circle.setAttribute("class", "zero-circle");
                    circle.setAttribute("cx", "12");
                    circle.setAttribute("cy", "12");
                    circle.setAttribute("r", "10");
                    svg.appendChild(circle);
                    
                    // Línea horizontal (prohibido)
                    const line = document.createElementNS("http://www.w3.org/2000/svg", "line");
                    line.setAttribute("class", "zero-line");
                    line.setAttribute("x1", "6");
                    line.setAttribute("y1", "12");
                    line.setAttribute("x2", "18");
                    line.setAttribute("y2", "12");
                    svg.appendChild(line);
                    
                    return svg;
                }

                createTooltip() {
                    const tooltip = document.createElement("div");
                    tooltip.className = "rating-tooltip";
                    this.updateTooltipText(tooltip);
                    return tooltip;
                }

                updateTooltipText(tooltip) {
                    if (this.currentRating === 0) {
                        tooltip.textContent = str_no_rating;
                    } else {
                        const votesText = this.totalVotes === 1 ? str_vote : str_votes;
                        tooltip.textContent = `${this.currentRating} ${str_stars} (${this.totalVotes} ${votesText})`;
                    }
                }
                
                init() {
                    this.container.innerHTML = "";
                    this.container.className = "star-rating" + (this.readonly ? " readonly" : "") + 
                                            (this.theme !== "default" ? " theme-" + this.theme : "")+  " shape-" + this.shape ;
                    
                    // Crear tooltip
                    this.tooltip = this.createTooltip();
                    this.container.appendChild(this.tooltip);

                    // Añadir opción de cero si está habilitada
                    if (this.showZeroOption) {
                        const zeroOption = document.createElement("div");
                        zeroOption.className = "zero-option";
                        const zeroSize = Math.round(this.width * 0.9);
                        zeroOption.style.width = zeroSize + "px";
                        zeroOption.style.height = zeroSize + "px";
                        zeroOption.title = "Sin calificación";
                        
                        zeroOption.appendChild(this.createZeroSVG());
                        
                        if (!this.readonly) {
                            // Guardar referencia a this para los event listeners
                            const self = this;
                            zeroOption.addEventListener("mouseover", function() { self.handleZeroHover(); });
                            zeroOption.addEventListener("mouseleave", function() { self.handleMouseLeave(); });
                            zeroOption.addEventListener("click", function() { self.handleZeroClick(); });
                        }
                        
                        this.container.appendChild(zeroOption);
                    }
                    
                    // Determinar qué forma usar según el theme
                    const shape = this.getShapeForTheme(this.shape);
                    
                    // Crear las estrellas con SVG
                    for (let i = 1; i <= this.maxStars; i++) {
                        const star = document.createElement("div");
                        star.className = "star";
                        star.dataset.rating = i;
                        star.style.width = this.width + "px";
                        star.style.height = this.height + "px";
                        
                        // SVG de fondo (forma vacía)
                        const svgBg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
                        svgBg.setAttribute("class", "star-svg");
                        svgBg.setAttribute("viewBox", "0 0 24 24");
                                                
                        const pathBg = document.createElementNS("http://www.w3.org/2000/svg", "path");
                        pathBg.setAttribute("class", "star-shape-bg");
                        pathBg.setAttribute("fill-rule", "evenodd");
                        pathBg.setAttribute("d", this.getSVGPath(shape));
                        svgBg.appendChild(pathBg);
                        star.appendChild(svgBg);
                        
                        // Contenedor para el relleno
                        const starFill = document.createElement("div");
                        starFill.className = "star-fill";
                        
                        // SVG de relleno (forma llena)
                        const svgFill = document.createElementNS("http://www.w3.org/2000/svg", "svg");
                        svgFill.setAttribute("class", "star-svg");
                        svgFill.setAttribute("fill-rule", "evenodd");
                        svgFill.setAttribute("viewBox", "0 0 24 24");
                        svgFill.style.width = this.width + "px";
                        
                        const pathFill = document.createElementNS("http://www.w3.org/2000/svg", "path");
                        pathFill.setAttribute("class", "star-shape-fill");
                        pathFill.setAttribute("d", this.getSVGPath(shape));
                        svgFill.appendChild(pathFill);
                        starFill.appendChild(svgFill);
                        
                        star.appendChild(starFill);
                        
                        if (!this.readonly) {
                            // Guardar referencia a this y a i para los event listeners
                            const self = this;
                            const starIndex = i;
                            star.addEventListener("mousemove", function(e) { self.handleMouseMove(e, starIndex); });
                            star.addEventListener("mouseleave", function() { self.handleMouseLeave(); });
                            star.addEventListener("click", function(e) { self.handleClick(e, starIndex); });
                        }
                        
                        this.container.appendChild(star);
                    }
                    
                    this.updateDisplay();
                }
                
                handleZeroHover() {
                    this.hoverRating = 0;
                    this.updateDisplay(true);
                    
                    if (this.debug) {
                        console.log("Hover en opción cero");
                    }
                }
                
                async handleZeroClick() {
                    if (this.ajaxurl) {
                        await this.sendRatingToServer(0);
                    } else {
                        this.currentRating = 0;
                        this.updateDisplay();
                        this.updateTooltipText(this.tooltip);
                        
                        if (this.onRate) {
                            this.onRate(this.currentRating);
                        }
                    }
                    
                    if (this.debug) {
                        console.log("Clicked en opción cero - Rating: 0");
                    }
                }
                
                handleMouseMove(e, starIndex) {
                    // Hover muestra estrella completa (sin medias)
                    this.hoverRating = starIndex;
                    this.updateDisplay(true);
                    
                    if (this.debug) {
                        console.log(`Hover - Rating: ${this.hoverRating}`);
                    }
                }
                
                handleMouseLeave() {
                    this.hoverRating = -1;
                    this.updateDisplay(false);
                    
                    if (this.debug) {
                        console.log("Mouse leave - Restoring current rating");
                    }
                }
                
                async handleClick(e, starIndex) {
                    // Click usa estrella completa (sin medias)
                    const newRating = starIndex;
                    
                    console.log('DEBUG CLICK:', {
                        starIndex: starIndex,
                        newRating: newRating
                    });

                    if (this.ajaxurl) {
                        await this.sendRatingToServer(newRating);
                    } else {
                        this.currentRating = newRating;
                        this.updateDisplay();
                        this.updateTooltipText(this.tooltip);
                        
                        if (this.onRate) {
                            this.onRate(this.currentRating);
                        }
                    }
                    
                    if (this.debug) {
                        console.log(`Clicked - Final Rating: ${this.currentRating}`);
                    }
                }
                
                async sendRatingToServer(rating) {
                    this.container.classList.add("rating-loading");

                    const dataToSend = {
                        'rating': rating,
                        'container_id': this.container.id,
                        'max_stars': this.maxStars,
                        'timestamp': new Date().toISOString()
                    };

                    //console.log("Enviando datos al servidor:", this.ajaxurl)
                    //console.table(dataToSend);

                    $.ajax({
                           method: "POST",
                           url:this.ajaxurl,
                           data: dataToSend, 
                           dataType: "json",
                           beforeSend: (xhr, settings) => { 
                               $(".ajax-loader").show();  
                           }  
                    })
                    .done(
                        data => {
                            if(data.error){
                                console.error(data);                     
                                notify(data.msg,'error',3000);

                            }else{    
                                console.table(data);       
                                
                                notify(data.msg,'success',3000);

                                // Redondear a 0.5 y formatear: quitar decimales si es .0
                                this.currentRating = this.formatRating(data.new_rating);
                                
                                // Actualizar número de votos si viene en la respuesta
                                if (data.total_votes !== undefined) {
                                    this.totalVotes = data.total_votes;
                                }
                                

                                
                                this.updateDisplay();
                                this.updateTooltipText(this.tooltip);
                                
                                if (this.onRate) {
                                    this.onRate(this.currentRating, data);
                                }
                            }
                            
                            if(typeof data.score !== 'undefined'){
                                document.querySelector('#user_score').innerText = data.score;
                            }                            
                        }
                     )
                    .fail( data => {
                        console.error(data);      
                     })
                    .always( () => {  
                        $(".ajax-loader").hide(); 
                        this.container.classList.remove("rating-loading");
                     })    
                }
                
                updateDisplay(isHover = false) {
                    const rating = isHover ? this.hoverRating : this.currentRating;
                    const stars = this.container.querySelectorAll(".star");
                    const zeroOption = this.container.querySelector(".zero-option");
                    
                    // Actualizar opción de cero
                    if (zeroOption) {
                        zeroOption.classList.remove("active");
                        if (rating === 0 && !isHover) {
                            zeroOption.classList.add("active");
                        }
                    }
                    
                    // Actualizar estrellas
                    stars.forEach((star, index) => {
                        const starIndex = index + 1;
                        const starFill = star.querySelector(".star-fill");
                        
                        star.classList.remove("hover");
                        
                        if (rating >= starIndex) {
                            starFill.style.width = "100%";
                            if (isHover && rating > 0) star.classList.add("hover");
                        } else if (rating >= starIndex - 0.5) {
                            starFill.style.width = "50%";
                            if (isHover && rating > 0) star.classList.add("hover");
                        } else {
                            starFill.style.width = "0%";
                        }
                    });
                }
                
                setRating(rating) {
                    this.currentRating = Math.max(0, Math.min(this.maxStars, rating));
                    this.updateDisplay();
                    this.updateTooltipText(this.tooltip);
                }
                
                getRating() {
                    return this.currentRating;
                }
                
                reset() {
                    this.currentRating = 0;
                    this.updateDisplay();
                    this.updateTooltipText(this.tooltip);
                    
                    if (this.onRate) {
                        this.onRate(this.currentRating);
                    }
                }
            }
            
            // Almacenar instancias globalmente
            window.StarRatingInstances = window.StarRatingInstances || {};
        </script>
        <?php
    }
    
    public function setRating($rating){
        
        $result = [];
        $result['error'] = 0;

        // Verificar autenticación
        if (!isset($_SESSION['valid_user']) || !$_SESSION['valid_user'] || !isset($_SESSION['userid']) || $_SESSION['userid'] <= 0) {
            $result['error'] = 1;
            $result['msg'] = t('MUST_LOGIN_TO_RATE','Debes iniciar sesión para valorar.','You must be logged in to rate.');
            return $result;
        }
        
        $user_id = (int)$_SESSION['userid'];
        
        // Verificar rate limit para ratings (máx 10 ratings por minuto)
        $storage = new SessionStorage();
        $rateLimiter = new RateLimiter([
            'prefix' => 'rating_' . $user_id . '_',
            'maxCapacity' => 10,
            'refillPeriod' => 60
        ], $storage);
        
        if (!$rateLimiter->check('default')) {
            // Penalizar por flooding
            Karma::penalizeFlooding($user_id);
            $result['error'] = 1;
            $result['msg'] = t('TOO_MANY_RATINGS','Demasiadas valoraciones. Espera un momento antes de continuar.','Too many ratings. Please wait a moment before continuing.');
            $result['score'] = Karma::getUserScore($user_id);
            return $result;
        }
        
        // Verificar que el usuario no esté calificando su propio post
        $sql_check_author = $this->getAuthorQuery();

        $post_author = null;
        if ($sql_check_author) {
            $post_author = self::sqlQueryPrepared($sql_check_author, [$this->post])[0] ?? null;

            if ($post_author && (int)$post_author['USER_ID'] === $user_id) {
                $result['error'] = 1;
                $result['msg'] = t('CANNOT_RATE_OWN_POST','No puedes valorar tu propio contenido.','You cannot rate your own post.');
                return $result;
            }
        }

        // ⚠️ IMPORTANTE: Obtener rating previo ANTES de guardar el nuevo
        $prevRow = self::sqlQueryPrepared(
            "SELECT id, rating FROM POST_RATINGS WHERE module_id = ? AND post_id = ? AND user_id = ? LIMIT 1",
            [self::$module, $this->post, $user_id]
        );
        $previousRating = $prevRow[0]['rating'] ?? null;        
        
        // DEBUG: Añadir info al resultado para verificar
        $result['debug_previousRating'] = $previousRating;
        $result['debug_newRating'] = $rating;

        // Guardar el rating (DESPUÉS de obtener el previo)
        self::sqlQueryPrepared( 
            "REPLACE INTO POST_RATINGS (module_id, post_id, user_id, rating, ip_address) VALUES (?, ?, ?, ?, ?)",
            [self::$module, $this->post, $user_id, $rating, $_SERVER['REMOTE_ADDR']]
        );

        // Aplicar karma (coste al votante y delta al autor)
        $owner_id = $post_author['USER_ID'] ?? null;
        if ($owner_id && (int)$owner_id !== $user_id) {
            $karmaResult = Karma::applyRating($user_id, (int)$owner_id, $rating, $previousRating);
            $result = array_merge($result, $karmaResult);
        } else {
            // Si no hay owner (ej: módulo page sin autor), aún así aplicar coste al votante
            $karmaResult = Karma::applyRating($user_id, 0, $rating, $previousRating);
            $result = array_merge($result, $karmaResult);
        }

        if (!isset($result['msg'])) {
            $result['msg'] = t('RATING_SAVED','Valoración guardada correctamente.','Rating saved successfully.');
        }

        return $result;
    }

    /**
     * Obtiene la query SQL para verificar el autor del post según el módulo
     * @return string|null Query SQL o null si no se puede determinar
     */
    private function getAuthorQuery() {

        // Mapeo de módulos a tablas y campos
        // Módulo 1 (PAGES) no se incluye porque son páginas estáticas sin autoría
        $module_tables = [
            2 => "SELECT USER_ID FROM NOT_NEWS WHERE NOT_ID = ?",       // News
            //3 => "SELECT user_id FROM CLI_BLOG WHERE id = ?",       // Blog
            // Añadir más módulos según sea necesario (que tengan campo user_id)
        ];
        
        return $module_tables[self::$module] ?? null;
       // return $module_tables[2] ?? null;
    }

    public function getRating(){
            
        $rating_query = self::sqlQueryPrepared(
             'SELECT avg(rating) AS item_rating, count(id) AS item_votes FROM POST_RATINGS WHERE module_id = ? AND post_id = ?',[self::$module, $this->post]
        ) ?? [];
             
        //echo 'MODULE '.self::$module;
        //echo 'POST '.$post_id;
        //print_r($rating_query );
        return $rating_query[0];
    }

    public function render() {
        
        // Incluir CSS base solo una vez
        $this->renderBaseCSS();
        
        // Cargar theme CSS si es necesario
        //$output .= $this->loadThemeCSS($this->theme);
        
        // Incluir JS solo una vez
        $this->renderJS();
        
        $rating_query = $this->getRating();
        $this->initialRating = $rating_query['item_rating'] ?? 0;
        $this->totalVotes = $rating_query['item_votes'] ?? 0;      

        // Generar el contenedor HTML
        $output = '<div id="' . htmlspecialchars($this->containerId) . '"></div>';
        
        // Generar el JavaScript específico para esta instancia
        $output .= '<script>';
       // $output .= 'document.addEventListener("DOMContentLoaded", function() {';
        $output .= 'window.StarRatingInstances["' . $this->containerId . '"] = new StarRating("' . $this->containerId . '", {';
        $output .= 'maxStars: ' . $this->maxStars . ',';
        $output .= 'initialRating: ' . $this->initialRating . ',';
        $output .= 'readonly: ' . ($this->readonly ? 'true' : 'false') . ',';
        $output .= 'debug: ' . ($this->debug ? 'true' : 'false') . ',';
        $output .= 'width: ' . self::$size[0] . ',';
        $output .= 'height: ' . self::$size[1] . ',';
        $output .= 'showZeroOption: ' . ($this->showZeroOption ? 'true' : 'false') . ',';
        $output .= 'theme: "' . htmlspecialchars(self::$theme) . '",';
        $output .= 'shape: "' . htmlspecialchars(self::$shape) . '",';
        $output .= 'ajaxurl: "' . htmlspecialchars($this->ajaxurl) . '",';
        $output .= 'totalVotes: ' . $this->totalVotes . ',';
        $output .= 'onRate: function(rating, data) {';
        $output .= 'console.log("Rating actualizado:", rating);';
        if ($this->debug) {
            $output .= 'console.log("Datos del servidor:", data);';
        }
        $output .= '}';
        $output .= '});';
        //$output .= '});';
        $output .= '</script>';
        
        echo $output;

    }

    public static function show($post_id) {
        $html  = '<div id="ratings-container" style="position:relative;">'
             //  . '<h1>Loading rating ... </h1>'
               . '<div id="rating-ajax-loader"  class="ajax-loader" style="background-color:transparent;"><div class="loader"></div></div>'
               . '</div>';
        echo $html;
        ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {    
                
                //$('#rating-ajax-loader').show();        

                console.log('Loading rating', '<?=self::$url?>/op=render_rating/module_id=<?=self::$module?>/post_id=<?=$post_id?>');

                $('#ratings-container').load( `<?=self::$url?>/op=render_rating/module_id=<?=self::$module?>/post_id=<?=$post_id?>`, 
                    function(){    

                        //$('#rating-ajax-loader').hide();

                    }
                );
                
                
            });
        </script>
        <?php
    }



}


