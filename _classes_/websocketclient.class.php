<?php

/**
 * Minimal WebSocket client for CLI workers.
 *
 * Intentional scope:
 * - RFC6455 handshake
 * - ws / wss
 * - text frames
 * - ping / pong / close handling
 *
 * PHP 8.4 migration:
 * - convert properties to typed properties
 * - convert generic RuntimeException messages to dedicated exceptions if useful
 */
class WebSocketClient
{
    /** @var string */
    private $url;
    /** @var array<string,mixed> */
    private $options;
    /** @var resource|null */
    private $stream = null;
    /** @var bool */
    private $connected = false;
    /** @var string */
    private $host = '';
    /** @var int */
    private $port = 0;
    /** @var string */
    private $path = '/';
    /** @var string */
    private $scheme = 'ws';
    /** @var string */
    private $secWebSocketKey = '';

    /**
     * @param array<string,mixed> $options
     */
    public function __construct(string $url, array $options = [])
    {
        $this->url = $url;
        $this->options = $options;
    }

    public function connect(): void
    {
        if ($this->connected) {
            return;
        }

        $this->parseUrl();
        $stream = $this->openSocket();

        $this->stream = $stream;
        $this->performHandshake();
        $this->connected = true;
    }

    public function isConnected(): bool
    {
        return $this->connected && is_resource($this->stream);
    }

    /**
     * @return resource|null
     */
    public function getStream()
    {
        return is_resource($this->stream) ? $this->stream : null;
    }

    public function sendText(string $payload): void
    {
        $this->sendFrame($payload, 0x1);
    }

    public function sendPing(string $payload = ''): void
    {
        $this->sendFrame($payload, 0x9);
    }

    public function close(int $statusCode = 1000, string $reason = ''): void
    {
        if (!$this->isConnected()) {
            return;
        }

        $payload = pack('n', $statusCode) . $reason;

        try {
            $this->sendFrame($payload, 0x8);
        } catch (Exception $e) {
            // Ignore close send failures; the goal is to release the socket.
        }

        if (is_resource($this->stream)) {
            fclose($this->stream);
        }

        $this->stream = null;
        $this->connected = false;
    }

    /**
     * Returns next text message payload or null on read timeout.
     */
    public function receiveText()
    {
        $message = '';
        $started = false;

        while (true) {
            $frame = $this->receiveFrame();
            if ($frame === null) {
                return $started ? $message : null;
            }

            $opcode = $frame['opcode'];
            $payload = $frame['payload'];
            $fin = $frame['fin'];

            if ($opcode === 0x8) {
                $this->close();
                return null;
            }

            if ($opcode === 0x9) {
                $this->sendFrame($payload, 0xA);
                continue;
            }

            if ($opcode === 0xA) {
                continue;
            }

            if ($opcode === 0x1) {
                $message .= $payload;
                $started = true;
                if ($fin) {
                    return $message;
                }
                continue;
            }

            if ($opcode === 0x0 && $started) {
                $message .= $payload;
                if ($fin) {
                    return $message;
                }
            }
        }
    }

    /**
     * @return array{fin:bool,opcode:int,payload:string}|null
     */
    private function receiveFrame()
    {
        if (!$this->isConnected()) {
            throw new RuntimeException('WebSocket is not connected.');
        }

        $header = $this->readBytes(2, true);
        if ($header === null) {
            return null;
        }

        $first = ord($header[0]);
        $second = ord($header[1]);

        $fin = (bool)($first & 0x80);
        $opcode = $first & 0x0F;
        $masked = (bool)($second & 0x80);
        $length = $second & 0x7F;

        if ($length === 126) {
            $extended = $this->readBytes(2);
            $parts = unpack('nlength', $extended);
            $length = (int)$parts['length'];
        } elseif ($length === 127) {
            $extended = $this->readBytes(8);
            $parts = unpack('Nhigh/Nlow', $extended);
            $length = ((int)$parts['high'] * 4294967296) + (int)$parts['low'];
            if ($length < 0) {
                throw new RuntimeException('Invalid 64-bit frame length.');
            }
        }

        $maskKey = '';
        if ($masked) {
            $maskKey = $this->readBytes(4);
        }

        $payload = $length > 0 ? $this->readBytes($length) : '';
        if ($masked && $payload !== '') {
            $payload = $this->applyMask($payload, $maskKey);
        }

        return [
            'fin' => $fin,
            'opcode' => $opcode,
            'payload' => $payload,
        ];
    }

