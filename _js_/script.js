
// Debug mode - cambiar a false en producción
var CONSOLE_DEBUG = false;

// Auto-submit PIN cuando se completan 4 dígitos (cambiar a true para activar)
const PASSWORDLESS_AUTO_SUBMIT_PIN = false;


// ============================================
// CSRF Token Interceptor para fetch()
// ============================================
// Intercepta todas las llamadas a fetch() y añade automáticamente
// el token CSRF cuando se hace un POST
// NOTA: Este interceptor es temporal mientras se migran todos los fetch() a $.ajax()

(function() {
    const originalFetch = window.fetch;

    window.fetch = function(url, options = {}) {
        // Solo modificar si es POST y existe _TOKEN_
        if (options.method === 'POST' && typeof _TOKEN_ !== 'undefined') {

            // Si hay body, intentar añadir el token
            if (options.body) {

                // Si body es FormData
                if (options.body instanceof FormData) {
                    if (!options.body.has('token') && !options.body.has('csrf_token')) {
                        options.body.append('token', _TOKEN_);
                        console_log('fetch interceptor: token añadido a FormData');
                    }
                }
                // Si body es URLSearchParams
                else if (options.body instanceof URLSearchParams) {
                    if (!options.body.has('token') && !options.body.has('csrf_token')) {
                        options.body.append('token', _TOKEN_);
                        console_log('fetch interceptor: token añadido a URLSearchParams');
                    }
                }
                // Si body es string (puede ser JSON o form-urlencoded)
                else if (typeof options.body === 'string') {
                    try {
                        // Intentar parsear como JSON
                        const data = JSON.parse(options.body);
                        if (!data.token && !data.csrf_token) {
                            data.token = _TOKEN_;
                            options.body = JSON.stringify(data);
                            console_log('fetch interceptor: token añadido a JSON body');
                        }
                    } catch(e) {
                        // No es JSON, asumir form-urlencoded
                        if (!options.body.includes('token=') && !options.body.includes('csrf_token=')) {
                            options.body += (options.body ? '&' : '') + 'token=' + encodeURIComponent(_TOKEN_);
                            console_log('fetch interceptor: token añadido a string body');
                        }
                    }
                }
                // Si body es objeto (se convertirá a JSON)
                else if (typeof options.body === 'object') {
                    if (!options.body.token && !options.body.csrf_token) {
                        options.body.token = _TOKEN_;
                        console_log('fetch interceptor: token añadido a object body');
                    }
                }
            }
            // Si no hay body, crear uno con el token
            else {
                options.body = 'token=' + encodeURIComponent(_TOKEN_);
                // Asegurarse de que el Content-Type sea correcto
                if (!options.headers) {
                    options.headers = {};
                }
                if (!options.headers['Content-Type']) {
                    options.headers['Content-Type'] = 'application/x-www-form-urlencoded';
                }
                console_log('fetch interceptor: body creado con token');
            }
        }

        // Llamar al fetch original
        return originalFetch(url, options);
    };

    console_log('fetch interceptor instalado - CSRF tokens se añadirán automáticamente');
})();

async function loadContent(selector, url) {
    const el = document.querySelector(selector);
    if (el) el.innerHTML = await (await fetch(url)).text();
}


/**
 * Función auxiliar para logs de debug
 * @param {...any} args - Argumentos a pasar a console.log
 */
function console_log(...args) {
    if (CONSOLE_DEBUG) {
        console.log(...args);
    }
}

// For translations in js files
function t(template, ...values) {
    return template.replace(/\$?\{(\d+)\}/g, (_, i) => {
        return values[Number(i)] ?? '';
    });
} 

function trim(str, chars) {return ltrim(rtrim(str, chars), chars);}
//function ltrim(str, chars) { chars = chars || "\\s"; return str.replace(new RegExp("^[" + chars + "]+", "g"), "");}
//function rtrim(str, chars) { chars = chars || "\\s"; return str.replace(new RegExp("[" + chars + "]+$", "g"), "");}
function ltrim(str, chars) { return str;}
function rtrim(str, chars) { return str;}

String.prototype.capitalize = function(){
   return this.toLowerCase().replace( /(^|\s)([a-z])/g , function(m,p1,p2){ return p1+p2.toUpperCase(); } );
   // return this.charAt(0).toUpperCase() + this.slice(1);
};

var isMobile = window.matchMedia("only screen and (max-width: 500px)").matches;

