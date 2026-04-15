/**
 * Device Linking System
 * Permite vincular nuevos dispositivos usando un token temporal
 */

const PasswordlessRegistration = {

    /**
     * Registrar nuevo usuario con passwordless (sin contraseña)
     */
    async register(email) {
        const statusDiv = document.getElementById('register-passwordless-status');
        const submitBtn = document.getElementById('btn-register-passwordless');

        if (!email || !email.includes('@')) {
            if (statusDiv) {
                statusDiv.style.display = 'block';
                statusDiv.style.background = '#fee';
                statusDiv.style.color = '#c00';
                statusDiv.textContent = 'Por favor introduce un email válido';
            }
            return;
        }

        // Deshabilitar botón
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Generando claves seguras...';
        }

        if (statusDiv) {
            statusDiv.style.display = 'block';
            statusDiv.style.background = '#ffe';
            statusDiv.style.color = '#660';
            statusDiv.textContent = '🔐 Generando claves criptográficas...';
        }

        try {
            // Generar claves passwordless
            const keys = await PasswordlessAuth.generateKeyPairs();
            const deviceId = await PasswordlessAuth.getOrCreateDeviceId();
            const deviceName = PasswordlessAuth.getDeviceName();

            if (statusDiv) {
                statusDiv.textContent = '📡 Registrando tu cuenta...';
            }

            // Preparar datos para enviar
            const invCodeInput = document.getElementById('shared-invitation-code');
            const requestData = {
                email: email,
                deviceId: deviceId,
                signPub: keys.signPubB64,
                encPub: keys.encPubB64,
                deviceName: deviceName,
                invitation_code: invCodeInput ? invCodeInput.value.trim() : ''
            };

            console.log('Enviando registro:', requestData);

            // Enviar registro al servidor
            const response = await fetch('/login/ajax/op=register_passwordless', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(requestData)
            });

            const data = await response.json();

            if (data.success) {
                if (data.requires_verification) {
                    // Cuenta creada pero necesita verificar email
                    if (statusDiv) {
                        statusDiv.style.background = '#e8f4fd';
                        statusDiv.style.color = '#0066cc';
                        statusDiv.innerHTML = `
                            <div style="text-align: center;">
                                <strong>📧 Revisa tu email</strong><br>
                                <p style="margin: 10px 0;">Hemos enviado un email de verificación a:<br>
                                <strong>${data.email}</strong></p>
                                <p style="font-size: 0.9em; color: #666;">
                                    Haz click en el enlace del email para activar tu cuenta y poder acceder.
                                </p>
                            </div>
                        `;
                    }

                    // Las claves se guardarán después de verificar el email
                    // Usamos localStorage (no sessionStorage) porque el link de verificación
                    // puede abrirse en otra pestaña y sessionStorage no se comparte entre pestañas
                    localStorage.setItem('pendingPasswordlessKeys', JSON.stringify({
                        keys: keys,
                        deviceId: deviceId,
                        email: data.email
                    }));
                    console.log('[PasswordlessRegistration] Claves guardadas en localStorage para verificación posterior');

                    if (submitBtn) {
                        submitBtn.style.display = 'none';
                    }
                } else {
                    // Registro completado y auto-login (flujo antiguo, por si acaso)
                    await PasswordlessAuth.saveKeys(keys, deviceId, data.user_id);

                    if (statusDiv) {
                        statusDiv.style.background = '#efe';
                        statusDiv.style.color = '#060';
                        statusDiv.textContent = '✓ ' + data.msg;
                    }

                    // Redirigir después de 1.5 segundos
                    setTimeout(() => {
                        window.location.href = data.redirect || '/';
                    }, 1500);
                }
            } else {
                if (statusDiv) {
                    statusDiv.style.background = '#fee';
                    statusDiv.style.color = '#c00';
                    statusDiv.textContent = '✗ ' + (data.msg || 'Error al registrar');
                }
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Registrarme sin contraseña';
                }
            }
        } catch (error) {
            console.error('Error:', error);
            if (statusDiv) {
                statusDiv.style.background = '#fee';
                statusDiv.style.color = '#c00';
                statusDiv.textContent = '✗ Error de conexión: ' + error.message;
            }
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Registrarme sin contraseña';
            }
        }
    }
};

