<?php
class Crypt {
    public static function generateKeyPair($bits = 2048) {
        $res = openssl_pkey_new(['private_key_bits' => $bits]);
        openssl_pkey_export($res, $privateKey);
        $details = openssl_pkey_get_details($res);
        $publicKey = $details['key'];
        return [
            'private' => $privateKey,
            'public' => $publicKey
        ];
    }

    public static function sign($data, $privateKey) {
        openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        return base64_encode($signature);
    }

    public static function verify($data, $signature, $publicKey) {
        return openssl_verify($data, base64_decode($signature), $publicKey, OPENSSL_ALGO_SHA256) === 1;
    }
}

class IPFSUploader {
    public $host;
    public $port;
    public $apiToken;
    public $useLocal = true;
    public $format = 'json'; // 'json' o 'html'
    public $gateway = 'ipfs.io';
    public $privateKey = null;
    public $publicKey = null;

    public function __construct($config = []) {
        $this->host = $config['host'] ?? 'localhost';
        $this->port = $config['port'] ?? 5001;
        $this->apiToken = $config['apiToken'] ?? null;
        $this->useLocal = $config['useLocal'] ?? true;
        $this->format = $config['format'] ?? 'json';
        $this->gateway = $config['gateway'] ?? 'ipfs.io';
        $this->privateKey = $config['privateKey'] ?? null;
        $this->publicKey = $config['publicKey'] ?? null;
    }

    public function publish($title, $text) {
        $timestamp = time();
        $content = [
            'title' => $title,
            'text' => $text,
            'timestamp' => $timestamp
        ];

        $serialized = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if ($this->privateKey && $this->publicKey) {
            $signature = Crypt::sign($serialized, $this->privateKey);
            $content = [
                'data' => $content,
                'signature' => $signature,
                'public_key' => base64_encode($this->publicKey)
            ];
            $serialized = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } elseif ($this->format === 'html') {
            $serialized = $this->generateHTML($title, $text);
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'ipfs_');
        file_put_contents($tempFile, $serialized);

        $response = $this->useLocal ? $this->uploadToLocal($tempFile) : $this->uploadToWeb3Storage($tempFile);

        unlink($tempFile);

        $cid = $response['cid'] ?? $response['Hash'] ?? null;

        return [
            'cid' => $cid,
            'url' => "https://{$this->gateway}/ipfs/{$cid}",
            'format' => $this->format,
            'signed' => $this->privateKey !== null,
            'timestamp' => $timestamp,
            'raw_response' => $response
        ];
    }

    private function generateHTML($title, $text) {
        return "<h1>" . htmlspecialchars($title) . "</h1><p>" . nl2br(htmlspecialchars($text)) . "</p>";
    }

    private function uploadToLocal($filePath) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://{$this->host}:{$this->port}/api/v0/add");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ['file' => new CURLFile($filePath)]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
    }

    private function uploadToWeb3Storage($filePath) {
        if (!$this->apiToken) {
            throw new Exception("API token requerido para web3.storage");
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.web3.storage/upload');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, file_get_contents($filePath));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->apiToken,
            'Content-Type: application/octet-stream'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
    }
}

// Ejemplo de uso:
/*
$keys = Crypt::generateKeyPair();

$ipfs = new IPFSUploader([
    'useLocal' => true,
    'format' => 'json',
    'privateKey' => $keys['private'],
    'publicKey' => $keys['public'],
    'gateway' => 'dweb.link'
]);

$result = $ipfs->publish("Mi artículo firmado", "Este es el contenido del artículo.");
echo "Publicado en IPFS: {$result['url']}\n";
*/




/////////////. EJEMPLO

require_once 'IPFSUploader.php'; // Asegúrate de incluir tu clase aquí

