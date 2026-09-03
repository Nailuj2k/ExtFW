<h1> Quick Start <h1>
<!-- Include the CSS and JS files -->
<link rel="stylesheet" href="simpleTabs.css">
<script src="simpleTabs.js"></script>

<!-- Basic example -->
 <div id="my-tabs">
  <ul>
    <li><a href="#tab1">Tab 1</a></li>
    <li><a href="#tab2">Tab 2</a></li>
  </ul>
  <div id="tab1">Content 1</div>
  <div id="tab2">Content 2</div>
</div>

<script>
  // Initialize with JavaScript
  document.addEventListener('DOMContentLoaded', function() {
    new SimpleTabs('#my-tabs');
    
    // Or with options
    new SimpleTabs('#other-tabs', {
      active: 1, // Start with second tab active
      breakpoint: 576 // Mobile breakpoint in pixels
    });
  });
</script>

<!-- Or use data attribute for auto-initialization -->
<div id="auto-tabs" data-simpletabs>
  <ul>
    <li><a href="#autotab1">Tab 1</a></li>
    <li><a href="#autotab2">Tab 2</a></li>
  </ul>
  <div id="autotab1">Content 1</div>
  <div id="autotab2">Content 2</div>
</div>


<!--

/**
 * SimpleTabs - A lightweight, responsive tabs implementation with no dependencies
 * 
 * Features:
 * - Properly handles nested tabs
 * - Transforms to accordion on mobile
 * - No jQuery or other dependencies
 * - Modern browsers only (ES6+)
 */

 
Key Features
Proper nesting support: Uses scoped selectors (:scope) to ensure tabs only find their own children
True responsiveness: Transforms to accordion style below the breakpoint (768px by default)
No dependencies: Vanilla JS with no jQuery required
Modern browsers: Uses ES6 features for cleaner code
Simple API: Easy to initialize with minimal code
This implementation should solve all your issues with responsiveTabs. It's lightweight, properly handles nesting, and transforms to accordions on mobile.


-->