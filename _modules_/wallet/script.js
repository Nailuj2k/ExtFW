/**
 * Wallet Module - JavaScript
 * UI tipo WoS / BlueWallet
 */
$(function() {

    const MODULE = 'wallet';
    const FIAT_LOCALE = window.WALLET_CONFIG?.fiatLocale || 'es-ES';
    const FIAT_CURRENCY = window.WALLET_CONFIG?.fiatCurrency || 'EUR';
    let currentBalance = 0;
    let btcRate = 0;
    let wallets = [];
    let selectedWalletId = null; // wallet_id del wallet seleccionado
    let selectedWalletType = 'ln'; // 'ln', 'onchain', 'watch'

    // Formateador de moneda fiat
    const fiatFormatter = new Intl.NumberFormat(FIAT_LOCALE, {
        style: 'currency',
        currency: FIAT_CURRENCY,
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });

    function formatFiat(amount) {
        return fiatFormatter.format(amount);
    }

    // ============================================================
    // Inicializacion
    // ============================================================
    function init() {
        console.log('WALLET INIT STARTED');
        loadBtcRate(function() {
            loadWallets();
        });
        loadHistory(1);
        bindEvents();
    }

    // ============================================================
    // Cargar wallets y balance
    // ============================================================
    function loadWallets() {
        $.ajax({
            url: '/' + MODULE + '/ajax/action=get_wallets',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                console.log('DEBUG get_wallets response:', response);
                if (response.debug) {
                    console.log('DEBUG CLI_USER data:', response.debug);
                }
                
                if (response.success) {
                    wallets = response.wallets || [];
                    currentBalance = response.total_balance || 0;

                    // Seleccionar el wallet por defecto (is_default=1) si no hay seleccion
                    if (!selectedWalletId && wallets.length > 0) {
                        const defaultWallet = wallets.find(w => w.is_default == 1) || wallets[0];
                        selectedWalletId = defaultWallet.wallet_id;
                        selectedWalletType = defaultWallet.wallet_type;
                    }

                    updateWalletSelector();
                    updateBalanceDisplay();
                    updateWalletUI();

                    // Cargar lightning address del wallet LN principal
                    const lnWallet = wallets.find(w => w.wallet_type === 'ln' && w.is_default == 1);
                    if (lnWallet && lnWallet.lightning_address) {
                        $('#input-ln-address').val(lnWallet.lightning_address);
                    }
                }

                /**
                if (response.success) {
                    wallets = response.wallets || [];
                    currentBalance = response.total_balance || 0;

                    // Seleccionar SIEMPRE el wallet principal LN (is_default=1, wallet_type='ln')
                    const defaultWallet = wallets.find(w => w.is_default == 1 && w.wallet_type === 'ln');
                    if (defaultWallet) {
                        selectedWalletId = defaultWallet.wallet_id;
                        selectedWalletType = defaultWallet.wallet_type;
                    } else if (wallets.length > 0) {
                        selectedWalletId = wallets[0].wallet_id;
                        selectedWalletType = wallets[0].wallet_type;
                    }

                    updateWalletSelector();
                    updateBalanceDisplay();

                    // Cargar lightning address del wallet LN principal
                    const lnWallet = wallets.find(w => w.wallet_type === 'ln' && w.is_default == 1);
                    if (lnWallet && lnWallet.lightning_address) {
                        $('#input-ln-address').val(lnWallet.lightning_address);
                    }
                }    
                **/           
            },
            error: function() {
                $('#balance-sats').text('Error');
            }
        });
    }

    // ============================================================
    // Actualizar selector de wallets
    // ============================================================
    function updateWalletSelector() {
        const select = $('#wallet-select');
        select.empty();

        // Todos los wallets vienen de la BD
        wallets.forEach(function(w) {
            const typeLabel = w.wallet_type === 'ln' ? 'LN' :
                              w.wallet_type === 'onchain' ? 'BTC' : 'Watch';
            const isDefault = w.is_default == 1 ? ' *' : '';
            select.append('<option value="' + w.wallet_id + '" data-type="' + w.wallet_type + '">' +
                          escapeHtml(w.wallet_name) + ' (' + typeLabel + ')' + isDefault + '</option>');
        });

        // Seleccionar el wallet actual
        if (selectedWalletId) {
            select.val(selectedWalletId);
        }
    }

    // ============================================================
    // Actualizar display de balance
    // ============================================================
    function updateBalanceDisplay() {
        const wallet = wallets.find(w => w.wallet_id == selectedWalletId);
        
        console.log('DEBUG: Actualizar balance display para wallet:', wallet);

        function showBalance(balance) {
            console.log('DEBUG: Mostrar balance:', balance);
            if (balance >= 100000) {
                // Mostrar en BTC con 2 decimales, unidad "BTC"
                const btc = (balance / 100000000).toFixed(2);
                $('#balance-sats').text(btc);
                $('.balance-unit').text('BTC');
                // Opcional: mostrar sats en tooltip
                $('#balance-sats').attr('title', formatNumber(balance) + ' sats');
            } else {
                // Mostrar en sats, unidad "sats"
                $('#balance-sats').text(formatNumber(balance));
                $('.balance-unit').text('sats');
                $('#balance-sats').removeAttr('title');
            }
            console.log('DEBUG: btcRate:', btcRate, 'balance:', balance);
            if (btcRate > 0 && balance > 0) {
                const btcAmount = balance / 100000000;
                const fiatAmount = btcAmount * btcRate;
                $('#balance-fiat').text('~' + formatFiat(fiatAmount));
            } else {
                $('#balance-fiat').text('--');
            }
        }

        if (wallet && wallet.wallet_type === 'watch') {
            console.log('DEBUG: Wallet de solo lectura, obteniendo balance via AJAX');
            console.log('URL','/wallet/ajax/action=get_address_balance/address=' + encodeURIComponent(wallet.wallet_address));
            $('#balance-sats').text('...');
            $('#balance-fiat').text('--');
            $('.balance-unit').text('');
            $.ajax({
                url: '/wallet/ajax/action=get_address_balance/address=' + encodeURIComponent(wallet.wallet_address),
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const balance = parseInt(response.balance) || 0;
                        showBalance(balance);
                    } else {
                        $('#balance-sats').text('Error');
                        $('#balance-fiat').text('--');
                        $('.balance-unit').text('');
                    }
                },
                error: function() {
                    $('#balance-sats').text('Error');
                    $('#balance-fiat').text('--');
                    $('.balance-unit').text('');
                }
            });
        } else {
            const balance = wallet ? parseInt(wallet.balance_sats) : 0;
            showBalance(balance);
        }
    }

    // ============================================================
    // Actualizar UI segun tipo de wallet
    // ============================================================
    function updateWalletUI() {
        const isWatchOnly = selectedWalletType === 'watch';
        const $walletCard = $('.wallet-main-balance');
        const $btnSend = $('#btn-send');
        const $btnReceive = $('#btn-receive');

        if (isWatchOnly) {
            $walletCard.addClass('watch-only');
            $btnSend.prop('disabled', true).addClass('disabled');
            $btnReceive.prop('disabled', true).addClass('disabled');
        } else {
            $walletCard.removeClass('watch-only');
            $btnSend.prop('disabled', false).removeClass('disabled');
            $btnReceive.prop('disabled', false).removeClass('disabled');
        }
    }

    // ============================================================
    // Cargar cotizacion BTC
    // ============================================================
    function loadBtcRate(callback) {
        $.ajax({
            url: '/' + MODULE + '/ajax/action=get_btc_rate',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                console.log('DEBUG get_btc_rate response:', response);
                if (response.success) {
                    btcRate = response.rate;
                    $('#btc-rate').text('1 BTC = ' + response.formatted);
                }
                if (callback) callback();
            },
            error: function(xhr, status, error) {
                console.log('DEBUG get_btc_rate error:', status, error);
                if (callback) callback();
            }
        });
    }

    // ============================================================
    // Cargar historial
    // ============================================================
    function loadHistory(page) {
        $('#history-container').html('<div class="loading-spinner"><i class="fa fa-spinner fa-spin"></i></div>');

        $.ajax({
            url: '/' + MODULE + '/ajax/action=get_history/page=' + page,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    renderHistory(response.transactions);
                    renderPagination(response.pagination);
                } else {
                    $('#history-container').html('<p class="text-muted" style="padding:20px;text-align:center;">No hay transacciones.</p>');
                }
            },
            error: function() {
                $('#history-container').html('<p class="text-danger" style="padding:20px;text-align:center;">Error al cargar.</p>');
            }
        });
    }

    // ============================================================
    // Renderizar historial como lista
    // ============================================================
    function renderHistory(transactions) {
        if (!transactions || transactions.length === 0) {
            $('#history-container').html('<p class="text-muted" style="padding:30px;text-align:center;">No hay transacciones todavia.</p>');
            return;
        }

        let html = '<ul class="tx-list">';
        transactions.forEach(function(tx) {
            const isCredit = tx.direction === 'credit';
            const dirClass = isCredit ? 'credit' : 'debit';
            const icon = isCredit ? 'fa-arrow-down' : 'fa-arrow-up';
            const prefix = isCredit ? '+' : '-';

            html += '<li class="tx-item">';
            html += '<div class="tx-icon ' + dirClass + '"><i class="fa ' + icon + '"></i></div>';
            html += '<div class="tx-info">';
            html += '<div class="tx-type">' + tx.type_label + '</div>';
            html += '<div class="tx-date">' + tx.created_at_formatted + '</div>';
            html += '</div>';
            html += '<div class="tx-amount ' + dirClass + '">' + prefix + tx.amount_formatted + ' sats</div>';
            html += '</li>';
        });
        html += '</ul>';

        $('#history-container').html(html);
    }

    // ============================================================
    // Renderizar paginacion
    // ============================================================
    function renderPagination(pagination) {
        if (!pagination || pagination.pages <= 1) {
            $('#history-pagination').html('');
            return;
        }

        let html = '<div class="pagination">';
        for (let i = 1; i <= pagination.pages; i++) {
            const activeClass = i === pagination.page ? 'active' : '';
            html += '<button class="page-btn ' + activeClass + '" data-page="' + i + '">' + i + '</button>';
        }
        html += '</div>';
        $('#history-pagination').html(html);
    }

    // ============================================================
    // Dialog: ENVIAR
    // ============================================================
    function showSendDialog() {
        const isLightning = selectedWalletType === 'ln';

        $("body").dialog({
            title: '<i class="fa fa-arrow-up"></i> Enviar',
            width: '400px',
            height: 'auto',
            openAnimation: 'zoom',
            closeAnimation: 'fade',
            content: `
                <div style="padding:15px;">
                    <p style="margin-bottom:15px;color:#666;">
                        ${isLightning ?
                            'Pega una factura Lightning (lnbc...) o una Lightning Address (usuario@dominio.com)' :
                            'Introduce la direccion Bitcoin de destino'}
                    </p>
                    <textarea id="dialog-send-destination" class="dialog-address-input" rows="3"
                              placeholder="${isLightning ? 'lnbc... o usuario@dominio.com' : 'bc1... o 1... o 3...'}"></textarea>

                    ${!isLightning ? `
                    <div style="margin-bottom:15px;">
                        <label style="display:block;margin-bottom:5px;font-weight:500;">Cantidad (sats)</label>
                        <input type="number" id="dialog-send-amount" class="dialog-amount-input"
                               placeholder="0" min="1" style="font-size:24px;"/>
                        <div id="dialog-send-fiat" class="dialog-fiat-equivalent">--</div>
                    </div>
                    ` : `
                    <div id="dialog-invoice-info" style="min-height:30px;text-align:center;margin-bottom:15px;"></div>
                    `}

                    <div id="dialog-send-status" style="min-height:20px;text-align:center;color:#666;"></div>
                </div>
            `,
            buttons: [
                {
                    text: 'Cancelar',
                    class: 'btn btn-outline',
                    action: function(event, overlay) {
                        document.body.removeChild(overlay);
                    }
                },
                {
                    text: '<i class="fa fa-paper-plane"></i> Enviar',
                    class: 'btn btn-primary',
                    action: function(event, overlay) {
                        processSend(overlay);
                    }
                }
            ],
            onLoad: function() {
                const destInput = document.getElementById('dialog-send-destination');
                const amountInput = document.getElementById('dialog-send-amount');

                if (destInput) {
                    destInput.addEventListener('input', function() {
                        const val = this.value.trim().toLowerCase();

                        // Si es Lightning invoice, parsear cantidad
                        if (val.startsWith('lnbc')) {
                            const amount = parseLnInvoiceAmount(val);
                            const infoEl = document.getElementById('dialog-invoice-info');
                            if (infoEl && amount) {
                                const canSend = amount <= currentBalance;
                                const color = canSend ? '#40c057' : '#ee5a5a';
                                infoEl.innerHTML = '<span style="color:' + color + '">' + formatNumber(amount) + ' sats</span>';
                            }
                        }
                    });
                }

                if (amountInput) {
                    amountInput.addEventListener('input', function() {
                        const sats = parseInt(this.value) || 0;
                        const fiatEl = document.getElementById('dialog-send-fiat');
                        if (fiatEl && btcRate > 0) {
                            const fiat = (sats / 100000000) * btcRate;
                            fiatEl.textContent = '~' + formatFiat(fiat);
                        }
                    });
                }
            }
        });
    }

    // ============================================================
    // Procesar envio
    // ============================================================
    function processSend(overlay) {
        const destination = document.getElementById('dialog-send-destination').value.trim();
        const amountInput = document.getElementById('dialog-send-amount');
        const token = $('#csrf-token').val();
        const statusEl = document.getElementById('dialog-send-status');

        if (!destination) {
            warning('Introduce un destino', 'Error');
            return;
        }

        statusEl.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Procesando...';
        overlay.querySelectorAll('.btn').forEach(b => b.disabled = true);

        // Determinar tipo de envio
        if (destination.toLowerCase().startsWith('lnbc')) {
            // Enviar a factura Lightning
            const encryptedInvoice = str2crypt(destination, token);

            fetch('/' + MODULE + '/ajax', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({ action: 'withdraw', invoice: encryptedInvoice, token: token })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.body.removeChild(overlay);
                    success(data.message || 'Enviado correctamente', 'OK', 5000);
                    loadWallets();
                    loadHistory(1);
                } else {
                    statusEl.textContent = '';
                    warning(data.message || data.error || 'Error al enviar', 'Error');
                    overlay.querySelectorAll('.btn').forEach(b => b.disabled = false);
                }
            })
            .catch(err => {
                statusEl.textContent = '';
                warning('Error de red', 'Error');
                overlay.querySelectorAll('.btn').forEach(b => b.disabled = false);
            });

        } else if (destination.includes('@')) {
            // Enviar a Lightning Address
            // TODO: Resolver LNURL y crear invoice
            statusEl.textContent = '';
            warning('Lightning Address no soportado todavia', 'Aviso');
            overlay.querySelectorAll('.btn').forEach(b => b.disabled = false);

        } else {
            // Enviar a direccion Bitcoin onchain
            const amount = parseInt(amountInput?.value) || 0;
            if (amount < 546) {
                statusEl.textContent = '';
                warning('Cantidad minima: 546 sats', 'Error');
                overlay.querySelectorAll('.btn').forEach(b => b.disabled = false);
                return;
            }

            // TODO: Implementar envio onchain via BTCPay
            statusEl.textContent = '';
            warning('Envio onchain no soportado todavia', 'Aviso');
            overlay.querySelectorAll('.btn').forEach(b => b.disabled = false);
        }
    }

    // ============================================================
    // Dialog: RECIBIR
    // ============================================================
    function showReceiveDialog() {
        const isLightning = selectedWalletType === 'ln';

        if (isLightning) {
            showReceiveLightningDialog();
        } else {
            showReceiveOnchainDialog();
        }
    }

    // ============================================================
    // Dialog: Recibir Lightning (crear invoice)
    // ============================================================
    function showReceiveLightningDialog() {
        $("body").dialog({
            title: '<i class="fa fa-arrow-down"></i> Recibir (Lightning)',
            width: '400px',
            height: 'auto',
            openAnimation: 'zoom',
            closeAnimation: 'fade',
            content: `
                <div style="padding:15px;">
                    <label style="display:block;margin-bottom:5px;font-weight:500;">Cantidad (sats)</label>
                    <input type="number" id="dialog-receive-amount" class="dialog-amount-input"
                           value="10000" min="100" style="font-size:24px;"/>
                    <div id="dialog-receive-fiat" class="dialog-fiat-equivalent">--</div>

                    <div class="amount-presets">
                        <button class="amount-preset-btn" data-amount="1000">1K</button>
                        <button class="amount-preset-btn" data-amount="5000">5K</button>
                        <button class="amount-preset-btn active" data-amount="10000">10K</button>
                        <button class="amount-preset-btn" data-amount="50000">50K</button>
                        <button class="amount-preset-btn" data-amount="100000">100K</button>
                    </div>

                    <div id="dialog-receive-qr" style="display:none;">
                        <div class="qr-container">
                            <img id="dialog-qr-image" src="" alt="QR" />
                        </div>
                        <div class="address-display" id="dialog-invoice-text"></div>
                        <button id="btn-copy-invoice" class="btn btn-outline copy-btn">
                            <i class="fa fa-copy"></i> Copiar Invoice
                        </button>
                        <div id="dialog-receive-status" style="margin-top:15px;text-align:center;color:#666;"></div>
                    </div>
                </div>
            `,
            buttons: [
                {
                    text: 'Cerrar',
                    class: 'btn btn-outline',
                    action: function(event, overlay) {
                        document.body.removeChild(overlay);
                    }
                },
                {
                    text: '<i class="fa fa-qrcode"></i> Generar Invoice',
                    class: 'btn btn-primary btn-generate',
                    action: function(event, overlay) {
                        generateLightningInvoice(overlay);
                    }
                }
            ],
            onLoad: function() {
                updateReceiveFiat();

                document.querySelectorAll('.amount-preset-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        document.getElementById('dialog-receive-amount').value = this.dataset.amount;
                        document.querySelectorAll('.amount-preset-btn').forEach(b => b.classList.remove('active'));
                        this.classList.add('active');
                        updateReceiveFiat();
                    });
                });

                document.getElementById('dialog-receive-amount').addEventListener('input', updateReceiveFiat);

                document.getElementById('btn-copy-invoice')?.addEventListener('click', function() {
                    const invoice = document.getElementById('dialog-invoice-text').textContent;
                    navigator.clipboard.writeText(invoice).then(() => {
                        success('Invoice copiado', 'OK', 2000);
                    });
                });
            }
        });
    }

    function updateReceiveFiat() {
        const sats = parseInt(document.getElementById('dialog-receive-amount')?.value) || 0;
        const fiatEl = document.getElementById('dialog-receive-fiat');
        if (fiatEl && btcRate > 0) {
            const fiat = (sats / 100000000) * btcRate;
            fiatEl.textContent = '~' + formatFiat(fiat);
        }
    }

    // ============================================================
    // Generar invoice Lightning
    // ============================================================
    function generateLightningInvoice(overlay) {
        const amount = parseInt(document.getElementById('dialog-receive-amount').value) || 0;

        if (amount < 100) {
            warning('Minimo 100 sats', 'Error');
            return;
        }

        const btnGenerate = overlay.querySelector('.btn-generate');
        btnGenerate.disabled = true;
        btnGenerate.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Generando...';

        $.ajax({
            url: '/' + MODULE + '/ajax/action=create_invoice/amount=' + amount,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Mostrar QR
                    document.getElementById('dialog-receive-qr').style.display = 'block';
                    document.getElementById('dialog-qr-image').src =
                        'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' +
                        encodeURIComponent(response.checkoutLink || response.invoiceId);
                    document.getElementById('dialog-invoice-text').textContent = response.invoiceId || '';

                    // Ocultar presets y input
                    document.querySelector('.amount-presets').style.display = 'none';
                    document.getElementById('dialog-receive-amount').style.display = 'none';
                    document.getElementById('dialog-receive-fiat').style.display = 'none';

                    btnGenerate.style.display = 'none';

                    // Iniciar polling
                    const statusEl = document.getElementById('dialog-receive-status');
                    statusEl.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Esperando pago...';

                    const checkInterval = setInterval(function() {
                        $.ajax({
                            url: '/' + MODULE + '/ajax/action=check_invoice/invoice_id=' + response.invoiceId,
                            method: 'GET',
                            dataType: 'json',
                            success: function(res) {
                                if (res.success && res.paid) {
                                    clearInterval(checkInterval);
                                    statusEl.innerHTML = '<span style="color:#40c057;"><i class="fa fa-check-circle"></i> Pago recibido!</span>';
                                    loadWallets();
                                    loadHistory(1);
                                    setTimeout(() => {
                                        if (overlay.parentNode) document.body.removeChild(overlay);
                                    }, 2000);
                                }
                            }
                        });
                    }, 3000);

                } else {
                    warning(response.message || 'Error al crear invoice', 'Error');
                    btnGenerate.disabled = false;
                    btnGenerate.innerHTML = '<i class="fa fa-qrcode"></i> Generar Invoice';
                }
            },
            error: function() {
                warning('Error de conexion', 'Error');
                btnGenerate.disabled = false;
                btnGenerate.innerHTML = '<i class="fa fa-qrcode"></i> Generar Invoice';
            }
        });
    }

    // ============================================================
    // Dialog: Recibir Onchain (mostrar direccion)
    // ============================================================
    function showReceiveOnchainDialog() {
        const wallet = wallets.find(w => w.wallet_id == selectedWalletId);
        const address = wallet?.wallet_address || '';

        if (!address) {
            warning('Este wallet no tiene direccion configurada', 'Error');
            return;
        }

        $("body").dialog({
            title: '<i class="fa fa-arrow-down"></i> Recibir (Bitcoin)',
            width: '400px',
            height: 'auto',
            openAnimation: 'zoom',
            closeAnimation: 'fade',
            content: `
                <div style="padding:15px;">
                    <div class="qr-container">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=bitcoin:${encodeURIComponent(address)}" alt="QR" />
                    </div>
                    <div class="address-display">${escapeHtml(address)}</div>
                    <button id="btn-copy-address" class="btn btn-outline copy-btn">
                        <i class="fa fa-copy"></i> Copiar direccion
                    </button>
                </div>
            `,
            buttons: [
                {
                    text: 'Cerrar',
                    class: 'btn btn-outline',
                    action: function(event, overlay) {
                        document.body.removeChild(overlay);
                    }
                }
            ],
            onLoad: function() {
                document.getElementById('btn-copy-address').addEventListener('click', function() {
                    navigator.clipboard.writeText(address).then(() => {
                        success('Direccion copiada', 'OK', 2000);
                    });
                });
            }
        });
    }

    // ============================================================
    // Dialog: Nuevo Wallet
    // ============================================================
    function showNewWalletDialog() {
        $("body").dialog({
            title: '<i class="fa fa-plus"></i> Nuevo Wallet',
            width: '420px',
            height: 'auto',
            openAnimation: 'zoom',
            closeAnimation: 'fade',
            content: `
                <div style="padding:15px;">
                    <div style="margin-bottom:15px;">
                        <label style="display:block;margin-bottom:5px;font-weight:500;">Tipo</label>
                        <select id="dialog-wallet-type" class="form-control">
                            <option value="watch">Solo lectura (seguimiento)</option>
                            <option value="onchain">Bitcoin Onchain</option>
                        </select>
                    </div>
                    <div style="margin-bottom:15px;">
                        <label style="display:block;margin-bottom:5px;font-weight:500;">Nombre</label>
                        <input type="text" id="dialog-wallet-name" class="form-control" placeholder="Mi wallet" />
                    </div>
                    <div id="dialog-address-section" style="margin-bottom:15px;">
                        <label style="display:block;margin-bottom:5px;font-weight:500;">Direccion Bitcoin</label>
                        <input type="text" id="dialog-wallet-address" class="form-control" placeholder="bc1q... o 1... o 3..." />
                    </div>
                    <div id="dialog-generate-section" style="display:none;">
                        <button id="btn-generate-entropy" class="btn btn-outline" style="width:100%;">
                            <i class="fa fa-magic"></i> Generar con entropia
                        </button>
                    </div>
                </div>
            `,
            buttons: [
                {
                    text: 'Cancelar',
                    class: 'btn btn-outline',
                    action: function(event, overlay) {
                        document.body.removeChild(overlay);
                    }
                },
                {
                    text: '<i class="fa fa-save"></i> Crear',
                    class: 'btn btn-primary',
                    action: function(event, overlay) {
                        createWallet(overlay);
                    }
                }
            ],
            onLoad: function() {
                document.getElementById('dialog-wallet-type').addEventListener('change', function() {
                    const genSection = document.getElementById('dialog-generate-section');
                    genSection.style.display = this.value === 'onchain' ? 'block' : 'none';
                });

                document.getElementById('btn-generate-entropy')?.addEventListener('click', function() {
                    showGenerateAddressDialog();
                });
            }
        });
    }

    // ============================================================
    // Crear wallet
    // ============================================================
    function createWallet(overlay) {
        const type = document.getElementById('dialog-wallet-type').value;
        const name = document.getElementById('dialog-wallet-name').value.trim();
        const address = document.getElementById('dialog-wallet-address').value.trim();
        const token = $('#csrf-token').val();

        if (!name) {
            warning('Introduce un nombre', 'Error');
            return;
        }

        if (!address) {
            warning('Introduce una direccion', 'Error');
            return;
        }

        overlay.querySelectorAll('.btn').forEach(b => b.disabled = true);

        console.log('DEBUG: Crear wallet', { type, name, address, token });
        
        $.ajax({
            url: '/' + MODULE + '/ajax/action=create_wallet',
            method: 'POST',
            data: {
                wallet_type: type,
                wallet_name: name,
                wallet_address: address,
                token: token
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    document.body.removeChild(overlay);
                    success(response.message || 'Wallet creado', 'OK', 3000);
                    loadWallets();
                } else {
                    warning(response.message || 'Error al crear', 'Error');
                    overlay.querySelectorAll('.btn').forEach(b => b.disabled = false);
                }
            },
            error: function() {
                warning('Error de conexion', 'Error');
                overlay.querySelectorAll('.btn').forEach(b => b.disabled = false);
            }
        });
        
    }

    // ============================================================
    // Dialog: Generar direccion con entropia
    // ============================================================
    function showGenerateAddressDialog() {
        $("body").dialog({
            title: '<i class="fa fa-magic"></i> Generar Direccion',
            type: 'ajax',
            url: '/' + MODULE + '/html/op=create_address',
            width: '550px',
            height: 'auto',
            openAnimation: 'zoom',
            closeAnimation: 'fade',
            buttons: [
                {
                    text: 'Cerrar',
                    class: 'btn btn-outline',
                    action: function(event, overlay) {
                        document.body.removeChild(overlay);
                    }
                }
            ]
        });
    }

    // ============================================================
    // Guardar Lightning Address
    // ============================================================
    function saveLnAddress() {
        const address = $('#input-ln-address').val().trim();
        const token = $('#csrf-token').val();

        $('#btn-save-ln-address').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        $.ajax({
            url: '/' + MODULE + '/ajax',
            method: 'POST',
            data: { action: 'save_ln_address', lightning_address: address, token: token },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#ln-address-status').html('<span class="text-success"><i class="fa fa-check"></i> Guardado</span>');
                } else {
                    $('#ln-address-status').html('<span class="text-danger"><i class="fa fa-times"></i> ' + response.message + '</span>');
                }
                setTimeout(() => $('#ln-address-status').html(''), 3000);
            },
            complete: function() {
                $('#btn-save-ln-address').prop('disabled', false).html('<i class="fa fa-save"></i>');
            }
        });
    }

    // ============================================================
    // Helpers
    // ============================================================
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function parseLnInvoiceAmount(invoice) {
        const match = invoice.match(/^lnbc([0-9]+)([munp]?)/i);
        if (!match) return null;
        const amount = parseFloat(match[1]);
        const unit = (match[2] || '').toLowerCase();
        const factors = { '': 100000000, 'm': 100000, 'u': 100, 'n': 0.1, 'p': 0.0001 };
        if (factors[unit] !== undefined) return Math.floor(amount * factors[unit]);
        return null;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // ============================================================
    // Bind eventos
    // ============================================================
    function bindEvents() {
        $('#btn-refresh-balance').on('click', function() {
            $(this).find('i').addClass('fa-spin');
            loadWallets();
            loadBtcRate();
            setTimeout(() => $('#btn-refresh-balance i').removeClass('fa-spin'), 1000);
        });

        $('#btn-send').on('click', showSendDialog);
        $('#btn-receive').on('click', showReceiveDialog);
        $('#btn-new-wallet').on('click', showNewWalletDialog);
        $('#btn-save-ln-address').on('click', saveLnAddress);

        $('#wallet-select').on('change', function() {
            console.log('DEBUG: Wallet seleccionado', this.value);
            selectedWalletId = $(this).val();
            const select = this;
            const selectedOption = select.options[select.selectedIndex];
            selectedWalletType = selectedOption.getAttribute('data-type');
            updateBalanceDisplay();
            updateWalletUI();
        });

        $(document).on('click', '.page-btn', function() {
            loadHistory($(this).data('page'));
        });

        // Evento de direccion generada desde entropia
        document.addEventListener('wallet:addressGenerated', function(e) {
            if (e.detail?.address) {
                const addressInput = document.getElementById('dialog-wallet-address');
                if (addressInput) addressInput.value = e.detail.address;
                success('Direccion generada', 'OK', 3000);
            }
        });
    }

    // ============================================================
    // Iniciar
    // ============================================================
    init();

});
