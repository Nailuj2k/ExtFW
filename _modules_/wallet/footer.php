<script type="text/javascript">
    $(document).ready(function() { 
   

        // This file is include after wQuery and othres .js files are loaded
        // Javascript can be placed here (or in script.js) to run when document is ready
        // here is posible insert php code to generate javascript variables if needed
        // if insert php is not needed, javascript code is better placed in script.js

        //   //Ejemplo de recepción de formulario con AJAX y wQuery

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
                showMessageInfo( data.msg );
                $('#debug').append(data.msg);

                console.log('KEY',data.key)
                console.log('TXT',data.text)
                console.log('MSG',data.msg)

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