/******
function shakeT(o) {
    var l = 20;  
    for( var i = 0; i < 6; i++ ) $( o ).animate( { 'margin-right': "+=" + ( l = -l ) + 'px' }, 50);  
}

function shake(o) {
    var l = 20;  
    for( var i = 0; i < 6; i++ ) $( o ).animate( { 'margin-left': "+=" + ( l = -l ) + 'px' }, 50);  
}
****/
function parseJson(jsonStr){
   var jsonObj = JSON.parse(jsonStr);
   var jsonPretty = JSON.stringify(jsonObj, null, '\t'); 
   return '<pre>'+jsonPretty+'</pre>';
}

function forceDownload(href) {
  	var anchor = document.createElement('a');
  	anchor.href = href;
  	anchor.download = href;
  	document.body.appendChild(anchor);
  	anchor.click();
}

function banner(id_banner,uri) {
    $.ajax( {url: "/control_panel/ajax/op=function/function=print_banner/table=GES_BANNERS", data:{"id": id_banner,"uri": uri},type:"POST",dataType: "json"} )
    .done(function(data) {
        //console.log( "success" );
        //console.log("SQL: " + data.sql); 
        setTimeout(function(){
           var element = (data.type<3) ? $('#banner_'+id_banner).html(data.html).fadeIn("slow").find("img") : $('#banner_'+id_banner).html(data.html).fadeIn("slow");
           element.click(function(){ 
               $.ajax( {url: "/control_panel/ajax/op=function/function=click/table=GES_BANNERS", data:{"id": id_banner,"uri": uri},type:"POST",dataType: "json"} )
               .done(function(data) {
                     //console.log('Click in '+id_banner) 
                     //console.log(url) 
               })
               .fail(function() {
                    //console.log( "error" );
               })
               .always(function() {
                    //console.log( "complete" );
               });
               forceDownload(data.link);
           });
        },300);
    })
    .fail(function() {
        //console.log( "error" );
    })
    .always(function() {
       //console.log( "complete" );
    });
}

function link_click(e) {
    let url = '/control_panel/ajax/op=function/function=click/table=CFG_CLICKS';
    console_log( url, e.data('tb'), e.data('link'), e.data('item') );
    $.ajax( {url: url, data:{"tablename": e.data('tb'),"id_item": e.data('item')},type:"POST",dataType: "json"} )
    .done(function(data) {
        //console.log(data);
        let href= e.data('link');
       	let anchor = document.createElement('a');
        anchor.href = href;
             if (href.indexOf("file/")>=0) $(anchor).attr("class", "open_file open_file_pdf").attr('data-href',href);
        else if(href.indexOf("http")>=0)   $(anchor).attr("target", "_blank");
        //anchor.download = href;
  	    document.body.appendChild(anchor);
  	    anchor.click();
    })
    .fail(function() {
        console.log('fail'); 
    })
    .always(function() {
        console_log('always');
    });

}

// https://codepen.io/shaikmaqsood/pen/XmydxJ
function copyToClipboard(element) {
  var $temp = $("<input>");
  $("body").append($temp);
  $temp.val($(element).text()).select();
  document.execCommand("copy");
  $temp.remove();
}





/*
 * * * * * * * * * * * * * * *
 *
 *
 *  show_message(type,element,msg,timeout,callback)
 *  show_error  (element,msg,timeout,callback)  
 *  show_info   (element,msg,timeout,callback)
 *  show_alert  (element,msg,timeout,callback)
 *
 *
 *  // Example
 *  show_message( 'info',                          //type ['info','error','alert']
 *                $('#element_id' ),
 *                '<p style="font-size:1.4em;">Message with with custom font size</p>',
 *                10000,                           // duration  [milliseconds,'never']
 *                function(e){                     // callback 
 *                    e.animate({'top':'+=200'});         
 *                })
 *
 *
 * * */
                     

function ready(fn) {
    if (document.readyState !== 'loading') {
        fn();
    } else {
        document.addEventListener('DOMContentLoaded', fn);
    }
}

// https://stackoverflow.com/questions/7616461/generate-a-hash-from-string-in-javascript
// function by bryc - https://stackoverflow.com/users/815680/bryc
const cyrb53 = function(str, seed = 0) {
    //console.log('STR',str);
    let h1 = 0xdeadbeef ^ seed, h2 = 0x41c6ce57 ^ seed;
    for (let i = 0, ch; i < str.length; i++) {
        ch = str.charCodeAt(i);
        h1 = Math.imul(h1 ^ ch, 2654435761);
        h2 = Math.imul(h2 ^ ch, 1597334677);
    }
    h1 = Math.imul(h1 ^ (h1>>>16), 2246822507) ^ Math.imul(h2 ^ (h2>>>13), 3266489909);
    h2 = Math.imul(h2 ^ (h2>>>16), 2246822507) ^ Math.imul(h1 ^ (h1>>>13), 3266489909);
    return 4294967296 * (2097151 & h2) + (h1>>>0);
};


