<?php
/**
 * Wallet Module - AJAX API
 *
 * Endpoints:
 *   - get_wallets        : Listar wallets del usuario
 *   - get_wallet         : Obtener un wallet especifico
 *   - create_wallet      : Crear nuevo wallet
 *   - update_wallet      : Actualizar wallet
 *   - delete_wallet      : Eliminar wallet
 *   - get_balance        : Obtener balance total y por wallet
 *   - get_history        : Historial de transacciones
 *   - create_invoice     : Crear factura LN para recargar
 *   - check_invoice      : Verificar estado de una factura
 *   - withdraw           : Retirar fondos via Lightning
 *   - get_btc_rate       : Obtener cotizacion BTC/EUR
 *   - save_ln_address    : Guardar lightning address
 *   - get_address_balance: Consultar balance de direccion onchain
 */

$action = $_ARGS['action'] ?? $_POST['action'] ?? '';

// Con action = API custom del modulo
if ($action) {

    header('Content-Type: application/json');

    // Verificar autenticacion
    $userId = $_SESSION['userid'] ?? 0;
    $isLoggedIn = ($_SESSION['valid_user'] ?? false) && $userId > 0;

    // Acciones que requieren autenticacion
    $authRequired = [
        'get_wallets', 'get_wallet', 'create_wallet', 'update_wallet', 'delete_wallet',
        'get_balance', 'get_history', 'create_invoice', 'check_invoice', 'withdraw',
        'save_ln_address'
    ];

    if (in_array($action, $authRequired) && !$isLoggedIn) {
        echo json_encode([
            'success' => false,
            'error' => 'AUTH_REQUIRED',
            'message' => t('MUST_LOGIN', 'Debes iniciar sesion')
        ]);
        exit;
    }

    // ============================================================
    // GET_WALLETS - Listar wallets del usuario
    // ============================================================
    if ($action === 'get_wallets') {

        // Obtener datos del usuario (balance y lightning_address)
        // Nota: usamos sqlQuery porque sqlQueryPrepared no funciona bien con esta tabla
        $userRow = Table::sqlQuery("SELECT balance_sats, lightning_address, username FROM CLI_USER WHERE user_id = $userId LIMIT 1")[0] ?? null;

        $userBalance = (int)($userRow['balance_sats'] ?? 0);
        $userLnAddress = $userRow['lightning_address'] ?? '';
        $userName = $userRow['username'] ?? 'Usuario';

        // Verificar si existe el wallet LN principal (is_default=1 y wallet_type=ln)
        $mainWallet = Table::sqlQueryPrepared(
            "SELECT wallet_id FROM CLI_USER_WALLETS WHERE user_id = ? AND wallet_type = 'ln' AND is_default = 1 LIMIT 1",
            [$userId]
        )[0] ?? null;

        // Si no existe, crearlo automaticamente
        if (!$mainWallet) {
            Table::sqlQueryPrepared(
                "INSERT INTO CLI_USER_WALLETS (user_id, wallet_type, wallet_name, wallet_address, lightning_address, balance_sats, is_default, derivation_path, address_index, xpub, created_at, updated_at)
                VALUES (?, 'ln', ?, '', ?, ?, 1, '', 0, '', ?, ?)",
                [$userId, t('MAIN_WALLET', 'Wallet Principal'), $userLnAddress, $userBalance, time(), time()]
            );
        } else {
            // Sincronizar balance del wallet LN principal con CLI_USER.balance_sats
            Table::sqlQueryPrepared(
                "UPDATE CLI_USER_WALLETS SET balance_sats = ?, lightning_address = ?, updated_at = ? WHERE wallet_id = ?",
                [$userBalance, $userLnAddress, time(), $mainWallet['wallet_id']]
            );
        }

        // Obtener todos los wallets
        $wallets = Table::sqlQueryPrepared(
            "SELECT wallet_id, wallet_type, wallet_name, wallet_address, lightning_address,
                    balance_sats, is_default, derivation_path, created_at
            FROM CLI_USER_WALLETS
            WHERE user_id = ?
            ORDER BY is_default DESC, created_at ASC",
            [$userId]
        ) ?? [];

        // Calcular balance total
        $totalBalance = 0;
        foreach ($wallets as &$w) {
            $totalBalance += (int)$w['balance_sats'];
            $w['balance_formatted'] = number_format($w['balance_sats'], 0, ',', '.');
        }

        echo json_encode([
            'success' => true,
            'wallets' => $wallets,
            'total_balance' => $totalBalance,
            'total_formatted' => number_format($totalBalance, 0, ',', '.'),
            // Debug info - remove after testing
            'debug' => [
                'user_id' => $userId,
                'cli_user_balance' => $userBalance
            ]
        ]);
        exit;
    }

    // ============================================================
    // GET_WALLET - Obtener un wallet especifico
    // ============================================================
    if ($action === 'get_wallet') {

        $walletId = (int)($_ARGS['wallet_id'] ?? $_POST['wallet_id'] ?? 0);

        $wallet = Table::sqlQueryPrepared(
            "SELECT * FROM CLI_USER_WALLETS WHERE wallet_id = ? AND user_id = ? LIMIT 1",
            [$walletId, $userId]
        )[0] ?? null;

        if (!$wallet) {
            echo json_encode(['success' => false, 'error' => 'WALLET_NOT_FOUND']);
            exit;
        }

        echo json_encode([
            'success' => true,
            'wallet' => $wallet
        ]);
        exit;
    }

    // ============================================================
    // CREATE_WALLET - Crear nuevo wallet
    // ============================================================
    if ($action === 'create_wallet') {

        $type = $_POST['wallet_type'] ?? 'ln';
        $name = trim($_POST['wallet_name'] ?? '');
        $address = trim($_POST['wallet_address'] ?? '');
        $lnAddress = trim($_POST['lightning_address'] ?? '');
        $derivationPath = trim($_POST['derivation_path'] ?? "m/84'/0'/0'/0/0");
        $xpub = trim($_POST['xpub'] ?? '');

        // Validaciones
        if (empty($name)) {
            echo json_encode(['success' => false, 'error' => 'NAME_REQUIRED', 'message' => t('NAME_REQUIRED', 'El nombre es obligatorio')]);
            exit;
        }

        if (!in_array($type, ['ln', 'onchain', 'watch'])) {
            echo json_encode(['success' => false, 'error' => 'INVALID_TYPE']);
            exit;
        }

        // Para onchain y watch, se requiere direccion o xpub
        if (in_array($type, ['onchain', 'watch']) && empty($address) && empty($xpub)) {
            echo json_encode(['success' => false, 'error' => 'ADDRESS_OR_XPUB_REQUIRED', 'message' => t('ADDRESS_OR_XPUB_REQUIRED', 'Se requiere una direccion o xPub')]);
            exit;
        }

        // Contar wallets existentes
        $count = Table::sqlQueryPrepared(
            "SELECT COUNT(*) as c FROM CLI_USER_WALLETS WHERE user_id = ?",
            [$userId]
        )[0]['c'] ?? 0;

        $isDefault = ($count == 0) ? 1 : 0;

        // Insertar
        Table::sqlQueryPrepared(
            "INSERT INTO CLI_USER_WALLETS (user_id, wallet_type, wallet_name, wallet_address, lightning_address, derivation_path, xpub, balance_sats, is_default, address_index, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, 0, ?, 0, ?, ?)",
            [$userId, $type, $name, $address, $lnAddress, $derivationPath, $xpub, $isDefault, time(), time()]
        );

        $walletId = Table::lastInsertId();

        LOG::$messages['wallet_create'] = "User $userId created wallet $walletId ($type: $name)";

        echo json_encode([
            'success' => true,
            'wallet_id' => $walletId,
            'message' => t('WALLET_CREATED', 'Wallet creado correctamente')
        ]);
        exit;
    }

    // ============================================================
    // UPDATE_WALLET - Actualizar wallet
    // ============================================================
    if ($action === 'update_wallet') {

        $walletId = (int)($_POST['wallet_id'] ?? 0);
        $name = trim($_POST['wallet_name'] ?? '');
        $lnAddress = trim($_POST['lightning_address'] ?? '');
        $isDefault = (int)($_POST['is_default'] ?? 0);

        // Verificar que el wallet pertenece al usuario
        $wallet = Table::sqlQueryPrepared(
            "SELECT wallet_id FROM CLI_USER_WALLETS WHERE wallet_id = ? AND user_id = ?",
            [$walletId, $userId]
        )[0] ?? null;

        if (!$wallet) {
            echo json_encode(['success' => false, 'error' => 'WALLET_NOT_FOUND']);
            exit;
        }

        // Si se marca como default, desmarcar los demas
        if ($isDefault) {
            Table::sqlQueryPrepared(
                "UPDATE CLI_USER_WALLETS SET is_default = 0 WHERE user_id = ?",
                [$userId]
            );
        }

        // Actualizar
        Table::sqlQueryPrepared(
            "UPDATE CLI_USER_WALLETS SET wallet_name = ?, lightning_address = ?, is_default = ?, updated_at = ? WHERE wallet_id = ?",
            [$name, $lnAddress, $isDefault, time(), $walletId]
        );

        echo json_encode([
            'success' => true,
            'message' => t('WALLET_UPDATED', 'Wallet actualizado')
        ]);
        exit;
    }

    // ============================================================
    // DELETE_WALLET - Eliminar wallet
    // ============================================================
    if ($action === 'delete_wallet') {

        $walletId = (int)($_POST['wallet_id'] ?? 0);

        // Verificar que el wallet pertenece al usuario
        $wallet = Table::sqlQueryPrepared(
            "SELECT wallet_id, balance_sats, wallet_name FROM CLI_USER_WALLETS WHERE wallet_id = ? AND user_id = ?",
            [$walletId, $userId]
        )[0] ?? null;

        if (!$wallet) {
            echo json_encode(['success' => false, 'error' => 'WALLET_NOT_FOUND']);
            exit;
        }

        // No permitir eliminar si tiene balance
        if ($wallet['balance_sats'] > 0) {
            echo json_encode([
                'success' => false,
                'error' => 'HAS_BALANCE',
                'message' => t('CANNOT_DELETE_WALLET_WITH_BALANCE', 'No puedes eliminar un wallet con saldo')
            ]);
            exit;
        }

        Table::sqlQueryPrepared("DELETE FROM CLI_USER_WALLETS WHERE wallet_id = ?", [$walletId]);

        LOG::$messages['wallet_delete'] = "User $userId deleted wallet $walletId ({$wallet['wallet_name']})";

        echo json_encode([
            'success' => true,
            'message' => t('WALLET_DELETED', 'Wallet eliminado')
        ]);
        exit;
    }

    // ============================================================
    // GET_BALANCE - Obtener balance total y por wallet
    // ============================================================
    if ($action === 'get_balance') {

        $walletId = (int)($_ARGS['wallet_id'] ?? 0);

        if ($walletId > 0) {
            // Balance de un wallet especifico
            $wallet = Table::sqlQueryPrepared(
                "SELECT balance_sats, wallet_address, wallet_type FROM CLI_USER_WALLETS WHERE wallet_id = ? AND user_id = ?",
                [$walletId, $userId]
            )[0] ?? null;

            if (!$wallet) {
                echo json_encode(['success' => false, 'error' => 'WALLET_NOT_FOUND']);
                exit;
            }

            // Si es el wallet LN principal, sincronizar con CLI_USER
            if ($wallet['wallet_type'] === 'ln') {
                $userBalance = Table::sqlQuery("SELECT balance_sats FROM CLI_USER WHERE user_id = $userId LIMIT 1")[0]['balance_sats'] ?? 0;
                $wallet['balance_sats'] = $userBalance;
            }

            echo json_encode([
                'wallet_id' => $walletId,
                'user_id' => $userId,
                'success' => true,
                'balance' => (int)$wallet['balance_sats'],
                'formatted' => number_format($wallet['balance_sats'], 0, ',', '.')
            ]);
            exit;
        }

        // Balance total de todos los wallets
        // Primero sincronizar el wallet LN principal
        $userRow = Table::sqlQuery("SELECT balance_sats, lightning_address FROM CLI_USER WHERE user_id = $userId LIMIT 1")[0] ?? null;

        $userBalance = (int)($userRow['balance_sats'] ?? 0);

        // Actualizar wallet LN principal
        Table::sqlQueryPrepared(
            "UPDATE CLI_USER_WALLETS SET balance_sats = ? WHERE user_id = ? AND wallet_type = 'ln' AND is_default = 1",
            [$userBalance, $userId]
        );

        // Sumar todos los balances
        $totalBalance = Table::sqlQueryPrepared(
            "SELECT COALESCE(SUM(balance_sats), 0) as total FROM CLI_USER_WALLETS WHERE user_id = ?",
            [$userId]
        )[0]['total'] ?? 0;

        error_log("DEBUG WALLET: user_id=$userId, userBalance=$userBalance");

        echo json_encode([
            'success' => true,
            'balance' => (int)$totalBalance,
            'formatted' => number_format($totalBalance, 0, ',', '.')
        ]);
        exit;
    }

    // ============================================================
    // GET_HISTORY - Historial de transacciones
    // ============================================================
    if ($action === 'get_history') {

        $page = max(1, (int)($_ARGS['page'] ?? 1));
        $limit = min(50, max(10, (int)($_ARGS['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;

        // Contar total
        $countRow = Table::sqlQueryPrepared(
            "SELECT COUNT(*) AS total FROM CLI_USER_TRANSACTIONS WHERE to_user = ? OR from_user = ?",
            [$userId, $userId]
        )[0] ?? null;
        $total = (int)($countRow['total'] ?? 0);

        // Obtener transacciones
        $transactions = Table::sqlQueryPrepared(
            "SELECT t.*,
                    CASE WHEN t.to_user = ? THEN 'credit' ELSE 'debit' END AS direction,
                    u_from.user_name AS from_username,
                    u_to.user_name AS to_username
            FROM CLI_USER_TRANSACTIONS t
            LEFT JOIN CLI_USER u_from ON t.from_user = u_from.user_id
            LEFT JOIN CLI_USER u_to ON t.to_user = u_to.user_id
            WHERE t.to_user = ? OR t.from_user = ?
            ORDER BY t.created_at DESC
            LIMIT ? OFFSET ?",
            [$userId, $userId, $userId, $limit, $offset]
        ) ?? [];

        // Mapear tipos de transaccion
        $typeLabels = [
            1 => t('TX_TYPE_ARTICLE_INCOME', 'Ingreso por articulo'),
            2 => t('TX_TYPE_WITHDRAWAL', 'Retiro de fondos'),
            3 => t('TX_TYPE_MANUAL_ADJUSTMENT', 'Ajuste manual'),
            4 => t('TX_TYPE_BALANCE_RECHARGE', 'Recarga de saldo'),
            5 => t('TX_TYPE_ZAP_SENT', 'Zap Nostr enviado'),
            6 => t('TX_TYPE_ZAP_RECEIVED', 'Zap Nostr recibido'),
        ];

        foreach ($transactions as &$tx) {
            $tx['type_label'] = $typeLabels[$tx['transaction_type']] ?? t('TX_TYPE_OTHER', 'Otro');
            $tx['created_at_formatted'] = date('d/m/Y H:i', $tx['created_at']);
            $tx['amount_formatted'] = number_format($tx['amount_sats'], 0, ',', '.');
        }

        echo json_encode([
            'success' => true,
            'transactions' => $transactions,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ]
        ]);
        exit;
    }

    // ============================================================
    // CREATE_INVOICE - Crear factura Lightning para recargar
    // ============================================================
    if ($action === 'create_invoice') {

        $amountSats = (int)($_ARGS['amount'] ?? $_POST['amount'] ?? 0);
        $walletId = (int)($_ARGS['wallet_id'] ?? $_POST['wallet_id'] ?? 0);

        // Validar cantidad
        if ($amountSats < 100) {
            echo json_encode([
                'success' => false,
                'error' => 'AMOUNT_TOO_LOW',
                'message' => t('MIN_AMOUNT_100_SATS', 'El minimo es 100 sats')
            ]);
            exit;
        }

        if ($amountSats > 10000000) {
            echo json_encode([
                'success' => false,
                'error' => 'AMOUNT_TOO_HIGH',
                'message' => t('MAX_AMOUNT_10M_SATS', 'El maximo es 10,000,000 sats')
            ]);
            exit;
        }

        $amountBtc = $amountSats / 100000000;

        // Crear factura en BTCPayServer
        $invoiceData = [
            'amount' => $amountBtc,
            'currency' => 'BTC',
            'metadata' => [
                'webhook' => SCRIPT_HOST . '/page/checkout/bitcoin/callback/raw/',
                'articleId' => 0,
                'moduleId' => 4, // 4 = wallet/recharge
                'userId' => $userId,
                'authorId' => $userId,
                'walletId' => $walletId,
                'amountSats' => $amountSats,
                'type' => 'wallet_recharge'
            ]
        ];

        $response = btcpay_request('stores/' . BTCPAY_STORE_ID . '/invoices', 'POST', $invoiceData);

        if ($response['status'] !== 200 || isset($response['data']['code'])) {
            $errorMsg = $response['data']['message'] ?? $response['error'] ?? t('ERROR_CREATING_INVOICE', 'Error creando la factura');
            echo json_encode([
                'success' => false,
                'error' => 'BTCPAY_ERROR',
                'message' => $errorMsg
            ]);
            exit;
        }

        $invoice = $response['data'];

        echo json_encode([
            'success' => true,
            'invoiceId' => $invoice['id'] ?? null,
            'checkoutLink' => $invoice['checkoutLink'] ?? null,
            'amount' => $amountSats
        ]);
        exit;
    }

    // ============================================================
    // CHECK_INVOICE - Verificar estado de una factura
    // ============================================================
    if ($action === 'check_invoice') {

        $invoiceId = $_ARGS['invoice_id'] ?? $_POST['invoice_id'] ?? '';

        if (empty($invoiceId)) {
            echo json_encode(['success' => false, 'error' => 'INVOICE_ID_REQUIRED']);
            exit;
        }

        $response = btcpay_request('stores/' . BTCPAY_STORE_ID . '/invoices/' . $invoiceId);

        if ($response['status'] !== 200) {
            echo json_encode(['success' => false, 'error' => 'INVOICE_NOT_FOUND']);
            exit;
        }

        $invoice = $response['data'];
        $status = $invoice['status'] ?? 'Unknown';

        // Obtener balance actualizado si esta pagada
        $newBalance = null;
        if ($status === 'Settled') {
            $row = Table::sqlQuery("SELECT balance_sats FROM CLI_USER WHERE user_id = $userId LIMIT 1")[0] ?? null;
            $newBalance = (int)($row['balance_sats'] ?? 0);
        }

        echo json_encode([
            'success' => true,
            'status' => $status,
            'paid' => ($status === 'Settled'),
            'newBalance' => $newBalance
        ]);
        exit;
    }

    // ============================================================
    // WITHDRAW - Retirar fondos via Lightning
    // ============================================================
    if ($action === 'withdraw') {

        // Verificar CSRF token
        $csrfToken = $_POST['token'] ?? null;
        if (!$csrfToken || $csrfToken !== ($_SESSION['token'] ?? null)) {
            echo json_encode([
                'success' => false,
                'error' => 'INVALID_TOKEN',
                'message' => t('INVALID_TOKEN', 'Token invalido')
            ]);
            exit;
        }

        // Obtener y descifrar la invoice
        $encryptedInvoice = $_POST['invoice'] ?? $_POST['invoice_ln'] ?? null;
        if (!$encryptedInvoice) {
            echo json_encode([
                'success' => false,
                'error' => 'INVOICE_MISSING',
                'message' => t('INVOICE_REQUIRED', 'Debes proporcionar una factura Lightning')
            ]);
            exit;
        }

        $invoice = Crypt::crypt2str($encryptedInvoice, $_SESSION['token']);
        if ($invoice === null || $invoice === '') {
            echo json_encode([
                'success' => false,
                'error' => 'DECRYPT_FAILED',
                'message' => t('SESSION_EXPIRED', 'Tu sesion ha expirado, recarga la pagina')
            ]);
            exit;
        }

        // Validar formato de invoice Lightning
        if (!preg_match('/^lnbc/i', $invoice)) {
            echo json_encode([
                'success' => false,
                'error' => 'INVALID_INVOICE_FORMAT',
                'message' => t('INVALID_LN_INVOICE', 'Factura Lightning invalida')
            ]);
            exit;
        }

        // Obtener balance actual
        $row = Table::sqlQuery("SELECT balance_sats FROM CLI_USER WHERE user_id = $userId LIMIT 1")[0] ?? null;
        $balance = (int)($row['balance_sats'] ?? 0);

        if ($balance <= 0) {
            echo json_encode([
                'success' => false,
                'error' => 'INSUFFICIENT_FUNDS',
                'message' => t('NO_BALANCE', 'No tienes saldo disponible'),
                'balance' => 0
            ]);
            exit;
        }

        // Parsear cantidad de la invoice
        $amountSats = null;
        if (preg_match('/^lnbc([0-9]+)([munp]?)/i', $invoice, $m)) {
            $amount = (float)$m[1];
            $unit = strtolower($m[2] ?? '');
            $factors = [
                '' => 100000000,
                'm' => 100000,
                'u' => 100,
                'n' => 0.1,
                'p' => 0.0001,
            ];
            if (isset($factors[$unit])) {
                $amountSats = (int)floor($amount * $factors[$unit]);
            }
        }

        if ($amountSats === null || $amountSats <= 0) {
            echo json_encode([
                'success' => false,
                'error' => 'INVOICE_AMOUNT_REQUIRED',
                'message' => t('INVOICE_NEEDS_AMOUNT', 'La factura debe incluir una cantidad'),
                'balance' => $balance
            ]);
            exit;
        }

        if ($amountSats > $balance) {
            echo json_encode([
                'success' => false,
                'error' => 'INSUFFICIENT_FUNDS',
                'message' => sprintf(t('INSUFFICIENT_BALANCE_%s_%s', 'Saldo insuficiente. Tienes %s sats, necesitas %s sats.'),
                    number_format($balance, 0, ',', '.'),
                    number_format($amountSats, 0, ',', '.')),
                'balance' => $balance,
                'requested' => $amountSats
            ]);
            exit;
        }

        // Pagar la invoice via BTCPayServer
        $payUrl = 'stores/' . BTCPAY_STORE_ID . '/lightning/BTC/invoices/pay';
        $response = btcpay_request($payUrl, 'POST', ['BOLT11' => $invoice]);

        if ($response['status'] !== 200 || isset($response['data']['code'])) {
            $errorMsg = $response['data']['message'] ?? $response['error'] ?? t('PAYMENT_FAILED', 'Error al procesar el pago');
            echo json_encode([
                'success' => false,
                'error' => 'PAYMENT_FAILED',
                'message' => $errorMsg
            ]);
            exit;
        }

        // Descontar del balance
        Table::sqlQueryPrepared(
            "UPDATE CLI_USER SET balance_sats = COALESCE(balance_sats, 0) - ? WHERE user_id = ? AND balance_sats >= ?",
            [$amountSats, $userId, $amountSats]
        );

        // Registrar transaccion
        Table::sqlQueryPrepared(
            "INSERT INTO CLI_USER_TRANSACTIONS (from_user, to_user, transaction_type, amount_sats, commission_sats, invoice_id, module_id, article_id, payment_method, direct_payment, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [$userId, 0, 2, $amountSats, 0, '', 4, 0, 'lightning', 1, time()]
        );

        // Obtener nuevo balance
        $newRow = Table::sqlQuery("SELECT balance_sats FROM CLI_USER WHERE user_id = $userId LIMIT 1")[0] ?? null;
        $newBalance = (int)($newRow['balance_sats'] ?? 0);

        LOG::$messages['wallet_withdraw'] = "User $userId withdrew $amountSats sats. New balance: $newBalance";

        echo json_encode([
            'success' => true,
            'withdrawn' => $amountSats,
            'balance' => $newBalance,
            'message' => sprintf(t('WITHDRAWAL_SUCCESS_%s', 'Retiro de %s sats completado'), number_format($amountSats, 0, ',', '.'))
        ]);
        exit;
    }

    // ============================================================
    // GET_BTC_RATE - Obtener cotizacion BTC/EUR
    // ============================================================
    if ($action === 'get_btc_rate') {

        $currency = strtoupper($_ARGS['currency'] ?? 'EUR');
        $allowedCurrencies = ['EUR', 'USD', 'GBP'];

        if (!in_array($currency, $allowedCurrencies)) {
            $currency = 'EUR';
        }

        $response = btcpay_request('stores/' . BTCPAY_STORE_ID . '/rates?currencyPair=BTC_' . $currency);

        if ($response['status'] === 200 && isset($response['data'][0]['rate'])) {
            $rate = (float)$response['data'][0]['rate'];
            echo json_encode([
                'success' => true,
                'rate' => $rate,
                'currency' => $currency,
                'formatted' => number_format($rate, 2, ',', '.') . ' ' . $currency
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'RATE_UNAVAILABLE',
                'message' => t('RATE_UNAVAILABLE', 'Cotizacion no disponible')
            ]);
        }
        exit;
    }

    // ============================================================
    // SAVE_LN_ADDRESS - Guardar lightning address del usuario
    // ============================================================
    if ($action === 'save_ln_address') {

        $csrfToken = $_POST['token'] ?? null;
        if (!$csrfToken || $csrfToken !== ($_SESSION['token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'INVALID_TOKEN']);
            exit;
        }

        $lnAddress = trim($_POST['lightning_address'] ?? '');
        $walletId = (int)($_POST['wallet_id'] ?? 0);

        // Validar formato
        if ($lnAddress !== '' && !preg_match('/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $lnAddress)) {
            echo json_encode([
                'success' => false,
                'error' => 'INVALID_LN_ADDRESS',
                'message' => t('INVALID_LN_ADDRESS_FORMAT', 'Formato de Lightning Address invalido')
            ]);
            exit;
        }

        if ($walletId > 0) {
            // Actualizar wallet especifico
            Table::sqlQueryPrepared(
                "UPDATE CLI_USER_WALLETS SET lightning_address = ?, updated_at = ? WHERE wallet_id = ? AND user_id = ?",
                [$lnAddress, time(), $walletId, $userId]
            );
        } else {
            // Actualizar usuario principal
            Table::sqlQueryPrepared(
                "UPDATE CLI_USER SET lightning_address = ? WHERE user_id = ?",
                [$lnAddress, $userId]
            );
        }

        LOG::$messages['wallet_ln_address'] = "User $userId updated lightning_address to: $lnAddress";

        echo json_encode([
            'success' => true,
            'lightning_address' => $lnAddress,
            'message' => $lnAddress ? t('LN_ADDRESS_SAVED', 'Lightning Address guardada') : t('LN_ADDRESS_REMOVED', 'Lightning Address eliminada')
        ]);
        exit;
    }

    // ============================================================
    // GET_ADDRESS_BALANCE - Consultar balance de direccion onchain (Mempool API)
    // ============================================================
    if ($action === 'get_address_balance') {

        $address = trim($_ARGS['address'] ?? '');

        if (empty($address)) {
            echo json_encode(['success' => false, 'error' => 'ADDRESS_REQUIRED']);
            exit;
        }

        // Consultar Mempool.space API
        $url = 'https://mempool.space/api/address/' . urlencode($address);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            echo json_encode(['success' => false, 'error' => 'API_ERROR', 'message' => 'No se pudo consultar el balance']);
            exit;
        }

        $data = json_decode($response, true);

        $funded = (int)($data['chain_stats']['funded_txo_sum'] ?? 0);
        $spent = (int)($data['chain_stats']['spent_txo_sum'] ?? 0);
        $balance = $funded - $spent;

        echo json_encode([
            'success' => true,
            'address' => $address,
            'balance' => $balance,
            'formatted' => number_format($balance, 0, ',', '.'),
            'tx_count' => ($data['chain_stats']['tx_count'] ?? 0)
        ]);
        exit;
    }

    // ============================================================
    // Accion no reconocida
    // ============================================================
    echo json_encode([
        'success' => false,
        'error' => 'UNKNOWN_ACTION',
        'message' => t('UNKNOWN_ACTION', 'Accion no reconocida')
    ]);

} else {
    // Sin action = Scaffold AJAX (operaciones CRUD de TABLE_*)
    include(SCRIPT_DIR_CLASSES.'/scaffold/ajax.php');
}
