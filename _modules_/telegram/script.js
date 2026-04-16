(function () {
    'use strict';

    const Telegram = {

        _cfg: {},

        init(cfg) {
            this._cfg = cfg;
            if (!cfg.isConfigured || !cfg.userId) return;
            this._checkStatus();
            this._bindEvents();
        },

        _ajax(action, params = {}) {
            const body = new URLSearchParams({ action, ...params });
            return fetch(this._cfg.ajaxUrl, { method: 'POST', body })
                .then(r => r.json());
        },

        _checkStatus() {
            this._ajax('status').then(r => {
                document.getElementById('tg-loading')?.style && (document.getElementById('tg-loading').style.display = 'none');
                if (r.linked) {
                    const name = document.getElementById('tg-linked-name');
                    if (name) name.textContent = r.first_name ? r.first_name + (r.username ? ' (@' + r.username + ')' : '') : r.chat_id;
                    document.getElementById('tg-linked-panel')?.style && (document.getElementById('tg-linked-panel').style.display = '');
                } else {
                    document.getElementById('tg-unlinked-panel')?.style && (document.getElementById('tg-unlinked-panel').style.display = '');
                }
            });
        },

        _bindEvents() {
            document.getElementById('btn-tg-link')?.addEventListener('click', () => this._startLink());
            document.getElementById('btn-tg-unlink')?.addEventListener('click', () => this._unlink());
            document.getElementById('btn-tg-test')?.addEventListener('click', () => this._test());
        },

        _startLink() {
            this._ajax('get_token').then(r => {
                if (r.error) { alert('Error: ' + r.msg); return; }

                const steps = document.getElementById('tg-link-steps');
                const link  = document.getElementById('tg-deep-link');
                const cmd   = document.getElementById('tg-start-cmd');

                if (r.deep_link) {
                    link.href = r.deep_link;
                    link.textContent = 'Abrir @' + r.bot_username + ' en Telegram';
                } else {
                    link.href = '#';
                    link.textContent = 'Bot no configurado';
                }

                if (cmd) cmd.textContent = '/start ' + r.token;

                steps.style.display = '';

                document.getElementById('btn-tg-copy')?.addEventListener('click', () => {
                    navigator.clipboard.writeText('/start ' + r.token).then(() => {
                        const btn = document.getElementById('btn-tg-copy');
                        if (btn) { btn.textContent = '¡Copiado!'; setTimeout(() => btn.textContent = 'Copiar', 2000); }
                    });
                });
            });
        },

        _unlink() {
            if (!confirm('¿Desvincular Telegram?')) return;
            this._ajax('unlink').then(() => location.reload());
        },

        _test() {
            this._ajax('test_message').then(r => {
                alert(r.ok ? 'Mensaje enviado correctamente.' : 'Error: ' + (r.msg || 'desconocido'));
            });
        },
    };

    window.Telegram = Telegram;

})();