    private function sendFrame(string $payload, int $opcode): void
    {
        if (!$this->isConnected()) {
            throw new RuntimeException('WebSocket is not connected.');
        }

        $finAndOpcode = 0x80 | ($opcode & 0x0F);
        $length = strlen($payload);
        $maskKey = random_bytes(4);
        $maskedPayload = $this->applyMask($payload, $maskKey);

        if ($length <= 125) {
            $header = chr($finAndOpcode) . chr(0x80 | $length);
        } elseif ($length <= 65535) {
            $header = chr($finAndOpcode) . chr(0x80 | 126) . pack('n', $length);
        } else {
            $high = (int)floor($length / 4294967296);
            $low = $length % 4294967296;
            $header = chr($finAndOpcode) . chr(0x80 | 127) . pack('NN', $high, $low);
        }

        $frame = $header . $maskKey . $maskedPayload;
        $offset = 0;
        $length = strlen($frame);

        while ($offset < $length) {
            $written = fwrite($this->stream, substr($frame, $offset));
            if ($written === false || $written === 0) {
                throw new RuntimeException('Unable to write WebSocket frame.');
            }
            $offset += $written;
        }
    }

    private function parseUrl(): void
    {
        $parts = parse_url($this->url);
        if (!is_array($parts)) {
            throw new InvalidArgumentException('Invalid websocket URL: ' . $this->url);
        }

        $scheme = strtolower((string)($parts['scheme'] ?? ''));
        if (!in_array($scheme, ['ws', 'wss'], true)) {
            throw new InvalidArgumentException('Unsupported websocket scheme: ' . $scheme);
        }

        $host = (string)($parts['host'] ?? '');
        if ($host === '') {
            throw new InvalidArgumentException('Missing websocket host in URL: ' . $this->url);
        }

        $port = isset($parts['port'])
            ? (int)$parts['port']
            : ($scheme === 'wss' ? 443 : 80);

        $path = (string)($parts['path'] ?? '/');
        if ($path === '') {
            $path = '/';
        }

        if (!empty($parts['query'])) {
            $path .= '?' . $parts['query'];
        }

        $this->scheme = $scheme;
        $this->host = $host;
        $this->port = $port;
        $this->path = $path;
    }

