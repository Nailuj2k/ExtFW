<div class="inner wallet-module">

    <?php if (!($_SESSION['valid_user'] ?? false) || empty($_SESSION['userid'])): ?>
        <!-- Usuario no autenticado -->
        <div class="wallet-login-required">
            <div class="alert alert-warning">
                <i class="fa fa-exclamation-triangle"></i>
                <?=t('WALLET_LOGIN_REQUIRED', 'Debes iniciar sesion para acceder a tu wallet.')?>
            </div>
            <a href="<?=SCRIPT_HOST?>/login" class="btn btn-primary">
                <i class="fa fa-sign-in-alt"></i> <?=t('LOGIN', 'Iniciar sesion')?>
            </a>
        </div>
    <?php else: ?>

        <!-- Balance Principal -->
        <div class="wallet-main-balance">
            <div class="wallet-selector">
                <select id="wallet-select" class="wallet-select">
                    <option value="main" data-type="ln"><?=t('MAIN_WALLET_LN', 'Wallet Principal (Lightning)')?></option>
                </select>
                <button id="btn-new-wallet" class="btn btn-sm btn-outline" title="<?=t('NEW_WALLET', 'Nuevo Wallet')?>">
                    <i class="fa fa-plus"></i>
                </button>
                <button id="btn-refresh-balance" class="btn btn-sm btn-outline" title="<?=t('REFRESH', 'Actualizar')?>">
                    <i class="fa fa-refresh"></i>
                </button>
            </div>

            <div class="balance-display">
                <span id="balance-sats" class="balance-amount">--</span>
                <span class="balance-unit">sats</span>
            </div>
            <div class="balance-fiat">
                <span id="balance-fiat">--</span>
                <span id="btc-rate" class="btc-rate"></span>
            </div>

            <div class="wallet-actions">
                <button id="btn-send" class="btn btn-send">
                    <i class="fa fa-arrow-up"></i>
                    <span><?=t('SEND', 'Enviar')?></span>
                </button>
                <button id="btn-receive" class="btn btn-receive">
                    <i class="fa fa-arrow-down"></i>
                    <span><?=t('RECEIVE', 'Recibir')?></span>
                </button>
            </div>
        </div>

        <!-- Historial de transacciones -->
        <div class="wallet-history-section">
            <div class="wallet-section-header">
                <h3><i class="fa fa-history"></i> <?=t('RECENT_TRANSACTIONS', 'Transacciones recientes')?></h3>
            </div>
            <div id="history-container" class="history-container">
                <div class="loading-spinner">
                    <i class="fa fa-spinner fa-spin"></i> <?=t('LOADING', 'Cargando...')?>
                </div>
            </div>
            <div id="history-pagination" class="pagination-container"></div>
        </div>

        <!-- Opciones avanzadas (colapsado) -->
        <details class="wallet-advanced-options">
            <summary><i class="fa fa-cog"></i> <?=t('ADVANCED_OPTIONS', 'Opciones avanzadas')?></summary>
            <div class="advanced-content">
                <!-- Lightning Address -->
                <div class="option-group">
                    <label><?=t('MY_LIGHTNING_ADDRESS', 'Mi Lightning Address')?></label>
                    <p class="help-text"><?=t('LN_ADDRESS_HELP', 'Configura una Lightning Address para recibir pagos directamente.')?></p>
                    <div class="ln-address-form">
                        <input type="text" id="input-ln-address" class="form-control" placeholder="usuario@getalby.com" />
                        <button id="btn-save-ln-address" class="btn btn-outline">
                            <i class="fa fa-save"></i>
                        </button>
                    </div>
                    <div id="ln-address-status" class="ln-address-status"></div>
                </div>

                <!-- Tabla de wallets (scaffold) -->
                <div class="option-group">
                    <label><?=t('MANAGE_WALLETS', 'Gestionar Wallets')?></label>
                    <?php
                        Table::init();
                        Table::show_table('CLI_USER_WALLETS');
                    ?>
                </div>
            </div>
        </details>

        <!-- Token CSRF para operaciones -->
        <input type="hidden" id="csrf-token" value="<?=htmlspecialchars($_SESSION['token'] ?? '')?>">

    <?php endif; ?>

</div>
