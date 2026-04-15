<?php
/**
 * html_create_address.php - Dialogo para generar direccion Bitcoin con entropia
 * Adaptado de timextamping/html_sign_hash.php
 */
?>
<div class="create-address-container">

    <p class="create-address-intro">
        <?=t('GENERATE_NEW_BITCOIN_ADDRESS_INTRO', 'Genera una nueva direccion Bitcoin para tu wallet. La clave privada se procesa solo en tu navegador.')?>
    </p>

    <!-- Checkbox para entropia adicional -->
    <div class="form-section">
        <label class="checkbox-label">
            <input type="checkbox" id="chkAddEntropy" class="checkbox-input">
            <span>🔐 <?=t('ADD_ADDITIONAL_ENTROPY', 'Anadir entropia adicional (recomendado para mayor seguridad)')?></span>
        </label>
    </div>

    <!-- Canvas overlay para pintar por toda la ventana -->
    <canvas id="mouseEntropyArea" class="canvas-overlay"></canvas>

    <div id="entropyCollector" class="entropy-collector" style="display: none;">
        <p><?=t('ADD_EXTRA_RANDOMNESS', 'Anade aleatoriedad extra:')?></p>
        <p id="entropyInstructions"><?=t('MOVE_MOUSE_TO_GENERATE_ENTROPY', 'Mueve el raton por toda la ventana del dialogo (se pintaran puntos de colores):')?></p>

        <div style="margin-bottom: 10px;">
            <label class="form-label"><?=t('GENERATED_ENTROPY_SHA256_HASH', 'Entropia generada (hash SHA-256):')?></label>
            <textarea id="entropyDisplay" readonly placeholder="<?=t('ENTROPY_WILL_BE_DISPLAYED_HERE', 'La entropia se mostrara aqui en tiempo real...')?>" class="entropy-textarea"></textarea>
        </div>

        <p class="entropy-status">
            <?=t('ENTROPY_COLLECTED', 'Entropia recolectada:')?> <strong id="entropyPercent">0%</strong>
            <span id="entropyStatus" class="entropy-status-inline"></span>
        </p>
    </div>

    <!-- Ruta de derivacion -->
    <div class="form-section">
        <label class="form-label"><?=t('DERIVATION_PATH', 'Ruta de derivacion:')?></label>
        <select id="derivationPath" class="form-select">
            <option value="m/84'/0'/0'/0/0">m/84'/0'/0'/0/0 (SegWit Nativo - bc1q...) - Recomendado</option>
            <option value="m/44'/0'/0'/0/0">m/44'/0'/0'/0/0 (Legacy - 1...)</option>
            <option value="m/49'/0'/0'/0/0">m/49'/0'/0'/0/0 (SegWit Compatible - 3...)</option>
        </select>
    </div>

    <button id="btnGenerateAddress" class="btn btn-primary btn-full">
        <i class="fa fa-magic"></i> <?=t('GENERATE_ADDRESS', 'Generar Direccion')?>
    </button>

    <!-- Resultado -->
    <div id="generatedResult" class="result-box" style="display: none;">
        <div class="warning-box">
            <strong>⚠️ <?=t('IMPORTANT', 'IMPORTANTE:')?></strong>
            <?=t('SAVE_YOUR_RECOVERY_PHRASE', 'Guarda tu frase de recuperacion en un lugar seguro. No podras recuperarla si la pierdes.')?>
        </div>

        <div class="key-section">
            <label class="form-label"><?=t('PUBLIC_ADDRESS', 'Direccion Publica:')?></label>
            <div class="flex-input-container">
                <input type="text" id="generatedAddress" readonly class="flex-input-full">
                <button id="btnCopyAddress" class="btn btn-secondary btn-no-wrap">
                    <i class="fa fa-copy"></i>
                </button>
            </div>
        </div>

        <div class="key-section">
            <label class="form-label"><?=t('RECOVERY_PHRASE_12_WORDS', 'Frase de Recuperacion (12 palabras):')?></label>
            <div class="flex-input-container">
                <input type="password" id="generatedMnemonic" readonly class="flex-input-full">
                <button id="btnToggleMnemonic" class="btn btn-secondary" title="<?=t('SHOW_HIDE', 'Mostrar/Ocultar')?>">
                    <i class="fa fa-eye"></i>
                </button>
                <button id="btnCopyMnemonic" class="btn btn-secondary" title="<?=t('COPY', 'Copiar')?>">
                    <i class="fa fa-copy"></i>
                </button>
            </div>
        </div>

        <div class="key-section">
            <label class="form-label"><?=t('PRIVATE_KEY_WIF', 'Clave Privada WIF:')?></label>
            <div class="flex-input-container">
                <input type="password" id="generatedWIF" readonly class="flex-input-full">
                <button id="btnToggleWIF" class="btn btn-secondary" title="<?=t('SHOW_HIDE', 'Mostrar/Ocultar')?>">
                    <i class="fa fa-eye"></i>
                </button>
                <button id="btnCopyWIF" class="btn btn-secondary" title="<?=t('COPY', 'Copiar')?>">
                    <i class="fa fa-copy"></i>
                </button>
            </div>
        </div>

        <div class="key-section">
            <label class="form-label">xPub:</label>
            <div class="flex-input-container">
                <input type="text" id="generatedXpub" readonly class="flex-input-full" style="font-size: 0.75em;">
                <button id="btnCopyXpub" class="btn btn-secondary" title="<?=t('COPY', 'Copiar')?>">
                    <i class="fa fa-copy"></i>
                </button>
            </div>
        </div>

        <div class="btn-flex-container">
            <button id="btnDownloadKeys" class="btn btn-secondary">
                <i class="fa fa-download"></i> <?=t('SAVE_TO_FILE', 'Guardar en archivo')?>
            </button>
            <button id="btnUseAddress" class="btn btn-primary btn-disabled" title="<?=t('FIRST_SAVE_KEYS', 'Primero guarda las claves')?>">
                <i class="fa fa-check"></i> <?=t('USE_THIS_ADDRESS', 'Usar esta direccion')?>
            </button>
        </div>
    </div>

    <div class="security-box">
        <strong>🔒 <?=t('SECURITY', 'Seguridad')?>:</strong>
        <?=t('KEY_PROCESSED_LOCALLY', 'Tu clave privada se procesa solo en tu navegador y nunca se envia al servidor.')?>
    </div>