/*
 *
 *   show_alert, show_error, show_info
 *   show_alert( $('#content'), 'hello!', 5000, () => {console.log('OnShow!')}, () => {console.log('OnClose!') }  ); 
 *
 *
 * * * * * */

//var timeout=2000,onShow=false;
function show_error  (element,msg,timeout,onShow,onClose){    show_message('error',  element,msg,timeout,onShow,onClose);}
function show_danger (element,msg,timeout,onShow,onClose){    show_message('danger', element,msg,timeout,onShow,onClose);}
function show_info   (element,msg,timeout,onShow,onClose){    show_message('info',   element,msg,timeout,onShow,onClose);}
function show_alert  (element,msg,timeout,onShow,onClose){    show_message('alert',  element,msg,timeout,onShow,onClose);}
function show_warning(element,msg,timeout,onShow,onClose){    show_message('warning',element,msg,timeout,onShow,onClose);}
function show_success(element,msg,timeout,onShow,onClose){    show_message('success',element,msg,timeout,onShow,onClose);}

const message_templates = [];

message_templates['error']   = '<span id="dialog-error" style="display:none;position:fixed;box-sizing: border-box;background-color:#f45e74;/*var(--red);*/color:white;padding:20px;z-index:4000000;cursor:pointer;font-family: var(--font-family-sans-serif);"></span>';
message_templates['danger']  = '<span id="dialog-alert" style="display:none;position:fixed;box-sizing: border-box;background-color:var(--red);color:white;padding:20px;z-index:4000000;cursor:pointer;font-family: var(--font-family-sans-serif);"></span>';
message_templates['info']    = '<span id="dialog-info"  style="display:none;position:fixed;box-sizing: border-box;background-color:#23af66;/*var(--green);*/color:white;padding:20px;z-index:4000000;cursor:pointer;font-family: var(--font-family-sans-serif);"></span>';
message_templates['alert']   = '<span id="dialog-alert" style="display:none;position:fixed;box-sizing: border-box;background-color:#fee432;/*var(--yellow);*/color:#484000;padding:20px;z-index:4000000;cursor:pointer;font-family: var(--font-family-sans-serif);"></span>';
message_templates['warning'] = '<span id="dialog-alert" style="display:none;position:fixed;box-sizing: border-box;background-color:var(--orange);color:#484000;padding:20px;z-index:4000000;cursor:pointer;font-family: var(--font-family-sans-serif);"></span>';
message_templates['success'] = '<span id="dialog-alert" style="display:none;position:fixed;box-sizing: border-box;background-color:var(--blue);color:white;padding:20px;z-index:4000000;cursor:pointer;font-family: var(--font-family-sans-serif);"></span>';
/*
const message_icons = [];
message_icons['error']   ='<i style="position:absolute;top:4px; left:4px;font-size:52px;opacity:0.15;" class="fa fa-bug"></i>';
message_icons['danger']  ='<i style="position:absolute;top:4px; left:4px;font-size:52px;opacity:0.15;" class="fa fa-bug"></i>';
message_icons['info']    ='<i style="position:absolute;top:4px; left:4px;font-size:52px;opacity:0.15;" class="fa fa-info-circle"></i>';
message_icons['alert']   ='<i style="position:absolute;top:4px; left:4px;font-size:52px;opacity:0.15;color:red;" class="fa fa-exclamation-triangle"></i>';
message_icons['warning'] ='<i style="position:absolute;top:4px; left:4px;font-size:52px;opacity:0.15;" class="fa fa-exclamation-triangle"></i>';
message_icons['success'] ='<i style="position:absolute;top:4px; left:4px;font-size:52px;opacity:0.15;" class="fa fa-check-square-o"></i>';
*/
/*
.xalert .xalert-icon{position:absolute;top:12px; left:12px;font-size:2em;}
.xalert .xalert-spin{font-size:.8em;}
.xalert .xalert-message{display: block;margin:10px 10px 10px 50px;line-height:1.1em;}
*/
const message_timeouts = [];

function show_message(type,element,msg,timeout,onShow,onClose,removeAll=false){
    //notify(msg);//,20000)
    console.log('ELEMENT',element)
   // document.querySelectorAll('.wq-dialog-overlay').forEach(el => el.remove());
    if(removeAll) $.dialog.removeAll('.target');
    notify( msg, type, timeout , element, onShow, onClose) 
}


