/**
 * SSEClient — cliente SSE reutilizable para ExtFW
 *
 * Uso básico:
 *   const sse = new SSEClient({
 *       url:      'https://queesbitcoin.net/sse',
 *       username: _SSE_USERNAME_,
 *       userid:   _SSE_USERID_,
 *       log:      'log',                          // id de un <div> para log automático (opcional)
 *       onMessage: (data) => { ... },             // recibe evento completo
 *       onPong:    (data) => { ... },             // heartbeat (time, count, limit)
 *   });
 *   sse.start();
 *   sse.sendMessage(1, 'hola mundo');
 *
 * Kinds soportados (inspirados en Nostr):
 *   1   texto público              { content }
 *   4   DM privado                 { content, to_userid }
 *   40  crear canal                { name, about, picture }
 *   41  metadatos canal            { name, about }
 *   42  mensaje en canal           { content, channel }
 *   100 notificación sistema       { content, action, url }
 */
class SSEClient {

    constructor(options = {}) {
        this.url      = options.url      || '';   // ej: 'https://queesbitcoin.net/sse'
        this.username = options.username || '';
        this.userid   = options.userid   || 0;
        this.logEl    = options.log ? document.getElementById(options.log) : null;

        // Callbacks
        this.onMessage    = options.onMessage    || null;  // fn(data)
        this.onPong       = options.onPong       || null;  // fn(data) — heartbeat
        this.onConnect    = options.onConnect    || null;  // fn()
        this.onDisconnect = options.onDisconnect || null;  // fn()
        this.onError      = options.onError      || null;  // fn(message)

        this._es                = null;
        this._lastMsgId         = 0;
        this._manualStop        = false;
        this._errorStop         = false;
        this._completed         = false;
        this._lastActivity      = Date.now();
        this._activityThrottle  = false;
        this._lastPong          = 0;
        this.autoConnect        = options.autoConnect ?? true;
        this.connected          = false;

        this._bindActivity();
    }

    // ── Conexión ──────────────────────────────────────────────────────────────

    start() {
        this.connected = true;
        if (this._es) return;
        this._completed  = false;
        this._manualStop = false;

        const streamUrl = this.url + '/raw/start/t=' + Date.now()
            + (this._lastMsgId ? '/from=' + this._lastMsgId : '');

        this._es = new EventSource(streamUrl, { withCredentials: true });

        this._es.onopen = () => {
            if (this.onConnect) this.onConnect();
            this._log('Conexión establecida', 'success');
        };

        this._es.onmessage = (e) => {
            try {
                const data = JSON.parse(e.data);

                if (data.error) {
                    this._log(data.message, 'error');
                    if (this.onError) this.onError(data.message);
                    this._errorStop = true;
                    this.stop(true); // error del servidor = parada definitiva, no reconectar

                } else if (data.message === 'complete') {
                    this._completed = true;
                    this.stop();
                    if (!this._manualStop && this._isUserActive(data.limit || 30)) {
                        setTimeout(() => this.start(), 500);
                    }

                } else if (data.msg !== undefined) {
                    // Compatibilidad: si msg llega como JSON string (servidor sin opcache actualizado),
                    // extraer content y reconstruir payload
                    if (typeof data.msg === 'string' && data.msg.charAt(0) === '{') {
                        try {
                            const p = JSON.parse(data.msg);
                            if (p.content !== undefined) {
                                data.payload = p;
                                data.msg     = p.content;
                            }
                        } catch(e) {}
                    }
                    if (data.id) this._lastMsgId = data.id;
                    if (this.onMessage) this.onMessage(data);
                    this._logMessage(data);

                } else {
                    // heartbeat / pong
                    this._lastPong = Date.now();
                    if (this.onPong) this.onPong(data);
                    // renovar conexión antes de que expire
                    if (data.count && data.limit && !this._manualStop) {
                        const remaining = data.limit - data.count;
                        if (remaining <= 3 && this._isUserActive(data.limit)) {
                            this._completed = true; // evita que onerror lo trate como error
                            this.stop();
                            setTimeout(() => this.start(), 200);
                        }
                    }
                }
            } catch (err) {
                this._log('Error parsing: ' + err.message, 'error');
            }
        };

        this._es.onerror = () => {
            const hadRecentPong = (Date.now() - this._lastPong) < 5000;
            if (!this._completed && !hadRecentPong) {
                // Fallo real: nunca conectó o llevas >5s sin heartbeat
                if (this.onError) this.onError('connection_error');
                this._log('Error en la conexión', 'error');
                this._errorStop = true;
                this.stop(true);
            } else {
                // Cierre limpio (race con complete, o early reconnect)
                this.stop();
                if (!this._manualStop && !this._errorStop && this._isUserActive(30)) {
                    setTimeout(() => this.start(), 500);
                }
            }
        };
    }

