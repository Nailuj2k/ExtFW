<script type="text/javascript">
    $(document).ready(function() { 
   
        //Ejemplo de recepción de formulario con AJAX y wQuery

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
                console.log( 'DONE',data.msg );
                $('#ajax-result').append(data.msg);

                //console.log('KEY',data.key)
                //console.log('TXT',data.text)
                //console.log('MSG',data.msg)

            }).fail(function() {
                console.log( "error" , data);
                $('#ajax-result').append('ERROR: '+data.msg);
            }).always(function() {
                $('.ajax-loader').hide();
                btn.removeClass('disabled');
                $('#text').val(str);
            });
        
        }); 


    }); 
</script>