function showMessage(msg,type,delay){
  if(msg){
    
    if(!type) type='info';
    if(!delay) delay=10000;
    notify( msg, type, delay , null);

  }
}

function showMessageError(msg)  {showMessage(/*'<i class="fa fa-bug"     aria-hidden="true"></i> '+*/msg,'error'  ,15000);}
function showMessageWarning(msg){showMessage(/*'<i class="fa fa-warning" aria-hidden="true"></i> '+*/msg,'warning',13000);}
function showMessageInfo(msg)   {showMessage(/*'<i class="fa fa-info"    aria-hidden="true"></i> '+*/msg,'info'   , 8000);}
function showMessageSuccess(msg){showMessage(/*'<i class="fa fa-check"   aria-hidden="true"></i> '+*/msg,'success', 8000);}





function OLD_show_message(type,element,msg,timeout,onShow,onClose){

    let btnclose = '<span style="position:absolute;top:5px;right:5px;color:white;"><i class="fa fa-times btn-close"></i></span>';  
    let identifier = 'msg_'+cyrb53(msg);
    let e = $(message_templates[type]);
    let css = {'position':'absolute','left':'0px','top':'0px','right':'0px','height':'auto'}
    let close = function (element){ 
        element.fadeOut(function(){
            if (onClose) onClose(element);
        })
        element.remove() 
    }

    if(trim(identifier)!='') 
        if($('#'+identifier).length>0) 
            close($('#'+identifier));

    if(!element) element=$('body');

    if(element=='bottom') {
        
        element = $('#content')
        css = {display:'block',position:'fixed',width:'100%',bottom:'10px',margin:'10px auto'};

    }else if(element=='top-right') {

        element = $('#content')
        css = {display:'block',position:'fixed',width:'auto',top:'10px',right:'10px',margin:'10px auto'};

    }else if(element=='center-top') {
        
        element = $('#content')
        css = {display:'block',position:'fixed',top:'25%',left:'50px',right:'50px',margin:'10px auto',textAlign:'center'} //,backgroundColor:'transparent'}

    }

    e.appendTo(element)
     .addClass('dialog-'+type)
     .html(message_icons[type]+msg+btnclose)
     .css(css)
     .attr('id',identifier)
     .fadeIn(function(){
       if (onShow) onShow(e);  
       if(timeout!=='never') {
            if(message_timeouts[identifier]) clearTimeout(message_timeouts[identifier]);
            message_timeouts[identifier] = setTimeout(function(){ 
                close(e);
            },timeout);
        }
      })
     .click( function(){
          close(e);
      })
     .find('.btn-close').click(function(){  
                            close(e);  
                        });

    //e.animate({'top':'+=120'});

}

const stopwords = ['y','de','por','del', 'la', 'el', 'lo', 'a', 'las', 'en'];
const fullStopWords = [
    'a', 'al', 'ante', 'bajo', 'cómo', 'con', 'cuando', 'de', 'del', 'desde', 
    'durante', 'e', 'el', 'ella', 'ellas', 'ellos', 'en', 'entre', 'es', 'esta', 
    'estas', 'este', 'esto', 'estos', 'fue', 'ha', 'han', 'hasta', 'la', 'las', 
    'lo', 'los', 'mediante', 'mientras', 'ni', 'o', 'para', 'pero', 'por', 'que', 
    'se', 'si', 'sin', 'sobre', 'su', 'sus', 'tras', 'tu', 'tus', 'un', 'una', 
    'unas', 'uno', 'unos', 'vos', 'vuestra', 'vuestras', 'vuestro', 'vuestros',
    'y'
  ];
const spanishStopWords = [
    'de', 'en', 'a', 'la', 'el', 'lo', 'las', 'los', 'un', 'una', 'unos', 'unas',
    'y', 'o', 'con', 'por', 'para', 'al', 'del', 'se', 'que', 'es', 'son', 'como',
    'su', 'sus', 'sin', 'sobre', 'bajo', 'ante', 'tras', 'durante', 'mediante'
];

function textToSlug(text, customStopWords = []) {
    const allStopWords = [...spanishStopWords, ...customStopWords];
    
    return text
      .toLowerCase()
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, "") // Elimina acentos
      .replace(/[^a-z0-9 ]/g, "") // Elimina caracteres especiales
      .trim()
      .split(/\s+/)
      .filter(word => !allStopWords.includes(word)) // Filtra stop words
      .join("-")
      .replace(/-+/g, "-");
}

