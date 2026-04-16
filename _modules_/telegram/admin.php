<div class="inner">
<?php

    Table::init();

    $tabs = new Tabs('telegram-admin');
    $tabs->addTab('Chats',  'tg-tab-chats');
    $tabs->addTab('Tokens', 'tg-tab-tokens');
    $tabs->addTab('Log',    'tg-tab-log');
    $tabs->addTab('Textos', 'tg-tab-texts');  // ── FEATURE: TGRAM_TEXTS
    $tabs->addTab('Setup',  'tg-tab-setup');

    $tabs->begin();

        $tabs->beginTab('tg-tab-chats');
            Table::show_table('TGRAM_CHATS');
        $tabs->endTab();

        $tabs->beginTab('tg-tab-tokens');
            Table::show_table('TGRAM_TOKENS');
        $tabs->endTab();

        $tabs->beginTab('tg-tab-log');
            Table::show_table('TGRAM_LOG');
        $tabs->endTab();

        // ── FEATURE: TGRAM_TEXTS ─────────────────────────────────────────────
        // Para añadir respuestas por palabras clave sin tocar el código,
        // gestiona las filas desde este tab.
        // La lógica de matching está en TelegramStore::findTextResponse().
        $tabs->beginTab('tg-tab-texts');
            Table::show_table('TGRAM_TEXTS');
        $tabs->endTab();
        // ── END FEATURE: TGRAM_TEXTS ─────────────────────────────────────────

        $tabs->beginTab('tg-tab-setup');
            $webhookUrl = TelegramBot::getWebhookUrl();
            $configured = TelegramBot::isConfigured();
?>
            <h3>Configuración del webhook</h3>
            <p><strong>URL del webhook:</strong> <code><?= htmlspecialchars($webhookUrl, ENT_QUOTES) ?></code></p>
            <p><strong>Bot token:</strong>
                <?php if ($configured): ?>
                    <span style="color:green">configurado</span>
                <?php else: ?>
                    <span style="color:red">NO configurado — añade <code>modules.telegram.bot_token</code> en CFG_CFG</span>
                <?php endif; ?>
            </p>
            <div style="margin: 1em 0;">
                <a class="button btn btn-primary" id="btn-set-webhook">Registrar webhook</a>
                <a class="button btn btn-info"    id="btn-webhook-info">Ver estado</a>
                <a class="button btn btn-warning" id="btn-del-webhook">Eliminar webhook</a>
                <a class="button btn btn-default" id="btn-get-me">Info del bot</a>
                <a class="button btn btn-success" id="btn-send-test">Enviar mensaje de prueba</a>
            </div>

            <h3>Test IA</h3>
            <p>Prueba el servicio de IA configurado (<strong><?= htmlspecialchars(TelegramStore::getAiService()) ?></strong>) sin necesidad del bot ni del webhook.</p>
            <div style="display:flex;gap:0.5em;align-items:center;margin-bottom:0.5em;">
                <input type="text" id="ai-test-prompt" value="Hola, ¿cómo estás?" style="flex:1;padding:0.4em;">
                <a class="button btn btn-primary" id="btn-test-ai">Probar IA</a>
            </div>
            <pre id="webhook-result" style="background:#f5f5f5;padding:1em;display:none;"></pre>
<?php
        $tabs->endTab();

    $tabs->end();
?>

<script>
(function () {
    const url = '<?= Vars::mkUrl(MODULE, 'ajax') ?>';

    function ajax(action, extra = {}) {
        const pre = document.getElementById('webhook-result');
        pre.textContent = 'Cargando…';
        pre.style.display = '';
        fetch(url, { method: 'POST', body: new URLSearchParams({ action, ...extra }) })
            .then(r => r.json())
            .then(data => { pre.textContent = JSON.stringify(data, null, 2); })
            .catch(e  => { pre.textContent = 'Error: ' + e; });
    }

    document.getElementById('btn-set-webhook')?.addEventListener('click', () => ajax('set_webhook'));
    document.getElementById('btn-webhook-info')?.addEventListener('click', () => ajax('webhook_info'));
    document.getElementById('btn-del-webhook')?.addEventListener('click',  () => ajax('delete_webhook'));
    document.getElementById('btn-get-me')?.addEventListener('click',       () => ajax('get_me'));
    document.getElementById('btn-send-test')?.addEventListener('click',    () => ajax('send_test'));
    document.getElementById('btn-test-ai')?.addEventListener('click', () => {
        const prompt = document.getElementById('ai-test-prompt')?.value || 'Hola';
        ajax('test_ai', { prompt });
    });
})();
</script>

</div>
