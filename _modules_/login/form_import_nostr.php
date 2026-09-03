          <div style="padding:15px;">
                        <!-- Tabs usando SimpleTabs del framework ExtFW -->
                        <div id="import-identity-tabs" data-simpletabs>
                            <ul style="margin: 10px 0 15px 0;">
                                <li><a href="#tab-content-import-nsec"><i class="fa fa-key"></i> Importar clave privada</a></li>
                                <li><a href="#tab-content-link-npub"><i class="fa fa-shield"></i> Vincular clave pública (más seguro)</a></li>
                            </ul>

                            <!-- Tab 1: Importar nsec -->
                            <div id="tab-content-import-nsec">
                            <div style="background:#e3f2fd;border:1px solid #90caf9;border-radius:8px;padding:15px;margin-bottom:15px;">
                                <p style="margin:0 0 10px 0;color:#1565c0;font-weight:bold;">
                                    <i class="fa fa-info-circle"></i> ¿Qué es esto?
                                </p>
                                <p style="margin:0 0 8px 0;font-size:0.9em;color:#1976d2;">
                                    Si ya tienes una identidad Nostr en otra app (Amethyst, Damus, Primal, etc.),
                                    puedes importarla aquí pegando tu clave privada (nsec).
                                </p>
                                <p style="margin:0;font-size:0.85em;color:#1976d2;">
                                    ✓ No necesitas extensión de navegador<br>
                                    ✓ Funciona en móviles y tablets<br>
                                    ✓ Tu clave se guarda de forma segura en este navegador
                                </p>
                            </div>

                            <div style="background:#fff3e0;border:1px solid #ffb74d;border-radius:8px;padding:15px;margin-bottom:15px;">
                                <p style="margin:0 0 8px 0;color:#e65100;font-weight:bold;">
                                    <i class="fa fa-exclamation-triangle"></i> ⚠️ ADVERTENCIA DE SEGURIDAD
                                </p>
                                <p style="margin:0;font-size:0.85em;color:#e65100;">
                                    Solo pega tu clave privada (nsec) si confías completamente en este sitio.
                                    Tu clave privada te da control total sobre tu identidad Nostr.
                                </p>
                            </div>

                            <div style="margin-bottom:15px;">
                                <label style="display:block;margin-bottom:5px;font-weight:bold;">
                                    Pega tu clave privada (nsec):
                                </label>
                                <input type="password" id="import-nsec-input"
                                    placeholder="nsec1..."
                                    style="width:100%;padding:10px;font-family:monospace;font-size:0.85em;border:2px solid #ddd;border-radius:4px;">
                                <label style="display:block;margin-top:5px;font-size:0.85em;">
                                    <input type="checkbox" id="show-nsec-checkbox" style="margin-right:5px;">
                                    Mostrar clave privada
                                </label>
                            </div>

                            <div id="import-nsec-status" style="margin-top:15px;"></div>
                            </div>

                            <!-- Tab 2: Vincular npub -->
                            <div id="tab-content-link-npub">
                            <div style="background:#e8f5e9;border:1px solid #81c784;border-radius:8px;padding:15px;margin-bottom:15px;">
                                <p style="margin:0 0 10px 0;color:#2e7d32;font-weight:bold;">
                                    <i class="fa fa-shield"></i> Opción más segura - Firma sin compartir tu nsec
                                </p>
                                <p style="margin:0 0 8px 0;font-size:0.9em;color:#388e3c;">
                                    <strong>¿No te fías mucho de compartir tu clave privada?</strong> Usa este método:
                                </p>
                                <ol style="margin:0 0 10px 0;padding-left:20px;font-size:0.9em;color:#388e3c;">
                                    <li style="margin-bottom:5px;">Pega aquí solo tu <strong>clave pública (npub)</strong></li>
                                    <li style="margin-bottom:5px;">Haz clic en "Continuar" y <strong>aparecerá un popup de tu extensión Nostr</strong> (Alby, nos2x, etc.)</li>
                                    <li style="margin-bottom:5px;"><strong>Aprueba la firma en el popup</strong> - tu nsec nunca sale de la extensión, solo se envía la firma criptográfica</li>
                                </ol>
                                <p style="margin:0;font-size:0.85em;color:#388e3c;background:#f1f8f4;padding:8px;border-radius:4px;">
                                    ✓ <strong>Máxima seguridad</strong> - Tu nsec permanece en tu extensión<br>
                                    ✓ Solo pegas tu npub (clave pública) - la firma se hace automáticamente<br>
                                    ✓ La extensión genera la firma sin revelar tu clave privada
                                </p>
                            </div>

                            <div style="margin-bottom:15px;">
                                <label style="display:block;margin-bottom:5px;font-weight:bold;">
                                    Pega tu clave pública (npub):
                                </label>
                                <input type="text" id="link-npub-input"
                                    placeholder="npub1..."
                                    style="width:100%;padding:10px;font-family:monospace;font-size:0.85em;border:2px solid #ddd;border-radius:4px;">
                                <p style="margin:5px 0 0 0;font-size:0.8em;color:#666;">
                                    <i class="fa fa-info-circle"></i> Puedes encontrar tu npub en la configuración de tu app Nostr
                                </p>
                            </div>

                            <div id="link-npub-status" style="margin-top:15px;"></div>
                            </div>
                        </div>
                    </div>