/**
 * PasswordlessAuth - Cliente JS para login sin contraseña
 * Usa Web Crypto API con IndexedDB para almacenamiento seguro
 * Genera dos pares de claves: ECDSA para firma y ECDH para cifrado
 */

const PasswordlessAuth = {

    // Configuración IndexedDB
    DB_NAME: 'PasswordlessAuthDB',
    DB_VERSION: 1,
    STORE_NAME: 'keys',

    /**
     * Verificar si el navegador soporta Web Crypto y IndexedDB
     */
    isSupported() {
        return window.crypto && window.crypto.subtle && window.indexedDB;
    },

    // ========== INDEXEDDB ==========

    /**
     * Abrir base de datos IndexedDB
     */
    async openDB() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(this.DB_NAME, this.DB_VERSION);

            request.onerror = () => reject(request.error);
            request.onsuccess = () => resolve(request.result);

            request.onupgradeneeded = (event) => {
                const db = event.target.result;
                if (!db.objectStoreNames.contains(this.STORE_NAME)) {
                    db.createObjectStore(this.STORE_NAME, { keyPath: 'id' });
                }
            };
        });
    },

    /**
     * Guardar claves en IndexedDB
     * @param {number} userId - ID del usuario (permite múltiples usuarios en mismo navegador)
     */
    async saveKeysToIndexedDB(signPriv, signPub, encPriv, encPup, deviceId, userId) {
        // IMPORTANTE: Asegurar que userId es siempre número para consistencia
        const numericUserId = parseInt(userId, 10);
        if (isNaN(numericUserId)) {
            console.error('[saveKeysToIndexedDB] ERROR: userId inválido:', userId);
            throw new Error('userId debe ser un número válido');
        }

        const db = await this.openDB();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(this.STORE_NAME, 'readwrite');
            const store = tx.objectStore(this.STORE_NAME);

            store.put({
                id: numericUserId, // Siempre número para consistencia
                deviceId: deviceId,
                signPrivateKey: signPriv,
                signPublicKey: signPub,
                encPrivateKey: encPriv,
                encPublicKey: encPup,
                savedAt: new Date().toISOString(),
                version: 2 // Para futuras migraciones
            });

            tx.oncomplete = () => resolve(true);
            tx.onerror = () => reject(tx.error);
        });
    },

    /**
     * Guardar claves generadas (wrapper para device_linking.js y PasswordlessRegistration)
     * @param {object} keys - Objeto con signPrivB64, signPubB64, encPrivB64, encPubB64
     * @param {string} deviceId - UUID del dispositivo
     * @param {number} userId - ID del usuario
     */
    async saveKeys(keys, deviceId, userId) {
        // IMPORTANTE: Forzar userId a número para consistencia con IndexedDB
        const numericUserId = parseInt(userId, 10);
        console_log('[saveKeys] userId original:', userId, 'type:', typeof userId);
        console_log('[saveKeys] userId numérico:', numericUserId, 'type:', typeof numericUserId);

        return await this.saveKeysToIndexedDB(
            keys.signPrivB64,
            keys.signPubB64,
            keys.encPrivB64,
            keys.encPubB64,
            deviceId,
            numericUserId
        );
    },

    /**
     * Cargar claves desde IndexedDB
     * @param {number} userId - ID del usuario (si no se proporciona, intenta obtenerlo del servidor)
     */
    async loadKeysFromIndexedDB(userId = null) {
        console_log('[loadKeysFromIndexedDB] Input userId:', userId, 'type:', typeof userId);

        // Si no se proporciona userId, intentar obtenerlo del servidor
        if (!userId) {
            const userInfo = await this.getCurrentUserId();
            userId = userInfo?.userId;
            console_log('[loadKeysFromIndexedDB] userId from server:', userId);
        }

        if (!userId) {
            console_log('[loadKeysFromIndexedDB] No userId available, returning null');
            return null; // No hay usuario logueado
        }

        // IMPORTANTE: Asegurar que userId es número para consistencia con cómo se guardó
        const numericUserId = parseInt(userId, 10);
        console_log('[loadKeysFromIndexedDB] Opening DB and getting key for userId:', numericUserId, 'type:', typeof numericUserId);

        const db = await this.openDB();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(this.STORE_NAME, 'readonly');
            const store = tx.objectStore(this.STORE_NAME);
            const request = store.get(numericUserId);

            request.onsuccess = () => {
                console_log('[loadKeysFromIndexedDB] Result:', request.result ? 'FOUND' : 'NOT FOUND');
                if (request.result) {
                    console_log('[loadKeysFromIndexedDB] Stored id type:', typeof request.result.id);
                }
                resolve(request.result || null);
            };
            request.onerror = () => {
                console.error('[loadKeysFromIndexedDB] Error:', request.error);
                reject(request.error);
            };
        });
    },

    /**
     * Eliminar claves de IndexedDB
     * @param {number} userId - ID del usuario (si no se proporciona, intenta obtenerlo del servidor)
     */
    async deleteKeysFromIndexedDB(userId = null) {
        // Si no se proporciona userId, intentar obtenerlo del servidor
        if (!userId) {
            const userInfo = await this.getCurrentUserId();
            userId = userInfo?.userId;
        }

        if (!userId) {
            return false; // No hay usuario logueado
        }

        // IMPORTANTE: Asegurar que userId es número para consistencia
        const numericUserId = parseInt(userId, 10);

        const db = await this.openDB();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(this.STORE_NAME, 'readwrite');
            const store = tx.objectStore(this.STORE_NAME);
            store.delete(numericUserId);

            tx.oncomplete = () => resolve(true);
            tx.onerror = () => reject(tx.error);
        });
    },

    /**
     * Obtener userId del usuario actualmente logueado
     * @returns {Promise<{userId: number}|null>}
     */
    async getCurrentUserId() {
        try {
            const response = await fetch('login/ajax/op=get_current_user_id', {
                method: 'POST'
            });
            const data = await response.json();
            return data.userId ? { userId: data.userId } : null;
        } catch (e) {
            console.error('Error getting current user ID:', e);
            return null;
        }
    },

    // ========== GENERACIÓN DE CLAVES ==========

    /**
     * Generar par de claves ECDSA P-256 para firma
     */
    async generateSignKeys() {
        return await crypto.subtle.generateKey(
            { name: "ECDSA", namedCurve: "P-256" },
            true, // extractable
            ["sign", "verify"]
        );
    },

    /**
     * Generar par de claves ECDH P-256 para cifrado
     */
    async generateEncKeys() {
        return await crypto.subtle.generateKey(
            { name: "ECDH", namedCurve: "P-256" },
            true, // extractable
            ["deriveKey"]
        );
    },

    /**
     * Exportar clave a base64
     */
    async exportKey(key, format) {
        const exported = await crypto.subtle.exportKey(format, key);
        return btoa(String.fromCharCode(...new Uint8Array(exported)));
    },

    /**
     * Generar ambos pares de claves y exportarlos (para registro passwordless)
     * @returns {Promise<{signPubB64, signPrivB64, encPubB64, encPrivB64}>}
     */
    async generateKeyPairs() {
        // Generar dos pares de claves
        const signKeyPair = await this.generateSignKeys();
        const encKeyPair = await this.generateEncKeys();

        // Exportar claves a base64
        const signPrivB64 = await this.exportKey(signKeyPair.privateKey, 'pkcs8');
        const signPubB64 = await this.exportKey(signKeyPair.publicKey, 'spki');
        const encPrivB64 = await this.exportKey(encKeyPair.privateKey, 'pkcs8');
        const encPubB64 = await this.exportKey(encKeyPair.publicKey, 'spki');

        return {
            signPubB64,
            signPrivB64,
            encPubB64,
            encPrivB64
        };
    },

    /**
     * Importar clave privada ECDSA desde base64
     */
    async importSignPrivateKey(base64) {
        const binary = Uint8Array.from(atob(base64), c => c.charCodeAt(0));
        return await crypto.subtle.importKey(
            "pkcs8",
            binary,
            { name: "ECDSA", namedCurve: "P-256" },
            true,
            ["sign"]
        );
    },

    /**
     * Firmar un challenge
     */
    async sign(data, privateKey) {
        const encoder = new TextEncoder();
        const signature = await crypto.subtle.sign(
            { name: "ECDSA", hash: "SHA-256" },
            privateKey,
            encoder.encode(data)
        );
        return btoa(String.fromCharCode(...new Uint8Array(signature)));
    },

    // ========== GESTIÓN DE CLAVES ==========

    /**
     * Verificar si hay claves guardadas en IndexedDB
     * @param {number} userId - ID del usuario (opcional, si no se proporciona intenta obtenerlo del servidor)
     */
    async hasKeys(userId = null) {
        try {
            const keys = await this.loadKeysFromIndexedDB(userId);
            return keys !== null;
        } catch (e) {
            console.error('Error checking IndexedDB:', e);
            return false;
        }
    },

    /**
     * Verificar si hay CUALQUIER clave guardada en IndexedDB (sin necesidad de userId)
     * Útil para la página de login donde no hay sesión activa
     */
    async hasAnyKeys() {
        try {
            const db = await this.openDB();
            return new Promise((resolve, reject) => {
                const tx = db.transaction(this.STORE_NAME, 'readonly');
                const store = tx.objectStore(this.STORE_NAME);
                const countRequest = store.count();

                countRequest.onsuccess = () => resolve(countRequest.result > 0);
                countRequest.onerror = () => reject(countRequest.error);
            });
        } catch (e) {
            console.error('Error checking IndexedDB:', e);
            return false;
        }
    },

    /**
     * Cargar clave privada de firma
     * @param {number} userId - ID del usuario (opcional)
     */
    async loadSignPrivateKey(userId = null) {
        const stored = await this.loadKeysFromIndexedDB(userId);
        if (!stored) return null;

        return await this.importSignPrivateKey(stored.signPrivateKey);
    },

    /**
     * Obtener claves públicas guardadas
     * @param {number} userId - ID del usuario (opcional)
     */
    async getPublicKeys(userId = null) {
        const stored = await this.loadKeysFromIndexedDB(userId);
        if (!stored) return null;

        return {
            deviceId: stored.deviceId,
            signPublicKey: stored.signPublicKey,
            encPublicKey: stored.encPublicKey
        };
    },

    /**
     * Obtener device ID guardado (primero de IndexedDB, luego de localStorage)
     * @param {number} userId - ID del usuario (opcional)
     */
    async getDeviceId(userId = null) {
        // Intentar obtener de IndexedDB primero
        const stored = await this.loadKeysFromIndexedDB(userId);
        if (stored?.deviceId) {
            return stored.deviceId;
        }

        // Si no está en IndexedDB, buscar en localStorage
        return localStorage.getItem('passwordless_device_id') || null;
    },

    /**
     * Obtener o generar device ID persistente
     * Si ya existe en localStorage, lo reutiliza. Si no, genera uno nuevo.
     */
    async getOrCreateDeviceId() {
        // Buscar en localStorage
        let deviceId = localStorage.getItem('passwordless_device_id');

        if (!deviceId) {
            // Generar nuevo UUID y guardarlo en localStorage
            deviceId = crypto.randomUUID();
            localStorage.setItem('passwordless_device_id', deviceId);
        }

        return deviceId;
    },

    /**
     * Eliminar claves
     */
    async deleteKeys() {
        await this.deleteKeysFromIndexedDB();
    },

    /**
     * Revocar dispositivo en el servidor
     */
    async revokeDevice(deviceId) {
        const response = await fetch('login/ajax/op=revoke_device', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ deviceId })
        });
        return await response.json();
    },

    // ========== FLUJO DE LOGIN ==========

    /**
     * Solicitar challenge al servidor
     */
    async requestChallenge(identifier) {
        const response = await fetch('login/ajax/op=passwordless_challenge', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ identifier })
        });
        return await response.json();
    },

    /**
     * Enviar firma al servidor
     */
    async submitSignature(identifier, challenge, signature, deviceId) {
        const response = await fetch('login/ajax/op=passwordless_verify', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ identifier, challenge, signature, deviceId })
        });
        return await response.json();
    },

    /**
     * Flujo completo de login
     */
    async login(identifier) {
        // 1. Solicitar challenge (nos da el user_id)
        const challengeResult = await this.requestChallenge(identifier);
        console_log('[login] Challenge result:', challengeResult);
        if (challengeResult.error) {
            return challengeResult;
        }

        // IMPORTANTE: Convertir a número porque PHP puede devolverlo como string
        const userId = parseInt(challengeResult.user_id, 10);
        console_log('[login] userId from server:', userId, '(type:', typeof userId, ')');
        if (!userId || isNaN(userId)) {
            return { error: 'no_user_id', msg: 'No se pudo obtener el ID del usuario' };
        }

        // 2. Cargar clave privada para este userId
        console_log('[login] Intentando cargar claves para userId:', userId);
        const stored = await this.loadKeysFromIndexedDB(userId);
        console_log('[login] Claves cargadas de IndexedDB:', stored);

        const privateKey = await this.loadSignPrivateKey(userId);
        console_log('[login] Private key loaded:', privateKey ? 'YES' : 'NO');
        if (!privateKey) {
            return { error: 'no_local_keys', msg: 'No tienes claves guardadas en este navegador para este usuario' };
        }

        const deviceId = await this.getDeviceId(userId);
        console_log('[login] deviceId:', deviceId);
        if (!deviceId) {
            return { error: 'no_device_id', msg: 'No se encontró el identificador del dispositivo' };
        }

        // 3. Firmar challenge
        const signature = await this.sign(challengeResult.challenge, privateKey);

        // 4. Enviar firma con device ID
        const result = await this.submitSignature(identifier, challengeResult.challenge, signature, deviceId);

        return result;
    },

    // ========== CONFIGURACIÓN DE CLAVES ==========

    /**
     * Registrar dispositivo con sus claves públicas en el servidor
     */
    async registerKeysOnServer(deviceId, signPubKey, encPubKey, deviceName = '') {
        // Generar nombre de dispositivo si no se proporciona
        if (!deviceName) {
            const browser = this.getBrowserName();
            const platform = navigator.platform || 'Unknown';
            deviceName = `${browser} en ${platform}`;
        }

        const response = await fetch('login/ajax/op=update_user_keys', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                deviceId: deviceId,
                signPub: signPubKey,
                encPub: encPubKey,
                deviceName: deviceName
            })
        });
        return await response.json();
    },

    /**
     * Obtener nombre del navegador
     */
    getBrowserName() {
        const ua = navigator.userAgent;
        if (ua.includes('Firefox/')) return 'Firefox';
        if (ua.includes('Edg/')) return 'Edge';
        if (ua.includes('Chrome/')) return 'Chrome';
        if (ua.includes('Safari/') && !ua.includes('Chrome/')) return 'Safari';
        if (ua.includes('Opera/') || ua.includes('OPR/')) return 'Opera';
        return 'Navegador';
    },

    /**
     * Generar nombre descriptivo del dispositivo
     */
    getDeviceName() {
        const browser = this.getBrowserName();
        const platform = navigator.platform || 'Unknown';
        return `${browser} en ${platform}`;
    },

    /**
     * Configurar login passwordless (generar claves y registrar)
     */
    async setup() {
        // 0. Obtener userId del usuario logueado
        const userInfo = await this.getCurrentUserId();
        console_log('[setup] userInfo:', userInfo);
        if (!userInfo || !userInfo.userId) {
            return { error: 'not_logged_in', msg: 'Debes iniciar sesión primero' };
        }

        // 1. Obtener o generar UUID del dispositivo (persistente en localStorage)
        const deviceId = await this.getOrCreateDeviceId();
        console_log('[setup] deviceId:', deviceId);

        // 2. Generar dos pares de claves
        const signKeyPair = await this.generateSignKeys();
        const encKeyPair = await this.generateEncKeys();

        // 3. Exportar claves
        const signPrivB64 = await this.exportKey(signKeyPair.privateKey, 'pkcs8');
        const signPubB64 = await this.exportKey(signKeyPair.publicKey, 'spki');
        const encPrivB64 = await this.exportKey(encKeyPair.privateKey, 'pkcs8');
        const encPubB64 = await this.exportKey(encKeyPair.publicKey, 'spki');

        // 4. Guardar en IndexedDB con device ID y userId
        console_log('[setup] Guardando en IndexedDB con userId:', userInfo.userId);
        await this.saveKeysToIndexedDB(signPrivB64, signPubB64, encPrivB64, encPubB64, deviceId, userInfo.userId);
        console_log('[setup] Claves guardadas en IndexedDB');

        // Verificar que se guardaron correctamente
        const verification = await this.loadKeysFromIndexedDB(userInfo.userId);
        console_log('[setup] Verificación - claves recuperadas:', verification ? 'YES' : 'NO');

        // 5. Registrar claves públicas y device ID en servidor
        console_log('[setup] Registrando en servidor...');
        const result = await this.registerKeysOnServer(deviceId, signPubB64, encPubB64);
        console_log('[setup] Resultado del servidor:', result);

        if (result.success) {
            return { success: true, msg: 'Login sin contraseña configurado correctamente' };
        }

        return result;
    },

    /**
     * Verificar si el usuario tiene claves en el servidor
     */
    async checkServerKeys() {
        const response = await fetch('login/ajax/op=get_user_has_keys', {
            method: 'POST'
        });
        return await response.json();
    },

    // ========== GESTIÓN DE PIN ==========

    /**
     * Guardar PIN en el servidor
     */
    async savePIN(pin) {
        const response = await fetch('login/ajax/op=save_pin', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ pin })
        });
        return await response.json();
    },

    /**
     * Verificar si el usuario tiene PIN configurado
     */
    async hasPIN() {
        const response = await fetch('login/ajax/op=check_user_pin', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ identifier: '' })
        });
        const data = await response.json();
        return data.has_pin || false;
    },

    /**
     * Verificar PIN (usado durante el login)
     */
    async verifyPIN(identifier, pin) {
        const response = await fetch('login/ajax/op=verify_pin', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ identifier, pin })
        });
        return await response.json();
    }
};

