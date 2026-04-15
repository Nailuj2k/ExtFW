<?php

    $tabla = new TableMysql( 'CLI_USER_TRANSACTIONS' );

    $tabla->addCols([
        $tabla->field(              'ID',      'int' )->len(  5)->editable(false)->hide(true),
        $tabla->field(       'from_user',      'int' )->len( 11)->editable(true)->label('From'),
        $tabla->field(         'to_user',      'int' )->len( 11)->editable(true)->label('To'),
        $tabla->field('transaction_type',   'select' )->len(  1)->editable(true)->values([
            1 => 'Ingreso por artículo vendido',
            2 => 'Retiro de fondos',
            3 => 'Ajuste manual',
            4 => 'Recarga de saldo',
            5 => 'Zap Nostr enviado',
            6 => 'Zap Nostr recibido',
        ]),
        $tabla->field( 'direct_payment',   'select' )->len(  1)->editable(true)->values([
            0 => 'No',
            1 => 'Sí',
        ])->default_value(0)->label('Direct Pay'),
        $tabla->field( 'payment_method',   'varchar')->len( 20)->editable(true)->default_value('balance')->label('Payment Method'),       
        $tabla->field('commission_sats',      'int' )->len( 11)->editable(true)->label('Fees'),
        $tabla->field(    'amount_sats',      'int' )->len( 11)->editable(true)->label('Amount'),
        $tabla->field(     'invoice_id',   'varchar')->len( 64)->editable(true)->label('Invoice ID'),
        $tabla->field(     'module_id',       'int' )->len( 11)->editable(true)->label('Mod.'),
        $tabla->field(     'article_id',      'int' )->len( 11)->editable(true)->label('Item'),
        $tabla->field(     'created_at', 'unixtime' )->editable(false)->width(120),
    ]);

    $tabla->showtitle = true;
    $tabla->page    = $page;
    $tabla->orderby = 'ID DESC';

    $tabla->perms['delete'] = Administrador();
    $tabla->perms['edit']   = Administrador();
    $tabla->perms['add']    = Administrador();
    $tabla->perms['setup']  = Root();
    $tabla->perms['reload'] = true;
    $tabla->perms['filter'] = true;
    $tabla->perms['view']   = true;
