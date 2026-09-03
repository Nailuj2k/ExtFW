<script>
        $(document).ready(function(){

            // ========================================================================
            // LOGIN PASSWORDLESS
            // ========================================================================
            $('#btn-passwordless').on('click', async function(e){
                e.preventDefault();

                const identifier = $('#username').val().trim();
                if(!identifier) {
                    alert('Introduce tu email o nombre de usuario');
                    $('#username').focus();
                    return;
                }

                if(typeof PasswordlessAuth === 'undefined') {
                    alert('Error: PasswordlessAuth no está disponible');
                    return;
                }

                if(!await PasswordlessAuth.hasAnyKeys()) {
                    showNoKeysDialog();
                    return;
                }

                // Verificar si el usuario tiene PIN configurado
                const pinCheck = await PasswordlessAuth.verifyPIN(identifier, '');
                console_log('PIN check result:', pinCheck);

                if(pinCheck.requires_pin) {
                    // Usuario tiene PIN, mostrar dialog para pedirlo
                    showPINDialog(identifier, $(this));
                    return;
                }

                // No tiene PIN, proceder con el login
                performPasswordlessLogin(identifier, $(this));
            });

            // Función auxiliar para realizar el login
            async function performPasswordlessLogin(identifier, btn) {
                const originalText = btn.html();
                btn.html('<i class="fa fa-spinner fa-spin"></i> Verificando...');
                btn.css('pointer-events', 'none');

                try {
                    const result = await PasswordlessAuth.login(identifier);
                    console_log('Passwordless result:', result);

                    if(result && result.success) {
                        btn.html('<i class="fa fa-check"></i> ¡Bienvenido!');
                        window.location.href = result.redirect || '/';
                    } else {
                        alert('Error: ' + (result?.msg || result?.error || 'Error desconocido'));
                        btn.html(originalText);
                        btn.css('pointer-events', '');
                    }
                } catch(err) {
                    console.error('Passwordless error:', err);
                    alert('Error: ' + err.message);
                    btn.html(originalText);
                    btn.css('pointer-events', '');
                }
            }

            // Dialog para pedir PIN
            function showPINDialog(identifier, btn) {
                // Función para verificar el PIN
                const verifyAndSubmit = async function() {
                    const pinInput = document.getElementById('pin-login-input');
                    const errorMsg = document.getElementById('pin-login-error');
                    const pin = pinInput.value.trim();

                    if(pin.length !== 4 || !/^[0-9]{4}$/.test(pin)) {
                        errorMsg.textContent = 'El PIN debe tener 4 dígitos';
                        errorMsg.style.display = 'block';
                        pinInput.focus();
                        return;
                    }

                    // Verificar PIN
                    try {
                        const result = await PasswordlessAuth.verifyPIN(identifier, pin);
                        if(result.success) {
                            const overlay = document.querySelector('.overlay');
                            if(overlay) document.body.removeChild(overlay);
                            performPasswordlessLogin(identifier, btn);
                        } else {
                            errorMsg.textContent = result.msg || 'PIN incorrecto';
                            errorMsg.style.display = 'block';
                            pinInput.value = '';
                            pinInput.focus();
                        }
                    } catch(e) {
                        errorMsg.textContent = 'Error: ' + e.message;
                        errorMsg.style.display = 'block';
                    }
                };

                $("body").dialog({
                    title: "Introduce tu PIN",
                    type: 'html',
                    width: '400px',
                    openAnimation: 'zoom',
                    closeAnimation: 'fade',
                    content: `
                        <div style="padding: 20px; text-align: center;">
                            <p style="margin-bottom: 20px; font-size: 14px; color: #666;">
                                Este usuario tiene PIN configurado para mayor seguridad.
                            </p>
                            <label style="display: block; margin-bottom: 10px; font-weight: bold;">PIN (4 dígitos):</label>
                            <input type="password" inputmode="numeric" id="pin-login-input" maxlength="4" pattern="[0-9]{4}"
                                   autocomplete="off"
                                   style="width: 200px; padding: 12px; font-size: 28px; text-align: center; letter-spacing: 15px; font-family: monospace; margin: 0 auto; border: 2px solid #ccc; border-radius: 4px;"
                                   autofocus>
                            <p id="pin-login-error" style="color: red; margin-top: 15px; display: none;"></p>
                        </div>
                    `,
                    buttons: [
                        {
                            text: 'Cancelar',
                            class: 'btn btn-secondary',
                            action: function(_, overlay) {
                                document.body.removeChild(overlay);
                            }
                        },
                        {
                            text: 'Continuar',
                            class: 'btn btn-primary',
                            action: verifyAndSubmit
                        }
                    ]
                });

                // Focus automático y configurar eventos
                setTimeout(() => {
                    const pinInput = document.getElementById('pin-login-input');
                    if (!pinInput) return;

                    pinInput.focus();

                    // Solo permitir números
                    pinInput.addEventListener('input', function(e) {
                        const value = e.target.value;
                        // Solo permitir números
                        e.target.value = value.replace(/[^0-9]/g, '');

                        // Auto-submit configurable
                        if (typeof PASSWORDLESS_AUTO_SUBMIT_PIN !== 'undefined' && PASSWORDLESS_AUTO_SUBMIT_PIN && e.target.value.length === 4) {
                            setTimeout(() => {
                                verifyAndSubmit();
                            }, 200);
                        }
                    });

                    // Submit con Enter
                    pinInput.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            verifyAndSubmit();
                        }
                    });
                }, 100);
            }

            // Dialog cuando no hay claves guardadas en el navegador
            function showNoKeysDialog() {
                $("body").dialog({
                    title: "No tienes claves en este navegador",
                    type: 'html',
                    width: '550px',
                    openAnimation: 'zoom',
                    closeAnimation: 'fade',
                    content: `
                        <div style="padding: 20px;">
                            <p style="margin-bottom: 20px; line-height: 1.6; color: #666;">
                                No tienes claves guardadas en este navegador. Para usar login sin contraseña desde aquí, tienes <strong>3 opciones</strong>:
                            </p>

                            <div style="margin-bottom: 15px; padding: 15px; background: #f8f9fa; border-left: 4px solid #007bff; border-radius: 4px;">
                                <strong style="color: #007bff;">1. Iniciar sesión con contraseña</strong>
                                <p style="margin: 8px 0 0 0; font-size: 14px; color: #666;">
                                    Usa tu contraseña tradicional para entrar, luego configura el login sin contraseña desde tu perfil.
                                </p>
                            </div>

                            <div style="margin-bottom: 15px; padding: 15px; background: #f8f9fa; border-left: 4px solid #28a745; border-radius: 4px;">
                                <strong style="color: #28a745;">2. Usar un código de vinculación</strong>
                                <p style="margin: 8px 0 8px 0; font-size: 14px; color: #666;">
                                    Si ya tienes login sin contraseña configurado en otro navegador/dispositivo, genera un código allí y úsalo aquí.
                                </p>
                                <a href="javascript:void(0)" onclick="document.body.querySelector('.overlay')?.remove(); DeviceLinking.showLinkForm();"
                                   style="display: inline-block; color: #28a745; text-decoration: underline; font-size: 14px; font-weight: 600;">
                                    🔗 ¿Tienes un código de vinculación?
                                </a>
                            </div>

                            <div style="margin-bottom: 10px; padding: 15px; background: #f8f9fa; border-left: 4px solid #ffc107; border-radius: 4px;">
                                <strong style="color: #e67e00;">3. Solicitar un Magic Link</strong>
                                <p style="margin: 8px 0 8px 0; font-size: 14px; color: #666;">
                                    Si perdiste acceso a todos tus dispositivos, te enviamos un enlace temporal por email para recuperar tu cuenta.
                                </p>
                                <a href="javascript:void(0)" onclick="document.body.querySelector('.overlay')?.remove(); DeviceLinking.showMagicLinkForm();"
                                   style="display: inline-block; color: #e67e00; text-decoration: underline; font-size: 14px; font-weight: 600;">
                                    🔑 ¿Perdiste acceso a tus dispositivos?
                                </a>
                            </div>
                        </div>
                    `,
                    buttons: [
                        {
                            text: 'Cerrar',
                            class: 'btn btn-secondary',
                            action: function(_, overlay) {
                                document.body.removeChild(overlay);
                            }
                        }
                    ]
                });
            }




        });


</script>
