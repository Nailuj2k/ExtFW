// Vanilla JS CryptoLib
        const CryptoLib = {
            
            // ========== HELPERS PARA ARCHIVOS GRANDES ==========
            
            // Convertir Uint8Array a base64 (safe para archivos grandes)
            arrayToBase64(bytes) {
                const CHUNK_SIZE = 0x8000; // 32KB chunks
                let result = '';
                for (let i = 0; i < bytes.length; i += CHUNK_SIZE) {
                    const chunk = bytes.subarray(i, i + CHUNK_SIZE);
                    result += String.fromCharCode.apply(null, chunk);
                }
                return btoa(result);
            },
            
            // Convertir base64 a Uint8Array
            base64ToArray(base64) {
                const binary = atob(base64);
                const bytes = new Uint8Array(binary.length);
                for (let i = 0; i < binary.length; i++) {
                    bytes[i] = binary.charCodeAt(i);
                }
                return bytes;
            },

            // Generate keys: separate for ECDH (encrypt) and ECDSA (sign)
            async generateKeys() {
                const encKeyPair = await crypto.subtle.generateKey(
                    { name: "ECDH", namedCurve: "P-256" },
                    true,
                    ["deriveBits"]
                );
                const signKeyPair = await crypto.subtle.generateKey(
                    { name: "ECDSA", namedCurve: "P-256" },
                    true,
                    ["sign", "verify"]
                );
                return { encKeyPair, signKeyPair };
            },

            // Export key to base64 (SPKI for pub, PKCS8 for priv)
            async exportKey(key, format) {
                const exported = await crypto.subtle.exportKey(format, key);
                return btoa(String.fromCharCode(...new Uint8Array(exported)));
            },

            // Import public key
            async importPublic(base64, algo) {
                const binary = Uint8Array.from(atob(base64), c => c.charCodeAt(0));
                return await crypto.subtle.importKey(
                    "spki",
                    binary,
                    algo,
                    true,
                    algo.name === "ECDH" ? [] : ["verify"]
                );
            },

            // Import private key
            async importPrivate(base64, algo, usages) {
                const binary = Uint8Array.from(atob(base64), c => c.charCodeAt(0));
                return await crypto.subtle.importKey(
                    "pkcs8",
                    binary,
                    algo,
                    true,
                    usages
                );
            },

            // HKDF key derivation (must match PHP: hash_hkdf('sha256', $shared, 32, 'AES-256-GCM key'))
            async deriveAesKey(sharedBits, usage) {
                const sharedKey = await crypto.subtle.importKey(
                    "raw",
                    sharedBits,
                    { name: "HKDF" },
                    false,
                    ["deriveBits", "deriveKey"]
                );
                return await crypto.subtle.deriveKey(
                    {
                        name: "HKDF",
                        hash: "SHA-256",
                        salt: new Uint8Array(0),
                        info: new TextEncoder().encode("AES-256-GCM key")
                    },
                    sharedKey,
                    { name: "AES-GCM", length: 256 },
                    false,
                    [usage]
                );
            },

            // Encrypt: Hybrid ECDH + AES-GCM with HKDF
            async encrypt(text, recipientPubBase64) {
                const recipientPub = await this.importPublic(recipientPubBase64, { name: "ECDH", namedCurve: "P-256" });
                
                // Ephemeral keypair
                const ephemeral = await crypto.subtle.generateKey(
                    { name: "ECDH", namedCurve: "P-256" },
                    true,
                    ["deriveBits"]
                );
                
                // Derive shared secret (32 bytes)
                const shared = await crypto.subtle.deriveBits(
                    { name: "ECDH", public: recipientPub },
                    ephemeral.privateKey,
                    256
                );
                
                // Derive AES key with HKDF (matches PHP hash_hkdf)
                const aesKey = await this.deriveAesKey(shared, "encrypt");
                
                const iv = crypto.getRandomValues(new Uint8Array(12));
                const data = new TextEncoder().encode(text);
                const encrypted = await crypto.subtle.encrypt(
                    { name: "AES-GCM", iv },
                    aesKey,
                    data
                );
                
                // Encrypted is ciphertext + tag (last 16 bytes tag)
                const encryptedArray = new Uint8Array(encrypted);
                const ciphertext = encryptedArray.slice(0, -16);
                const tag = encryptedArray.slice(-16);
                
                // Export ephemeral pub
                const ephPub = await this.exportKey(ephemeral.publicKey, "spki");
                
                return {
                    ephPub,
                    iv: btoa(String.fromCharCode(...iv)),
                    ciphertext: btoa(String.fromCharCode(...ciphertext)),
                    tag: btoa(String.fromCharCode(...tag))
                };
            },

            // Decrypt
            async decrypt(encData, myEncPrivBase64) {
                const myPriv = await this.importPrivate(myEncPrivBase64, { name: "ECDH", namedCurve: "P-256" }, ["deriveBits"]);
                const ephPub = await this.importPublic(encData.ephPub, { name: "ECDH", namedCurve: "P-256" });
                
                const shared = await crypto.subtle.deriveBits(
                    { name: "ECDH", public: ephPub },
                    myPriv,
                    256
                );
                
                // Derive AES key with HKDF (matches PHP hash_hkdf)
                const aesKey = await this.deriveAesKey(shared, "decrypt");
                
                const iv = Uint8Array.from(atob(encData.iv), c => c.charCodeAt(0));
                const ciphertext = Uint8Array.from(atob(encData.ciphertext), c => c.charCodeAt(0));
                const tag = Uint8Array.from(atob(encData.tag), c => c.charCodeAt(0));
                const combined = new Uint8Array(ciphertext.length + tag.length);
                combined.set(ciphertext);
                combined.set(tag, ciphertext.length);
                
                const decrypted = await crypto.subtle.decrypt(
                    { name: "AES-GCM", iv },
                    aesKey,
                    combined
                );
                
                return new TextDecoder().decode(decrypted);
            },

            // Sign: Raw r+s (64 bytes)
            async sign(text, mySignPrivBase64) {
                const priv = await this.importPrivate(mySignPrivBase64, { name: "ECDSA", namedCurve: "P-256" }, ["sign"]);
                const data = new TextEncoder().encode(text);
                const sig = await crypto.subtle.sign(
                    { name: "ECDSA", hash: "SHA-256" },
                    priv,
                    data
                );
                return btoa(String.fromCharCode(...new Uint8Array(sig)));
            },

            // Verify: with raw sig
            async verify(text, sigBase64, signerPubBase64) {
                const pub = await this.importPublic(signerPubBase64, { name: "ECDSA", namedCurve: "P-256" });
                const data = new TextEncoder().encode(text);
                const sig = Uint8Array.from(atob(sigBase64), c => c.charCodeAt(0));
                return await crypto.subtle.verify(
                    { name: "ECDSA", hash: "SHA-256" },
                    pub,
                    sig,
                    data
                );
            },

            // ========== FILE ENCRYPTION ==========

            // Encrypt a File object
            async encryptFile(file, recipientPubBase64) {
                const recipientPub = await this.importPublic(recipientPubBase64, { name: "ECDH", namedCurve: "P-256" });
                
                // Read file as ArrayBuffer
                const fileData = await file.arrayBuffer();
                const fileBytes = new Uint8Array(fileData);
                
                // Ephemeral keypair
                const ephemeral = await crypto.subtle.generateKey(
                    { name: "ECDH", namedCurve: "P-256" },
                    true,
                    ["deriveBits"]
                );
                
                // Derive shared secret
                const shared = await crypto.subtle.deriveBits(
                    { name: "ECDH", public: recipientPub },
                    ephemeral.privateKey,
                    256
                );
                
                // Derive AES key with HKDF
                const aesKey = await this.deriveAesKey(shared, "encrypt");
                
                const iv = crypto.getRandomValues(new Uint8Array(12));
                const encrypted = await crypto.subtle.encrypt(
                    { name: "AES-GCM", iv },
                    aesKey,
                    fileBytes
                );
                
                const encryptedArray = new Uint8Array(encrypted);
                const ciphertext = encryptedArray.slice(0, -16);
                const tag = encryptedArray.slice(-16);
                
                const ephPub = await this.exportKey(ephemeral.publicKey, "spki");
                
                // Calculate hash for filename
                const hashBuffer = await crypto.subtle.digest('SHA-256', ciphertext);
                const hashArray = Array.from(new Uint8Array(hashBuffer));
                const hash = hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
                
                // Get extension from filename
                const ext = file.name.includes('.') ? file.name.split('.').pop() : '';
                
                return {
                    ephPub,
                    iv: btoa(String.fromCharCode(...iv)),
                    ciphertext: this.arrayToBase64(ciphertext),  // Safe para archivos grandes
                    tag: btoa(String.fromCharCode(...tag)),
                    filename: file.name,
                    mimetype: file.type,
                    size: file.size,
                    hash,
                    ext
                };
            },

            // Decrypt to file (returns Blob)
            async decryptFile(encData, myEncPrivBase64) {
                const myPriv = await this.importPrivate(myEncPrivBase64, { name: "ECDH", namedCurve: "P-256" }, ["deriveBits"]);
                const ephPub = await this.importPublic(encData.ephPub, { name: "ECDH", namedCurve: "P-256" });
                
                const shared = await crypto.subtle.deriveBits(
                    { name: "ECDH", public: ephPub },
                    myPriv,
                    256
                );
                
                const aesKey = await this.deriveAesKey(shared, "decrypt");
                
                const iv = this.base64ToArray(encData.iv);
                const ciphertext = this.base64ToArray(encData.ciphertext);  // Safe para archivos grandes
                const tag = this.base64ToArray(encData.tag);
                const combined = new Uint8Array(ciphertext.length + tag.length);
                combined.set(ciphertext);
                combined.set(tag, ciphertext.length);
                
                const decrypted = await crypto.subtle.decrypt(
                    { name: "AES-GCM", iv },
                    aesKey,
                    combined
                );
                
                return new Blob([decrypted], { type: encData.mimetype || 'application/octet-stream' });
            },

            // Download blob as file
            downloadBlob(blob, filename) {
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            },

            // Download encrypted data as .enc file
            downloadEncrypted(encData) {
                const json = JSON.stringify(encData);
                const blob = new Blob([json], { type: 'application/json' });
                const filename = encData.hash + '.' + (encData.ext || 'bin') + '.enc';
                this.downloadBlob(blob, filename);
            }
        };

