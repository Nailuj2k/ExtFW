/*
$(document).ready(function(){
	
	//Menu toggle Button
	$('.nav-button').click(function() {
		$('body').toggleClass('show_menu');
		$('.nav-wrap ul.top_nav').slideToggle();
	});

	//Append down-arrow Span
	$('ul.top_nav > li:has(ul)').addClass('has-submenu').append('<span class="down-arrow"></span>');
	$('li.has-submenu ul > li:has(ul)').addClass('has-submenu').append('<span class="down-arrow"></span>');

	//Navigation Menu 

 	//Multi level
	$(".top_nav li:has(ul)").on("click",function(e){  

		console.log('SUBMENU')
		e.stopPropagation();
		$(this).children('ul').slideToggle(400);

	});

})
*/


// Helper functions for slideToggle, slideUp, slideDown
function slideUp(element, duration = 400) {
    element.style.transitionProperty = 'height, margin, padding';
    element.style.transitionDuration = duration + 'ms';
    element.style.boxSizing = 'border-box';
    element.style.height = element.offsetHeight + 'px';
    element.offsetHeight; // Trigger reflow
    element.style.overflow = 'hidden';
    element.style.height = '0';
    element.style.paddingTop = '0';
    element.style.paddingBottom = '0';
    element.style.marginTop = '0';
    element.style.marginBottom = '0';
    window.setTimeout(() => {
        element.style.display = 'none';
        element.style.removeProperty('height');
        element.style.removeProperty('padding-top');
        element.style.removeProperty('padding-bottom');
        element.style.removeProperty('margin-top');
        element.style.removeProperty('margin-bottom');
        element.style.removeProperty('overflow');
        element.style.removeProperty('transition-duration');
        element.style.removeProperty('transition-property');
    }, duration);
}

function slideDown(element, duration = 400) {
    element.style.removeProperty('display');
    let display = window.getComputedStyle(element).display;
    if (display === 'none') {
        // Attempt to get a sensible default display value
        // For UL or LI, 'block' is usually appropriate.
        if (['UL', 'LI', 'DIV'].includes(element.tagName)) {
            display = 'block';
        } else {
            // Fallback for other elements, though 'block' might often be fine.
            // Or, store original display style if possible before hiding.
            display = 'block'; 
        }
    }
    element.style.display = display;

    let height = element.scrollHeight;
    element.style.overflow = 'hidden';
    element.style.height = '0';
    element.style.paddingTop = '0';
    element.style.paddingBottom = '0';
    element.style.marginTop = '0';
    element.style.marginBottom = '0';
    element.offsetHeight; // Trigger reflow
    element.style.boxSizing = 'border-box';
    element.style.transitionProperty = "height, margin, padding";
    element.style.transitionDuration = duration + 'ms';
    element.style.height = height + 'px';
    element.style.removeProperty('padding-top');
    element.style.removeProperty('padding-bottom');
    element.style.removeProperty('margin-top');
    element.style.removeProperty('margin-bottom');
    window.setTimeout(() => {
        element.style.removeProperty('height');
        element.style.removeProperty('overflow');
        element.style.removeProperty('transition-duration');
        element.style.removeProperty('transition-property');
    }, duration);
}

function slideToggle(element, duration = 400) {
    if (window.getComputedStyle(element).display === 'none') {
        return slideDown(element, duration);
    } else {
        return slideUp(element, duration);
    }
}

document.addEventListener('DOMContentLoaded', function() {

    /*Menu toggle Button*/
    const navButtons = document.querySelectorAll('.nav-button');
    navButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {

            console.log('SLIDETOGGLE');
            e.stopPropagation();
            e.preventDefault();
            
            document.body.classList.toggle('show_menu');
            const topNav = document.querySelector('.nav-wrap ul.top_nav');
            if (topNav) {
                slideToggle(topNav);
            }
        });
    });

    /*Append down-arrow Span*/
    // For ul.top_nav > li that have a sub-ul
    document.querySelectorAll('ul.top_nav > li').forEach(function(li) {
        if (li.querySelector('ul')) { // Check if it has a 'ul' descendant
            li.classList.add('has-submenu');
            const span = document.createElement('span');
            span.className = 'down-arrow';
            li.appendChild(span);
        }
    });

    // For li.has-submenu ul > li that have a sub-ul (nested submenus)
    document.querySelectorAll('li.has-submenu ul > li').forEach(function(li) {
        if (li.querySelector('ul')) { // Check if it has a 'ul' descendant
            li.classList.add('has-submenu');
            const span = document.createElement('span');
            span.className = 'down-arrow';
            li.appendChild(span);
        }
    });

    /*Navigation Menu */
    /*Multi level*/
    document.querySelectorAll(".top_nav li").forEach(function(liElement) {
        // Check if this li has a DIRECT child ul
        const directChildUl = Array.from(liElement.children).find(child => child.tagName === 'UL');

        if (directChildUl) {
            liElement.addEventListener('click', function(e) {
                // 'this' refers to liElement
                // Stop the event from bubbling up to parent LIs, which might also have this listener
                e.stopPropagation();
                slideToggle(directChildUl, 400);
            });
        }
    });
});