    var CryptoJSAesJson = {
        stringify: function (cipherParams) {
            var j = {ct: cipherParams.ciphertext.toString(CryptoJS.enc.Base64)};
            if (cipherParams.iv) j.iv = cipherParams.iv.toString();
            if (cipherParams.salt) j.s = cipherParams.salt.toString();
            return JSON.stringify(j);
        },
        parse: function (jsonStr) {
            var j = JSON.parse(jsonStr);
            var cipherParams = CryptoJS.lib.CipherParams.create({ciphertext: CryptoJS.enc.Base64.parse(j.ct)});
            if (j.iv) cipherParams.iv = CryptoJS.enc.Hex.parse(j.iv)
            if (j.s) cipherParams.salt = CryptoJS.enc.Hex.parse(j.s)
            return cipherParams;
        }
    }

    function json2string(str) {
        return str.replace('":"','_99_').replace('","','_98_')
                  .replace('":"','_99_').replace('","','_98_')
                  .replace('":"','_99_').replace('","','_98_')
                  .replace('":"','_99_').replace('","','_98_')
                  .replace('{"','_96_' ).replace('"}','_97_' );
    }
     
    function string2json(str) {
        return str.replace('_99_','":"').replace('_98_','","')
                  .replace('_99_','":"').replace('_98_','","')
                  .replace('_99_','":"').replace('_98_','","')
                  .replace('_99_','":"').replace('_98_','","')
                  .replace('_96_','{"' ).replace('_97_','"}' );
    }

    function str2crypt(str,key){
        //str = trim(str); 
        if (!str || str=='')
            return str;
        else
            return json2string(CryptoJS.AES.encrypt(JSON.stringify(str), key, {format: CryptoJSAesJson}).toString()) 
    }

    function crypt2str(encrypted,key){
        if (!encrypted || encrypted=='') 
            return $encrypted;
        else
            return JSON.parse(CryptoJS.AES.decrypt(string2json(encrypted), key, {format: CryptoJSAesJson}).toString(CryptoJS.enc.Utf8));
    }



    // pruebas


/**
 * Función para encriptar texto con una clave pública RSA compatible con Crypt::decrypt() de PHP
 * 
 * @param {string} text - Texto a encriptar
 * @param {string} publicKey - Clave pública RSA generada por Crypt::keys() en PHP
 * @returns {string} - Texto encriptado que puede ser desencriptado con Crypt::decrypt()
 */

async function NNencrypt(text, publicKey) {
    // Importar la clave pública
    const importedKey = await importPublicKey(publicKey);
    
    // Dividir el texto en fragmentos de 10 caracteres (como en PHP)
    const chunkSize = 10;
    let encryptedChunks = [];
    
    for (let i = 0; i < text.length; i += chunkSize) {
        const chunk = text.substr(i, chunkSize);
        const encryptedChunk = await encryptChunk(chunk, importedKey);
        encryptedChunks.push(encryptedChunk);
    }
    
    // Unir los chunks encriptados con ":::" como en PHP
    return encryptedChunks.join(":::");
}

/**
 * Importa una clave pública PEM a formato WebCrypto
 * 
 * @param {string} publicKeyPem - Clave pública en formato PEM
 * @returns {CryptoKey} - Objeto CryptoKey para usar con la API WebCrypto
 */
async function importPublicKey(publicKeyPem) {
    // Limpiar el formato PEM para obtener solo la parte Base64
    const pemHeader = "-----BEGIN PUBLIC KEY-----";
    const pemFooter = "-----END PUBLIC KEY-----";
    const pemContents = publicKeyPem.substring(
        publicKeyPem.indexOf(pemHeader) + pemHeader.length,
        publicKeyPem.indexOf(pemFooter)
    ).replace(/\s/g, '');
    
    // Decodificar la parte Base64 a un ArrayBuffer
    const binaryDer = base64ToArrayBuffer(pemContents);
    
    // Importar la clave usando la API WebCrypto
    return window.crypto.subtle.importKey(
        "spki",
        binaryDer,
        {
            name: "RSA-OAEP",
            hash: { name: "SHA-1" }, // Usamos SHA-1 para compatibilidad con la implementación PHP
        },
        false,
        ["encrypt"]
    );
}

/**
 * Convierte una cadena Base64 a ArrayBuffer
 * 
 * @param {string} base64 - Cadena en formato Base64
 * @returns {ArrayBuffer} - Datos binarios en formato ArrayBuffer
 */
function base64ToArrayBuffer(base64) {
    const binaryString = window.atob(base64);
    const bytes = new Uint8Array(binaryString.length);
    for (let i = 0; i < binaryString.length; i++) {
        bytes[i] = binaryString.charCodeAt(i);
    }
    return bytes.buffer;
}

/**
 * Encripta un fragmento de texto con la clave pública
 * 
 * @param {string} chunk - Fragmento de texto a encriptar
 * @param {CryptoKey} publicKey - Clave pública importada
 * @returns {string} - Fragmento encriptado en formato compatible con PHP
 */
async function encryptChunk(chunk, publicKey) {
    // Convertir el texto a ArrayBuffer
    const encoder = new TextEncoder();
    const data = encoder.encode(chunk);
    
    // Encriptar los datos
    const encryptedBuffer = await window.crypto.subtle.encrypt(
        {
            name: "RSA-OAEP"
        },
        publicKey,
        data
    );
    
    // Convertir el resultado a Base64 para compatibilidad con PHP
    return arrayBufferToBase64(encryptedBuffer);
}

/**
 * Convierte un ArrayBuffer a una cadena Base64
 * 
 * @param {ArrayBuffer} buffer - Datos binarios en formato ArrayBuffer
 * @returns {string} - Cadena en formato Base64
 */
function arrayBufferToBase64(buffer) {
    const bytes = new Uint8Array(buffer);
    let binary = '';
    for (let i = 0; i < bytes.byteLength; i++) {
        binary += String.fromCharCode(bytes[i]);
    }
    return window.btoa(binary);
}    