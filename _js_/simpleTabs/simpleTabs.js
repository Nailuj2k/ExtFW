/**
 * SimpleTabs - A lightweight, responsive tabs implementation with no dependencies
 * 
 * Features:
 * - Properly handles nested tabs
 * - Transforms to accordion on mobile
 * - No jQuery or other dependencies
 * - Modern browsers only (ES6+)
 * - Support for custom events
 * - Dynamic tab creation and removal
 */
(function() {
    'use strict';
    
    class SimpleTabs {
        constructor(element, options = {}) {
            // Store DOM reference
            this.element = typeof element === 'string' ? document.querySelector(element) : element;
            if (!this.element) {
                console.error('SimpleTabs: Element not found');
                return;
            }
            
            // Default options
            this.options = Object.assign({
                active: 0,
                breakpoint: 768, // Mobile breakpoint in pixels
                classes: {
                    container: 'st-tabs',
                    nav: 'st-tabs-nav',
                    tab: 'st-tabs-tab',
                    panel: 'st-tabs-panel',
                    active: 'st-active',
                    accordion: 'st-accordion'
                },
                // Events callbacks
                onCreateTabs: null,
                onActivateTab: null,
                onAddTab: null,
                onRemoveTab: null
            }, options);
            
            // Initialize tabs
            this.init();
            
            // Handle responsiveness
            this.handleResize = this.handleResize.bind(this);
            window.addEventListener('resize', this.handleResize);
            this.handleResize();
        }
        
        /**
         * Initialize tabs from existing HTML structure
         */
        init() {
            // Apply container class
            this.element.classList.add(this.options.classes.container);
            
            // Find tab elements - only search direct children to support nesting
            const ul = this.element.querySelector(':scope > ul');
            if (!ul) {
                // Create navigation if it doesn't exist
                const newUl = document.createElement('ul');
                this.element.prepend(newUl);
                this.navContainer = newUl;
            } else {
                this.navContainer = ul;
            }
            
            this.navContainer.classList.add(this.options.classes.nav);
            
            // Get tabs and create panels array
            this.tabs = Array.from(this.navContainer.querySelectorAll(':scope > li'));
            this.panels = [];
            this.accordionTitles = [];
            
            // Process each tab
            this.tabs.forEach((tab, index) => this._processTab(tab, index));
            
            // Activate initial tab
            if (this.tabs.length > 0) {
                this.activate(this.options.active);
            }
            
            if(console_log) console.log(`SimpleTabs: Initialized with ${this.tabs.length} tabs and ${this.panels.length} panels`);
            
            // Trigger onCreateTabs event/callback
            this._triggerEvent('create-tabs', { tabs: this.tabs });
            if (typeof this.options.onCreateTabs === 'function') {
                this.options.onCreateTabs.call(this, { tabs: this.tabs });
            }
        }
        
        /**
         * Process a single tab during initialization
         * @private
         */
        _processTab(tab, index) {
            // Add tab class
            tab.classList.add(this.options.classes.tab);
            
            // Get link and target panel
            const link = tab.querySelector('a');
            if (!link || !link.getAttribute('href')) return;
            
            const href = link.getAttribute('href');
            if (href.charAt(0) !== '#') return;
            
            const panelId = href.substring(1);
            const panel = document.getElementById(panelId);
            if (!panel) return;
            
            // Store panel
            panel.classList.add(this.options.classes.panel);
            this.panels.push(panel);
            
            // Set up click handler
            link.addEventListener('click', (e) => {
                e.preventDefault();
                this.activate(index);
            });
            
            // Create accordion title for mobile view
            const accordionTitle = document.createElement('div');
            accordionTitle.className = 'st-accordion-title';
            accordionTitle.innerHTML = link.innerHTML;
            accordionTitle.setAttribute('data-index', index);
            accordionTitle.addEventListener('click', () => {
                if (this.isAccordionMode() && this.activeTabIndex === index) {
                    this.collapse();
                } else {
                    this.activate(index);
                }
            });
            
            // Insert before the panel
            panel.parentNode.insertBefore(accordionTitle, panel);
            this.accordionTitles.push(accordionTitle);
        }
        
        /**
         * Activate a specific tab
         * @param {number} index - The index of the tab to activate
         */
        activate(index) {
            if (index >= this.tabs.length) {
                index = 0;
            }
            
            // Remember the active tab
            this.activeTabIndex = index;
            
            // Deactivate all tabs and panels
            this.tabs.forEach(tab => tab.classList.remove(this.options.classes.active));
            this.panels.forEach(panel => {
                panel.classList.remove(this.options.classes.active);
                panel.style.display = 'none';
            });
            
            // Get accordion titles
            const accordionTitles = Array.from(
                this.element.querySelectorAll('.st-accordion-title')
            );
            accordionTitles.forEach(title => title.classList.remove(this.options.classes.active));
            
            // Activate the selected tab and panel
            if (this.tabs[index]) {
                this.tabs[index].classList.add(this.options.classes.active);
            }
            
            if (this.panels[index]) {
                this.panels[index].classList.add(this.options.classes.active);
                this.panels[index].style.display = 'block';
            }
            
            // Activate accordion title
            const activeAccordionTitle = accordionTitles.find(
                title => parseInt(title.getAttribute('data-index')) === index
            );
            if (activeAccordionTitle) {
                activeAccordionTitle.classList.add(this.options.classes.active);
            }
            
            // Trigger events
            const eventDetail = { index: index, tab: this.tabs[index], panel: this.panels[index] };
            this._triggerEvent('tab-change', eventDetail);
            if (typeof this.options.onActivateTab === 'function') {
                this.options.onActivateTab.call(this, eventDetail);
            }
        }
        
        /**
         * Toggle responsive mode based on window size
         */
        handleResize() {
            const mobile = window.innerWidth < this.options.breakpoint;
            this.element.classList.toggle(this.options.classes.accordion, mobile);
        }

        /**
         * Returns true when in accordion (mobile) mode
         */
        isAccordionMode() {
            return this.element.classList.contains(this.options.classes.accordion);
        }

        /**
         * Collapse all panels (accordion mode only)
         */
        collapse() {
            this.activeTabIndex = -1;
            this.tabs.forEach(tab => tab.classList.remove(this.options.classes.active));
            this.panels.forEach(panel => {
                panel.classList.remove(this.options.classes.active);
                panel.style.display = 'none';
            });
            const accordionTitles = Array.from(this.element.querySelectorAll('.st-accordion-title'));
            accordionTitles.forEach(title => title.classList.remove(this.options.classes.active));
        }

        /**
         * Trigger a custom event with optional details
         * @private
         */
        _triggerEvent(eventName, detail = {}) {
            this.element.dispatchEvent(new CustomEvent(`simpletabs-${eventName}`, {
                bubbles: true,
                detail: detail
            }));
        }
        
        /**
         * Add a new tab dynamically
         * @param {Object} config - Configuration object for the new tab
         * @param {string} config.title - The tab title
         * @param {string|Element} config.content - HTML content or DOM element for the panel
         * @param {string} [config.id] - Optional ID for the panel (auto-generated if not provided)
         * @param {number} [config.position] - Position to insert the tab (end if not specified)
         * @returns {number} The index of the newly added tab
         */
        addTab(config) {
            // Generate unique ID if not provided
            const panelId = config.id || `st-panel-${Date.now()}-${Math.floor(Math.random() * 1000)}`;
            
            // Create tab element
            const tab = document.createElement('li');
            tab.classList.add(this.options.classes.tab);
            
            // Create link
            const link = document.createElement('a');
            link.href = `#${panelId}`;
            link.innerHTML = config.title || 'New Tab';
            tab.appendChild(link);
            
            // Create panel
            const panel = document.createElement('div');
            panel.id = panelId;
            panel.classList.add(this.options.classes.panel);
            
            // Add content to panel
            if (typeof config.content === 'string') {
                panel.innerHTML = config.content;
            } else if (config.content instanceof Element) {
                panel.appendChild(config.content);
            }
            
            // Create accordion title
            const accordionTitle = document.createElement('div');
            accordionTitle.className = 'st-accordion-title';
            accordionTitle.innerHTML = link.innerHTML;
            
            // Determine position
            const position = (typeof config.position === 'number' && 
                              config.position >= 0 && 
                              config.position <= this.tabs.length) 
                ? config.position 
                : this.tabs.length;
            
            // Insert tab at specified position
            if (position === this.tabs.length) {
                this.navContainer.appendChild(tab);
            } else {
                this.navContainer.insertBefore(tab, this.tabs[position]);
            }
            
            // Insert panel and accordion title to DOM
            this.element.appendChild(panel);
            panel.parentNode.insertBefore(accordionTitle, panel);
            
            // Update arrays with new elements
            if (position === this.tabs.length) {
                this.tabs.push(tab);
                this.panels.push(panel);
                this.accordionTitles.push(accordionTitle);
            } else {
                this.tabs.splice(position, 0, tab);
                this.panels.splice(position, 0, panel);
                this.accordionTitles.splice(position, 0, accordionTitle);
            }
            
            // Update data-index attributes for accordion titles
            this.accordionTitles.forEach((title, i) => {
                title.setAttribute('data-index', i);
            });
            
            // Set up event handlers
            link.addEventListener('click', (e) => {
                e.preventDefault();
                this.activate(this.tabs.indexOf(tab));
            });
            
            accordionTitle.addEventListener('click', () => {
                const idx = this.tabs.indexOf(tab);
                if (this.isAccordionMode() && this.activeTabIndex === idx) {
                    this.collapse();
                } else {
                    this.activate(idx);
                }
            });
            
            // Trigger events
            const eventDetail = { 
                index: position, 
                tab: tab, 
                panel: panel,
                title: config.title,
                content: config.content
            };
            
            this._triggerEvent('tab-add', eventDetail);
            if (typeof this.options.onAddTab === 'function') {
                this.options.onAddTab.call(this, eventDetail);
            }
            
            // Return the index of the new tab
            return position;
        }
        
        /**
         * Remove a tab
         * @param {number} index - The index of the tab to remove
         * @returns {boolean} Success indicator
         */
        removeTab(index) {
            // Validate index
            if (index < 0 || index >= this.tabs.length) {
                console.error('SimpleTabs: Invalid tab index for removal');
                return false;
            }
            
            // Get references to elements being removed
            const tab = this.tabs[index];
            const panel = this.panels[index];
            const accordionTitle = this.accordionTitles[index];
            
            // Store references for the event
            const removedTab = tab;
            const removedPanel = panel;
            
            // Remove from DOM
            if (tab && tab.parentNode) tab.parentNode.removeChild(tab);
            if (panel && panel.parentNode) panel.parentNode.removeChild(panel);
            if (accordionTitle && accordionTitle.parentNode) {
                accordionTitle.parentNode.removeChild(accordionTitle);
            }
            
            // Remove from arrays
            this.tabs.splice(index, 1);
            this.panels.splice(index, 1);
            this.accordionTitles.splice(index, 1);
            
            // Update data-index attributes for accordion titles
            this.accordionTitles.forEach((title, i) => {
                title.setAttribute('data-index', i);
            });
            
            // If the active tab was removed, activate another tab
            if (this.activeTabIndex === index) {
                // Activate the tab at the same position, or the last tab if this was the last one
                const newIndex = this.tabs.length > 0 ? 
                    (index < this.tabs.length ? index : this.tabs.length - 1) : 
                    null;
                
                if (newIndex !== null) {
                    this.activate(newIndex);
                }
            } else if (this.activeTabIndex > index) {
                // If the active tab was after the removed tab, adjust the index
                this.activeTabIndex--;
            }
            
            // Trigger events
            const eventDetail = { 
                index: index, 
                tab: removedTab, 
                panel: removedPanel 
            };
            
            this._triggerEvent('tab-remove', eventDetail);
            if (typeof this.options.onRemoveTab === 'function') {
                this.options.onRemoveTab.call(this, eventDetail);
            }
            
            return true;
        }
        
        /**
         * Clean up event listeners and prepare for destruction
         */
        destroy() {
            window.removeEventListener('resize', this.handleResize);
            
            // Remove event listeners from tabs
            this.tabs.forEach(tab => {
                const link = tab.querySelector('a');
                if (link) {
                    link.removeEventListener('click', this._handleTabClick);
                }
            });
            
            // Remove event listeners from accordion titles
            this.accordionTitles.forEach(title => {
                title.removeEventListener('click', this._handleAccordionClick);
            });
            
            // Trigger destroy event
            this._triggerEvent('destroy', {});
        }
    }
    
    // Expose to global scope
    window.SimpleTabs = SimpleTabs;
    
    // Auto-initialize all tabs with data-simpletabs attribute
    document.addEventListener('DOMContentLoaded', () => {
        const tabElements = document.querySelectorAll('[data-simpletabs]');
        tabElements.forEach(el => new SimpleTabs(el,{

                onCreateTabs: function(event) {
                    // console.log('Tabs created:', event.tabs);
                },
                onActivateTab: function(event) {
                    // console.log('Tab activated:', event.index,event.panel.id);
                    if (typeof onActivateTab === 'function') {
                        onActivateTab(event,event.panel.id);
                    }
                },
                onAddTab: function(event) {
                    // console.log('Tab added at index:', event.index);
                },
                onRemoveTab: function(event) {
                   // console.log('Tab removed from index:', event.index);
                }

        }));
    });
})();




/**
 * 
 * Key Features Implemented
Zero Dependencies - Pure vanilla JavaScript with no jQuery required
Responsive Design - Automatically transforms to accordion view on mobile screens
Proper Nesting Support - Nested tabs work correctly using scoped selectors
Event System - Both callback and custom event approaches
Dynamic Tab Management - Methods to add and remove tabs programmatically
Modern Code - Using ES6+ features for cleaner implementation
The Event System
The event system gives you two ways to respond to tab changes:

Callback approach - Via options like onCreateTabs, onActivateTab, etc.
Custom events - Via event listeners for simpletabs-tab-change, etc.
Dynamic Tab Methods
The API provides intuitive methods for manipulating tabs:

addTab({title, content, id, position}) - Add a new tab
removeTab(index) - Remove a tab at the specified index
activate(index) - Activate a specific tab
Future Enhancement Ideas
If you ever need additional features, here are some ideas:

Tab persistence using localStorage
Animation options for tab transitions
Additional themes/skins
Lazy-loading content in tabs
URL hash navigation support
Feel free to reach out if you need any of these features or have any questions about the implementation!


 */