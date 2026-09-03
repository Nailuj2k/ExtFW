var clicked = '';

if (typeof ImageEditor !== 'undefined')
    ImageEditor.editable_images('.editable-image-png', '/control_panel/ajax/save_image/type=logo', 'png');

// ==================== TOGGLE DARK / LIGHT ====================
// Mismo convenio que el theme noxtr: data-theme en <html> y <body>,
// clase dark-theme en body (la usan los css de módulos), localStorage 'theme'.
(function() {
    var htmlEl = document.documentElement;

    function applyTheme(theme, persist) {
        var safe = theme === 'dark' ? 'dark' : 'light';
        htmlEl.setAttribute('data-theme', safe);
        document.body.setAttribute('data-theme', safe);
        document.body.classList.toggle('dark-theme', safe === 'dark');
        if (persist) { try { localStorage.setItem('theme', safe); } catch (e) {} }
        var btn = document.getElementById('theme-toggle');
        if (btn) {
            var icon = btn.querySelector('i');
            if (icon) icon.className = safe === 'dark' ? 'fa fa-sun-o' : 'fa fa-moon-o';
        }
    }

    function init() {
        var saved = null;
        try { saved = localStorage.getItem('theme'); } catch (e) {}
        // El pre-paint del <head> ya fijó data-theme en <html>; aquí solo sincronizamos body/botón
        applyTheme(saved || htmlEl.getAttribute('data-theme') || 'light', false);
        var btn = document.getElementById('theme-toggle');
        if (btn) btn.addEventListener('click', function() {
            var current = htmlEl.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
            applyTheme(current === 'dark' ? 'light' : 'dark', true);
        });
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();


    const inputSearch = document.querySelector('#top #input-search');
    let searchResults = document.querySelector('#search-results');


    if (inputSearch) {
        inputSearch.addEventListener('keyup', function() {
            debouncedSearch();
        });
    }


     function debounceSearch(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    const debouncedSearch = debounceSearch(doSearch, 300);

    function doSearch() {
        const searchValue = inputSearch.value;
        if (searchValue) {
            
            // $('.ajax-loader').show();

            $.ajax({
               method: "POST",
               url: 'openai/ajax',
               data: { 'query': 'busca '+searchValue},
              //data: { 'query': 'busca '+encodeURIComponent(searchValue)},
               dataType: "json",
               beforeSend: function( xhr, settings ) {
                  //////////////////////$('#resultao').html('calculando..'); 
                  //$('#ajaxmonitor').html('<img src="<?=$imagespath?>gears.gif">');
               }
            }).done(function( data ) {

                    //console.log('RESPONSE',data);
                    var s = '';

                    if (data.error && data.error.message) {
                        s =  "Error: " + data.error.message;
                    }  else if (data.choices && data.choices[0].message.content) {
                        s = data.choices[0].message.content;
                    }

                    if (s !== "") {
                        searchResults.innerHTML = s;
                    }

            }).fail(function(data) {
                
                //showMessageError( "error" );

                let ddd = data.responseText;
                console.log('FAIL.DATA',ddd);
                searchResults.append('ERROR'+ddd);

            }).always(function(data) {
                 console.log('ALWAYS',data);
                 // $('.ajax-loader').hide();

            });

            //searchResults.append('/search/' + encodeURIComponent(searchValue));
            searchResults.style.display = 'block';
        }
    }

    if (inputSearch) {
        inputSearch.addEventListener('keypress', function(event) {
            if (event.key === 'Enter') {
                doSearch();
            }
        });
    }
    
    searchResults.addEventListener('mouseleave', function() {
        searchResults.style.display = 'none';
    });
    
    inputSearch.addEventListener('mouseenter', function() {
       // if (searchResults.innerHTML !== '') {
            searchResults.style.display = 'block';
        //}
    });
    
})();
