<div class="inner">

    <h1><?=ucwords(MODULE)?></h1>



    <?php

        if($_ARGS[1]=='admin'){

            Table::init();
            Table::show_table('MKP_MARKETPLACE');

        }else{

            ?>
            <div id="marketplace"></div>
              
            <?php if (Root()) {?>
            <div>
                <h3>Instalación manual</h3> 

                <div>
                
                    <select id="select-type">
                        <option value="module" selected>Módulo</option>
                        <option value="theme">Theme</option>
                        <option value="other">Other</option>
                    </select>
                    
                    <enhanced-select id="select-repo" placeholder="Selecciona o escribe una opción">
                        <option value="default_repo" selected><?=str_replace('https://','',CFG::$vars['repo']['url'])?></option>
                        <option value="noxtr">noxtr.net</option>
                    </enhanced-select>
                    
                    <enhanced-select id="select-module" placeholder="Selecciona o escribe una opción">
                        <option value="contact" selected>Contact</option>
                        <option value="news">News</option>
                        <option value="shop">Shop</option>
                        <option value="erp">ERP</option>
                        <option value="edit">Edit</option>
                        <option value="dbadmin">DbAdmin</option>
                        <option value="links">Links</option>
                        <option value="alerts">Alerts</option>
                        <option value="drawing">Drawing</option>
                    </enhanced-select>
                    
                    <button id="btn-install" class="btn btn-primary">Instalar</button>

                </div>       


                <!--
                <div style="margin-top: 20px;">
                    <h3>Valores seleccionados:</h3>
                    <pre id="valoresSeleccionados"></pre>
                </div>
                -->
 
            </div>
            <?php } ?>

            <?php
            
        }

        if(Administrador()){

            ?><p style="margin-right:-13px;border-top:1px solid #699ebe;margin-top:20px;padding-top:5px; text-align:right;"><a href="<?=MODULE?><?=$_ARGS[1]=='admin'?'':'/admin'?>" class="btn btn-success"><?=$_ARGS[1]=='admin'?'Volver':'Administrar'?></a></p><?php

        }
    
    ?>




</div>

