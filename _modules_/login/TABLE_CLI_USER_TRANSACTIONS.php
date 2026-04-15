<?php


    include(SCRIPT_DIR_MODULES.'/comments/TABLE_CLI_USER_TRANSACTIONS.php');
    
    /*
    $tabla = new TableMysql( 'CLI_USER_TRANSACTIONS' );

    $tabla->addCols([
        $tabla->field(              'ID',      'int' )->len(  5)->editable(false)->hide(true),
        $tabla->field(       'from_user',      'int' )->len( 11)->editable(true),
        $tabla->field(         'to_user',      'int' )->len( 11)->editable(true),
        $tabla->field('transaction_type',   'select' )->len(  1)->editable(true)->values([
            1 => 'Ingreso por artículo',
            2 => 'Retiro de fondos',
            3 => 'Ajuste manual',
            4 => 'Recarga de saldo',
        ]),
        $tabla->field(    'amount_sats',      'int' )->len( 11)->editable(true),
        $tabla->field(     'invoice_id',   'varchar')->len( 64)->editable(true),
        $tabla->field(      'module_id',      'int' )->len( 11)->editable(true),
        $tabla->field(     'article_id',      'int' )->len( 11)->editable(true),
        $tabla->field(     'created_at',      'int' )->len( 11)->editable(true),
    ]);

    $tabla->showtitle = true;
    $tabla->page    = $page;
    $tabla->orderby = 'ID DESC';
    $tabla->page_num_items = 10;
    

    $tabla->perms['delete'] = Administrador();
    $tabla->perms['edit']   = Administrador();
    $tabla->perms['add']    = Administrador();
    $tabla->perms['setup']  = Root();
    $tabla->perms['reload'] = true;
    $tabla->perms['filter'] = true;
    $tabla->perms['view']   = true;
    */
  
    $tabla->where = 'to_user = '.$_SESSION['userid'].' OR from_user = '.$_SESSION['userid'];
