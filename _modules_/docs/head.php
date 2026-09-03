<?php

    Breadcrumb::$replace['docs'] = [ t('DOCS'), 'docs/tag/all'];
    HTML::js(SCRIPT_DIR_LIB.'/qrcode/qrcode.min.js','defer');