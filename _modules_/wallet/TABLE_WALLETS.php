<?php
/**
 * TABLE_WALLETS - Tabla de wallets del usuario
 *
 * Tipos de wallet:
 *   - ln       : Lightning Network (custodial en BTCPayServer)
 *   - onchain  : Bitcoin onchain (direccion propia)
 *   - watch    : Solo lectura / seguimiento de saldo
 */

$table_name  = 'CLI_WALLETS';

$table = [

    'table' => $table_name,
    'primary_key' => 'wallet_id',

    'config' => [
        'title' => t('WALLETS', 'Wallets'),
        'subtitle' => t('MY_WALLETS', 'Mis Wallets'),
        'icon' => 'fa-wallet',
        'paginate' => 20,
        'sortable' => true,
        'searchable' => true,
        'exportable' => false,
        'can_add' => false,
        'can_edit' => true,
        'can_delete' => true,
        'can_view' => true,
    ],

    'tabs' => [
        'general' => t('GENERAL', 'General'),
        'security' => t('SECURITY', 'Seguridad'),
    ],

    'fields' => [

        'wallet_id' => [
            'type' => 'int',
            'auto_increment' => true,
            'editable' => false,
            'show_in_list' => false,
        ],

        'user_id' => [
            'type' => 'int',
            'label' => t('USER', 'Usuario'),
            'editable' => false,
            'show_in_list' => false,
            'default_value' => '$_SESSION[\'userid\']',
        ],

        'wallet_type' => [
            'type' => 'select',
            'label' => t('TYPE', 'Tipo'),
            'options' => [
                'ln' => 'Lightning Network',
                'onchain' => 'Bitcoin Onchain',
                'watch' => t('WATCH_ONLY', 'Solo lectura'),
            ],
            'default_value' => 'ln',
            'required' => true,
            'width' => 120,
            'fieldset' => 'general',
        ],

        'wallet_name' => [
            'type' => 'varchar',
            'label' => t('NAME', 'Nombre'),
            'len' => 100,
            'required' => true,
            'searchable' => true,
            'width' => 200,
            'fieldset' => 'general',
        ],

        'wallet_address' => [
            'type' => 'varchar',
            'label' => t('ADDRESS', 'Direccion'),
            'len' => 255,
            'required' => false,
            'searchable' => true,
            'fieldset' => 'general',
            'display' => 'code',
        ],

        'lightning_address' => [
            'type' => 'varchar',
            'label' => 'Lightning Address',
            'len' => 255,
            'required' => false,
            'placeholder' => 'usuario@getalby.com',
            'fieldset' => 'general',
        ],

        'balance_sats' => [
            'type' => 'int',
            'label' => t('BALANCE', 'Balance') . ' (sats)',
            'default_value' => 0,
            'editable' => false,
            'width' => 100,
            'format' => 'number',
        ],

        'is_default' => [
            'type' => 'bool',
            'label' => t('DEFAULT', 'Principal'),
            'default_value' => 0,
            'width' => 80,
            'fieldset' => 'general',
        ],

        'derivation_path' => [
            'type' => 'varchar',
            'label' => t('DERIVATION_PATH', 'Ruta derivacion'),
            'len' => 50,
            'default_value' => "m/84'/0'/0'/0/0",
            'fieldset' => 'security',
            'show_in_list' => false,
        ],

        'address_index' => [
            'type' => 'int',
            'label' => t('ADDRESS_INDEX', 'Indice direccion'),
            'default_value' => 0,
            'fieldset' => 'security',
            'show_in_list' => false,
        ],

        'xpub' => [
            'type' => 'text',
            'label' => 'xPub / zPub',
            'fieldset' => 'security',
            'show_in_list' => false,
            'editable' => false,
        ],

        'created_at' => [
            'type' => 'int',
            'label' => t('CREATED', 'Creado'),
            'default_value' => 'time()',
            'editable' => false,
            'display' => 'datetime',
            'width' => 140,
        ],

        'updated_at' => [
            'type' => 'int',
            'label' => t('UPDATED', 'Actualizado'),
            'editable' => false,
            'display' => 'datetime',
            'show_in_list' => false,
        ],

    ],

    'filters' => [
        'default' => 'user_id = $_SESSION[\'userid\']',
    ],

    'events' => [

        'OnBeforeInsert' => function(&$data, &$error) {
            $data['user_id'] = $_SESSION['userid'];
            $data['created_at'] = time();
            $data['updated_at'] = time();

            // Si es el primer wallet, hacerlo default
            $count = Table::sqlQueryPrepared(
                "SELECT COUNT(*) as c FROM CLI_WALLETS WHERE user_id = ?",
                [$_SESSION['userid']]
            )[0]['c'] ?? 0;

            if ($count == 0) {
                $data['is_default'] = 1;
            }
        },

        'OnBeforeUpdate' => function(&$data, &$error) {
            $data['updated_at'] = time();

            // Si se marca como default, desmarcar los demas
            if (isset($data['is_default']) && $data['is_default'] == 1) {
                Table::sqlQueryPrepared(
                    "UPDATE CLI_WALLETS SET is_default = 0 WHERE user_id = ? AND wallet_id != ?",
                    [$_SESSION['userid'], $data['wallet_id']]
                );
            }
        },

        'OnBeforeDelete' => function($id, &$error) {
            // No permitir eliminar si tiene balance
            $wallet = Table::sqlQueryPrepared(
                "SELECT balance_sats FROM CLI_WALLETS WHERE wallet_id = ? AND user_id = ?",
                [$id, $_SESSION['userid']]
            )[0] ?? null;

            if ($wallet && $wallet['balance_sats'] > 0) {
                $error = t('CANNOT_DELETE_WALLET_WITH_BALANCE', 'No puedes eliminar un wallet con saldo. Primero retira los fondos.');
                return false;
            }
        },

    ],

];