// ============================================================================
// INICIALIZACIÓN EN PÁGINA DE PERFIL
// ============================================================================
document.addEventListener('DOMContentLoaded', function() {
    const setupDiv = document.getElementById('passwordless-setup');
    if (!setupDiv) return; // No estamos en la página de perfil

    const statusEl = document.getElementById('passwordless-status');
    const setupBtn = document.getElementById('btn-setup-passwordless');
    const removeBtn = document.getElementById('btn-remove-passwordless');

    if (!statusEl || !setupBtn || !removeBtn) return;

    // Verificar estado
    (async function() {
        try {
            const hasLocalKeys = await PasswordlessAuth.hasKeys();
            const serverCheck = await PasswordlessAuth.checkServerKeys();

            if (serverCheck.has_keys && hasLocalKeys) {
                statusEl.innerHTML = '<span style="color:green;">✓ Login sin contraseña activo</span>';
                removeBtn.style.display = 'inline-block';
            } else if (serverCheck.has_keys && !hasLocalKeys) {
                statusEl.innerHTML = '<span style="color:orange;">⚠ Claves en servidor pero no en este navegador. Reconfigura.</span>';
                setupBtn.style.display = 'inline-block';
            } else {
                statusEl.innerHTML = 'No configurado. Genera claves para acceder sin contraseña.';
                setupBtn.style.display = 'inline-block';
            }
        } catch (e) {
            statusEl.innerHTML = '<span style="color:red;">Error al verificar estado</span>';
            console.error('Passwordless check error:', e);
        }
    })();

    // Evento: Configurar
    setupBtn.addEventListener('click', async function() {
        if (!confirm('¿Generar claves para login sin contraseña?\n\nSe crearán dos pares de claves:\n• Clave de firma (autenticación)\n• Clave de cifrado (mensajes seguros)\n\nLas claves se guardarán de forma segura en este navegador.')) return;

        setupBtn.disabled = true;
        setupBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Generando claves...';

        try {
            const result = await PasswordlessAuth.setup();
            if (result.success) {
                // Claves generadas correctamente, preguntar por PIN
                showPINSetupDialog();
            } else {
                alert('Error: ' + (result.msg || result.error));
                setupBtn.disabled = false;
                setupBtn.innerHTML = '<i class="fa fa-shield"></i> Configurar login sin contraseña';
            }
        } catch (e) {
            alert('Error: ' + e.message);
            setupBtn.disabled = false;
            setupBtn.innerHTML = '<i class="fa fa-shield"></i> Configurar login sin contraseña';
        }
    });

    // Evento: Eliminar claves locales
    removeBtn.addEventListener('click', async function() {
        if (!confirm('¿Eliminar claves de este navegador?\n\nNo podrás usar login sin contraseña desde aquí.')) return;

        removeBtn.disabled = true;
        removeBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Eliminando...';

        try {
            // Obtener device ID antes de eliminar
            const deviceId = await PasswordlessAuth.getDeviceId();

            // 1. Revocar en servidor (ACTIVE=0 y borrar claves públicas)
            if (deviceId) {
                const revokeResult = await PasswordlessAuth.revokeDevice(deviceId);
                if (!revokeResult.success) {
                    console.warn('Error al revocar en servidor:', revokeResult.error);
                }
            }

            // 2. Eliminar de IndexedDB
            await PasswordlessAuth.deleteKeys();

            alert('Claves eliminadas de este navegador.');
            location.reload();
        } catch (e) {
            alert('Error al eliminar claves: ' + e.message);
            removeBtn.disabled = false;
            removeBtn.innerHTML = '<i class="fa fa-trash"></i> Eliminar claves de este navegador';
        }
    });

    // ========== DIALOG PARA CONFIGURAR PIN ==========
    function showPINSetupDialog() {
        $("body").dialog({
            title: "Configurar PIN (opcional)",
            type: 'html',
            width: '500px',
            openAnimation: 'zoom',
            closeAnimation: 'fade',
            closeOnEscape: false,
            closeOnClickOutside: false,
            content: `
                <div style="padding: 20px;">
                    <p style="margin-bottom: 15px; line-height: 1.5;">
                        <strong>¿Desea configurar un PIN de 4 dígitos?</strong>
                    </p>
                    <p style="margin-bottom: 15px; line-height: 1.5; color: #666;">
                        El PIN es <strong>opcional pero muy recomendable</strong> si otras personas tienen acceso físico a este equipo.
                    </p>
                    <p style="margin-bottom: 20px; line-height: 1.5; color: #666;">
                        Si es usted el único que usa este equipo, puede omitirlo y continuar sin PIN.
                    </p>
                    <div id="pin-input-section" style="display:none; margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: bold;">PIN (4 dígitos):</label>
                        <input type="text" inputmode="numeric" id="pin-input-1" maxlength="4" pattern="[0-9]{4}"
                               autocomplete="off"
                               style="width: 100%; padding: 10px; font-size: 24px; text-align: center; letter-spacing: 10px; font-family: monospace; border: 2px solid #ccc; border-radius: 4px;">
                        <label style="display: block; margin-top: 15px; margin-bottom: 5px; font-weight: bold;">Confirmar PIN:</label>
                        <input type="text" inputmode="numeric" id="pin-input-2" maxlength="4" pattern="[0-9]{4}"
                               autocomplete="off"
                               style="width: 100%; padding: 10px; font-size: 24px; text-align: center; letter-spacing: 10px; font-family: monospace; border: 2px solid #ccc; border-radius: 4px;">
                        <p id="pin-error" style="color: red; margin-top: 10px; display: none;"></p>
                    </div>
                </div>
            `,
            buttons: [
                {
                    text: 'Cancelar',
                    class: 'btn btn-danger',
                    action: async function(event, overlay) {
                        // Cancelar = Eliminar las claves recién creadas
                        if (confirm('¿Cancelar la configuración?\n\nSe eliminarán las claves recién creadas.')) {
                            try {
                                await PasswordlessAuth.deleteKeys();
                                const deviceId = localStorage.getItem('passwordless_device_id');
                                if (deviceId) {
                                    await PasswordlessAuth.revokeDevice(deviceId);
                                }
                            } catch (e) {
                                console.error('Error al cancelar:', e);
                            }
                            document.body.removeChild(overlay);
                            location.reload();
                        }
                    }
                },
                {
                    text: 'Continuar sin PIN',
                    class: 'btn btn-secondary',
                    action: function(event, overlay) {
                        document.body.removeChild(overlay);
                        location.reload();
                    }
                },
                {
                    text: 'Configurar PIN',
                    class: 'btn btn-primary',
                    action: async function(event, overlay) {
                        const pinSection = document.getElementById('pin-input-section');
                        const pin1Input = document.getElementById('pin-input-1');
                        const pin2Input = document.getElementById('pin-input-2');
                        const errorMsg = document.getElementById('pin-error');

                        // Si aún no se mostraron los inputs, mostrarlos
                        if (pinSection.style.display === 'none') {
                            pinSection.style.display = 'block';
                            // Cambiar texto del botón
                            event.target.textContent = 'Guardar PIN';

                            pin1Input.focus();

                            // Configurar eventos para ambos inputs
                            const setupPinInput = (input, nextInput = null) => {
                                // Solo permitir números
                                input.addEventListener('input', function(e) {
                                    e.target.value = e.target.value.replace(/[^0-9]/g, '');

                                    // Si se completaron 4 dígitos y hay siguiente input, hacer focus
                                    if (e.target.value.length === 4 && nextInput) {
                                        nextInput.focus();
                                    }
                                });

                                // Submit con Enter
                                input.addEventListener('keydown', function(e) {
                                    if (e.key === 'Enter') {
                                        e.preventDefault();
                                        if (e.target.value.length === 4) {
                                            if (nextInput) {
                                                nextInput.focus();
                                            } else {
                                                // Es el segundo input, hacer click en Guardar
                                                const saveBtn = document.querySelector('.overlay .btn-primary');
                                                if (saveBtn) saveBtn.click();
                                            }
                                        }
                                    }
                                });
                            };

                            setupPinInput(pin1Input, pin2Input);
                            setupPinInput(pin2Input, null);

                            // Auto-submit configurable
                            if (typeof PASSWORDLESS_AUTO_SUBMIT_PIN !== 'undefined' && PASSWORDLESS_AUTO_SUBMIT_PIN) {
                                pin2Input.addEventListener('input', function(e) {
                                    if (e.target.value.length === 4 && pin1Input.value.length === 4) {
                                        setTimeout(() => {
                                            const saveBtn = document.querySelector('.overlay .btn-primary');
                                            if (saveBtn) saveBtn.click();
                                        }, 300);
                                    }
                                });
                            }

                            return;
                        }

                        // Validar PINs
                        const pin1 = pin1Input.value.trim();
                        const pin2 = pin2Input.value.trim();

                        if (pin1.length !== 4 || !/^[0-9]{4}$/.test(pin1)) {
                            errorMsg.textContent = 'El PIN debe tener exactamente 4 dígitos';
                            errorMsg.style.display = 'block';
                            pin1Input.focus();
                            return;
                        }

                        if (pin1 !== pin2) {
                            errorMsg.textContent = 'Los PINs no coinciden';
                            errorMsg.style.display = 'block';
                            pin2Input.value = '';
                            pin2Input.focus();
                            return;
                        }

                        // Guardar PIN en servidor
                        try {
                            const result = await PasswordlessAuth.savePIN(pin1);
                            if (result.success) {
                                document.body.removeChild(overlay);
                                alert('✓ PIN configurado correctamente');
                                location.reload();
                            } else {
                                errorMsg.textContent = 'Error al guardar PIN: ' + (result.msg || result.error);
                                errorMsg.style.display = 'block';
                            }
                        } catch (e) {
                            errorMsg.textContent = 'Error: ' + e.message;
                            errorMsg.style.display = 'block';
                        }
                    }
                }
            ]
        });
    }
});

// El botón de login passwordless se maneja en footer.php del módulo login
