<div class="inner" id="telegram-app">

<?php if (!TelegramUsuario()): ?>
    <div class="alert">Necesitas estar identificado para usar esta sección.</div>
<?php elseif (!TelegramBot::isConfigured()): ?>
    <div class="alert alert-warning">El módulo Telegram no está configurado. Contacta con el administrador.</div>
<?php else: ?>

    <h2>Telegram</h2>

    <div id="tg-status-panel">
        <p id="tg-loading">Comprobando estado...</p>
    </div>

    <div id="tg-linked-panel" style="display:none">
        <p>Tu cuenta está vinculada a Telegram: <strong id="tg-linked-name"></strong></p>
        <a class="button btn btn-sm btn-info" id="btn-tg-test">Enviar mensaje de prueba</a>
        <a class="button btn btn-sm btn-warning" id="btn-tg-unlink">Desvincular</a>
    </div>

    <div id="tg-unlinked-panel" style="display:none">
        <p>Vincula tu cuenta de Telegram para recibir notificaciones directamente en tu móvil.</p>
        <a class="button btn btn-primary" id="btn-tg-link">Vincular Telegram</a>

        <div id="tg-link-steps" style="display:none; margin-top: 1em;">

            <p><strong>Opción A — desde el móvil:</strong> pulsa el enlace y se vincula automáticamente.</p>
            <a id="tg-deep-link" href="#" target="_blank" rel="noopener" class="button btn btn-success">Abrir bot en Telegram</a>

            <p style="margin-top:1.5em;"><strong>Opción B — desde el ordenador:</strong> abre el bot en Telegram y envía este mensaje:</p>
            <div style="display:flex; align-items:center; gap:0.5em;">
                <code id="tg-start-cmd" style="font-size:1.2em; background:#f0f0f0; padding:0.4em 0.8em; border-radius:4px; letter-spacing:0.05em;">/start ????????</code>
                <a class="button btn btn-sm btn-default" id="btn-tg-copy">Copiar</a>
            </div>

            <p style="font-size:0.85em; color:#888; margin-top:1em;">El código caduca en 10 minutos. Si caduca, pulsa "Vincular Telegram" de nuevo.</p>
        </div>
    </div>

<?php endif; ?>

</div>
