<?php

    $tabla = new TableMysql( 'CLI_USER_TIMESTAMP' );

    $tabla->addCols([
        $tabla->field(              'ID',      'int' )->len(  5)->editable(false),
        $tabla->field(         'user_id',      'int' )->len( 11)->readonly(true),
        $tabla->field(            'txid',  'varchar' )->len( 64)->readonly(true)->hide(true),
        $tabla->field(            'hash',  'varchar' )->len( 64)->readonly(true)->hide(true),
        $tabla->field(        'filename',  'varchar' )->len(255)->readonly(true)->searchable(true),
        $tabla->field(        'filesize',      'int' )->len( 20)->readonly(true),
        $tabla->field(        'fee_sats',      'int' )->len( 11)->readonly(true),
        $tabla->field(       'cost_sats',      'int' )->len( 11)->readonly(true),
        $tabla->field(       'signature', 'textarea' )->fieldset('signature')->wysiwyg(false)->readonly(true)->hide(true), 
        $tabla->field(  'signer_address',  'varchar' )->len( 62)->fieldset('signature')->readonly(true)->hide(true),     
        $tabla->field(      'created_at', 'unixtime' )->readonly(true),
        $tabla->field(    'confirmed_at', 'unixtime' )->readonly(true),
        $tabla->field(   'confirmations',      'int' )->len( 11)->readonly(true),
        $tabla->field(    'block_height',      'int' )->len( 11)->readonly(true)->searchable(true)
    ]);

    $tabla->showtitle = true;
    $tabla->page    = $page;
    $tabla->orderby = 'id DESC';
    $tabla->page_num_items = 4;

    $tabla->setParent('user_id', $parent); 

    $tabla->perms['delete'] = Administrador();
    $tabla->perms['edit']   = Administrador();
    $tabla->perms['add']    = Administrador();
    $tabla->perms['setup']  = Root();
    $tabla->perms['reload'] = true;
    $tabla->perms['filter'] = true;
    $tabla->perms['view']   = true;
