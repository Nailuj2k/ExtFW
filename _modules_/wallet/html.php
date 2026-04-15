<?php
/**
 * html.php - Serves HTML content for wallet module dialogs
 * Pattern from timextamping module
 */

if ($_ARGS['op'] == 'create_address') {

    include(SCRIPT_DIR_MODULE . '/html_create_address.php');

} else {

    ?>
    <div style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 0.9em; color: #333;">
        <div>
            <?=t('WALLET_MODULE', 'Modulo Wallet')?>
        </div>
    </div>
    <?php

}
