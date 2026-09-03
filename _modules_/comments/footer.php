<script type="text/javascript">




    function addMessage(target,message) {
        var messages = document.getElementById(target);       
        var msg = document.createElement("p");
        msg.innerHTML = message;
        var isAtBottom = messages.scrollTop == (messages.scrollHeight - messages.clientHeight );
        //var isAtBottom = messages.scrollTop >= (messages.scrollHeight - messages.clientHeight - 5);
        messages.appendChild(msg);
        if (isAtBottom) {
            messages.scrollTo({
                top: messages.scrollHeight,
                behavior: 'smooth'
            });
        } else {
            // Notificación sutil: añadir clase para highlight
            messages.classList.add('new-message');
            
            setTimeout(function() {
                messages.classList.remove('new-message');
            }, 800);
        }
    }

    $(document).ready(function() { 
   
        // Usar on() con 'click touchend' para capturar ambos tipos de eventos
        $('#test_form #submit').on('click touchend', function(e){
            // Prevenir comportamiento predeterminado para evitar dobles ejecuciones
            e.preventDefault();
            
            var btn = $(this);
            var str = $('#text').val();  
            var key = $('#key').val(); 
            $('#text').val( str2crypt( str, key ) );
            // Cambiado serializeArray() por serialize() para obtener los datos en formato URL-encoded
            var datastring = $("#test_form").serialize();

            $.ajax({
                   method: 'POST',
                   url: $('#test_form').attr('action'),
                   data:datastring, 
                   dataType: "json",
                   beforeSend: function( xhr, settings ) {
                       $('.ajax-loader').show(); 
                       btn.addClass('disabled');
                   }
            }).done(function( data ) {
                //showMessageInfo( data.msg );

                addMessage('log',data.msg)


                /*
                console.log('KEY',data.key)
                console.log('TXT',data.text)
                console.log('MSG',data.msg)
                */

            }).fail(function() {
                showMessageError( "error" );
            }).always(function() {
                $('.ajax-loader').hide();
                btn.removeClass('disabled');
                $('#text').val(str);
            });
        
        }); 


    }); 
</script>