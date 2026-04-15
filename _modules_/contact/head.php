<?php

    HTML::css(SCRIPT_DIR_THEME.'/style.contact.css?ver=1.0.6');
    HTML::js(SCRIPT_DIR_MODULE.'/script.js?ver=1.0.5','defer');
    HTML::js(SCRIPT_DIR_JS.'/sse/sse.js');

    Breadcrumb::$replace['contact'] = array(t('CONTACT'),'contact');