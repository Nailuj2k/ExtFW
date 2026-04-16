<?php
    
    // Telegram module init

    $db_engine = 'scaffold';

    $after_init = true;

    TelegramStore::ensureTables();

    function TelegramAdministrador() {
        global $_ACL;
        return $_ACL->userHasRoleName('Administradores');
    }

    function TelegramRoot() {
        global $_ACL;
        return $_ACL->userHasRoleName('Root');
    }

    function TelegramUsuario() {
        return ($_SESSION['userid'] ?? 0) > 0;
    }
