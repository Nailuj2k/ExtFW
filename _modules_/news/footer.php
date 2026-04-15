<?php


    if($_SHOW_ONE){

        ?>
        <script type="text/javascript">
            /*
            window.addEventListener('scroll', function() {
            const docHeight = document.documentElement.scrollHeight - window.innerHeight;
            const scrolled = (window.scrollY / docHeight) * 100;
            document.getElementById('progress-bar').style.width = scrolled + '%';
            });

            */
            const progressBar = document.getElementById('progress-bar');
                let ticking = false;

                window.addEventListener('scroll', function() {
                    // Solo ejecutamos el cálculo si el navegador está listo para pintar un frame
                    if (!ticking) {
                        window.requestAnimationFrame(function() {
                            updateBar();
                            ticking = false;
                        });
                        ticking = true;
                    }
                });

                function updateBar() {
                    const docHeight = document.documentElement.scrollHeight - window.innerHeight;
                    // Evitar división por cero si la página no tiene scroll
                    if (docHeight <= 0) return; 
                    
                    const scrolled = (window.scrollY / docHeight) * 100;
                    progressBar.style.width = scrolled + '%';
                }

            // --- Publish to Nostr ---
            var btnNostr = document.getElementById('btn-publish-nostr');
            if (btnNostr) {

                // Configure noble-secp256k1 sha256
                if (typeof nobleSecp256k1 !== 'undefined' && nobleSecp256k1.utils) {
                    nobleSecp256k1.utils.sha256 = async function() {
                        var t = 0; for (var i = 0; i < arguments.length; i++) t += arguments[i].length;
                        var m = new Uint8Array(t), p = 0;
                        for (var i = 0; i < arguments.length; i++) { m.set(arguments[i], p); p += arguments[i].length; }
                        return new Uint8Array(await crypto.subtle.digest('SHA-256', m));
                    };
                }

                function bytesToHex(bytes) {
                    return Array.from(bytes).map(function(b) { return b.toString(16).padStart(2, '0'); }).join('');
                }

                function loadNostrKeys() {
                    return new Promise(function(resolve) {
                        try {
                            var req = indexedDB.open('JuxNostrKeys', 1);
                            req.onerror = function() { resolve(null); };
                            req.onupgradeneeded = function(e) { e.target.transaction.abort(); resolve(null); };
                            req.onsuccess = function(e) {
                                var db = e.target.result;
                                if (!db.objectStoreNames.contains('keys')) { db.close(); resolve(null); return; }
                                try {
                                    var tx = db.transaction('keys', 'readonly');
                                    var all = tx.objectStore('keys').getAll();
                                    all.onsuccess = function() {
                                        db.close();
                                        var results = all.result || [];
                                        for (var i = 0; i < results.length; i++) {
                                            if (results[i].privkeyHex) { resolve(results[i]); return; }
                                        }
                                        resolve(null);
                                    };
                                    all.onerror = function() { db.close(); resolve(null); };
                                } catch(er) { db.close(); resolve(null); }
                            };
                        } catch(er) { resolve(null); }
                    });
                }

                function escapeHtml(str) {
                    var div = document.createElement('div');
                    div.textContent = str;
                    return div.innerHTML;
                }

                // Sign & publish to relays
                async function signAndPublish(keys, content, nostrTags) {
                    var pubkey = keys ? keys.pubkeyHex : await window.nostr.getPublicKey();
                    var ev = { pubkey: pubkey, created_at: Math.floor(Date.now() / 1000), kind: 1, tags: nostrTags, content: content };

                    var serialized = JSON.stringify([0, ev.pubkey, ev.created_at, ev.kind, ev.tags, ev.content]);
                    var hash = await crypto.subtle.digest('SHA-256', new TextEncoder().encode(serialized));
                    ev.id = bytesToHex(new Uint8Array(hash));

                    var signed;
                    if (keys && keys.privkeyHex) {
                        var sig = await nobleSecp256k1.schnorr.sign(ev.id, keys.privkeyHex);
                        ev.sig = typeof sig === 'string' ? sig : bytesToHex(sig);
                        signed = ev;
                    } else {
                        signed = await window.nostr.signEvent(ev);
                    }

                    var relays = ['wss://relay.damus.io', 'wss://nos.lol', 'wss://relay.nostr.band'];
                    var published = 0;
                    var promises = relays.map(function(url) {
                        return new Promise(function(resolve) {
                            try {
                                var ws = new WebSocket(url);
                                var done = false;
                                ws.onopen = function() { ws.send(JSON.stringify(['EVENT', signed])); };
                                ws.onmessage = function(e) {
                                    try { var msg = JSON.parse(e.data); if (msg[0] === 'OK' && msg[2]) published++; } catch(er) {}
                                    if (!done) { done = true; ws.close(); resolve(); }
                                };
                                ws.onerror = function() { if (!done) { done = true; resolve(); } };
                                setTimeout(function() { if (!done) { done = true; try { ws.close(); } catch(e) {} resolve(); } }, 5000);
                            } catch(e) { resolve(); }
                        });
                    });
                    await Promise.all(promises);
                    return published;
                }

                // Build preview dialog HTML
                function buildPreviewHtml(art) {
                    var html = '<div class="nostr-preview" style="padding:10px;">';

                    if (art.image) {
                        html += '<div class="nostr-preview-image"><img src="' + escapeHtml(art.image) + '" alt=""></div>';
                    }

                    html += '<h3 class="nostr-preview-title">' + escapeHtml(art.title) + '</h3>';
                    html += '<div class="nostr-preview-text" id="nostr-preview-text">' + escapeHtml(art.excerpt) + '</div>';

                    // Options
                    html += '<div class="nostr-preview-options">';
                    html += '<label><input type="checkbox" id="nostr-opt-fulltext"> Incluir texto completo</label>';
                    html += '<label><input type="checkbox" id="nostr-opt-link" checked> Incluir enlace a la web</label>';
                    if (art.image) {
                        html += '<label><input type="checkbox" id="nostr-opt-image" checked> Incluir imagen</label>';
                    }
                    html += '</div>';

                    // Tags with enhanced-select
                    html += '<div class="nostr-preview-tags">';
                    html += '<label>Tags:</label>';
                    html += '<enhanced-select id="nostr-tag-select" multiple keyboard-navigation placeholder="Añadir tag...">';
                    for (var i = 0; i < art.tags.length; i++) {
                        html += '<option value="' + escapeHtml(art.tags[i]) + '" selected>' + escapeHtml(art.tags[i]) + '</option>';
                    }
                    html += '</enhanced-select>';
                    html += '</div>';

                    html += '</div>';
                    return html;
                }

                btnNostr.onclick = async function() {
                    var keys = await loadNostrKeys();

                    if (!keys && typeof window.nostr === 'undefined') {
                        alert('No se encontraron claves Nostr. Inicia sesión en el módulo Noxtr primero.');
                        return;
                    }

                    var articleId = btnNostr.dataset.id;
                    var module = btnNostr.dataset.module;
                    btnNostr.textContent = 'Cargando...';
                    btnNostr.style.pointerEvents = 'none';

                    try {
                        var resp = await fetch('<?= Vars::mkUrl("noxtr", "ajax") ?>', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: 'action=get_article&module=' + module + '&id=' + articleId
                        });
                        var data = await resp.json();
                        if (data.error) throw new Error(data.msg);

                        var art = data.data;

                        // Show preview dialog
                        $('body').dialog({
                            title: 'Publicar en Nostr',
                            type: 'html',
                            content: buildPreviewHtml(art),
                            width: '560px',
                            height: 'auto',
                            buttons: [
                                {
                                    text: 'Publicar',
                                    class: 'btn-primary',
                                    action: async function(event, overlay) {
                                        var btnPub = event.target;
                                        btnPub.textContent = 'Publicando...';
                                        btnPub.style.pointerEvents = 'none';

                                        try {
                                            var useFullText = document.getElementById('nostr-opt-fulltext').checked;
                                            var includeLink = document.getElementById('nostr-opt-link').checked;
                                            var tagSelect = document.getElementById('nostr-tag-select');
                                            var selectedTags = tagSelect.value;
                                            if (typeof selectedTags === 'string') selectedTags = selectedTags ? [selectedTags] : [];

                                            var includeImage = art.image && document.getElementById('nostr-opt-image') && document.getElementById('nostr-opt-image').checked;

                                            // Build content
                                            var body = useFullText ? art.text : art.excerpt;
                                            var content = art.title + '\n\n' + body;
                                            if (includeLink) content += '\n\n' + art.url;
                                            if (includeImage) content += '\n\n' + art.image;
                                            if (selectedTags.length) content += '\n\n' + selectedTags.map(function(t) { return '#' + t; }).join(' ');

                                            // Build Nostr tags
                                            var nostrTags = selectedTags.map(function(t) { return ['t', t.toLowerCase()]; });
                                            if (includeLink) nostrTags.push(['r', art.url]);
                                            if (includeImage) nostrTags.push(['image', art.image]);

                                            var published = await signAndPublish(keys, content, nostrTags);

                                            if (published > 0) {
                                                overlay._dialogInstance.close();
                                                btnNostr.textContent = 'Publicado en ' + published + ' relay' + (published > 1 ? 's' : '');
                                                btnNostr.classList.add('btn-nostr-done');
                                            } else {
                                                throw new Error('No se pudo conectar a los relays');
                                            }
                                        } catch(e) {
                                            alert('Error: ' + e.message);
                                            btnPub.textContent = 'Publicar';
                                            btnPub.style.pointerEvents = '';
                                        }
                                    }
                                },
                                {
                                    text: 'Cancelar',
                                    class: 'btn-secondary',
                                    action: function(event, overlay) {
                                        overlay._dialogInstance.close();
                                    }
                                }
                            ],
                            onLoad: function() {
                                // Toggle full text / excerpt preview
                                var chk = document.getElementById('nostr-opt-fulltext');
                                var previewDiv = document.getElementById('nostr-preview-text');
                                if (chk && previewDiv) {
                                    chk.onchange = function() {
                                        previewDiv.textContent = chk.checked ? art.text : art.excerpt;
                                    };
                                }
                            },
                            onClose: function() {
                                btnNostr.textContent = 'Publicar en Nostr';
                                btnNostr.style.pointerEvents = '';
                            }
                        });

                    } catch(e) {
                        alert('Error: ' + e.message);
                    }

                    btnNostr.textContent = 'Publicar en Nostr';
                    btnNostr.style.pointerEvents = '';
                };
            }
        </script>
        <?php


    }else if($_SHOW_ALL){


    }else if($_SHOW_404){

       //CFG::$vars['widget']['404']=true;
       //ajax_load_url('404'); 
        ?>
        <script type="text/javascript">
            $(function(){

                //$('#div-404').load('/404/html');
                
                $.get('/404/html',function(data){
                    $('#div-404').html(data);
                }).fail(function(){
                    $('#div-404').html('error');
                });
                
            });
        </script>
        <?php

    }else{

      

    }

    include( SCRIPT_DIR_MODULES  . '/page/inline_footer.php' ); 