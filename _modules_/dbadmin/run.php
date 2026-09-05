<div id="sql-editor" class="inner inner-page">
    <div id="col-left"><!--[TABLE LIST]-->
        <table id="tables" class="NO_fixed_headers"><thead><tr><th></th><th>Table</th></tr></thead><tbody></tbody></table>
    </div>
    <div id="col-right">
        <div id="row-top">
            <div id="sql-editor-toolbar"><!--[SQL_EDITOR_TOOLBAR]-->
                <span id="page">Pág: <span id="page-num">0</span></span>
                <a class="btn" data-sql="prev"      title="Página anterior" aria-label="Página anterior"><i class="fa fa-angle-double-left"></i></a>
                <a class="btn" data-sql="select"    title="Generar SELECT" aria-label="Generar SELECT"><i class="fa fa-database"></i></a>
                <a class="btn" data-sql="next"      title="Página siguiente" aria-label="Página siguiente"><i class="fa fa-angle-double-right"></i></a>
                <a class="btn" data-sql="insert"    title="Insertar filas" aria-label="Insertar filas"><i class="fa fa-plus-circle"></i></a>
                <a class="btn" data-sql="update"    title="Actualizar filas" aria-label="Actualizar filas"><i class="fa fa-pencil"></i></a>
                <a class="btn" data-sql="add_col"   title="Agregar columna" aria-label="Agregar columna"><i class="fa fa-plus-square"></i></a>
                <a class="btn" data-sql="alter_col" title="Modificar columna" aria-label="Modificar columna"><i class="fa fa-exchange"></i></a>
                <a class="btn" data-sql="drop_col"  title="Eliminar columna" aria-label="Eliminar columna"><i class="fa fa-minus-square"></i></a>
                <a class="btn" data-sql="autoinc"   title="Auto incremento" aria-label="Auto incremento"><i class="fa fa-sort-numeric-asc"></i></a>
                <a class="btn" data-sql="dump"      title="Exportar datos" aria-label="Exportar datos"><i class="fa fa-download"></i></a>
                <a class="btn" data-sql="showcreate"  title="Show Create SQL"><i class="fa fa-cog"></i></a>                    
                <a class="btn" data-sql="exec"      title="Ejecutar SQL"><i class="fa fa-play"></i></a>                    
                <a class="btn" id="btn-full-view" title="Maximizar"><i class="fa fa-window-maximize"></i></a>                    
            </div>
            <div id="sql-editor-query-editor"><!--[SQL_EDITOR]-->
                <textarea name="sql" id="sql" placeholder="SELECT user_id as ID, username AS NAME FROM CLI_USER"></textarea>
            </div>
        </div>
        <div id="row-bottom">
            <div id="sql-editor-query-result"><!--[QUERY_RESULTS]-->
                <!--<a id="btn-csv" title="Exportar a Excel"> <img src="_images_/filetypes/icon_xlsx.png"></a>--> 
                <table id="select" class="sebra"><thead></thead><tbody><tr><td><?php 

echo 'Running as: ' . get_current_user() . '<br>';
echo 'Effective UID: ' . posix_geteuid() . '<br>'; // if posix extension enabled
//if (!is_dir($path)) mkdir($path, 0775, true);
//if (is_writable(SCRIPT_DIR_MEDIA)) echo SCRIPT_DIR_MEDIA.' is writable by PHP';


                ?></td></tr></tbody></table>
            </div>
            <div id="sql-editor-query-status"><!--[QUERY_STATUS]-->
                <div id="output">...</div>
            </div>
        </div>
    </div>
</div>  




<?php

// https://www.tutsmake.com/login-with-facebook-using-php/
// https://www.tutsmake.com/ajax-image-upload-using-php-and-jquery-without-refreshing-page/

?>
