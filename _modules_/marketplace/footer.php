<?php

    if($_ARGS['admin']){


    }else{

        ?>
        <script>

            function Load(dialogBody,html){

                let loadinghtml = '<div class="bubblingG"><span id="bubblingG_1"></span><span id="bubblingG_2"></span><span id="bubblingG_3"></span></div>';
                $(dialogBody).html(loadinghtml);

            }

            function Loaded(dialogBody,html){
                const datosrecibidos = JSON.parse(html);
                if(datosrecibidos.error==0){
                   if(datosrecibidos.name)
                       $(dialogBody).html(`<div class="info" style="margin-bottom:0;position:absolute;top:0;left:0;right:0;bottom:0;align-content:center;">El ${datosrecibidos.type} <b>${datosrecibidos.name}</b> ha sido instalado o actualizado. 🙂</div>`);
                   else  
                       $(dialogBody).html(datosrecibidos.msg[0]);
                }else{
                   $(dialogBody).html(datosrecibidos.msg[0]);
                }

            }

            $(function() {     
                $('#marketplace').load('<?=CFG::$vars['repo']['url']?>/marketplace/html/key=12345')
            });
            

            <?php if (Root()) {?>

            document.addEventListener('DOMContentLoaded', () => {
                const selectType = document.getElementById('select-type');
                const selectRepo = document.getElementById('select-repo');
                const selectModule = document.getElementById('select-module');
                /***
                const valoresSeleccionados = document.getElementById('valoresSeleccionados');
             

                // Función para mostrar los valores seleccionados
                function mostrarValores(select, nombre) {
                    const valores = select.value ?? select.getValue();
                    let texto = '';
                    
                    if (Array.isArray(valores)) {
                        texto = `${nombre}: ${JSON.stringify(valores.map(v => ({
                            valor: v.value,
                            texto: v.text
                        })), null, 2)}`;
                    } else {
                        texto = `${nombre}: ${JSON.stringify(valores, null, 2)}`;
                    }
                    
                    return texto;
                }

                // Función para actualizar la visualización de todos los valores
                function actualizarVisualizacion() {
                    const textos = [
                        mostrarValores(selectType, 'Select Type'),                    
                        mostrarValores(selectRepo, 'Select Repo'),                    
                        mostrarValores(selectModule, 'Select Module'),                    
                    ];
                    valoresSeleccionados.textContent = textos.join('\n\n');
                }

                // Escuchar cambios en todos los selects
                [selectType,selectRepo, selectModule].forEach(select => {
                    select.addEventListener('change', actualizarVisualizacion);
                    if (select.matches('enhanced-select')) {
                        select.addEventListener('input', actualizarVisualizacion);
                    }
                });

                // Mostrar valores iniciales
                actualizarVisualizacion();
                ****/

                document.addEventListener('click', function(event) {
                    if (event.target.id === 'btn-install') {
                        const type = selectType.value;
                        const repo = selectRepo.getValue();
                        const module = selectModule.getValue().toLowerCase();

                        const url = '<?=SCRIPT_DIR?>/control_panel/ajax/update/'+type+'/'+module+'/host='+repo+'/key=<?=time()?>';       
                        //console.log('URL',url);
                        
                        let data_title = 'Instalando '+selectModule.getValue();
                        let href = url;
                        let w= '500px';
                        let h= '300px';
                        $("body").dialog({
                            title:  data_title,
                            width: w,
                            height: h,
                            type: 'ajax',
                            content: href,
                            buttons: [$.dialog.closeButton],
                            onLoad: function() { console.log('ONLOAD');  console.log($(this))/*dialogBody.innerHTML ='LOADING';*/ },               // Al cargar
                            //onLoaded: loaded
                        });
                        



                    }
                }); 

            });

            <?php } ?>


        </script>
        <?php
        
    }
