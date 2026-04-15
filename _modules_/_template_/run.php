<div class="inner">

    <h1><?=ucwords(MODULE)?></h1>

    <p>

    Main content

    <?php
    
    // URL segments is $_ARGS[0], $_ARGS[1], $_ARGS[2] ... etc.
    //if a segemt is in key=value format then we also have $_ARGS['key']='value'

    Vars::debug_var($_ARGS);

    //// Scaffold classes usage examples
    
    // Table::init();   // Initialize Scaffold engine
    
    // Show a existing table
    // if TABLE_TABLENAME.php file exists and table not exists table wil be cerated using the definition in the file
    // if TABLE_TABLENAME.php file not exists but TABLENAME exists in database TABLE_TABLENAME.php will be created using the database definition
    // Table::show_table(TABLENAME,$modulename=false,$element=true,$page_number=1,$sortable=false)


    ?>
 
    <div id="div-form-test" style="border:1px solid silver;padding:10px;">
        <h4>AJAX Form Template</h4>
        <p>Ejemplo de formulario. Se envía mediante ajax.</p>
        <form id="test_form" action="<?=MODULE?>/ajax/option=test_form">
            <input type="hidden" id="token" name="token" value="<?=$_SESSION['token']?>">
            <input type="hidden" id="key" name="key" value="<?=Str::password()?>"><!--/*TIP use md5 of any known data. vg: name */-->
            <input type="text" name="nombre" placeholder="Nombre">
            <input type="text" name="apellidos" placeholder="Apellidos">
            <textarea name="text" id="text" placeholder="notas"></textarea>
            <a id="submit" class="btn btn-small btn-secondary">Enviar</a>
        </form>
        <h4>Respuesta</h4>
        <p>Datos devueltos en respuesta al envío del formulario</p>
        <pre id="ajax-result"></pre>
    </div>

    </p>



   <?php

   // Example of TABS

   $tabs_numbers = sprintf( TAB_TAB_TAB, 'tab_one', '<i class="fa fa-user"></i>  '.t('ONE') )
                 . sprintf( TAB_TAB_TAB, 'tab_two', t('TWO') )
                 . sprintf( TAB_TAB_TAB, 'tab_three', '<i class="fa fa-envelope"></i> '.t('THREE') )
                 . sprintf( TAB_TAB_TAB, 'tab_four', t('FOUR') )
                 ;  

    echo sprintf(TAB_HEADER,'tabs-numbers',$tabs_numbers); 
                     
        echo sprintf(TAB_TAB_BEGIN,'tab_one');
        echo 'Content of tab ONE';
        echo TAB_TAB_END;  


        echo sprintf(TAB_TAB_BEGIN,'tab_two');
        echo 'Content of tab TWO';
        echo TAB_TAB_END;  

        echo sprintf(TAB_TAB_BEGIN,'tab_three');
        echo 'Content of tab THREE';
        echo TAB_TAB_END;  

        echo sprintf(TAB_TAB_BEGIN,'tab_four');
        echo 'Content of tab FOUR';
        echo TAB_TAB_END;  

    echo sprintf(TAB_FOOTER,'tabs-numbers');

    ?>

</div>
