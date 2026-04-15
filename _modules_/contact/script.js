  $(document).ready(function() { 

    //console.log('CONTACT');
/*********
    var options = { 
        //target:    '#output',  
        beforeSubmit:  showRequest,  
        success:       showResponse, 
        error:         showError,
        complete:      onComplete,
        dataType:     'json'      
    };  
 ***************/
    $('.row').find('label').append('<span style="float:right;display:none;" class="text-after"></span>'); //.parent(); //.find('.text-after');

    $('.row').click(function () {
        //console.log('row',$('.row'));
        $('.row').removeClass('selected');
        $(this).addClass('selected');    
    });
 
    $('input,select,textarea,label,#captcha-reload').click(function () {
        $('.row').removeClass('selected');
        $(this).closest('.row').addClass('selected');       
    });

    $('#nombre,#email,#notas').keyup(function () {
         validate();
    });

    $('#nombre,#email,#notas,#chk_priv').change(function(){      
         validate();
    });

     //  https://programarivm.com/envia-por-ajax-el-nuevo-recaptcha-de-google-y-validalo-con-php/
     
    $('#contact').submit(function() { 
        if($('#btnsubmit').hasClass('disabled')) return false;
        $('.ajax-loader').show();
        console.log('submit');
        ///////////////////$(this).ajaxSubmit(options); 
        return false; 
    }); 
/*****************
    function showRequest(formData, jqForm, options) { 
        var queryString = $.param(formData); 
        console.log('showRequest',queryString);
        return true; 
    } 
    
    // post-submit callback 
    function showResponse(data, statusText, xhr, $form)  { 
        console.log('showResponse',data,statusText)
        if (data.error==1){
           // $("#contact .btnsubmit").show('fast');
            $('#help').css('visibility','visible');
            $('#error').html(data.msg).show('fast');
        }else{
            if(bum_effect) bum();
            setTimeout( function(){ $('form#contact').html(data.msg); },250);
        }
    }     

    function onComplete(xhr) {
        $('.ajax-loader').hide();
    }              

    function showError()  {
          $('form#contact').html('Error al enviar datos');
    }
*********************/
    function showmessage(e,type,msg){
        //var t = e.parent(); //.find('.text-after');
        var t = e.closest('.row').find('.text-after'); //.parent(); //.find('.text-after');
        //t.show('fast').attr('class', 'text-after').addClass(type);
        t.show('fast').addClass(type);
        if     (type=='success'){
            e.css('background-color','#dbeeff');
            t.html(' '+msg+' <i class="fa fa-check" style="color:#00e409;"></i>');
        }else if(type='error'){
            t.html(' '+msg+' <i class="fa fa-warning" style="color:#ff0000;"></i>');
            //e.focus();
            e.css('background-color','#f99999 !important');
       }else if(type='info'){
            e.css('background-color','#f2f0f0');
            t.html(' '+msg+' <i class="fa fa-warning" style="color:#a9aba7;"></i>');
        }
        setTimeout(function(){t.hide('slow')},5000);
    }

    function validate(){
        const un = /^[A-Za-z0-9.]{5,1000}$/;
        const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
      //const nm = /^\s*([a-zñáéíóú]{1,}([\.,] |[-']| ))+[a-zñáéíóú]+\.?\s*$/;
      //const nm = /^\s*([A-Za-zÑÁÉÍÓÚñáéíóú]{1,}([\.,] |[-']| ))+[A-Za-zÑÁÉÍÓÚñáéíóú]+\.?\s*$/;
        const nm = /^[A-Za-z\ áéíóúñÑÁÉÍÓÚÑ]{4,40}$/;
      //const tx = /^.{10,500}$/s;  // /[^A-Za-z0-9 .'?!,@$#\-_]/;
        const tx = /^[A-Za-z0-9. ÑÁÉÍÓÚñáéíóú()@"'ºª%&=?¿ç,;:\[\]\\\n\r]{5,1000}$/;
        var nombre = $('#nombre').val();
        var valid_email = re.test(String($('#email').val()).toLowerCase());
        var valid_name  = nm.test(nombre);
        var valid_text  = tx.test(String($('#notas').val()).toLowerCase());
        
        //  !(/[^A-Za-z0-9ñç .'?!,@$#-_\r\n$]/).test( String($('#notas').val() ) );
        //  OKIS   /^[A-Za-z0-9. ÑÁÉÍÓÚñáéíóú()@"'ç,;:\[\]\\\n\r]{5,1000}$/.test( String($('#notas').val() ) );
      //  var ok_priv = $('#chk_priv').is(":checked");
       //  var ok_priv = $('#chk_priv:checked').length==1;
         var ok_priv =  $('#chk_priv').length==0 || $('#chk_priv:checked').length==1;

        //console.log(valid_email,valid_name,valid_text,ok_priv);
        if(valid_email&&valid_name&&valid_text&&ok_priv){
            console.log('disabled');
            $('#btnsubmit').removeClass('disabled');
            //$('#btnsubmit').prop('disabled', false);
        }else{
            //console.log('enabled');
            if(nombre && !valid_name) showmessage($('#nombre'),'error','Nombre no válido');
            if($('#email').val() && !valid_email) showmessage($('#email'),'error','Email no válido');

            $('#btnsubmit').addClass('disabled');
            //$('#btnsubmit').prop('disabled', true);
        }
    }
    
   //  $('#link_priv').click(function(){      
   //      $('#checkout_warning').toggle('fast');
   //});

   
    setTimeout(function(){
        validate();
    },500);

    //$('#chk_priv').attr('checked','checked');
}); 
/**********************
    function onSubmitForm(token) {
        console.log('onSubmitForm');
        $('#error').html('...').hide('fast');
        $('#help').css('visibility','hidden');
        $("#contact").submit();
    }
*********/
    function onTableEvent(data) {



        if (data.error==1){
           // $("#contact .btnsubmit").show('fast');
            $('#help').css('visibility','visible');
            $('#error').html(data.msg).show('fast');
        }else{
            if(bum_effect) bum();
            setTimeout( function(){ $('.inner.contact').html(data.text); },250);
        }







        // Aquí puedes manejar eventos específicos de la tabla
        // Por ejemplo, actualizar una tabla o mostrar un mensaje
    }

//    console.log('onTableEvent', response);


/**
window.onhashchange = () => {
    //alert(`Hash changed -> ${window.location.hash}`)
    //return false;
}

<button onclick="window.location.hash=Math.random()">hash to Math.Random</button>

<button onclick="window.location.hash='ABC'">Hash to ABC</button>

<button onclick="window.location.hash='XYZ'">Hash to XYZ</button>
**/
/**
window.addEventListener('hashchange', doSomethingWithChangeFunction);


function doSomethingWithChangeFunction () {
    let urlParam = window.location.hash; // Get new hash value
 ///alert(urlParam);   
    // ... Do something with new hash value
};

**/

/** */
var n =0;
const sse = new SSEClient({
    url:      'https://queesbitcoin.net/sse',
    username: 'pepito',
    userid:   42,

    onConnect:    ()    => { console.log('conectado'); },
    onDisconnect: ()    => { console.log('desconectado'); },
    onError:      (msg) => { console.error(msg); },

    onPong: (data) => {
        n++;
        console.log('OnPong',data)
        // heartbeat: data.time, data.count, data.limit
    },

    onMessage: (data) => {
        console.log('OnMessage',data)
        // mensaje real: data.id, data.kind, data.username,
        //               data.domain, data.avatar, data.msg, data.payload
    },
});

sse.start();

setTimeout( function(){

    sse.stop();

},5000)

/**/