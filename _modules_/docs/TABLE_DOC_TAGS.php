<?php

$tabla = new TableMysql('DOC_TAGS');
//$tabla->uploaddir = './media/files';

include(SCRIPT_DIR_MODULES.'/control_panel/TPL_TABLE_TAGS.php');
