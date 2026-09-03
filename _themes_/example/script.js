if (typeof ImageEditor !== 'undefined')
    ImageEditor.editable_images('.editable-image-png', '/control_panel/ajax/save_image/type=logo', 'png');

// Toggle claro/oscuro. Convenio compartido con los themes default y noxtr:
// data-theme en <html> y <body>, clase dark-theme en body (la usan css de
// modulos) y preferencia en localStorage. El boton #theme-toggle lo pinta
// _themes_/default/index.top.php, que este theme incluye.
(function () {
    var htmlEl = document.documentElement;
    var btn;

    function apply(theme, persist) {
        var safe = theme === 'dark' ? 'dark' : 'light';
        htmlEl.setAttribute('data-theme', safe);
        document.body.setAttribute('data-theme', safe);
        document.body.classList.toggle('dark-theme', safe === 'dark');
        if (persist) try { localStorage.setItem('theme', safe); } catch (e) {}
        var icon = btn && btn.querySelector('i');
        if (icon) icon.className = safe === 'dark' ? 'fa fa-sun-o' : 'fa fa-moon-o';
    }

    function init() {
        btn = document.getElementById('theme-toggle');
        // El pre-paint de index.php ya decidio el tema; aqui solo se sincroniza.
        apply(htmlEl.getAttribute('data-theme'), false);
        if (btn) btn.addEventListener('click', function () {
            apply(htmlEl.getAttribute('data-theme') === 'dark' ? 'light' : 'dark', true);
        });
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();
})();
