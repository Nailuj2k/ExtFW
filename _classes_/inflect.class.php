<?php

$_inflect_lang = preg_match('/^[a-z]{2,5}$/', $_SESSION['lang'] ?? '') ? $_SESSION['lang'] : 'es';
include(SCRIPT_DIR_CLASSES.'/inflect.'.$_inflect_lang.'.class.php');