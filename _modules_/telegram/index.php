<?php

    if (OUTPUT === 'ajax') {

        include(SCRIPT_DIR_MODULE . '/ajax.php');

    } elseif (OUTPUT === 'raw') {

        include(SCRIPT_DIR_MODULE . '/raw.php');

    } elseif (($_ARGS[1] ?? '') === 'admin' && TelegramAdministrador()) {

        include(SCRIPT_DIR_MODULE . '/admin.php');

    } elseif (($_ARGS[1] ?? '') === 'install' && TelegramAdministrador()) {

        include(SCRIPT_DIR_MODULE . '/install.php');

    } else {

        include(SCRIPT_DIR_MODULE . '/run.php');

    }
