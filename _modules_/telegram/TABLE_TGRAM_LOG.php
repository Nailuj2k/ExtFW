<?php

    $tabla = new TableMysql('TGRAM_LOG');

    $tabla->addCols([
        $tabla->field(        'id',     'int')->len(5)->editable(false)->hide(true),
        $tabla->field('created_at', 'unixtime')->readonly(true)->searchable(true),
        $tabla->field(   'chat_id', 'varchar')->len(32)->editable(false)->searchable(true)->filtrable(true)->label('Chat ID'),
        $tabla->field(   'user_id',     'int')->len(11)->editable(false)->filtrable(true)->label('User ID'),
        $tabla->field( 'direction', 'varchar')->len(3)->editable(false)->filtrable(true)->label('Dir')->values([
            'in'  => 'in',
            'out' => 'out',
        ]),
        $tabla->field(        'ok',    'bool')->editable(false)->filtrable(true)->label('OK'),
        $tabla->field('message_text', 'textarea')->wysiwyg(false)->readonly(true)->searchable(true)->label('Message'),
    ]);

    $tabla->showtitle = true;
    $tabla->title     = 'Telegram Log';
    $tabla->page      = $page;
    $tabla->orderby   = 'id DESC';

    $tabla->perms['delete'] = TelegramAdministrador();
    $tabla->perms['edit']   = false;
    $tabla->perms['add']    = false;
    $tabla->perms['setup']  = TelegramRoot();
    $tabla->perms['reload'] = true;
    $tabla->perms['filter'] = true;
    $tabla->perms['view']   = true;