const DeviceLinking = {

    /**
     * Generar token para vincular nuevo dispositivo
     * Se llama desde el perfil del usuario (dispositivo 1)
     */
    async generateToken() {
        try {
            const response = await fetch( '/login/ajax/op=generate_device_link_token', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'}
            });

            const data = await response.json();

            if (data.success) {
                this.showToken(data.token, data.expires_in);
            } else {
                alert(data.msg || 'Error al generar token');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error al generar token de vinculación');
        }
    },

    /**
     * Mostrar token en modal/dialog
     */
    showToken(token, expiresIn) {
        const expiresMin = Math.floor(expiresIn / 60);

        const html = `
            <div id="device-link-modal" style="
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.7);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 10000;
            ">
                <div style="
                    background: white;
                    padding: 30px;
                    border-radius: 12px;
                    max-width: 500px;
                    width: 90%;
                    box-shadow: 0 8px 24px rgba(0,0,0,0.3);
                ">
                    <h2 style="margin-top: 0; color: #333;">🔗 Código de Vinculación</h2>
                    <p style="color: #666; margin-bottom: 20px;">
                        Usa este código en tu otro dispositivo para vincularlo.
                        <br>
                        <strong>Válido por ${expiresMin} minutos</strong>
                    </p>

                    <div style="
                        background: #f5f5f5;
                        padding: 20px;
                        border-radius: 8px;
                        text-align: center;
                        margin: 20px 0;
                        border: 2px dashed #ccc;
                    ">
                        <div style="
                            font-family: 'Courier New', monospace;
                            font-size: 24px;
                            font-weight: bold;
                            color: #8B5CF6;
                            letter-spacing: 2px;
                            word-break: break-all;
                            user-select: all;
                        " id="device-link-token">${token}</div>
                    </div>

                    <div style="display: flex; gap: 10px; margin-top: 20px;">
                        <button onclick="DeviceLinking.copyToken('${token}')" style="
                            flex: 1;
                            background: #8B5CF6;
                            color: white;
                            border: none;
                            padding: 12px;
                            border-radius: 6px;
                            font-size: 16px;
                            cursor: pointer;
                            font-weight: 600;
                        ">
                            📋 Copiar Código
                        </button>
                        <button onclick="DeviceLinking.closeModal()" style="
                            flex: 1;
                            background: #666;
                            color: white;
                            border: none;
                            padding: 12px;
                            border-radius: 6px;
                            font-size: 16px;
                            cursor: pointer;
                        ">
                            Cerrar
                        </button>
                    </div>

                    <div id="qr-container" style="text-align: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee;">
                        <p style="color: #999; font-size: 0.9em; margin-bottom: 10px;">O escanea este QR desde tu móvil:</p>
                        <div id="qr-code"></div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', html);

        // Generar QR code (requiere librería qrcodejs o similar)
        // Si tienes una librería QR disponible, descomenta esto:
        /*
        if (typeof QRCode !== 'undefined') {
            new QRCode(document.getElementById("qr-code"), {
                text: token,
                width: 200,
                height: 200
            });
        } else {
            document.getElementById('qr-container').style.display = 'none';
        }
        */

        // Si no tienes librería QR, usar API externa
        const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(token)}`;
        document.getElementById('qr-code').innerHTML = `<img src="${qrUrl}" alt="QR Code" style="border: 2px solid #ddd; padding: 10px; background: white;">`;
    },

    /**
     * Copiar token al portapapeles
     */
    copyToken(token) {
        navigator.clipboard.writeText(token).then(() => {
            const btn = event.target;
            const originalText = btn.textContent;
            btn.textContent = '✓ Copiado!';
            btn.style.background = '#4CAF50';
            setTimeout(() => {
                btn.textContent = originalText;
                btn.style.background = '#8B5CF6';
            }, 2000);
        }).catch(err => {
            // Fallback para navegadores viejos
            const input = document.createElement('input');
            input.value = token;
            document.body.appendChild(input);
            input.select();
            document.execCommand('copy');
            document.body.removeChild(input);
            alert('Código copiado: ' + token);
        });
    },

    /**
     * Cerrar modal
     */
    closeModal() {
        const modal = document.getElementById('device-link-modal');
        if (modal) {
            modal.remove();
        }
    },

    /**
     * Mostrar formulario para usar token (en login)
     */
    showLinkForm() {
        const html = `
            <div id="device-link-form-modal" style="
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.7);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 10000;
            ">
                <div style="
                    background: white;
                    padding: 30px;
                    border-radius: 12px;
                    max-width: 450px;
                    width: 90%;
                    box-shadow: 0 8px 24px rgba(0,0,0,0.3);
                ">
                    <h2 style="margin-top: 0; color: #333;">🔗 Vincular Dispositivo</h2>
                    <p style="color: #666; margin-bottom: 20px;">
                        Introduce el código de vinculación que generaste en tu otro dispositivo
                    </p>

                    <div>
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #555;">
                                Código de vinculación:
                            </label>
                            <input
                                type="text"
                                id="link-token"
                                placeholder="Ej: a1b2c3d4e5f6..."
                                style="
                                    width: 100%;
                                    padding: 12px;
                                    border: 2px solid #ddd;
                                    border-radius: 6px;
                                    font-size: 16px;
                                    font-family: 'Courier New', monospace;
                                    box-sizing: border-box;
                                "
                            >
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #555;">
                                Tu email o usuario:
                            </label>
                            <input
                                type="text"
                                id="link-identifier"
                                placeholder="tu@email.com"
                                style="
                                    width: 100%;
                                    padding: 12px;
                                    border: 2px solid #ddd;
                                    border-radius: 6px;
                                    font-size: 16px;
                                    box-sizing: border-box;
                                "
                            >
                        </div>

                        <div id="link-status" style="
                            margin-bottom: 15px;
                            padding: 10px;
                            border-radius: 6px;
                            display: none;
                        "></div>

                        <div style="display: flex; gap: 10px;">
                            <button id="btn-link-device" type="button" style="
                                flex: 1;
                                background: #8B5CF6;
                                color: white;
                                border: none;
                                padding: 12px;
                                border-radius: 6px;
                                font-size: 16px;
                                cursor: pointer;
                                font-weight: 600;
                            ">
                                Vincular
                            </button>
                            <button type="button" onclick="DeviceLinking.closeLinkForm()" style="
                                flex: 1;
                                background: #666;
                                color: white;
                                border: none;
                                padding: 12px;
                                border-radius: 6px;
                                font-size: 16px;
                                cursor: pointer;
                            ">
                                Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', html);

        // Añadir event listener al botón de vincular
        const btnLink = document.getElementById('btn-link-device');
        if (btnLink) {
            btnLink.addEventListener('click', () => DeviceLinking.verifyToken());
        }
    },

    /**
     * Cerrar formulario de vinculación
     */
    closeLinkForm() {
        const modal = document.getElementById('device-link-form-modal');
        if (modal) {
            modal.remove();
        }
    },

    /**
     * Verificar token y vincular dispositivo
     */
    async verifyToken() {
        const token = document.getElementById('link-token').value.trim();
        const identifier = document.getElementById('link-identifier').value.trim();
        const statusDiv = document.getElementById('link-status');
        const submitBtn = document.getElementById('btn-link-device');

        if (!token || !identifier) {
            statusDiv.style.display = 'block';
            statusDiv.style.background = '#fee';
            statusDiv.style.color = '#c00';
            statusDiv.textContent = 'Por favor completa todos los campos';
            return;
        }

        // Deshabilitar botón mientras procesa
        submitBtn.disabled = true;
        submitBtn.textContent = 'Vinculando...';

        statusDiv.style.display = 'block';
        statusDiv.style.background = '#ffe';
        statusDiv.style.color = '#660';
        statusDiv.textContent = 'Generando claves criptográficas...';

        try {
            // Generar claves passwordless en este dispositivo
            const keys = await PasswordlessAuth.generateKeyPairs();
            const deviceId = await PasswordlessAuth.getOrCreateDeviceId();
            const deviceName = PasswordlessAuth.getDeviceName();

            statusDiv.textContent = 'Verificando código con el servidor...';

            // Enviar al servidor
            const response = await fetch( '/login/ajax/op=verify_device_link_token', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    token: token,
                    identifier: identifier,
                    deviceId: deviceId,
                    signPub: keys.signPubB64,
                    encPub: keys.encPubB64,
                    deviceName: deviceName
                })
            });

            const data = await response.json();

            if (data.success) {
                // Guardar claves en IndexedDB
                await PasswordlessAuth.saveKeys(keys, deviceId, data.user_id);

                statusDiv.style.background = '#efe';
                statusDiv.style.color = '#060';
                statusDiv.textContent = '✓ ' + data.msg;

                // Redirigir después de 2 segundos
                setTimeout(() => {
                    window.location.href = data.redirect || '/';
                }, 2000);
            } else {
                statusDiv.style.background = '#fee';
                statusDiv.style.color = '#c00';
                statusDiv.textContent = '✗ ' + (data.msg || 'Error al vincular dispositivo');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Vincular';
            }
        } catch (error) {
            console.error('Error:', error);
            statusDiv.style.background = '#fee';
            statusDiv.style.color = '#c00';
            statusDiv.textContent = '✗ Error de conexión: ' + error.message;
            submitBtn.disabled = false;
            submitBtn.textContent = 'Vincular';
        }
    },

    /**
     * Mostrar formulario de solicitud de magic link
     */
    showMagicLinkForm() {
        const html = `
            <div id="magic-link-form-modal" style="
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.6);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 10000;
            ">
                <div style="
                    background: white;
                    border-radius: 12px;
                    max-width: 500px;
                    width: 90%;
                    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
                ">
                    <div style="padding: 30px;">
                        <h3 style="margin: 0 0 20px 0; font-size: 24px; color: #333;">
                            🔑 Recuperar Acceso
                        </h3>

                        <p style="color: #666; margin-bottom: 20px; font-size: 14px;">
                            Introduce tu email y recibirás un enlace temporal para acceder a tu cuenta.
                            El enlace será válido durante 30 minutos.
                        </p>

                        <div id="magic-link-status" style="
                            display: none;
                            padding: 15px;
                            border-radius: 8px;
                            margin-bottom: 20px;
                            font-size: 14px;
                        "></div>

                        <div style="margin-bottom: 20px;">
                            <label style="
                                display: block;
                                margin-bottom: 8px;
                                font-weight: 500;
                                color: #333;
                            ">Email:</label>
                            <input
                                type="email"
                                id="magic-link-email"
                                placeholder="tu@email.com"
                                autocomplete="email"
                                style="
                                    width: 100%;
                                    padding: 12px;
                                    border: 2px solid #ddd;
                                    border-radius: 6px;
                                    font-size: 16px;
                                    box-sizing: border-box;
                                "
                            >
                        </div>

                        <div style="display: flex; gap: 12px;">
                            <button id="btn-request-magic-link" type="button" style="
                                flex: 2;
                                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                                color: white;
                                border: none;
                                padding: 12px;
                                border-radius: 6px;
                                font-size: 16px;
                                cursor: pointer;
                                font-weight: 500;
                            ">
                                Enviar Enlace
                            </button>
                            <button type="button" onclick="DeviceLinking.closeMagicLinkForm()" style="
                                flex: 1;
                                background: #666;
                                color: white;
                                border: none;
                                padding: 12px;
                                border-radius: 6px;
                                font-size: 16px;
                                cursor: pointer;
                            ">
                                Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', html);

        // Añadir event listener al botón
        const btnRequest = document.getElementById('btn-request-magic-link');
        if (btnRequest) {
            btnRequest.addEventListener('click', () => DeviceLinking.requestMagicLink());
        }

        // Focus en el input
        setTimeout(() => {
            document.getElementById('magic-link-email')?.focus();
        }, 100);
    },

    /**
     * Cerrar formulario de magic link
     */
    closeMagicLinkForm() {
        const modal = document.getElementById('magic-link-form-modal');
        if (modal) {
            modal.remove();
        }
    },

    /**
     * Solicitar magic link
     */
    async requestMagicLink() {
        const email = document.getElementById('magic-link-email').value.trim();
        const statusDiv = document.getElementById('magic-link-status');
        const submitBtn = document.getElementById('btn-request-magic-link');

        if (!email) {
            statusDiv.style.display = 'block';
            statusDiv.style.background = '#fee';
            statusDiv.style.color = '#c00';
            statusDiv.textContent = '✗ Por favor introduce tu email';
            return;
        }

        // Validar formato de email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            statusDiv.style.display = 'block';
            statusDiv.style.background = '#fee';
            statusDiv.style.color = '#c00';
            statusDiv.textContent = '✗ Por favor introduce un email válido';
            return;
        }

        submitBtn.disabled = true;
        submitBtn.textContent = 'Enviando...';
        statusDiv.style.display = 'none';

        try {
            const response = await fetch(window.location.origin + '/login/ajax', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    op: 'request_magic_link',
                    email: email
                })
            });

            const data = await response.json();

            if (data.success) {
                statusDiv.style.display = 'block';
                statusDiv.style.background = '#e8f5e9';
                statusDiv.style.color = '#2e7d32';
                statusDiv.innerHTML = '✓ ' + data.msg + '<br><br>Revisa tu bandeja de entrada.';

                // Limpiar el input
                document.getElementById('magic-link-email').value = '';

                // Cerrar el modal después de 3 segundos
                setTimeout(() => {
                    DeviceLinking.closeMagicLinkForm();
                }, 3000);
            } else {
                statusDiv.style.display = 'block';
                statusDiv.style.background = '#fee';
                statusDiv.style.color = '#c00';
                statusDiv.textContent = '✗ ' + (data.msg || 'Error al solicitar enlace');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Enviar Enlace';
            }
        } catch (error) {
            console.error('Error:', error);
            statusDiv.style.display = 'block';
            statusDiv.style.background = '#fee';
            statusDiv.style.color = '#c00';
            statusDiv.textContent = '✗ Error de conexión: ' + error.message;
            submitBtn.disabled = false;
            submitBtn.textContent = 'Enviar Enlace';
        }
    },


    toggleLoginMode() {
        const passwordRow = document.getElementById('password-row');
        const saveRow = document.getElementById('save-row');
        const btnSubmit = document.getElementById('btnsubmit');
        const btnPasswordless = document.getElementById('btn-passwordless');
        const isPasswordlessMode = passwordRow.style.display === 'none';

        if (isPasswordlessMode) {
            // Cambiar a modo tradicional
            passwordRow.style.display = 'block';
            if (saveRow) saveRow.style.display = 'block';
            btnSubmit.style.display = 'block';
            if (btnPasswordless) btnPasswordless.style.display = 'none';
            document.querySelector('#toggle-login-mode span').textContent  = str_sign_passwordless;
            document.querySelectorAll('.only-passwordless').forEach(el => el.style.display = 'none');
            document.querySelectorAll('.btn-reset').forEach(el => el.style.display = 'block');
        } else {
            // Cambiar a modo passwordless
            passwordRow.style.display = 'none';
            if (saveRow) saveRow.style.display = 'none';
            btnSubmit.style.display = 'none';
            if (btnPasswordless) btnPasswordless.style.display = 'block';
            document.querySelector('#toggle-login-mode span').textContent = str_sign_password;
            document.querySelectorAll('.only-passwordless').forEach(el => el.style.display = 'block');
            document.querySelectorAll('.btn-reset').forEach(el => el.style.display = 'none');
        }
    }


};
