<?php

    // This file is included in control_panel/ajax.php when creating a zip file for the module. 
    // It should contain calls to addToZip() for any additional files that need to be included in the zip file.

    addToZip($hzip,SCRIPT_DIR_LIB.'/pdf.js');
    addToZip($hzip,SCRIPT_DIR_LIB.'/jquery/jquery.filedrop.js');    
    addToZip($hzip,SCRIPT_DIR_LIB.'/getID3');