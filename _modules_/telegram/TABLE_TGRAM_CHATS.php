<?php

    $tabla = new TableMysql('TGRAM_CHATS');

    $tabla->addCols([
        $tabla->field(      'id',       'int')->len(5)->editable(false)->hide(true),
        $tabla->field('created_at', 'unixtime')->readonly(true)->searchable(true),
        $tabla->field('linked_at',  'unixtime')->readonly(true)->searchable(true),
        $tabla->field( 'user_id',       'int')->len(11)->editable(true)->filtrable(true)->label('User ID'),
        $tabla->field( 'chat_id',   'varchar')->len(32)->editable(true)->searchable(true)->filtrable(true)->label('Chat ID'),
        $tabla->field('username',   'varchar')->len(128)->editable(true)->searchable(true)->label('Username'),
        $tabla->field('first_name', 'varchar')->len(128)->editable(true)->searchable(true)->label('First name'),
        $tabla->field( 'last_name', 'varchar')->len(128)->editable(true)->searchable(true)->label('Last name'),
        $tabla->field(  'active',      'bool')->editable(true)->filtrable(true)->label('Active'),
    ]);

    $tabla->showtitle = true;
    $tabla->title     = 'Telegram Chats';
    $tabla->page      = $page;
    $tabla->orderby   = 'id DESC';

    $tabla->perms['delete'] = TelegramAdministrador();
    $tabla->perms['edit']   = TelegramAdministrador();
    $tabla->perms['add']    = false;
    $tabla->perms['setup']  = TelegramRoot();
    $tabla->perms['reload'] = true;
    $tabla->perms['filter'] = true;
    $tabla->perms['view']   = true;