function titleCase(str) {
    let splitStr = str.toLowerCase().split(' ');
    //console.log(splitStr);
    for (var i = 0; i < splitStr.length; i++) {
        // You do not need to check if i is larger than splitStr length, as your for does that for you
        // Assign it back to the array
        //  console.log('*'+splitStr[i]+'*');
        if (stopwords.indexOf(splitStr[i])==-1)
            splitStr[i] = splitStr[i].charAt(0).toUpperCase() + splitStr[i].substring(1);     

        let n = splitStr[i].indexOf('-');
        if (n>-1) {
            splitStr[i]=splitStr[i].replace(
                splitStr[i].charAt(n)+splitStr[i].charAt(n+1),
                (splitStr[i].charAt(n)+splitStr[i].charAt(n+1)).toUpperCase()
            );
        }
    }
    // Directly return the joined string
    return splitStr.join(' '); 
}


function valid_email(email){ return  /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/.test(email);}
//function valid_email(email){ return /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/.test(email); }
function valid_name(name)  { return  /^[A-Za-z\ \-çÇáéíóúñÑÁÉÍÓÚÑ]{4,80}$/.test(name); }
function valid_phone(phone){ return  phone=='' || /^[0-9\-\+\ ]{9,12}$/.test(phone); }
function valid_zip(zip)    { return  zip=='' || /^[A-Z0-9\.\-]{5,10}$/.test(zip); }
function valid_address(address)  { return /^[A-Za-z,;_\.\ \/\-º°ª0-9()çÇáéíóúñÑÁÉÍÓÚÑ]{8,100}$/.test(address); }
function valid_card_id(card_id)  { return /^[A-Za-z0-9\.\-]{5,10}$/.test(card_id); }



function parseURL(url) {
              // - Supported YouTube URL formats:
              //   - http://www.youtube.com/watch?v=My2FRPA3Gf8
              //   - http://youtu.be/My2FRPA3Gf8
              //   - https://youtube.googleapis.com/v/My2FRPA3Gf8
              // - Supported Vimeo URL formats:
              //   - http://vimeo.com/25451551
              //   - http://player.vimeo.com/video/25451551
              // - Also supports relative URLs:
              //   - //player.vimeo.com/video/25451551

              //https://vimeo.com/248148941

              url.match(/(http:|https:|)\/\/(player.|www.)?(vimeo\.com|youtu(be\.com|\.be|be\.googleapis\.com))\/(video\/|embed\/|watch\?v=|v\/)?([A-Za-z0-9._%-]*)(\&\S+)?/);

              if (RegExp.$3.indexOf('youtu') > -1) {
                  var type = 'youtube';
                  var id = RegExp.$6;
              } else if (RegExp.$3.indexOf('vimeo') > -1) {
                  var type = 'vimeo';
                  var id = RegExp.$6;
              }

              if(!type){
                 url = url+'/';
                 var match = url.match(/([a-zA-Z0-9_-]{16,})[$/&?]/i);
                 //console.log( match ? match[1] : 'null');
                 if(match){
                     // console.log('URL',url,url.indexOf('file') > 0);
                     //console.log(url.indexOf('file') == true ? 'google_drive_file' : 'google_drive_folder');  
                     var type =  (url.indexOf('file')) > 0  
                              ? 'google_drive_file' 
                              : (url.indexOf('folder')) > 0 
                                 ? 'google_drive_folder' 
                                 : (url.indexOf('forms.gle')) > 0 
                                    ? 'google_form' 
                                    : (url.indexOf('presentation')) > 0 
                                       ? 'google_presentation' : -1;

                     var id = match[1];
                 }

                // console.log('notype');
              }

              return {
                  type: type,
                  id: id
              };
}


    //
    // 
    // El elemento id necesita un div anidado para que esto funcione.
    // Ejemplo:
    // '<div id="my-id"><div>Texto que se reducirá hasta que se vea entero en #my-id</div></div>'
    // 
    // adjust_font_size('my-id');
    // Ajustamos sólo la altura, pues la anchura se define en el css
    //
