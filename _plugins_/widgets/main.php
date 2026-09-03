<?php

    APP::$shortcodes->add_shortcode('latest_posts', function ($atts) {
        $limit = isset($atts['limit']) ? intval($atts['limit']) : 5;
        $posts = Table::sqlQuery("SELECT item_title AS title ,item_name AS url FROM CLI_PAGES LIMIT $limit");
        $output = '<ul>';
        foreach ($posts as $post) {
            $output .= '<li><a href="' . $post['url'] . '">' . $post['title'] . '</a></li>';
        }
        $output .= '</ul>';
        return $output;
    });

    APP::$shortcodes->add_shortcode('ajax', function ($atts) {
        $url = isset($atts['url']) ? $atts['url'] : 'news/html';
        $hash = hash('crc32b', $url );
        $output = '<div id="ajax-content-'.$hash.'"><p style="text-align:center">Loading ... <!--'. $url .'--></p><span class="ajaxloader"></span></div>'
                . '<script>'
                . 'setTimeout(async function(){'
                . '    let url = `/'.$url.'`;'
                . '    loadContent(`#ajax-content-'.$hash.'`,url);'
                . '},1000);'
                . '</script>';
        return $output;
    });

    APP::$shortcodes->add_shortcode('jsfiddle', function ($atts) {
        $code = isset($atts['code']) ? $atts['code'] : '#';
        $tabs = isset($atts['tabs']) ? $atts['tabs'] : '';
        $output = '<iframe width="100%" height="300" src="//jsfiddle.net/Imabot/'.$code.'/embedded/'.$tabs.'" frameborder="0" loading="lazy" allowtransparency="true" allowfullscreen="true"></iframe>';
        //$output = '<script async src="//jsfiddle.net/Nailuj2000/'.$code.'/3/embed/'.$tabs.'/"></script>';
        return $output;
    });

    APP::$shortcodes->add_shortcode('hash', function ($atts) {
        $url = isset($atts['url']) ? $atts['url'] : '#';
        $texto = file_get_contents($url);
        if ($texto === false) {
            $hash = '{no hash}';
        }else{
            $hash = hash('sha256', $texto);    // Calcular el hash SHA-256 del texto plano
        }
        return $hash;
        // return '<a href="' . $url . '" class="btn">' . $text . '</a>';

    });

    include(SCRIPT_DIR_MODULES.'/spotify/shortcode.php');

    APP::$shortcodes->add_shortcode('now_playing', function ($atts) {

        $output = '<div class="npw">'
                . '    <div class="npw-cover"></div>'
                . '    <div class="npw-info">'
                . '        <div class="npw-title"></div>'
                . '        <div class="npw-artist"></div>'
                . '        <div class="npw-progress-bar">'
                . '            <div class="npw-progress"></div>'
                . '        </div>'
                . '        <div class="npw-time-row">'
                . '            <span class="npw-current-time">0:00</span>'
                . '            <span class="npw-total-time">0:00</span>'
                . '        </div>'
                . '    </div>'
                . '</div>';

        return $output;

    });
    
    // Shortcode [zap lnaddress="
    // https://noxtr.net/.well-known/lnurlp/<user>/amount=<sats*1000>
    //
    APP::$shortcodes->add_shortcode('zap', function ($atts) {
        $lnaddr = isset($atts['lnaddress']) ? trim($atts['lnaddress']) : '';
        if (!preg_match('/^[^\s@]+@[^\s@]+\.[^\s@]+$/', $lnaddr)) {
            return '<span style="color:#c00">' . t('ZAP_BAD_SHORTCODE_LNADDR','[zap] lnaddress invalida','[zap] invalid lnaddress') . '</span>';
        }
        $rawLabel = $atts['text'] ?? $atts['label'] ?? ('⚡ ' . t('DONATE','Donar','Donate'));
        $label = htmlspecialchars($rawLabel, ENT_QUOTES, 'UTF-8');
        $lnaddrEsc = htmlspecialchars($lnaddr, ENT_QUOTES, 'UTF-8');
        $instanceId = 'zap-' . substr(hash('sha256', $lnaddr . microtime(true) . random_int(0, PHP_INT_MAX)), 0, 10);

        // CSS/JS comunes inyectados una sola vez por pagina.
        static $assetsLoaded = false;
        $assets = '';
        if (!$assetsLoaded) {
            $assetsLoaded = true;
            // Textos traducibles para el JS del widget. json_encode produce literales JS seguros
            // (comillas y acentos incluidos); se interpolan en el heredoc como {$zapI18nJs}.
            $zapI18nJs = json_encode([
                'invalidAmount'   => t('ZAP_INVALID_AMOUNT','Importe invalido','Invalid amount'),
                'generating'      => t('ZAP_GENERATING','Generando factura…','Generating invoice…'),
                'invalidLnaddr'   => t('ZAP_INVALID_LNADDR','lnaddress invalida','invalid lnaddress'),
                'noCallback'      => t('ZAP_NO_CALLBACK','Sin callback','No callback'),
                'noInvoice'       => t('ZAP_NO_INVOICE','Sin invoice','No invoice'),
                'min'             => t('ZAP_MIN','Mínimo: ','Minimum: '),
                'max'             => t('ZAP_MAX','Máximo: ','Maximum: '),
                'openWallet'      => t('ZAP_OPEN_WALLET','Abrir wallet','Open wallet'),
                'copy'            => t('ZAP_COPY','Copiar','Copy'),
                'copied'          => t('ZAP_COPIED','Copiado','Copied'),
                'waitingPayment'  => t('ZAP_WAITING_PAYMENT','Esperando pago…','Waiting for payment…'),
                'paymentReceived' => t('ZAP_PAYMENT_RECEIVED','¡Pago recibido! Gracias.','Payment received! Thank you.'),
            ], JSON_UNESCAPED_UNICODE);
            $assets = <<<HTML
            <script>if (typeof QRCode === 'undefined') { document.write('<script src="/_lib_/qrcode/qrcode.min.js"><\/script>'); }</script>
            <style>
                .zap-btn{display:inline-block;padding:6px 14px;background:#f7931a;color:#fff;border:none;border-radius:6px;cursor:pointer;font:600 14px/1 system-ui,sans-serif;vertical-align:middle}
                .zap-btn:hover{background:#e67e0a}
                .zap-modal-bg{display:none;position:fixed;inset:0;background:rgba(0,0,0,.65);z-index:99999;align-items:center;justify-content:center;font:14px/1.4 system-ui,sans-serif}
                .zap-modal-bg.open{display:flex}
                .zap-modal{background:#fff;color:#111;border-radius:10px;padding:20px;max-width:380px;width:90%;max-height:92vh;overflow-y:auto;position:relative;box-shadow:0 8px 32px rgba(0,0,0,.3)}
                .zap-modal h3{margin:0 0 14px;font-size:16px;padding-right:24px;word-break:break-all}
                .zap-modal-close{position:absolute;top:6px;right:10px;background:none;border:none;font-size:24px;line-height:1;cursor:pointer;color:#666;padding:4px 8px}
                .zap-modal label{display:block;margin:8px 0 4px;font-size:12px;color:#555}
                .zap-modal input{width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;font-size:14px;box-sizing:border-box}
                .zap-presets{display:flex;gap:6px;flex-wrap:wrap;margin:8px 0}
                .zap-preset{flex:1;min-width:54px;padding:6px;background:#f5f5f5;border:1px solid #ddd;border-radius:4px;cursor:pointer;font-size:13px}
                .zap-preset:hover{background:#e8e8e8}
                .zap-generate{width:100%;margin-top:12px;padding:10px;background:#f7931a;color:#fff;border:none;border-radius:6px;font-weight:600;cursor:pointer;font-size:14px}
                .zap-generate:disabled{opacity:.6;cursor:wait}
                .zap-qr{text-align:center;margin:14px 0;background:#fff;padding:8px;border-radius:6px}
                .zap-qr img{display:inline-block;max-width:100%;height:auto}
                .zap-bolt11{font:11px/1.3 monospace;word-break:break-all;background:#f5f5f5;padding:8px;border-radius:4px;max-height:90px;overflow-y:auto;color:#333}
                .zap-actions{display:flex;gap:6px;margin-top:8px}
                .zap-actions a,.zap-actions button{flex:1;padding:8px;text-align:center;background:#333;color:#fff;border:none;border-radius:4px;cursor:pointer;text-decoration:none;font-size:13px}
                .zap-actions a:hover,.zap-actions button:hover{background:#555}
                .zap-error{color:#c00;padding:8px;background:#fee;border-radius:4px;margin:8px 0;font-size:13px}
                .zap-loading{text-align:center;padding:18px;color:#666}
            </style>
            <script>
            window.ZapWidget = window.ZapWidget || (function(){
                var I18N = {$zapI18nJs};
                function el(id){return document.getElementById(id);}
                // Polling state por instancia: { intervalId, timeoutId }
                var pollers = {};
                function stopPolling(id){
                    var p = pollers[id];
                    if (!p) return;
                    if (p.intervalId) clearInterval(p.intervalId);
                    if (p.timeoutId) clearTimeout(p.timeoutId);
                    delete pollers[id];
                }
                function startPolling(id, statusUrl, onPaid){
                    stopPolling(id);
                    var attempts = 0;
                    var maxAttempts = 120; // 120 * 2.5s = 5 min
                    async function tick(){
                        attempts++;
                        if (attempts > maxAttempts) { stopPolling(id); return; }
                        try {
                            var r = await fetch(statusUrl, { cache: 'no-store' });
                            var s = await r.json();
                            if (s && s.paid) { stopPolling(id); onPaid(); }
                        } catch (e) { /* ignora errores transitorios y sigue intentando */ }
                    }
                    pollers[id] = {
                        intervalId: setInterval(tick, 2500),
                        timeoutId: setTimeout(function(){ stopPolling(id); }, maxAttempts * 2500 + 1000)
                    };
                }
                function firePaidUI(id){
                    var out = el('zr-'+id);
                    if (out) {
                        out.innerHTML = '<div style="text-align:center;padding:24px;color:#0a7d2e;font-weight:600;font-size:16px">✅ ' + I18N.paymentReceived + '</div>';
                    }
                    if (typeof window.bum === 'function') {
                        try { window.bum(); } catch(e) { console.error('[zap] bum() error', e); }
                    }
                }
                return {
                    open: function(id){el('zm-'+id).classList.add('open');},
                    close: function(id){ stopPolling(id); el('zm-'+id).classList.remove('open'); },
                    setAmount: function(id, sats){el('za-'+id).value = sats;},
                    generate: async function(id, addr){
                        var input = el('za-'+id);
                        var out = el('zr-'+id);
                        var btn = el('zg-'+id);
                        var sats = parseInt(input.value, 10);
                        if (!sats || sats < 1) { out.innerHTML = '<div class="zap-error">' + I18N.invalidAmount + '</div>'; return; }
                        btn.disabled = true;
                        stopPolling(id);
                        out.innerHTML = '<div class="zap-loading">' + I18N.generating + '</div>';
                        try {
                            var parts = addr.split('@');
                            if (parts.length !== 2) throw new Error(I18N.invalidLnaddr);
                            var disco = await fetch('https://' + parts[1] + '/.well-known/lnurlp/' + encodeURIComponent(parts[0]));
                            var meta = await disco.json();
                            if (!meta || !meta.callback) throw new Error((meta && meta.reason) || I18N.noCallback);
                            var msat = sats * 1000;
                            if (meta.minSendable && msat < meta.minSendable) throw new Error(I18N.min + Math.ceil(meta.minSendable/1000) + ' sats');
                            if (meta.maxSendable && msat > meta.maxSendable) throw new Error(I18N.max + Math.floor(meta.maxSendable/1000) + ' sats');
                            var sep = meta.callback.indexOf('?') === -1 ? '?' : '&';
                            var cb = await fetch(meta.callback + sep + 'amount=' + msat);
                            var res = await cb.json();
                            if (!res || !res.pr) throw new Error((res && res.reason) || I18N.noInvoice);
                            var bolt11 = res.pr;
                            var safe = bolt11.replace(/'/g, '&#39;');
                            var qrHolderId = 'zq-' + id;
                            out.innerHTML = '<div class="zap-qr"><div id="' + qrHolderId + '"></div></div>'
                                + '<div class="zap-bolt11">' + safe + '</div>'
                                + '<div class="zap-actions">'
                                +   '<a href="lightning:' + safe + '">' + I18N.openWallet + '</a>'
                                +   '<button type="button" onclick="navigator.clipboard.writeText(this.dataset.b);this.textContent=this.dataset.copied" data-b="' + safe + '" data-copied="' + I18N.copied + '">' + I18N.copy + '</button>'
                                + '</div>'
                                + '<div style="text-align:center;margin-top:8px;font-size:11px;color:#888">' + I18N.waitingPayment + '</div>';
                            // Render QR con _lib_/qrcode (API: new QRCode(el, {text, width, height})).
                            var qrHolder = el(qrHolderId);
                            if (qrHolder && typeof QRCode !== 'undefined') {
                                new QRCode(qrHolder, {
                                    text: 'lightning:' + bolt11.toUpperCase(),
                                    width: 220, height: 220,
                                    correctLevel: QRCode.CorrectLevel.M
                                });
                            }
                            // Polling solo si el endpoint LNURLp lo soporta (campo no estandar añadido por raw.php).
                            if (res._paymentStatusUrl) {
                                startPolling(id, res._paymentStatusUrl, function(){ firePaidUI(id); });
                            }
                        } catch (e) {
                            out.innerHTML = '<div class="zap-error">' + (e && e.message ? e.message : e) + '</div>';
                        } finally {
                            btn.disabled = false;
                        }
                    }
                };
            })();
            </script>
            HTML;
        }

        // En heredoc los tags PHP cortos no se ejecutan (salen literales): se precalcula y se interpola {$var}.
        $tAmountSats      = t('AMOUNT_SATS', 'Importe en sats','Amount sats');
        $tGenerateInvoice = t('GENERATE_INVOICE', 'Generar factura','Generate invoice');

        $widget = <<<HTML
                <button type="button" class="zap-btn" onclick="ZapWidget.open('{$instanceId}')">{$label}</button>
                <div class="zap-modal-bg" id="zm-{$instanceId}" onclick="if(event.target===this)ZapWidget.close('{$instanceId}')">
                    <div class="zap-modal">
                        <button type="button" class="zap-modal-close" onclick="ZapWidget.close('{$instanceId}')">&times;</button>
                        <h3>⚡ {$lnaddrEsc}</h3>
                        <label>{$tAmountSats}</label>
                        <input type="number" id="za-{$instanceId}" value="1000" min="1" inputmode="numeric">
                        <div class="zap-presets">
                            <button type="button" class="zap-preset" onclick="ZapWidget.setAmount('{$instanceId}',210)">210</button>
                            <button type="button" class="zap-preset" onclick="ZapWidget.setAmount('{$instanceId}',1000)">1k</button>
                            <button type="button" class="zap-preset" onclick="ZapWidget.setAmount('{$instanceId}',5000)">5k</button>
                            <button type="button" class="zap-preset" onclick="ZapWidget.setAmount('{$instanceId}',21000)">21k</button>
                        </div>
                        <button type="button" class="zap-generate" id="zg-{$instanceId}" onclick="ZapWidget.generate('{$instanceId}','{$lnaddrEsc}')">{$tGenerateInvoice}</button>
                        <div id="zr-{$instanceId}"></div>
                    </div>
                </div>
                HTML;

        return $assets . $widget;
    });

    APP::$shortcodes->add_shortcode('btcpay', function ($atts) {
        $text     = htmlspecialchars($atts['text'] ?? $atts['label'] ?? ('⚡ ' . t('DONATE','Donar','Donate')), ENT_QUOTES, 'UTF-8');
        $amount   = (string)($atts['amount'] ?? '');
        $currency = strtoupper(preg_replace('/[^A-Za-z]/', '', (string)($atts['currency'] ?? 'EUR')));
        // BTC-LightningNetwork | BTC-CHAIN (on-chain) | vacio = elige el donante en el checkout
        $method   = (string)($atts['method'] ?? '');
        $redirect = (string)($atts['redirect'] ?? '');

        // El wrapper server-side (raw.php action=btcpay_pay) crea la invoice con el api_key
        // server-side y redirige al checkout BTCPay. No expone storeId ni requiere
        // "Allow anyone to create invoice" en BTCPay.
        $qs = [];
        if ($amount !== '')   $qs['amount']   = $amount;
        if ($currency !== '') $qs['currency'] = $currency;
        if ($method !== '')   $qs['method']   = $method;
        if ($redirect !== '') $qs['redirect'] = $redirect;
        $href = '/noxtr/raw?action=btcpay_pay&' . http_build_query($qs);
        // addslashes para usar el url dentro de un onclick con comillas simples.
        $hrefJs = addslashes($href);

        static $btcpayStyleLoaded = false;
        $style = '';
        if (!$btcpayStyleLoaded) {
            $btcpayStyleLoaded = true;
            $style = '<style>'
                   . '.btcpay-btn{display:inline-block;padding:6px 14px;background:#0f3b82;color:#fff;border:none;border-radius:6px;cursor:pointer;font:600 14px/1 system-ui,sans-serif;vertical-align:middle;text-decoration:none}'
                   . '.btcpay-btn:hover{background:#143fa3;color:#fff;text-decoration:none}'
                   . '</style>';
        }

        // Usa wquery.dialog ($.dialog) — mismo patron que _classes_/comments.class.php (boton "Dar propina").
        // type:'iframe' carga la URL en un iframe dentro del dialog; responsive out-of-the-box.
        $btcpayDialogTitle = t('BTCPAY_DIALOG_TITLE','Pago seguro · BTCPay','Secure payment · BTCPay');
        $onclick = '$(\'body\').dialog({'
                 . 'title:\'' . addslashes($btcpayDialogTitle) . '\','
                 . 'width:\'500px\','
                 . 'height:\'874px\','
                 . 'type:\'iframe\','
                 . 'content:\'' . $hrefJs . '\','
                 . 'buttons:[$.dialog.closeButton]'
                 . '})';

        return $style
             . '<button type="button" class="btcpay-btn" onclick="' . htmlspecialchars($onclick, ENT_QUOTES, 'UTF-8') . '">' . $text . '</button>';
    });

    APP::$shortcodes->add_shortcode('whatsapp', function ($atts) {

        // Ponemos un icono whatsapp abajo a la derecha de la pantalla, con un enlace a un número de teléfono específico
        $phone = isset($atts['phone']) ? $atts['phone'] : '1234567890';
        $message = isset($atts['message']) ? $atts['message'] : 'Hello';
        $url = 'https://wa.me/'.$phone.'?text='.urlencode($message);
        $output = '<a href="'.$url.'" class="whatsapp-float" target="_blank">'
                . '    <i class="fa fa-whatsapp whatsapp-icon"></i>'
                . '</a>'
                . '<style>'
                . '.whatsapp-float {'
                . '    position: fixed;'
                . '    width: 60px;'
                . '    height: 60px;'           
                . '    bottom: 20px;'
                . '    right: 20px;'
                . '    background-color: #25d366;'
                . '    color: #fff;'
                . '    border-radius: 50%;'
                . '    text-align: center;'
                . '    font-size: 30px;'
                . '    box-shadow: 0 2px 5px rgba(0,0,0,0.3);'
                . '    z-index: 1000;'
                . '}'
                . '.whatsapp-icon {'
                . '    line-height: 60px;'
                . '}'
                . '</style>';
        return $output;

    
    });

    APP::$shortcodes->add_shortcode('tip', function ($atts) {
        $type   = isset($atts['type']) ? intval($atts['type']) : 5;
        $tip    = Table::sqlQuery("SELECT a.ID,a.DESCRIPTION,a.TEXT,af.FILE_NAME FROM CFG_ALERTS a,CFG_ALERTS_FILES af WHERE a.TYPE = {$type} AND a.ID=af.ITEM_ID AND af.MAIN=1 ORDER BY RAND() LIMIT 1")[0];
        $output = '<div class="tip-banner" style="background:url(/media/CFG_ALERTS_FILES/files/'.$tip['ID'].'/'.$tip['FILE_NAME'].');background-repeat:no-repeat;background-position: right;background-size: contain;background-color: lightyellow;"><h3>'.$tip['DESCRIPTION'] .'</h3><p>'.$tip['TEXT'] .'</p></div>';
        return $output;       
    });