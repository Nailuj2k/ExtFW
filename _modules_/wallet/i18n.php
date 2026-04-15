<?php
/**
 * Wallet Module - Internationalization Strings
 *
 * Usage in PHP: t('KEY', 'Default value')
 * Usage in JS:  str_key_name
 *
 * Note: Common strings like str_cancel, str_confirm, str_loading, str_save,
 * str_error, str_success, str_close, str_refresh, str_login are already
 * defined globally in the framework.
 */
?>
<script>
    // Wallet specific
    const str_wallet = '<?=t('WALLET', 'Wallet')?>';
    const str_your_balance = '<?=t('YOUR_BALANCE', 'Tu Balance')?>';
    const str_available_balance = '<?=t('AVAILABLE_BALANCE', 'Saldo disponible')?>';

    // Deposit
    const str_deposit = '<?=t('DEPOSIT', 'Recargar')?>';
    const str_deposit_funds = '<?=t('DEPOSIT_FUNDS', 'Recargar Saldo')?>';
    const str_amount_sats = '<?=t('AMOUNT_SATS', 'Cantidad (sats)')?>';
    const str_generate_invoice = '<?=t('GENERATE_INVOICE', 'Generar Factura')?>';
    const str_open_checkout = '<?=t('OPEN_CHECKOUT', 'Abrir en BTCPay')?>';
    const str_waiting_payment = '<?=t('WAITING_PAYMENT', 'Esperando pago...')?>';
    const str_payment_received = '<?=t('PAYMENT_RECEIVED', 'Pago Recibido')?>';
    const str_balance_updated = '<?=t('BALANCE_UPDATED', 'Tu balance ha sido actualizado.')?>';
    const str_min_amount_100 = '<?=t('MIN_AMOUNT_100_SATS', 'El minimo es 100 sats')?>';
    const str_max_amount_10m = '<?=t('MAX_AMOUNT_10M_SATS', 'El maximo es 10,000,000 sats')?>';
    const str_error_creating_invoice = '<?=t('ERROR_CREATING_INVOICE', 'Error creando la factura')?>';

    // Withdraw
    const str_withdraw = '<?=t('WITHDRAW', 'Retirar')?>';
    const str_withdraw_funds = '<?=t('WITHDRAW_FUNDS', 'Retirar Fondos')?>';
    const str_withdraw_help = '<?=t('WITHDRAW_HELP', 'Pega una factura Lightning (BOLT11) para retirar tus fondos.')?>';
    const str_lightning_invoice = '<?=t('LIGHTNING_INVOICE', 'Factura Lightning')?>';
    const str_confirm_withdraw = '<?=t('CONFIRM_WITHDRAW', 'Confirmar Retiro')?>';
    const str_invoice_required = '<?=t('INVOICE_REQUIRED', 'Debes proporcionar una factura Lightning')?>';
    const str_invalid_ln_invoice = '<?=t('INVALID_LN_INVOICE', 'Factura Lightning invalida')?>';
    const str_invoice_must_start_lnbc = '<?=t('INVOICE_MUST_START_LNBC', 'La factura debe empezar con lnbc')?>';
    const str_insufficient_funds = '<?=t('INSUFFICIENT_FUNDS', 'Saldo insuficiente')?>';
    const str_no_balance = '<?=t('NO_BALANCE', 'No tienes saldo disponible')?>';
    const str_payment_failed = '<?=t('PAYMENT_FAILED', 'Error al procesar el pago')?>';

    // Lightning Address
    const str_lightning_address = '<?=t('LIGHTNING_ADDRESS', 'Lightning Address')?>';
    const str_ln_address_help = '<?=t('LN_ADDRESS_HELP', 'Configura tu Lightning Address para recibir pagos directamente en tu wallet externo.')?>';
    const str_ln_address_saved = '<?=t('LN_ADDRESS_SAVED', 'Lightning Address guardada')?>';
    const str_ln_address_removed = '<?=t('LN_ADDRESS_REMOVED', 'Lightning Address eliminada')?>';
    const str_invalid_ln_address = '<?=t('INVALID_LN_ADDRESS_FORMAT', 'Formato de Lightning Address invalido')?>';

    // History
    const str_no_transactions = '<?=t('NO_TRANSACTIONS', 'No hay transacciones todavia.')?>';
    const str_no_pending_invoices = '<?=t('NO_PENDING_INVOICES', 'No hay facturas pendientes.')?>';

    // Transaction types
    const str_tx_article_income = '<?=t('TX_TYPE_ARTICLE_INCOME', 'Ingreso por articulo')?>';
    const str_tx_withdrawal = '<?=t('TX_TYPE_WITHDRAWAL', 'Retiro de fondos')?>';
    const str_tx_manual = '<?=t('TX_TYPE_MANUAL_ADJUSTMENT', 'Ajuste manual')?>';
    const str_tx_recharge = '<?=t('TX_TYPE_BALANCE_RECHARGE', 'Recarga de saldo')?>';
    const str_tx_zap_sent = '<?=t('TX_TYPE_ZAP_SENT', 'Zap Nostr enviado')?>';
    const str_tx_zap_received = '<?=t('TX_TYPE_ZAP_RECEIVED', 'Zap Nostr recibido')?>';
    const str_tx_other = '<?=t('TX_TYPE_OTHER', 'Otro')?>';

    // Wallet login
    const str_wallet_login_required = '<?=t('WALLET_LOGIN_REQUIRED', 'Debes iniciar sesion para acceder a tu wallet.')?>';
</script>
