   /**
     * Splitter.js (vanilla)
     */
    class Splitter {
      constructor({
        orientation = 'vertical',
        elementLeftOrTop,
        elementRightOrBottom,
        size = 5,
        color = 'orange',
        minSize = 30
      }) {
        this.orientation = orientation;
        this.elA = document.getElementById(elementLeftOrTop);
        this.elB = document.getElementById(elementRightOrBottom);
        if (!this.elA || !this.elB)
          throw new Error('Splitter: elemento no encontrado.');
       if (this.elA.parentElement !== this.elB.parentElement)
          throw new Error('Splitter: deben compartir el mismo padre.');

        this.parent = this.elA.parentElement;
        const ps = getComputedStyle(this.parent);
        if (ps.position === 'static') this.parent.style.position = 'relative';

        this.size = size;
        this.color = color;
        this.minSize = minSize;
        this._createSplitter();
        this._initEvents();
        this._updatePosition();
        window.addEventListener('resize', () => this._updatePosition());
        
        // Usar ResizeObserver para detectar cambios en el tamaño del parent
        this.resizeObserver = new ResizeObserver(() => this._updatePosition());
        this.resizeObserver.observe(this.parent);


      }

      _createSplitter() {
        this.splitterEl = document.createElement('div');
        this.splitterEl.classList.add('splitter', this.orientation);
        if (this.orientation === 'vertical')
          this.splitterEl.style.width = this.size + 'px';
        else
          this.splitterEl.style.height = this.size + 'px';
        this.splitterEl.style.backgroundColor = this.color;
        this.parent.appendChild(this.splitterEl);
      }

      _initEvents() {
        this.splitterEl.addEventListener('pointerdown', e => this._onPointerDown(e));
      }

      _onPointerDown(e) {
        e.preventDefault();
        this.splitterEl.setPointerCapture(e.pointerId);
        const rectA = this.elA.getBoundingClientRect();
        const rectB = this.elB.getBoundingClientRect();
        this.start = { x: e.clientX, y: e.clientY };
        this.startSizeA = { w: rectA.width, h: rectA.height };
        this.startSizeB = { w: rectB.width, h: rectB.height };

        this._move = e => this._onPointerMove(e);
        this._up   = e => this._onPointerUp(e);
        document.addEventListener('pointermove', this._move);
        document.addEventListener('pointerup',   this._up);
      }

      _onPointerMove(e) {
        const dx = e.clientX - this.start.x;
        const dy = e.clientY - this.start.y;
        if (this.orientation === 'vertical') {
          let a = this.startSizeA.w + dx;
          let b = this.startSizeB.w - dx;
          if (a < this.minSize || b < this.minSize) return;
          this.elA.style.width = a + 'px';
          this.elB.style.width = b + 'px';
        } else {
          let a = this.startSizeA.h + dy;
          let b = this.startSizeB.h - dy;
          if (a < this.minSize || b < this.minSize) return;
          this.elA.style.height = a + 'px';
          this.elB.style.height = b + 'px';
        }
        this._updatePosition();
      }

      _onPointerUp(e) {
        document.removeEventListener('pointermove', this._move);
        document.removeEventListener('pointerup',   this._up);
        this.splitterEl.releasePointerCapture(e.pointerId);
      }

      _updatePosition() {
        //console.log('SPLITTER.UPDATEPOSITION');
        //console.log('SPLITTER.PARENT.ID',this.parent.id)
        const pRect = this.parent.getBoundingClientRect();
        const aRect = this.elA.getBoundingClientRect();
        if (this.orientation === 'vertical') {
          const left = aRect.right - pRect.left - this.size/2;
          this.splitterEl.style.left = left + 'px';
          this.splitterEl.style.top = '0';
          this.splitterEl.style.bottom = '0';
        } else {
          const top = aRect.bottom - pRect.top - this.size/2;
          this.splitterEl.style.top = top + 'px';
          this.splitterEl.style.left = '0';
          this.splitterEl.style.right = '0';
        }
      }
      
      // Método para desconectar listeners cuando el splitter ya no se use
      destroy() {
        window.removeEventListener('resize', this._updatePosition.bind(this));
        if (this.resizeObserver) {
          this.resizeObserver.disconnect();
          this.resizeObserver = null;
        }
      }
    }