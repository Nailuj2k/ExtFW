

if(typeof ImageEditor !== 'undefined')
    ImageEditor.editable_images('.editable-image-png','/control_panel/ajax/save_image/type=logo','png');  

(function () {
    const t = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', t);
})();
function toggleTheme() {
    const el = document.documentElement;
    const next = el.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    el.setAttribute('data-theme', next);
    localStorage.setItem('theme', next);
}