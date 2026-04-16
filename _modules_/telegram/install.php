<?php

    $sql_values = [];

    if (!isset(CFG::$vars['modules']['telegram']['bot_token']))
        $sql_values[] = "('modules.telegram.bot_token', '', 'Telegram Bot API token from @BotFather', 1)";

    if (!isset(CFG::$vars['modules']['telegram']['webhook_secret']))
        $sql_values[] = "('modules.telegram.webhook_secret', '" . bin2hex(random_bytes(16)) . "', 'Secret token sent by Telegram in X-Telegram-Bot-Api-Secret-Token header', 1)";

    if (count($sql_values) > 0) {
        foreach ($sql_values as $sql) {
            $_sql = "INSERT INTO CFG_CFG (K,V,DESCRIPTION,ACTIVE) VALUES " . $sql;
            Install::runsql($_sql);
        }
    }

    echo '<h1>Telegram module installed</h1><pre>';
    print_r(CFG::$vars['modules']['telegram'] ?? []);
    echo '</pre>';

    echo '<p>Siguiente paso: añade el <strong>bot_token</strong> en el panel de configuración, luego ve a '
       . '<a href="/telegram/admin/tab=setup">telegram/admin/tab=setup</a> y pulsa "Registrar webhook".</p>';

?>

<p>Return to <a href="/<?= MODULE ?>"><?= MODULE ?></a></p>
