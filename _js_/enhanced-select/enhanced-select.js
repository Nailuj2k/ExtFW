class EnhancedSelect extends HTMLElement {
    constructor() {
        super();
        this.originalOptions = [];
        this.selectedValues = new Set();
        this.manualInput = false;
        this.manualInputText = '';
        this._ajaxUrlAdd = null;
        this.isAddingOption = false;
        this.activeIndex = -1;
        this.isOpen = false;
    }

    static get observedAttributes() {
        return [
            'placeholder',
            'disabled',
            'required',
            'clearable',
            'keyboard-navigation',
            'multiple',
            'search-mode', // 'startsWith', 'contains', 'exact'
            'ajax-url-add'
        ];
    }

    get config() {
        return {
            disabled: this.hasAttribute('disabled'),
            required: this.hasAttribute('required'),
            clearable: this.hasAttribute('clearable'),
            keyboardNavigation: this.hasAttribute('keyboard-navigation'),
            multiple: this.hasAttribute('multiple'),
            searchMode: this.getAttribute('search-mode') || 'contains'
        };
    }

    get ajaxUrlAdd() {
        return this._ajaxUrlAdd;
    }

    set ajaxUrlAdd(value) {
        if (value === null || value === undefined || value === '') {
            this._ajaxUrlAdd = null;
            this.removeAttribute('ajax-url-add');
            return;
        }

        const stringValue = String(value);
        this._ajaxUrlAdd = stringValue;
        if (this.getAttribute('ajax-url-add') !== stringValue) {
            this.setAttribute('ajax-url-add', stringValue);
        }
    }

    get ajax_url_add() {
        return this.ajaxUrlAdd;
    }

    set ajax_url_add(value) {
        this.ajaxUrlAdd = value;
    }

    connectedCallback() {
        this.classList.add('enhanced-select');
        this.originalOptions = Array.from(this.querySelectorAll('option')).map(option => ({
            value: option.value,
            text: option.textContent,
            disabled: option.hasAttribute('disabled'),
            selected: option.hasAttribute('selected')
        }));

        this._ajaxUrlAdd = this.getAttribute('ajax-url-add');
        
        this.render();
        this.setupEventListeners();
        this.initializeSelection();
    }

    initializeSelection() {
        const selectedOptions = this.originalOptions.filter(opt => opt.selected);
        if (selectedOptions.length > 0) {
            if (this.config.multiple) {
                selectedOptions.forEach(opt => {
                    this.selectedValues.add(opt.value);
                });
            } else {
                this.selectedValues.add(selectedOptions[0].value);
            }
            this.updateSelectedValues();
        }
    }

    attributeChangedCallback(name, oldValue, newValue) {
        if (this.input) {
            switch (name) {
                case 'placeholder':
                    this.input.placeholder = newValue;
                    break;
                case 'disabled':
                    this.input.disabled = this.config.disabled;
                    this.toggleClass('disabled', this.config.disabled);
                    break;
                case 'required':
                    this.input.required = this.config.required;
                    break;
            }
        }

        if (name === 'ajax-url-add') {
            this._ajaxUrlAdd = newValue;
        }
    }

    render() {
        const wrapper = document.createElement('div');
        wrapper.className = 'select-wrapper';

        // Contenedor para el input y los tags
        this.inputWrapper = document.createElement('div');
        this.inputWrapper.className = 'input-wrapper';

        // Input principal
        this.input = document.createElement('input');
        this.input.type = 'text';
        this.input.placeholder = this.getAttribute('placeholder') || 'Selecciona una opción';
        this.input.disabled = this.config.disabled;
        this.input.required = this.config.required;

        // Contenedor de valores múltiples
        this.selectedContainer = document.createElement('div');
        this.selectedContainer.className = 'selected-values';
        
        // Botón de limpiar
        if (this.config.clearable) {
            this.clearButton = document.createElement('button');
            this.clearButton.className = 'clear-button';
            this.clearButton.innerHTML = '×';
            this.clearButton.style.display = 'none';
            wrapper.appendChild(this.clearButton);
        }

        // Dropdown
        const dropdown = document.createElement('div');
        dropdown.className = 'dropdown';

        // Estructura del componente
        this.inputWrapper.appendChild(this.selectedContainer);
        this.inputWrapper.appendChild(this.input);
        wrapper.appendChild(this.inputWrapper);
        wrapper.appendChild(dropdown);
        
        if (this.clearButton) {
            wrapper.appendChild(this.clearButton);
        }

        this.innerHTML = '';
        this.appendChild(wrapper);

        this.renderOptions();
        this.updateSelectedValues();
    }

    setupEventListeners() {
        if (this.config.disabled) return;

        // Input events
        this.inputWrapper.addEventListener('click', () => {
            if (!this.isOpen) {
                this.input.focus();
            }
        });

        this.input.addEventListener('input', (e) => {
            this.manualInput = true;
            this.manualInputText = e.target.value;
            this.showDropdown();
            this.filterOptions(e.target.value); // Agregar esta línea
            if (this.config.clearable) {
                this.clearButton.style.display = e.target.value ? 'block' : 'none';
            }
        });

        this.input.addEventListener('focus', () => this.showDropdown());

        // Keyboard navigation
        if (this.config.keyboardNavigation) {
            this.input.addEventListener('keydown', (e) => this.handleKeyboard(e));
        }

        // Clear button
        if (this.config.clearable) {
            this.clearButton.addEventListener('click', (e) => {
                e.stopPropagation();
                this.clearSelection();
            });
        }

        // Click outside
        document.addEventListener('click', (e) => {
            if (!this.contains(e.target)) {
                this.hideDropdown();
            }
        });
    }

    handleKeyboard(e) {
        const options = this.getVisibleOptions();
        
        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                this.activeIndex = Math.min(this.activeIndex + 1, options.length - 1);
                this.updateActiveOption();
                break;
            case 'ArrowUp':
                e.preventDefault();
                this.activeIndex = Math.max(this.activeIndex - 1, -1);
                this.updateActiveOption();
                break;
            case 'Enter':
                e.preventDefault();
                if (this.activeIndex >= 0) {
                    const option = options[this.activeIndex];
                    if (option.dataset && option.dataset.addOption === 'true') {
                        this.handleAddOption();
                    } else {
                        this.selectOption(option.dataset.value, option.textContent);
                    }
                } else if (this.config.multiple && this.input.value.trim()) {
                    // Create custom tag from input text
                    this.createCustomTag(this.input.value.trim());
                }
                break;
            case 'Tab':
                if (this.config.multiple && this.input.value.trim()) {
                    e.preventDefault();
                    // Create custom tag from input text
                    this.createCustomTag(this.input.value.trim());
                }
                break;
            case 'Escape':
                this.hideDropdown();
                break;
        }
    }

    updateActiveOption() {
        const options = Array.from(this.querySelectorAll('.option'));
        options.forEach(opt => opt.classList.remove('active'));
        
        if (this.activeIndex >= 0 && options[this.activeIndex]) {
            options[this.activeIndex].classList.add('active');
            options[this.activeIndex].scrollIntoView({
                block: 'nearest',
                inline: 'start'
            });
        }
    }

    filterOptions(query) {
        const filteredOptions = this.originalOptions.filter(option => {
            if (option.disabled) return false;
            
            const text = option.text.toLowerCase();
            query = query.toLowerCase();
            
            switch (this.config.searchMode) {
                case 'startsWith':
                    return text.startsWith(query);
                case 'exact':
                    return text === query;
                case 'contains':
                default:
                    return text.includes(query);
            }
        });
        
        this.renderOptions(filteredOptions);
    }

    renderOptions(options = this.originalOptions) {
        const dropdown = this.querySelector('.dropdown');
        if (!dropdown) return;

        const list = Array.isArray(options) ? options : this.originalOptions;
        dropdown.innerHTML = list.map(option => `
            <div class="option ${option.disabled ? 'disabled' : ''}" 
                 data-value="${option.value}"
                 ${option.disabled ? 'aria-disabled="true"' : ''}>
                ${option.text}
            </div>
        `).join('');

        const baseManualText = this.manualInputText !== undefined && this.manualInputText !== null
            ? this.manualInputText
            : (this.input ? this.input.value : '');
        const manualText = (baseManualText || '').trim();
        const manualTextLower = manualText.toLowerCase();
        const canAddManual = Boolean(
            this._ajaxUrlAdd &&
            this.manualInput &&
            manualText &&
            !this.originalOptions.some(opt => (opt.text || '').toLowerCase() === manualTextLower)
        );

        if (canAddManual) {
            const addOption = document.createElement('div');
            addOption.className = 'option add-option';
            addOption.dataset.addOption = 'true';
            addOption.textContent = `Add ${manualText}`;
            dropdown.appendChild(addOption);
        }

        dropdown.querySelectorAll('.option').forEach(option => {
            if (option.classList.contains('disabled')) return;
            if (option.dataset.addOption === 'true') {
                option.addEventListener('click', () => this.handleAddOption());
            } else {
                option.addEventListener('click', () => this.selectOption(option.dataset.value, option.textContent));
            }
        });
    }

    buildAddRequestUrl(text) {
        const base = this._ajaxUrlAdd;
        if (!base) return null;

        if (!base.includes('[TEXT]')) {
            return base;
        }

        return base.replace('[TEXT]', encodeURIComponent(text));
    }

    async handleAddOption() {
        if (!this._ajaxUrlAdd || !this.input) return;
        const rawText = this.input.value || '';
        const text = rawText.trim();
        if (!text || this.isAddingOption) return;

        const requestUrl = this.buildAddRequestUrl(text);
        if (!requestUrl) return;

        this.isAddingOption = true;

        try {
            const response = await fetch(requestUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'text/plain',
                    'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
                },
                body: `text=${encodeURIComponent(text)}`
            });

            if (!response.ok) {
                throw new Error(`EnhancedSelect: failed to add option (${response.status})`);
            }

            const payload = (await response.text()).trim();
            if (!payload) {
                throw new Error('EnhancedSelect: empty response when adding option');
            }

            let value = payload;

            if (payload.startsWith('{') || payload.startsWith('[')) {
                try {
                    const data = JSON.parse(payload);
                    if (data && typeof data.id !== 'undefined' && data.id !== null) {
                        value = data.id;
                    }
                } catch (parseError) {
                    console.warn('EnhancedSelect: unable to parse JSON response from ajax-url-add', parseError);
                }
            }

            value = String(value).trim();
            if (!value) {
                throw new Error('EnhancedSelect: invalid value returned when adding option');
            }

            if (!this.originalOptions.some(opt => opt.value === value)) {
                this.originalOptions.push({
                    value,
                    text,
                    disabled: false,
                    selected: false
                });
            }

            this.manualInput = false;
            this.manualInputText = '';
            this.selectOption(value, text);
            if (this.input) {
                this.filterOptions('');
            }
        } catch (error) {
            console.error(error);
        } finally {
            this.isAddingOption = false;
        }
    }

    selectOption(value, text) {
        if (this.config.multiple) {
            if (this.selectedValues.has(value)) {
                this.selectedValues.delete(value);
            } else {
                this.selectedValues.add(value);
            }
            this.input.value = '';
            this.filterOptions('');
        } else {
            this.selectedValues.clear();
            this.selectedValues.add(value);
            this.hideDropdown();
        }

        this.manualInput = false;
        this.manualInputText = '';
        this.updateSelectedValues();
        this.dispatchEvent(new Event('change', { bubbles: true }));
        this.dispatchEvent(new CustomEvent('select-change', {
            detail: { 
                values: Array.from(this.selectedValues),
                selected: this.getSelectedOptions()
            }
        }));
    }

    createCustomTag(text) {
        if (!this.config.multiple || !text) return;
        
        // Sanitize text for use as value
        const sanitizedText = text.toLowerCase().replace(/[^a-z0-9]/g, '_');
        const customValue = `custom_${sanitizedText}`;
        
        // Check if this text already exists as a tag
        const existingOption = this.originalOptions.find(opt => opt.text.toLowerCase() === text.toLowerCase());
        if (existingOption) {
            // If it exists, just select it
            this.selectOption(existingOption.value, existingOption.text);
            return;
        }
        
        // Check if we already have a custom tag with this text
        const existingCustom = Array.from(this.selectedValues).find(value => {
            const option = this.originalOptions.find(opt => opt.value === value);
            return option && option.text.toLowerCase() === text.toLowerCase();
        });
        
        if (existingCustom) {
            this.input.value = '';
            return;
        }
        
        // Add as a new custom option
        this.originalOptions.push({
            value: customValue,
            text: text,
            disabled: false,
            selected: false,
            custom: true
        });
        
        // Select the new custom option
        this.selectedValues.add(customValue);
        this.input.value = '';
        this.manualInput = false;
        this.manualInputText = '';
        this.updateSelectedValues();
        this.filterOptions('');
        
        this.dispatchEvent(new Event('change', { bubbles: true }));
        this.dispatchEvent(new CustomEvent('select-change', {
            detail: { 
                values: Array.from(this.selectedValues),
                selected: this.getSelectedOptions()
            }
        }));
        
        this.dispatchEvent(new CustomEvent('custom-tag-created', {
            detail: {
                value: customValue,
                text: text
            }
        }));
    }

    updateSelectedValues() {
        if (this.config.multiple) {
            this.selectedContainer.innerHTML = '';
            const selectedOptions = this.getSelectedOptions();
            
            selectedOptions.forEach(({ value, text }) => {
                const tag = document.createElement('div');
                tag.className = 'selected-tag';
                tag.textContent = text;
                
                const removeBtn = document.createElement('button');
                removeBtn.className = 'remove-tag';
                removeBtn.innerHTML = '&times;';
                removeBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    this.selectOption(value, text);
                });
                
                tag.appendChild(removeBtn);
                this.selectedContainer.appendChild(tag);
            });

            if (this.selectedValues.size > 0) {
                this.classList.add('has-values');
                this.input.placeholder = '';
            } else {
                this.classList.remove('has-values');
                this.input.placeholder = this.getAttribute('placeholder') || 'Selecciona una opción';
            }

        } else {
            const selected = this.getSelectedOptions()[0];
            this.input.value = selected ? selected.text : '';
        }

        if (this.config.clearable) {
            this.clearButton.style.display = this.selectedValues.size ? 'block' : 'none';
        }
    }

    showDropdown() {
        if (this.config.disabled) return;
        
        const dropdown = this.querySelector('.dropdown');
        dropdown.style.display = 'block';
        this.isOpen = true;
        this.activeIndex = -1;
    }

    hideDropdown() {
        const dropdown = this.querySelector('.dropdown');
        dropdown.style.display = 'none';
        this.isOpen = false;
        this.activeIndex = -1;
    }

    clearSelection() {
        this.selectedValues.clear();
        this.manualInput = false;
        this.manualInputText = '';
        this.input.value = '';
        this.updateSelectedValues();
        this.dispatchEvent(new Event('change', { bubbles: true }));
        this.dispatchEvent(new CustomEvent('select-change', {
            detail: { 
                values: [],
                selected: []
            }
        }));
    }

    getSelectedOptions() {
        return Array.from(this.selectedValues).map(value => 
            this.originalOptions.find(opt => opt.value === value)
        ).filter(Boolean);
    }

    getVisibleOptions() {
        return Array.from(this.querySelectorAll('.option:not(.disabled)'));
    }

    getValue() {
        if (this.config.multiple) {
            return this.getSelectedOptions();
        }
        const selected = this.getSelectedOptions()[0];

        let input = this.querySelector('input')
        let vv = input.value;

        return vv || selected || null;
    }

    // Propiedad value que devuelve solo los valores (como select normal)
    get value() {
        if (this.config.multiple) {
            return Array.from(this.selectedValues);
        }
        const selected = this.getSelectedOptions()[0];
        const inputValue = this.input ? this.input.value : '';

        if (this.manualInput) {
            return inputValue ? -1 : 0;
        }

        return selected ? selected.value : 0;
    }

    set value(newValue) {
        this.manualInput = false;
        this.manualInputText = '';
        this.selectedValues.clear();
        if (newValue) {
            if (Array.isArray(newValue)) {
                newValue.forEach(val => this.selectedValues.add(val));
            } else {
                this.selectedValues.add(newValue);
            }
        }
        this.updateSelectedValues();
    }

    // API Pública
    addOption(value, text, disabled = false) {
        this.originalOptions.push({ value, text, disabled });
        this.renderOptions();
    }

    removeOption(value) {
        const index = this.originalOptions.findIndex(opt => opt.value === value);
        if (index !== -1) {
            this.originalOptions.splice(index, 1);
            this.selectedValues.delete(value);
            this.renderOptions();
            this.updateSelectedValues();
        }
    }

    enable() {
        this.removeAttribute('disabled');
    }

    disable() {
        this.setAttribute('disabled', '');
    }

    toggleClass(className, force) {
        if (force) {
            this.classList.add(className);
        } else {
            this.classList.remove(className);
        }
    }
}

// Registrar el componente
customElements.define('enhanced-select', EnhancedSelect);