function verificarDesdeCID($cid, $gateway = 'ipfs.io') {
    $url = "https://$gateway/ipfs/$cid";
    $json = file_get_contents($url);
    $data = json_decode($json, true);

    if (!isset($data['data'], $data['signature'], $data['public_key'])) {
        return ['valido' => false, 'motivo' => 'Formato no firmado o incompleto'];
    }

    $contenido = json_encode($data['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    $firma = $data['signature'];
    $clavePublica = base64_decode($data['public_key']);

    $valido = Crypt::verify($contenido, $firma, $clavePublica);

    return [
        'valido' => $valido,
        'titulo' => $data['data']['title'] ?? '',
        'texto' => $data['data']['text'] ?? '',
        'timestamp' => $data['data']['timestamp'] ?? null,
        'firma' => $firma
    ];
}

// Ejemplo de uso:
$cid = $_GET['cid'] ?? null;

if ($cid) {
    $verificacion = verificarDesdeCID($cid);
    header('Content-Type: application/json');
    echo json_encode($verificacion, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} else {
    echo "Proporcione un CID con ?cid=...";
}

/////////////. EJEMPLO

/*
<form action="verificar_firma.php" method="get">
    <label for="cid">Introduce un CID de IPFS:</label><br>
    <input type="text" id="cid" name="cid" style="width: 400px"><br>
    <input type="submit" value="Verificar firma">
</form>
*/


/////////////. EJEMPLO

async function verifySignature({ jsonData, signatureB64, publicKeyPEM }) {
    // 1. Convertir la clave pública PEM en ArrayBuffer
    const pemHeader = "-----BEGIN PUBLIC KEY-----";
    const pemFooter = "-----END PUBLIC KEY-----";
    const pemContents = publicKeyPEM
        .replace(pemHeader, "")
        .replace(pemFooter, "")
        .replace(/\s/g, "");
    const binaryDer = Uint8Array.from(atob(pemContents), c => c.charCodeAt(0));

    // 2. Importar la clave como CryptoKey
    const publicKey = await crypto.subtle.importKey(
        "spki",
        binaryDer.buffer,
        {
            name: "RSASSA-PKCS1-v1_5",
            hash: "SHA-256",
        },
        false,
        ["verify"]
    );

    // 3. Codificar el JSON original como UTF-8
    const encoder = new TextEncoder();
    const data = encoder.encode(JSON.stringify(jsonData, null, 2));

    // 4. Decodificar la firma
    const signature = Uint8Array.from(atob(signatureB64), c => c.charCodeAt(0));

    // 5. Verificar
    const isValid = await crypto.subtle.verify(
        "RSASSA-PKCS1-v1_5",
        publicKey,
        signature,
        data
    );

    return isValid;
}

/////////////. EJEMPLO

const data = {
  title: "Ejemplo",
  text: "Esto es un artículo",
  timestamp: 1712152000
};

const signature = "Z2VqaHRyZWN..."  // cadena base64
const publicKey = `-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAz...
-----END PUBLIC KEY-----`;

verifySignature({ jsonData: data, signatureB64: signature, publicKeyPEM: publicKey })
  .then(result => {
    console.log("¿Firma válida?", result);
  });


?>


<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Verificador IPFS</title>
</head>
<body>
  <h2>Verificar firma de contenido en IPFS (JSON firmado)</h2>
  <form id="verificador">
    <label for="cid">CID de IPFS:</label><br>
    <input type="text" id="cid" name="cid" style="width: 400px" required><br><br>

    <label for="gateway">Gateway:</label><br>
    <input type="text" id="gateway" value="ipfs.io" style="width: 200px"><br><br>

    <label for="pubkey">Clave pública (PEM):</label><br>
    <textarea id="pubkey" rows="10" cols="60" placeholder="-----BEGIN PUBLIC KEY-----\n...\n-----END PUBLIC KEY-----" required></textarea><br><br>

    <button type="submit">Verificar</button>
  </form>

  <pre id="resultado"></pre>

  <script>
    async function verifySignature({ jsonData, signatureB64, publicKeyPEM }) {
      const pemHeader = "-----BEGIN PUBLIC KEY-----";
      const pemFooter = "-----END PUBLIC KEY-----";
      const pemContents = publicKeyPEM
        .replace(pemHeader, "")
        .replace(pemFooter, "")
        .replace(/\s/g, "");
      const binaryDer = Uint8Array.from(atob(pemContents), c => c.charCodeAt(0));

      const publicKey = await crypto.subtle.importKey(
        "spki",
        binaryDer.buffer,
        {
          name: "RSASSA-PKCS1-v1_5",
          hash: "SHA-256",
        },
        false,
        ["verify"]
      );

      const encoder = new TextEncoder();
      const data = encoder.encode(JSON.stringify(jsonData, null, 2));
      const signature = Uint8Array.from(atob(signatureB64), c => c.charCodeAt(0));

      const isValid = await crypto.subtle.verify(
        "RSASSA-PKCS1-v1_5",
        publicKey,
        signature,
        data
      );

      return isValid;
    }

    document.getElementById("verificador").addEventListener("submit", async function (e) {
      e.preventDefault();
      const cid = document.getElementById("cid").value.trim();
      const gateway = document.getElementById("gateway").value.trim();
      const pubkey = document.getElementById("pubkey").value.trim();
      const output = document.getElementById("resultado");
      output.textContent = "Cargando...";

      try {
        const res = await fetch(`https://${gateway}/ipfs/${cid}`);
        const body = await res.json();

        if (!body.data || !body.signature || !body.public_key) {
          output.textContent = "El contenido no contiene estructura de firma válida.";
          return;
        }

        const valido = await verifySignature({
          jsonData: body.data,
          signatureB64: body.signature,
          publicKeyPEM: pubkey
        });

        output.textContent = valido
          ? "✅ Firma válida. El contenido es auténtico.\n\n" + JSON.stringify(body.data, null, 2)
          : "❌ Firma no válida. El contenido ha sido alterado o la clave no es correcta.";
      } catch (err) {
        output.textContent = "Error al verificar: " + err;
      }
    });
  </script>
</body>
</html>
