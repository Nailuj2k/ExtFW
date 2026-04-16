<?php

    $tabla = new TableMysql('TGRAM_TOKENS');

    $tabla->addCols([
        $tabla->field(        'id',     'int')->len(5)->editable(false)->hide(true),
        $tabla->field(   'user_id',     'int')->len(11)->editable(false)->filtrable(true)->label('User ID'),
        $tabla->field(     'token', 'varchar')->len(16)->editable(false)->searchable(true)->label('Token'),
        $tabla->field('created_at', 'unixtime')->readonly(true)->label('Creado'),
        $tabla->field(   'used_at', 'unixtime')->readonly(true)->label('Usado'),
    ]);

    $tabla->showtitle = true;
    $tabla->title     = 'Telegram Tokens';
    $tabla->page      = $page;
    $tabla->orderby   = 'id DESC';

    $tabla->perms['delete'] = TelegramAdministrador();
    $tabla->perms['edit']   = false;
    $tabla->perms['add']    = false;
    $tabla->perms['setup']  = TelegramRoot();
    $tabla->perms['reload'] = true;
    $tabla->perms['filter'] = true;
    $tabla->perms['view']   = true;