</div>

<style>
.create-address-container {
    padding: 10px;
}
.create-address-intro {
    margin-bottom: 15px;
    color: var(--text-secondary);
}
.form-section {
    margin-bottom: 15px;
}
.form-label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
}
.form-select {
    width: 100%;
    padding: 8px;
    border: 1px solid var(--border-color);
    border-radius: 4px;
    background: var(--bg-secondary);
    color: var(--text-primary);
}
.checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}
.checkbox-input {
    width: 18px;
    height: 18px;
}
.canvas-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 9998;
    display: none;
}
.entropy-collector {
    background: var(--bg-tertiary);
    border: 1px dashed var(--border-color);
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
}
.entropy-textarea {
    width: 100%;
    height: 60px;
    font-family: monospace;
    font-size: 0.75em;
    padding: 8px;
    border: 1px solid var(--border-color);
    border-radius: 4px;
    background: var(--bg-secondary);
    color: var(--text-primary);
    resize: none;
}
.entropy-status {
    margin-top: 10px;
    font-size: 0.9em;
}
.entropy-status-inline {
    margin-left: 10px;
}
.result-box {
    background: var(--bg-tertiary);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 15px;
    margin: 15px 0;
}
.warning-box {
    background: rgba(255, 193, 7, 0.1);
    border: 1px solid #ffc107;
    border-radius: 4px;
    padding: 10px;
    margin-bottom: 15px;
    font-size: 0.9em;
}
.key-section {
    margin-bottom: 12px;
}
.flex-input-container {
    display: flex;
    gap: 5px;
}
.flex-input-full {
    flex: 1;
    padding: 8px;
    border: 1px solid var(--border-color);
    border-radius: 4px;
    background: var(--bg-secondary);
    color: var(--text-primary);
    font-family: monospace;
}
.btn-full {
    width: 100%;
    padding: 12px;
}
.btn-flex-container {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}
.btn-flex-container .btn {
    flex: 1;
}
.btn-disabled {
    opacity: 0.5;
    pointer-events: none;
}
.btn-no-wrap {
    white-space: nowrap;
}
.security-box {
    background: rgba(40, 167, 69, 0.1);
    border: 1px solid #28a745;
    border-radius: 4px;
    padding: 10px;
    margin-top: 15px;
    font-size: 0.85em;
}
</style>