    /**
     * @return resource
     */
    private function openSocket()
    {
        $transport = $this->scheme === 'wss' ? 'ssl' : 'tcp';
        $remote = $transport . '://' . $this->host . ':' . $this->port;

        $timeout = (float)($this->options['connect_timeout'] ?? 10);
        $readTimeout = (int)($this->options['read_timeout'] ?? 5);
        $readTimeoutUsec = (int)($this->options['read_timeout_usec'] ?? 0);

        $contextOptions = [];
        if ($this->scheme === 'wss') {
            $contextOptions['ssl'] = [
                'SNI_enabled' => true,
                'peer_name' => $this->host,
                'verify_peer' => (bool)($this->options['verify_peer'] ?? true),
                'verify_peer_name' => (bool)($this->options['verify_peer_name'] ?? true),
                'allow_self_signed' => (bool)($this->options['allow_self_signed'] ?? false),
            ];
        }

        $context = stream_context_create($contextOptions);
        $errno = 0;
        $errstr = '';

        $stream = @stream_socket_client(
            $remote,
            $errno,
            $errstr,
            $timeout,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if (!is_resource($stream)) {
            throw new RuntimeException(
                'Unable to connect to websocket relay ' . $this->url . ' [' . $errno . '] ' . $errstr
            );
        }

        stream_set_blocking($stream, true);
        stream_set_timeout($stream, $readTimeout, $readTimeoutUsec);

        return $stream;
    }

    private function performHandshake(): void
    {
        $this->secWebSocketKey = base64_encode(random_bytes(16));

        $hostHeader = $this->host;
        $defaultPort = $this->scheme === 'wss' ? 443 : 80;
        if ($this->port !== $defaultPort) {
            $hostHeader .= ':' . $this->port;
        }

        $headers = [
            'GET ' . $this->path . ' HTTP/1.1',
            'Host: ' . $hostHeader,
            'Upgrade: websocket',
            'Connection: Upgrade',
            'Sec-WebSocket-Key: ' . $this->secWebSocketKey,
            'Sec-WebSocket-Version: 13',
            'User-Agent: ExtFW-WebSocketClient/0.1',
        ];

        if (!empty($this->options['origin'])) {
            $headers[] = 'Origin: ' . $this->options['origin'];
        }

        if (!empty($this->options['subprotocol'])) {
            $headers[] = 'Sec-WebSocket-Protocol: ' . $this->options['subprotocol'];
        }

        $request = implode("\r\n", $headers) . "\r\n\r\n";
        $written = fwrite($this->stream, $request);
        if ($written === false) {
            throw new RuntimeException('Unable to send websocket handshake.');
        }

        $response = '';
        while (!feof($this->stream)) {
            $line = fgets($this->stream);
            if ($line === false) {
                $meta = stream_get_meta_data($this->stream);
                if (!empty($meta['timed_out'])) {
                    throw new RuntimeException('Timed out while reading websocket handshake.');
                }
                break;
            }

            $response .= $line;
            if (rtrim($line, "\r\n") === '') {
                break;
            }
        }

        $this->validateHandshakeResponse($response);
    }

    private function validateHandshakeResponse(string $response): void
    {
        if ($response === '') {
            throw new RuntimeException('Empty websocket handshake response.');
        }

        $lines = preg_split("/\r\n/", trim($response));
        if (!is_array($lines) || empty($lines[0])) {
            throw new RuntimeException('Invalid websocket handshake response.');
        }

        if (strpos($lines[0], '101') === false) {
            throw new RuntimeException('Websocket handshake failed: ' . $lines[0]);
        }

        $headers = [];
        foreach (array_slice($lines, 1) as $line) {
            if (strpos($line, ':') === false) {
                continue;
            }
            list($name, $value) = explode(':', $line, 2);
            $headers[strtolower(trim($name))] = trim($value);
        }

        $accept = $headers['sec-websocket-accept'] ?? '';
        $expected = base64_encode(
            sha1($this->secWebSocketKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true)
        );

        if ($accept !== $expected) {
            throw new RuntimeException('Invalid Sec-WebSocket-Accept header.');
        }
    }

    private function applyMask(string $payload, string $maskKey): string
    {
        $out = '';
        $length = strlen($payload);
        for ($i = 0; $i < $length; $i++) {
            $out .= $payload[$i] ^ $maskKey[$i % 4];
        }
        return $out;
    }

    private function readBytes(int $length, bool $allowTimeout = false)
    {
        $buffer = '';
        $startedAt = microtime(true);

        while (strlen($buffer) < $length) {
            $chunk = fread($this->stream, $length - strlen($buffer));
            if ($chunk === false || $chunk === '') {
                $meta = stream_get_meta_data($this->stream);
                if (!empty($meta['timed_out']) && $allowTimeout && $buffer === '') {
                    return null;
                }
                if (!empty($meta['eof'])) {
                    throw new RuntimeException('WebSocket connection closed by peer.');
                }
                if (!empty($meta['timed_out'])) {
                    if ($allowTimeout && $buffer !== '') {
                        if ((microtime(true) - $startedAt) >= 2.0) {
                            throw new RuntimeException('Timed out while completing websocket frame.');
                        }
                        continue;
                    }
                    throw new RuntimeException('Timed out while reading websocket frame.');
                }
                continue;
            }

            $buffer .= $chunk;
        }

        return $buffer;
    }
}