/*
    function adjust_font_size_to_height(element) {
                //console.log('ELEMENT',element)
                let child = $(element).find('div');
                let fontsize = child.css('font-size');
                child.css('fontSize', parseFloat(fontsize) - 1);
                if (child.height() >= $(element).height()) {
                    adjust_font_size_to_height(element);
                }
    }
*/
// Función mejorada para ajustar el tamaño de fuente a la altura
function adjust_font_size_to_height(element) {
    // Usamos una función autoejecutada para mantener un contador privado
    return (function adjustWithCounter(el, attempts = 0) {
        // Límite de intentos para evitar bucles infinitos
        if (attempts > 50) return;
        
        const $el = $(el);
        const $child = $el.find('div').first();
        
        // Si no hay elementos hijos, salir
        if ($child.length === 0) return;
        
        // Obtener el tamaño actual de fuente y reducirlo
        const currentSize = parseFloat($child.css('font-size'));
        $child.css('fontSize', (currentSize - 0.5) + 'px');
        
        // Si la altura del hijo sigue siendo mayor que la del contenedor, recurrir
        if ($child.outerHeight() > $el.height()) {
            return adjustWithCounter(el, attempts + 1);
        }
        
        return attempts; // Devuelve el número de intentos para diagnóstico
    })(element);
}


document.addEventListener('DOMContentLoaded', function() {

   setTimeout(function(){
    // Convertir enlaces de la galería a array
        const galleryLinks = document.querySelectorAll('[rel="g2"]');
        
        // Preparar array de imágenes para la galería
        const images = Array.from(galleryLinks).map(link => {
            return {
                type: 'image',
                content: link.getAttribute('href'),
                title: link.getAttribute('title') || link.getAttribute('alt')
            };
        });

                // Crear la galería
        const gallery = ImageGallery.createGallery(images, {
            showCounter: true,
            slideAnimation: true,
            fullscreen: false
        });

        // Manejar clics en las imágenes
        galleryLinks.forEach((link, index) => {
            link.addEventListener('click', function(e) {
                e.preventDefault();

                gallery.show(index);
            });
        });

   },1000);

});


$(function(){
    //////////////////////////////$(".swipebox").swipebox({selector:'.swipebox',hideBarsDelay : 0,});
   //$(function(){  $(".form-tabs").tabs();  });
   
   //const images = 
/***************************
   setTimeout(function(){
          galleryFromSelector('.swipebox'); //`[rel="gallery"]`);
          galleryFromSelector('.open_file_image'); 
   },1000);
*******************/



   // $('[rel="g2"].open_file_image').removeClass('open_file_image')
   // galleryFromSelector('[rel="g2"]');


   /** *
   $('body').on('click','.swipebox',function(e){
    e.preventDefault();
    let file_img = $(this).attr('href') || $(this).data('href');
    let file_img_title = $(this).attr('title') || $(this).data('title') || $(this).attr('alt')||file_img;
    J("body").dialog({
      title: file_img_title,
      type:'image',
      width: "auto",
      height: "auto",
      buttons: [ { text: "Cerrar",action: function(event, overlay) { document.body.removeChild(overlay); } } ],
      content: file_img
    });
  });
   **/
});

function load_widget(widget_id,widget_name,params){
    $.get(widget_name+'/'+($params?$params+'/':'')+'html',function(data){
        $('#widget-'+widget_id).html(data);
    }).fail(function(){
        $('#widget-'+widget_id).html('error');
    });
}





//
//  https://jsfiddle.net/12aueufy/1/
//
var shakingElements = [];

var shake = function (element, magnitude = 16, angular = false) {
  //First set the initial tilt angle to the right (+1) 
  var tiltAngle = 1;

  //A counter to count the number of shakes
  var counter = 1;

  //The total number of shakes (there will be 1 shake per frame)
  var numberOfShakes = 60;

  //Capture the element's position and angle so you can
  //restore them after the shaking has finished
  var startX = 0,
      startY = 0,
      startAngle = 0;

  // Divide the magnitude into 10 units so that you can 
  // reduce the amount of shake by 10 percent each frame
  var magnitudeUnit = magnitude / numberOfShakes;

  //The `randomInt` helper function
  var randomInt = (min, max) => {
    return Math.floor(Math.random() * (max - min + 1)) + min;
  };

  //Add the element to the `shakingElements` array if it
  //isn't already there
  if(shakingElements.indexOf(element) === -1) {
    //console.log("added")
    shakingElements.push(element);

    //Add an `updateShake` method to the element.
    //The `updateShake` method will be called each frame
    //in the game loop. The shake effect type can be either
    //up and down (x/y shaking) or angular (rotational shaking).
    if(angular) {
      angularShake();
    } else {
      upAndDownShake();
    }
  }

  //The `upAndDownShake` function
  function upAndDownShake() {

    //Shake the element while the `counter` is less than 
    //the `numberOfShakes`
    if (counter < numberOfShakes) {

      //Reset the element's position at the start of each shake
      element.style.transform = 'translate(' + startX + 'px, ' + startY + 'px)';

      //Reduce the magnitude
      magnitude -= magnitudeUnit;

      //Randomly change the element's position
      var randomX = randomInt(-magnitude, magnitude);
      var randomY = randomInt(-magnitude, magnitude);

      element.style.transform = 'translate(' + randomX + 'px, ' + randomY + 'px)';

      //Add 1 to the counter
      counter += 1;

      requestAnimationFrame(upAndDownShake);
    }

    //When the shaking is finished, restore the element to its original 
    //position and remove it from the `shakingElements` array
    if (counter >= numberOfShakes) {
      element.style.transform = 'translate(' + startX + ', ' + startY + ')';
      shakingElements.splice(shakingElements.indexOf(element), 1);
    }
  }

  //The `angularShake` function
  function angularShake() {
    if (counter < numberOfShakes) {
      ///////console.log(tiltAngle);
      //Reset the element's rotation
      element.style.transform = 'rotate(' + startAngle + 'deg)';

      //Reduce the magnitude
      magnitude -= magnitudeUnit;

      //Rotate the element left or right, depending on the direction,
      //by an amount in radians that matches the magnitude
      var angle = Number(magnitude * tiltAngle).toFixed(2);
      ///////////console.log(angle);
      element.style.transform = 'rotate(' + angle + 'deg)';
      counter += 1;

      //Reverse the tilt angle so that the element is tilted
      //in the opposite direction for the next shake
      tiltAngle *= -1;

      requestAnimationFrame(angularShake);
    }

    //When the shaking is finished, reset the element's angle and
    //remove it from the `shakingElements` array
    if (counter >= numberOfShakes) {
      element.style.transform = 'rotate(' + startAngle + 'deg)';
      shakingElements.splice(shakingElements.indexOf(element), 1);
      //console.log("removed")
    }
  }

};