    stop(manual = false) {
        this.connected = false;
        if (manual) this._manualStop = true;
        if (this._es) {
            this._es.close();
            this._es = null;
            if (this.onDisconnect) this.onDisconnect();
        }
    }

    // ── Envío ─────────────────────────────────────────────────────────────────

    /**
     * @param {number} kind     — tipo de mensaje (1, 4, 40, 41, 42, 100...)
     * @param {string} content  — texto principal (campo 'content' del payload)
     * @param {object} extra    — campos adicionales según kind (to_userid, channel, action, url, name...)
     * @returns {Promise<object>}
     */
    async sendMessage(kind = 1, content = '', extra = {}) {
        const params = {
            kind,
            text:     content,
            username: this.username,
            userid:   this.userid,
            ...extra
        };

        const body = new URLSearchParams(
            Object.entries(params).filter(([, v]) => v !== '' && v !== null && v !== undefined)
        );

        const response = await fetch(this.url + '/ajax/op=msg', {
            method:      'POST',
            /////////////////////////////////////////////////////////credentials: 'include',
            body,
        });
        const json = await response.json();

        if (json.error > 0) {
            const errorMsg = json.msg || 'Error desconocido';
            this._log('Error al enviar mensaje: ' + errorMsg, 'error');
            if (this.onError) this.onError(errorMsg);
            return { success: false, error: errorMsg };
        }

        // Envío exitoso: si el stream estaba parado por error del servidor, reiniciarlo
        if (this._errorStop) {
            this._errorStop  = false;
            this._manualStop = false;
            this.start();
        }

        return json;
    }

    // ── Actividad del usuario ─────────────────────────────────────────────────

    _bindActivity() {
        if (this.autoConnect !== true) return;
        const events = ['mousemove', 'keydown', 'click', 'scroll', 'touchstart', 'touchmove'];
        events.forEach(ev => document.addEventListener(ev, () => {
            if (this._activityThrottle) return;
            this._activityThrottle = true;
            this._lastActivity = Date.now();
            if (!this._es && !this._manualStop) this.start();
            setTimeout(() => { this._activityThrottle = false; }, 2000);
        }, { passive: true }));
    }

    _isUserActive(seconds) {
        return (Date.now() - this._lastActivity) < seconds * 1000;
    }

    // ── Log ───────────────────────────────────────────────────────────────────

    _log(msg, type = 'info') {
        if (!this.logEl) return;
        const div = document.createElement('div');
        div.className = 'message ' + type;
        div.textContent = '[' + new Date().toLocaleTimeString() + '] ' + msg;
        this.logEl.appendChild(div);
        this.logEl.scrollTop = this.logEl.scrollHeight;
    }

    _logMessage(data) {
        if (!this.logEl) return;
        const who = (data.username || data.domain)
            ? (data.username || '') + (data.domain ? '@' + data.domain.replace(/https?:\/\//, '') : '') + ': '
            : '';
        const div = document.createElement('div');
        div.className = 'message info';
        div.textContent = '[' + data.time + '] ' + who + data.msg;
        this.logEl.appendChild(div);
        this.logEl.scrollTop = this.logEl.scrollHeight;
    }

}
