$(document).ready(function() {


    function complete(data) {
        $('#result').html(data.msg);
    }

    setTimeout(function(){
       $('.highlight').highlightSlow();
       setInterval(function(){
          $('.highlight').highlightSlow();
       },5000);

    },2000);
 
    /******************
    $('#username,#password').change(function(){
        console.log('CHANGE',$('#username').val());
        console.log('CHANGE',$('#password').val());
        if ($(this).val())  $(this).addClass('pop-outin'); else $(this).removeClass('pop-outin');
    });

    setTimeout(function(){
       $('#username,#password').change();
    },2000);

    setTimeout(function(){
       $('#username,#password').change();
    },3000);
    **********/

    $('#data-protect-info #btn-show-lpd').click(function(){ $('#data-protect-info p:not(:first-child)').toggle('fast'); });
            
});

function poptastic(url) {
    var newWindow = window.open(url, 'name', 'height=600,width=450');
    if (window.focus) {
        newWindow.focus();
    }
}

function popupwindow(url, title, w, h) {
    var left = (screen.width / 2) - (w / 2);
    var tops = (screen.height / 2) - (h / 2);
    return window.open(url, title, 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width=' + w + ', height=' + h + ', top=' + tops + ', left=' + left);
}

function PopupCenter(url, title, w, h) {
    var userAgent = navigator.userAgent,
        mobile = function() {
            return /\b(iPhone|iP[ao]d)/.test(userAgent) || /\b(iP[ao]d)/.test(userAgent) || /Android/i.test(userAgent) || /Mobile/i.test(userAgent);
        },
        screenX = typeof window.screenX != 'undefined' ? window.screenX : window.screenLeft,
        screenY = typeof window.screenY != 'undefined' ? window.screenY : window.screenTop,
        outerWidth = typeof window.outerWidth != 'undefined' ? window.outerWidth : document.documentElement.clientWidth,
        outerHeight = typeof window.outerHeight != 'undefined' ? window.outerHeight : document.documentElement.clientHeight - 22,
        targetWidth = mobile() ? null : w,
        targetHeight = mobile() ? null : h,
        V = screenX < 0 ? window.screen.width + screenX : screenX,
        left = parseInt(V + (outerWidth - targetWidth) / 2, 10),
        right = parseInt(screenY + (outerHeight - targetHeight) / 2.5, 10),
        features = [];
    if (targetWidth !== null) {
        features.push('width=' + targetWidth);
    }
    if (targetHeight !== null) {
        features.push('height=' + targetHeight);
    }
    features.push('left=' + left);
    features.push('top=' + right);
    features.push('scrollbars=1');
    var newWindow = window.open(url, title, features.join(','));
    if (window.focus) {
        newWindow.focus();
    }
    return newWindow;
}


var sync_ldap_url = '/login/ajax/op=sync-ldap';  
var busy = false;
function syncLDAP(){
    if(busy) return false; 
    busy = true;
    $.ajax({
        method: "POST",
        url: sync_ldap_url,  // data:order_data, 
        dataType: "json",
        beforeSend: function( xhr, settings ) {  $('.sync-ldap-ajax-loader').show(); }
    }).done(function( data ) {
        $('.table_roles').html(data.roles).highlight();
        $('.table_perms').html(data.perms).highlight();
        showMessageInfo( "Datos sincronizados" );
    }).fail(function() {
        showMessageError( "error" );
    }).always(function() {
        $('.sync-ldap-ajax-loader').hide();
        busy = false;
    })
}

$('#sync-ldap').click(function(){
    syncLDAP();
});                          
