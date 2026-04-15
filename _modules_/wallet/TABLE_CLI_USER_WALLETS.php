<?php
/**
 * TABLE_CLI_USER_WALLETS - Tabla de wallets del usuario
 *
 * Tipos de wallet:
 *   - ln       : Lightning Network (custodial en BTCPayServer)
 *   - onchain  : Bitcoin onchain (direccion propia)
 *   - watch    : Solo lectura / seguimiento de saldo
 */

$tabla = new TableMysql('CLI_USER_WALLETS');

$tabla->addCols([
    $tabla->field(       'wallet_id',      'int' )->len( 11)->editable(false)->hide(true),
    $tabla->field(         'user_id',      'int' )->len( 11)->editable(false)->label(t('USER', 'Usuario'))->hide(true),
    $tabla->field(     'wallet_type',   'select' )->len( 10)->editable(true)->values([
        'ln'      => 'Lightning Network',
        'onchain' => 'Bitcoin Onchain',
        'watch'   => t('WATCH_ONLY', 'Solo lectura'),
    ])->default_value('ln')->label(t('TYPE', 'Tipo'))->width(120),
    $tabla->field(     'wallet_name',  'varchar' )->len(100)->editable(true)->label(t('NAME', 'Nombre'))->width(200)->searchable(true),
    $tabla->field(  'wallet_address',  'varchar' )->len(255)->editable(true)->label(t('ADDRESS', 'Direccion'))->searchable(true)->hide(true),
    $tabla->field('lightning_address', 'varchar' )->len(255)->editable(true)->label('Lightning Address')->hide(true),
    $tabla->field(    'balance_sats',      'int' )->len( 11)->editable(false)->label(t('BALANCE', 'Balance').' (sats)')->default_value(0)->width(100),
    $tabla->field(      'is_default',   'select' )->len(  1)->editable(true)->values([
        0 => 'No',
        1 => t('YES', 'Si'),
    ])->default_value(0)->label(t('DEFAULT', 'Principal'))->width(80),
    $tabla->field( 'derivation_path',  'varchar' )->len( 50)->editable(true)->label(t('DERIVATION_PATH', 'Ruta derivacion'))->hide(true),
    $tabla->field(   'address_index',      'int' )->len(  5)->editable(false)->label(t('ADDRESS_INDEX', 'Indice'))->default_value('0')->hide(true),
    $tabla->field(            'xpub', 'textarea' )->editable(false)->label('xPub / zPub')->hide(true),
    $tabla->field(      'created_at', 'unixtime' )->editable(false)->label(t('CREATED', 'Creado'))->width(140),
    $tabla->field(      'updated_at', 'unixtime' )->editable(false)->label(t('UPDATED', 'Actualizado'))->hide(true)
    
]);

$tabla->title = t('WALLETS', 'Wallets');
$tabla->showtitle = true;
$tabla->page = $page;
$tabla->page_num_items = 20;
$tabla->orderby = 'is_default DESC, created_at ASC';

// Filtrar por usuario actual
$tabla->sql_where = 'user_id = ' . (int)($_SESSION['userid'] ?? 0);

$tabla->perms['delete'] = Usuario();
$tabla->perms['edit']   = Usuario();
$tabla->perms['add']    = false; // Se crean desde el dashboard
$tabla->perms['setup']  = Root();
$tabla->perms['reload'] = true;
$tabla->perms['filter'] = true;
$tabla->perms['view']   = true;

class user_wallets_Events extends defaultTableEvents implements iEvents {

    function OnBeforeShow($owner) {
    }

    function OnShow($owner) {
    }

    function OnDrawRow($owner, &$row, &$class) {
    }

    function OnCalculate($owner, &$row) {
        // Formatear balance
        $row['balance_sats'] = number_format($row['balance_sats'], 0, ',', '.');
    }

    function OnInsert($owner, &$result, &$post) {
        $post['user_id'] = $_SESSION['userid'];
        $post['created_at'] = time();
        $post['updated_at'] = time();
        $post['derivation_path'] = "m/84'/0'/0'/0/0";

        // Si es el primer wallet, hacerlo default
        $count = Table::sqlQueryPrepared(
            "SELECT COUNT(*) as c FROM CLI_USER_WALLETS WHERE user_id = ?",
            [$_SESSION['userid']]
        )[0]['c'] ?? 0;

        if ($count == 0) {
            $post['is_default'] = 1;
        }
    }

    function OnUpdate($owner, &$result, &$post) {
        $post['updated_at'] = time();

        // Si se marca como default, desmarcar los demas
        if (isset($post['is_default']) && $post['is_default'] == 1) {
            Table::sqlQueryPrepared(
                "UPDATE CLI_USER_WALLETS SET is_default = 0 WHERE user_id = ? AND wallet_id != ?",
                [$_SESSION['userid'], $post['wallet_id']]
            );
        }
    }

    function OnDelete($owner, &$result, $id) {
        // No permitir eliminar si tiene balance
        $row = $owner->getRow($id);
        $wallet = Table::sqlQueryPrepared(
            "SELECT balance_sats FROM CLI_USER_WALLETS WHERE wallet_id = ? AND user_id = ?",
            [$row['wallet_id'], $_SESSION['userid']]
        )[0] ?? null;

        if ($wallet && $wallet['balance_sats'] > 0) {
            $result['error'] = true;
            $result['msg'] = t('CANNOT_DELETE_WALLET_WITH_BALANCE', 'No puedes eliminar un wallet con saldo. Primero retira los fondos.');
            return false;
        }
    }
}

$tabla->events = new user_wallets_Events();