/******
  //shake(document.querySelector('.login-login'),20,true);
    document.querySelectorAll('#loginformXX,#registerform,.login-login').forEach((el, i) => {shake(el)});
  //document.querySelectorAll('i,img,p,a,b,li').forEach((el, i) => {shake(el)});

document.querySelector('.red').addEventListener('mouseenter', (e) => {
  shake(e.currentTarget);
});

*****/


// Funciones auxiliares para la animación
function slideUp(element) {
    element.style.height = element.offsetHeight + 'px';
    element.offsetHeight; // Force reflow
    element.style.transition = 'height 0.3s ease-out';
    element.style.height = '0';
    element.style.overflow = 'hidden';
    
    setTimeout(() => {
        element.style.display = 'none';
        element.style.height = '';
        element.style.transition = '';
        element.style.overflow = '';
    }, 300);
}

function slideDown(element) {
    element.style.display = 'block';
    const height = element.offsetHeight;
    element.style.height = '0';
    element.style.overflow = 'hidden';
    element.style.transition = 'height 0.3s ease-out';
    element.offsetHeight; // Force reflow
    element.style.height = height + 'px';
    
    setTimeout(() => {
        element.style.height = '';
        element.style.transition = '';
        element.style.overflow = '';
    }, 300);
}

function formatBytesColorized(size) {
    const mb = 1024 * 1024;
    const gb = mb * 1024;
 
    if      (size > gb)      return `<span style="color:var(--yellow);background-color:var(--red);">${(size/gb).toFixed(2)}<b>G</b></span>`;
    else if (size > (mb*10)) return `<span style="color:var(--yellow);background-color:var(--red);">${(size/mb).toFixed(2)}<b>M</b></span>`;
    else if (size > (mb*2))  return `<span style="color:var(--red);">${(size/mb).toFixed(2)}<b>M</b></span>`;
    else if (size > mb)      return `<span style="color:var(--orange);">${(size/mb).toFixed(2)}<b>M</b></span>`;
    else if (size >= 1024)   return `<span style="color:silver;">${(size/1024).toFixed(2)}<b>K</b></span>`;
    else                     return `<span style="color:var(--gray);">${size}<b>b</b></span>`;
    
}

// FROM ChatGPT
// Agregar método addClass al prototipo de Element
Element.prototype.addClass = function (className) {
    if (!className) return this;
    this.classList.add(...className.split(" "));
    return this;
};

// Agregar método removeClass al prototipo de Element
Element.prototype.removeClass = function (className) {
    if (!className) return this;
    this.classList.remove(...className.split(" "));
    return this;
};

// Agregar método setClass al prototipo de Element
Element.prototype.setClass = function (className, condition) {
    if (!className) return this;
    if (condition) this.addClass(className)
              else this.removeClass(className);
    return this;
};