<script>
(function() {
    // Referencias a elementos
    const chkAddEntropy = document.getElementById('chkAddEntropy');
    const entropyCollector = document.getElementById('entropyCollector');
    const mouseEntropyArea = document.getElementById('mouseEntropyArea');
    const derivationPath = document.getElementById('derivationPath');
    const btnGenerateAddress = document.getElementById('btnGenerateAddress');
    const generatedResult = document.getElementById('generatedResult');

    // Sistema de entropia
    let userEntropy = [];
    let entropyCollected = 0;
    const ENTROPY_TARGET = 1024;

    // Detectar movil
    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

    // Actualizar instrucciones para movil
    const entropyInstructions = document.getElementById('entropyInstructions');
    if (entropyInstructions && isMobile) {
        entropyInstructions.textContent = '📱 Mueve tu dispositivo en todas direcciones:';
    }

    // Toggle entropia
    if (chkAddEntropy) {
        chkAddEntropy.addEventListener('change', function() {
            const canvas = mouseEntropyArea;

            if (this.checked) {
                entropyCollector.style.display = 'block';

                if (canvas) {
                    const dialogOverlay = canvas.closest('.overlay');
                    if (dialogOverlay) {
                        const rect = dialogOverlay.getBoundingClientRect();
                        canvas.style.display = 'block';
                        canvas.style.pointerEvents = 'auto';
                        canvas.width = rect.width;
                        canvas.height = rect.height;
                    } else {
                        canvas.style.display = 'block';
                        canvas.style.pointerEvents = 'auto';
                        canvas.width = window.innerWidth;
                        canvas.height = window.innerHeight;
                    }
                }

                // Activar sensores en movil
                if (isMobile) {
                    activateDeviceSensors();
                }
            } else {
                entropyCollector.style.display = 'none';
                if (canvas) {
                    canvas.style.display = 'none';
                    const ctx = canvas.getContext('2d');
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                }
                userEntropy = [];
                entropyCollected = 0;
                updateEntropyDisplay();
            }
        });
    }

    // Recolectar entropia del raton
    if (mouseEntropyArea) {
        let lastX = 0, lastY = 0, lastTime = Date.now();
        const ctx = mouseEntropyArea.getContext('2d');

        document.addEventListener('mousemove', function(e) {
            if (!chkAddEntropy || !chkAddEntropy.checked) return;
            if (entropyCollected >= ENTROPY_TARGET) return;
            if (entropyCollector && entropyCollector.style.display === 'none') return;

            const canvas = mouseEntropyArea;
            const rect = canvas.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            if (x < 0 || x >= canvas.width || y < 0 || y >= canvas.height) return;

            const now = Date.now();
            if (Math.abs(x - lastX) > 2 || Math.abs(y - lastY) > 2) {
                userEntropy.push(x, y, now - lastTime, e.movementX || 0, e.movementY || 0);
                entropyCollected = Math.min(entropyCollected + 2, ENTROPY_TARGET);
                updateEntropyDisplay();

                // Dibujar punto
                const hue = (entropyCollected * 1.4) % 360;
                ctx.fillStyle = `hsl(${hue}, 70%, 50%)`;
                ctx.beginPath();
                ctx.arc(x, y, 3, 0, Math.PI * 2);
                ctx.fill();

                lastX = x;
                lastY = y;
                lastTime = now;
            }
        });
    }

    // Soporte para sensores de movimiento (movil)
    let deviceMotionHandler = null;
    let lastDeviceX = 0, lastDeviceY = 0, lastDeviceZ = 0;

    async function activateDeviceSensors() {
        try {
            if (typeof DeviceMotionEvent !== 'undefined' && typeof DeviceMotionEvent.requestPermission === 'function') {
                const permission = await DeviceMotionEvent.requestPermission();
                if (permission === 'granted') {
                    window.addEventListener('devicemotion', handleDeviceMotion);
                }
            } else {
                window.addEventListener('devicemotion', handleDeviceMotion);
            }
        } catch (error) {
            console.error('Error activando sensores:', error);
        }
    }

    function handleDeviceMotion(event) {
        if (!chkAddEntropy || !chkAddEntropy.checked) return;
        if (entropyCollected >= ENTROPY_TARGET) return;
        if (!event.accelerationIncludingGravity) return;

        const accelX = event.accelerationIncludingGravity.x || 0;
        const accelY = event.accelerationIncludingGravity.y || 0;
        const accelZ = event.accelerationIncludingGravity.z || 0;

        const change = Math.abs(accelX - lastDeviceX) + Math.abs(accelY - lastDeviceY) + Math.abs(accelZ - lastDeviceZ);

        if (change > 0.15) {
            userEntropy.push(
                Math.floor(accelX * 1000),
                Math.floor(accelY * 1000),
                Math.floor(accelZ * 1000),
                Date.now()
            );
            entropyCollected = Math.min(entropyCollected + 1, ENTROPY_TARGET);
            updateEntropyDisplay();

            // Dibujar punto en canvas
            const canvas = mouseEntropyArea;
            if (canvas && canvas.style.display !== 'none') {
                const ctx = canvas.getContext('2d');
                const centerX = canvas.width / 2;
                const centerY = canvas.height / 2;
                const x = Math.max(0, Math.min(canvas.width, centerX - (accelX * 30)));
                const y = Math.max(0, Math.min(canvas.height, centerY + (accelY * 30)));

                const hue = (entropyCollected * 1.4) % 360;
                ctx.fillStyle = `hsl(${hue}, 70%, 50%)`;
                ctx.beginPath();
                ctx.arc(x, y, 3, 0, Math.PI * 2);
                ctx.fill();
            }

            lastDeviceX = accelX;
            lastDeviceY = accelY;
            lastDeviceZ = accelZ;
        }
    }

    // Variables para debounce del hash
    let lastHashUpdate = 0;
    let hashUpdatePending = false;
    const HASH_UPDATE_INTERVAL = 200;

    function updateEntropyHash() {
        const entropyDisplay = document.getElementById('entropyDisplay');
        if (!entropyDisplay || userEntropy.length === 0) return;

        const entropyBuffer = new Uint8Array(userEntropy.length);
        for (let i = 0; i < userEntropy.length; i++) {
            entropyBuffer[i] = userEntropy[i] & 0xFF;
        }

        let displayString = '';
        if (window.bitcoin && window.bitcoin.crypto && window.bitcoin.crypto.sha256) {
            try {
                const hashes = [];
                for (let i = 0; i < 5; i++) {
                    const saltedBuffer = new Uint8Array(entropyBuffer.length + 1);
                    saltedBuffer.set(entropyBuffer);
                    saltedBuffer[entropyBuffer.length] = i;
                    const hash = window.bitcoin.crypto.sha256(Buffer.from(saltedBuffer));
                    const hashHex = Array.from(hash).map(b => b.toString(16).padStart(2, '0')).join('');
                    hashes.push(hashHex);
                }
                displayString = hashes.join('');
            } catch (e) {
                displayString = Array.from(entropyBuffer.slice(0, 160)).map(n => n.toString(16).padStart(2, '0')).join('');
            }
        } else {
            displayString = Array.from(entropyBuffer.slice(0, 160)).map(n => n.toString(16).padStart(2, '0')).join('');
        }

        entropyDisplay.value = displayString;
    }

    function updateEntropyDisplay() {
        const percent = Math.min(100, Math.floor((entropyCollected / ENTROPY_TARGET) * 100));
        const entropyPercent = document.getElementById('entropyPercent');
        const entropyStatus = document.getElementById('entropyStatus');

        if (entropyPercent) entropyPercent.textContent = percent + '%';

        if (entropyStatus) {
            if (percent >= 100) {
                entropyStatus.textContent = '✅ Suficiente';
                entropyStatus.style.color = 'green';
                setTimeout(() => {
                    const canvas = document.getElementById('mouseEntropyArea');
                    if (canvas) canvas.style.display = 'none';
                }, 1000);
            } else if (percent >= 50) {
                entropyStatus.textContent = '⚠️ Continua...';
                entropyStatus.style.color = 'orange';
            } else {
                entropyStatus.textContent = '❌ Insuficiente';
                entropyStatus.style.color = 'red';
            }
        }

        const now = Date.now();
        if (now - lastHashUpdate >= HASH_UPDATE_INTERVAL && !hashUpdatePending) {
            updateEntropyHash();
            lastHashUpdate = now;
        } else if (!hashUpdatePending) {
            hashUpdatePending = true;
            setTimeout(() => {
                updateEntropyHash();
                lastHashUpdate = Date.now();
                hashUpdatePending = false;
            }, HASH_UPDATE_INTERVAL);
        }
    }

    function mixEntropy() {
        if (!chkAddEntropy || !chkAddEntropy.checked || userEntropy.length === 0) {
            return null;
        }

        const entropyBuffer = new Uint8Array(userEntropy.slice(0, 256));
        const entropyHash = window.bitcoin.crypto.sha256(Buffer.from(entropyBuffer));

        const systemRandom = new Uint8Array(32);
        window.crypto.getRandomValues(systemRandom);

        const mixed = new Uint8Array(32);
        for (let i = 0; i < 32; i++) {
            mixed[i] = entropyHash[i] ^ systemRandom[i];
        }

        return Buffer.from(mixed);
    }

    // Variable para guardar datos generados
    let generatedData = null;

    // Generar direccion
    if (btnGenerateAddress) {
        btnGenerateAddress.addEventListener('click', async function() {
            const waitForLibraries = () => {
                return new Promise((resolve, reject) => {
                    let attempts = 0;
                    const maxAttempts = 100;

                    const checkLibraries = () => {
                        if (window.bitcoin && window.bip39 && window.bip32) {
                            resolve();
                        } else if (attempts >= maxAttempts) {
                            reject(new Error('Timeout esperando librerias'));
                        } else {
                            attempts++;
                            setTimeout(checkLibraries, 100);
                        }
                    };

                    checkLibraries();
                });
            };

            try {
                await waitForLibraries();

                // Generar mnemonico
                let mnemonic;
                const extraEntropy = mixEntropy();

                if (extraEntropy) {
                    const entropy16 = extraEntropy.slice(0, 16);
                    mnemonic = window.bip39.entropyToMnemonic(entropy16);
                    console.log('✓ Mnemonico generado con entropia del usuario');
                } else {
                    mnemonic = window.bip39.generateMnemonic();
                    console.log('✓ Mnemonico generado con entropia del sistema');
                }

                const path = derivationPath.value;

                // Derivar clave
                const seed = window.bip39.mnemonicToSeedSync(mnemonic);
                const root = window.bip32.fromSeed(seed);

                // Obtener xpub del account level
                let accountPath;
                if (path.startsWith("m/84'")) {
                    accountPath = "m/84'/0'/0'";
                } else if (path.startsWith("m/49'")) {
                    accountPath = "m/49'/0'/0'";
                } else {
                    accountPath = "m/44'/0'/0'";
                }
                const accountNode = root.derivePath(accountPath);
                const xpub = accountNode.neutered().toBase58();

                // Derivar primera direccion
                const child = root.derivePath(path);
                const keyPair = window.bitcoin.ECPair.fromPrivateKey(child.privateKey);
                const wif = keyPair.toWIF();

                // Obtener direccion segun tipo
                let address;
                if (path.startsWith("m/84'")) {
                    address = window.bitcoin.payments.p2wpkh({ pubkey: keyPair.publicKey }).address;
                } else if (path.startsWith("m/49'")) {
                    address = window.bitcoin.payments.p2sh({
                        redeem: window.bitcoin.payments.p2wpkh({ pubkey: keyPair.publicKey })
                    }).address;
                } else {
                    address = window.bitcoin.payments.p2pkh({ pubkey: keyPair.publicKey }).address;
                }

                // Guardar datos
                generatedData = {
                    address: address,
                    mnemonic: mnemonic,
                    wif: wif,
                    xpub: xpub,
                    derivationPath: path
                };

                // Mostrar resultado
                document.getElementById('generatedAddress').value = address;
                document.getElementById('generatedMnemonic').value = mnemonic;
                document.getElementById('generatedWIF').value = wif;
                document.getElementById('generatedXpub').value = xpub;
                generatedResult.style.display = 'block';

                // Ocultar entropia
                if (entropyCollector) {
                    entropyCollector.style.display = 'none';
                }
                const canvas = document.getElementById('mouseEntropyArea');
                if (canvas) {
                    canvas.style.display = 'none';
                    const ctx = canvas.getContext('2d');
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                }

                // Limpiar entropia
                userEntropy = [];
                entropyCollected = 0;

            } catch (e) {
                console.error('Error generando direccion:', e);
                alert('Error generando direccion: ' + e.message);
            }
        });
    }

    // Copiar direccion
    const btnCopyAddress = document.getElementById('btnCopyAddress');
    if (btnCopyAddress) {
        btnCopyAddress.addEventListener('click', function() {
            const input = document.getElementById('generatedAddress');
            input.select();
            document.execCommand('copy');
            this.innerHTML = '<i class="fa fa-check"></i>';
            setTimeout(() => { this.innerHTML = '<i class="fa fa-copy"></i>'; }, 2000);
        });
    }

    // Toggle y copiar mnemonico
    const btnToggleMnemonic = document.getElementById('btnToggleMnemonic');
    const generatedMnemonic = document.getElementById('generatedMnemonic');
    if (btnToggleMnemonic) {
        btnToggleMnemonic.addEventListener('click', function() {
            if (generatedMnemonic.type === 'password') {
                generatedMnemonic.type = 'text';
                this.innerHTML = '<i class="fa fa-eye-slash"></i>';
            } else {
                generatedMnemonic.type = 'password';
                this.innerHTML = '<i class="fa fa-eye"></i>';
            }
        });
    }

    const btnCopyMnemonic = document.getElementById('btnCopyMnemonic');
    if (btnCopyMnemonic) {
        btnCopyMnemonic.addEventListener('click', function() {
            const type = generatedMnemonic.type;
            generatedMnemonic.type = 'text';
            generatedMnemonic.select();
            document.execCommand('copy');
            generatedMnemonic.type = type;
            this.innerHTML = '<i class="fa fa-check"></i>';
            setTimeout(() => { this.innerHTML = '<i class="fa fa-copy"></i>'; }, 2000);
        });
    }

    // Toggle y copiar WIF
    const btnToggleWIF = document.getElementById('btnToggleWIF');
    const generatedWIF = document.getElementById('generatedWIF');
    if (btnToggleWIF) {
        btnToggleWIF.addEventListener('click', function() {
            if (generatedWIF.type === 'password') {
                generatedWIF.type = 'text';
                this.innerHTML = '<i class="fa fa-eye-slash"></i>';
            } else {
                generatedWIF.type = 'password';
                this.innerHTML = '<i class="fa fa-eye"></i>';
            }
        });
    }

    const btnCopyWIF = document.getElementById('btnCopyWIF');
    if (btnCopyWIF) {
        btnCopyWIF.addEventListener('click', function() {
            const type = generatedWIF.type;
            generatedWIF.type = 'text';
            generatedWIF.select();
            document.execCommand('copy');
            generatedWIF.type = type;
            this.innerHTML = '<i class="fa fa-check"></i>';
            setTimeout(() => { this.innerHTML = '<i class="fa fa-copy"></i>'; }, 2000);
        });
    }

    // Copiar xpub
    const btnCopyXpub = document.getElementById('btnCopyXpub');
    if (btnCopyXpub) {
        btnCopyXpub.addEventListener('click', function() {
            const input = document.getElementById('generatedXpub');
            input.select();
            document.execCommand('copy');
            this.innerHTML = '<i class="fa fa-check"></i>';
            setTimeout(() => { this.innerHTML = '<i class="fa fa-copy"></i>'; }, 2000);
        });
    }

    // Descargar claves
    const btnDownloadKeys = document.getElementById('btnDownloadKeys');
    const btnUseAddress = document.getElementById('btnUseAddress');

    if (btnDownloadKeys) {
        btnDownloadKeys.addEventListener('click', function() {
            if (!generatedData) {
                alert('No hay claves generadas para guardar.');
                return;
            }

            const now = new Date();
            const fileContent = {
                "generado": now.toISOString(),
                "tipo": "Bitcoin Wallet (BIP39)",
                "advertencia": "MANTÉN ESTE ARCHIVO EN SECRETO. Quien tenga acceso puede gastar tus fondos.",
                "direccion_publica": generatedData.address,
                "frase_recuperacion_12_palabras": generatedData.mnemonic,
                "ruta_derivacion": generatedData.derivationPath,
                "clave_privada_WIF": generatedData.wif,
                "xpub": generatedData.xpub
            };

            const blob = new Blob([JSON.stringify(fileContent, null, 2)], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'bitcoin-wallet-' + now.toISOString().slice(0, 19).replace(/:/g, '-') + '.json';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);

            this.innerHTML = '<i class="fa fa-check"></i> Guardado';
            setTimeout(() => {
                this.innerHTML = '<i class="fa fa-download"></i> Guardar en archivo';
            }, 2000);

            // Habilitar boton de usar direccion
            btnUseAddress.classList.remove('btn-disabled');
            btnUseAddress.style.pointerEvents = 'auto';
            btnUseAddress.title = '';
        });
    }

    // Usar direccion generada
    if (btnUseAddress) {
        btnUseAddress.addEventListener('click', function() {
            if (this.classList.contains('btn-disabled')) {
                alert('Primero guarda las claves en un archivo seguro.');
                return;
            }

            if (!generatedData) return;

            // Disparar evento custom para que script.js lo capture
            const event = new CustomEvent('wallet:addressGenerated', {
                detail: {
                    address: generatedData.address,
                    xpub: generatedData.xpub,
                    derivationPath: generatedData.derivationPath
                }
            });
            document.dispatchEvent(event);

            // Cerrar dialogo
            const overlay = document.querySelector('.overlay');
            if (overlay && overlay.dialog && overlay.dialog.close) {
                overlay.dialog.close();
            } else {
                // Fallback: buscar y cerrar
                const closeBtn = document.querySelector('.overlay .btn-cancel, .overlay .close-btn');
                if (closeBtn) closeBtn.click();
            }
        });
    }

})();
</script>
