<?php

    APP::$shortcodes->add_shortcode('minichat', function ($atts) {

        static $n = 0;
        $n++;
        $id    = 'minichat_' . $n;
        
        $url   = $atts['url']   ?? CFG::$vars['proto'] . $_SERVER['HTTP_HOST'] . '/sse';
        $limit   = (int)($atts['limit']   ?? 50);
        $history = (int)($atts['history'] ?? 10);

        $title = htmlspecialchars($atts['title'] ?? 'Chat', ENT_QUOTES | ENT_HTML5);
        $sse_server = '<span style="opacity:0.5;font-weight:300;">'.str_replace(['https://','/sse'],'',$atts['url']).'</span>';

        HTML::js('/_js_/sse/sse.js');
       // HTML::css('/_plugins_/widgets/minichat.css');

        $username    = htmlspecialchars($_SESSION['username'] ?? '', ENT_QUOTES | ENT_HTML5);
        $userid      = (int)($_SESSION['userid'] ?? 0);
        $avatar_file = Login::getUrlAvatar();
        $avatar      = $avatar_file ? CFG::$vars['proto'].$_SERVER['HTTP_HOST'].'/'.strtok($avatar_file, '?') : '';

        $js = <<<JS
document.addEventListener('DOMContentLoaded', function(){

    const win    = document.getElementById('{$id}_win');
    const bubble = document.getElementById('{$id}_bubble');

    let unread = 0;
    const badge = document.getElementById('{$id}_badge');

    function setUnread(n) {
        unread = n;
        badge.textContent = n > 99 ? '99+' : n;
        badge.style.display = n > 0 ? 'flex' : 'none';
    }

    if (localStorage.getItem('mc_{$id}') === 'min') {
        win.classList.add('mc-hidden');
        bubble.classList.add('mc-visible');
    }

    win.querySelector('.mc-btn-min').addEventListener('click', function() {
        win.classList.add('mc-hidden');
        bubble.classList.add('mc-visible');
        localStorage.setItem('mc_{$id}', 'min');
    });
    bubble.addEventListener('click', function() {
        win.classList.remove('mc-hidden');
        bubble.classList.remove('mc-visible');
        document.getElementById('{$id}_msgs').scrollTop = 99999;
        setUnread(0);
        localStorage.setItem('mc_{$id}', 'open');
    });

    function mcRelTime(ts) {
        const s = Math.floor((Date.now() - ts) / 1000);
        if (s < 60)   return 'hace ' + s + 's';
        if (s < 3600) return 'hace ' + Math.floor(s / 60) + 'min';
        return 'hace ' + Math.floor(s / 3600) + 'h';
    }

    setInterval(function() {
        document.querySelectorAll('#{$id}_msgs .mc-msg[data-ts]').forEach(function(msg) {
            const timeEl = msg.querySelector('.mc-who-time');
            if (timeEl) timeEl.textContent = mcRelTime(parseInt(msg.dataset.ts));
        });
    }, 10000);

    const sse = new SSEClient({
        url:      "{$url}",
        username: "{$username}",
        userid:   {$userid},
        onPong: function(data) {
            const cd = document.getElementById('{$id}_cd');
            if (cd && data.count != null && data.limit) {
                cd.textContent = (data.limit - data.count) + 's';
            }
        },
        onDisconnect: function() {
            const cd = document.getElementById('{$id}_cd');
            if (cd) cd.textContent = '';
        },
        onError: function(msg) {
            const input = document.getElementById('{$id}_input');
            input.style.borderColor = '#e94560';
            setTimeout(() => { input.style.borderColor = ''; }, 2000);
            const msgs = document.getElementById('{$id}_msgs');
            const div  = document.createElement('div');
            div.className   = 'mc-error';
            div.textContent = msg;
            msgs.appendChild(div);
            msgs.scrollTop  = msgs.scrollHeight;
        },

        onMessage: function(data) { renderMsg(data, true); },
    });

    function renderMsg(data, live) {
        const msgs = document.getElementById('{$id}_msgs');
        const div  = document.createElement('div');
        div.className = 'mc-msg';
        if (live) div.dataset.ts = Date.now();

        // Avatar
        const av = document.createElement('div');
        av.className = 'mc-av';
        if (data.avatar) {
            const img = document.createElement('img');
            img.src       = data.avatar;
            img.className = 'mc-av-img';
            img.alt       = data.username || '?';
            img.onerror   = function() {
                this.style.display = 'none';
                av.textContent = (data.username || '?').charAt(0).toUpperCase();
            };
            av.appendChild(img);
        } else {
            av.textContent = (data.username || '?').charAt(0).toUpperCase();
        }

        // Body
        const body = document.createElement('div');
        body.className = 'mc-body';

        const who = document.createElement('div');
        who.className = 'mc-who';
        const dom = data.domain ? data.domain.replace(/https?:\/\//, '') : '';
        const nameSpan = document.createElement('span');
        nameSpan.className   = 'mc-who-name';
        nameSpan.textContent = data.username || dom || '?';
        who.appendChild(nameSpan);
        if (dom && data.username) {
            const domSpan = document.createElement('span');
            domSpan.className   = 'mc-who-domain';
            domSpan.textContent = '@' + dom;
            who.appendChild(domSpan);
        }
        const timeSpan = document.createElement('span');
        timeSpan.className   = 'mc-who-time';
        timeSpan.textContent = data.time;
        who.appendChild(timeSpan);

        const txt = document.createElement('div');
        txt.className   = 'mc-text';
        txt.textContent = data.msg;

        body.appendChild(who);
        body.appendChild(txt);
        div.appendChild(av);
        div.appendChild(body);
        msgs.appendChild(div);
        msgs.scrollTop = msgs.scrollHeight;
        if (msgs.children.length > {$limit}) msgs.children[0].remove();
        if (live && win.classList.contains('mc-hidden')) setUnread(unread + 1);
    }

    // Historial al cargar
    fetch("{$url}/ajax/op=history/n={$history}")
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.error === 0 && data.messages) {
                data.messages.forEach(function(msg) { renderMsg(msg, false); });
            }
        });

    sse.start();

    const btn   = document.getElementById('{$id}_send');
    const input = document.getElementById('{$id}_input');
    btn.addEventListener('click', async function() {
        if (!input.value.trim()) return;
        const result = await sse.sendMessage(1, input.value, {avatar: "{$avatar}"});
        if (!result || result.success !== false) input.value = '';
    });
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') btn.click();
    });
});
JS;
                
        return '<link href="'.SCRIPT_DIR_PLUGINS.'/sse/minichat.css?v=1.0.0" media="screen" rel="stylesheet" type="text/css" />'
             . '<div id="'.$id.'_bubble" class="mc-bubble" title="'.$title.'">&#128172;<span id="'.$id.'_badge" class="mc-badge" style="display:none"></span></div>'
             . '<div id="'.$id.'_win" class="mc-win">'
             .   '<div class="mc-header">'
             .     '<span class="mc-title">'.$title.$sse_server.'</span>'
             .     '<span id="'.$id.'_cd" class="mc-countdown"></span>'
             .     '<button class="mc-btn-min" title="Minimizar">&#8722;</button>'
             .   '</div>'
             .   '<div id="'.$id.'_msgs" class="mc-messages"></div>'
             .   '<div class="mc-footer">'
             .     '<input type="text" id="'.$id.'_input" placeholder="Escribe un mensaje...">'
             .     '<button id="'.$id.'_send">&#9658;</button>'
             .   '</div>'
             . '</div>'
             . '<script>' . $js . '</script>';
    });