// Extender NodeList para manejar múltiples elementos
NodeList.prototype.addClass    = function (className)            {  this.forEach(el => el.addClass(className));            return this;};
NodeList.prototype.removeClass = function (className)            {  this.forEach(el => el.removeClass(className));         return this;};
NodeList.prototype.setClass    = function (className, condition) {  this.forEach(el => el.setClass(className, condition)); return this;};


function fadeOut(element, duration=1000) {
    const animation = element.animate([
        { opacity: 1 },
        { opacity: 0 }
    ], {
        duration: duration,
        easing: 'linear'
    });
    animation.onfinish = () => {
        element.style.display = 'none';
    };
}

function fadeIn(element, duration=1000, display='block') {
    element.style.display = display;
    element.animate([
        { opacity: 0 },
        { opacity: 1 }
    ], {
        duration: duration,
        easing: 'linear'
    });
}


$.fn.highlight = function () {  //http://stackoverflow.com/questions/848797/yellow-fade-effect-with-jquery
    $(this).each(function () {
        var el = $(this);
        $("<div/>")
        .width(el.outerWidth())
        .height(el.outerHeight())
        .css({
            "position": "absolute",
            "left": el.offset().left,
            "top": el.offset().top,
            "background-color": "#ffff99",
            "opacity": ".95",
            "z-index": "9999999"
        }).appendTo('body').fadeOut(1000).queue(function () { $(this).remove(); });
    });
}
$.fn.highlightSlow = function () {  //http://stackoverflow.com/questions/848797/yellow-fade-effect-with-jquery
    $(this).each(function () {
        var el = $(this);
        $("<div/>")
        .width(el.outerWidth())
        .height(el.outerHeight())
        .css({
            "position": "absolute",
            "left": el.offset().left,
            "top": el.offset().top,
            "background-color": "#fde8f9",
            "opacity": ".40",
            "z-index": "9999999"
        }).appendTo('body').fadeOut(2000).queue(function () { $(this).remove(); });
    });
}


function _tooltip(element,options) {
    /*
    options??={
        theme: 'tomato',
        content: (o) => o.getAttribute('title'),
    };
    */
    //tippy(element, options);
    
    tooltip(element, { theme: 'tomato', attribute: 'title' });

}






    /*
            tippy('.btn', {
            //followCursor: true,
            theme: 'tomato',
            content: (o) => o.getAttribute('title'),
        });
*/

/*
function logoutFromGoogle() {
    // This loads a hidden iframe that logs the user out from Google
    var iframe = document.createElement('iframe');
    iframe.style.display = 'none';
    iframe.src = 'https://accounts.google.com/logout';
    document.body.appendChild(iframe);
    
    // You can remove the iframe after a delay
    setTimeout(function() {
        document.body.removeChild(iframe);
    }, 5000);
}

*/







        /**
             * Crea efecto de chispas/partículas alrededor de un elemento
             */
            function createSparkles(element, vote) {
                const rect = element.getBoundingClientRect();
                const centerX = rect.left + rect.width / 2;
                const centerY = rect.top + rect.height / 2;
                
                // Número de partículas
                const particleCount = 8;
                
                // Color según el tipo de voto
                let color;
                if (vote === 'up') {
                    color = '#28a745';
                } else if (vote === 'down') {
                    color = '#e3183d';
                } else {
                    color = '#000000';
                }
                
                for (let i = 0; i < particleCount; i++) {
                    createParticle(centerX, centerY, color);
                }
            }

            /**
             * Crea una partícula individual
             */
            function createParticle(x, y, color) {
                const particle = document.createElement('div');
                particle.className = 'sparkle-particle';
                particle.style.left = x + 'px';
                particle.style.top = y + 'px';
                particle.style.backgroundColor = color;
                
                // Ángulo aleatorio para la dirección
                const angle = Math.random() * Math.PI * 2;
                const velocity = 50 + Math.random() * 30; // Velocidad aleatoria
                const size = 3 + Math.random() * 4; // Tamaño aleatorio
                
                particle.style.width = size + 'px';
                particle.style.height = size + 'px';
                
                // Calcular desplazamiento final
                const deltaX = Math.cos(angle) * velocity;
                const deltaY = Math.sin(angle) * velocity;
                
                particle.style.setProperty('--deltaX', deltaX + 'px');
                particle.style.setProperty('--deltaY', deltaY + 'px');
                
                document.body.appendChild(particle);
                
                // Animar y eliminar
                setTimeout(() => {
                    particle.style.transform = `translate(var(--deltaX), var(--deltaY)) scale(0)`;
                    particle.style.opacity = '0';
                }, 10);
                
                setTimeout(() => {
                    particle.remove();
                }, 600);
            }
