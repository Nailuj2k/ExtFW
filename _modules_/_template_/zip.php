<?php

    // This file is included in control_panel/ajax.php when creating a zip file for the module. 
    // It should contain calls to addToZip() for any additional files that need to be included 
    // in the zip file.

    // Note: Do not use SCRIPT_DIR_MODULE or SCRIPT_DIR_THEME here, because file is incuded in 
    // control_panel/ajax.php, which is not in the module or theme directory. Use SCRIPT_DIR_LIB 
    // or SCRIPT_DIR_JS or any other relative path instead.
    
    // Examples:
    // addToZip($hzip,SCRIPT_DIR_LIB.'/any_directory_or_file');  
    // addToZip($hzip,SCRIPT_DIR_MODULES.'/any_module/any_directory_or_file'); 
    // addToZip($hzip,SCRIPT_DIR_THEMES.'/any_theme/any_directory_or_file');