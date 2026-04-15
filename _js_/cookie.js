function setCookie(nombre, valor, caducidad) {
	//jQuery.cookie(nombre,valor,{expires: 365});
    var d = new Date();
    d.setTime(d.getTime() + (caducidad * 24 * 60 * 60 * 1000));
    var expires = "expires="+d.toUTCString();
    document.cookie = nombre + "=" + valor + ";" + expires + ";path=/;samesite=lax";
}

function unsetCookie(nombre) {
	//jQuery.cookie(nombre,null);
    setCookie(nombre, null, 1);
}

function getCookie(nombre,default_value='') {/*
    //return jQuery.cookie(nombre);
    var name = nombre + "=";
    var ca = document.cookie.split(';');
    for(var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
          c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
          return c.substring(name.length, c.length);
        }
    }*/
    return default_value;
}

function saveCookie(NombreCookie,valor,caducidad=365) {    
  	if(accept_cookies || getCookie('accept_cookies')=='yes') setCookie(NombreCookie, valor, caducidad);
}

function deleteCookie(NombreCookie) {    
    document.cookie = NombreCookie + "=;expires=Thu, 01-Jan-70 00:00:01 GMT;path=/;samesite=lax";
    //$.removeCookie(NombreCookie, { path: '/' });
}

/***
function accept_cookies(){
    $.ajax({
        type: "POST",
        url: '/cookies',
        dataType: "json",
        success: function(response) {
            $('.cookies').css('display','none')
        }
    });
}
**/