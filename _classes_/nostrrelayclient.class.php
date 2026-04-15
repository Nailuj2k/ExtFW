<?php

/**
 * Multi-relay Nostr client on top of WebSocketClient.
 *
 * Current scope:
 * - connect to many relays in parallel
 * - send the same REQ subscription to all
 * - receive relay JSON arrays and hand them to the monitor
 *
 * PHP 8.4 migration:
 * - typed properties
 * - readonly where appropriate
 */
class NostrRelayClient implements RelayClientInterface
{
    /** @var array<int,array<string,mixed>> */
    private $connections = [];
    /** @var string|null */
    private $subscriptionId = null;
    /** @var bool */
    private $verbose = false;

    public function __construct(bool $verbose = false)
    {
        $this->verbose = $verbose;
    }

    public function connect(array $relayUrls): void
    {
        $this->disconnect();

        foreach (array_values(array_unique($relayUrls)) as $relayUrl) {
            try {
                $client = new WebSocketClient($relayUrl, [
                    'connect_timeout' => 10,
                    'read_timeout' => 1,
                    'read_timeout_usec' => 0,
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                ]);
                $client->connect();

                $stream = $client->getStream();
                if (!is_resource($stream)) {
                    continue;
                }

                $this->connections[] = [
                    'url' => $relayUrl,
                    'client' => $client,
                    'stream' => $stream,
                ];

                if ($this->verbose) {
                    echo "[relay] connected -> {$relayUrl}\n";
                }
            } catch (Exception $e) {
                if ($this->verbose) {
                    echo "[relay] connect failed -> {$relayUrl} | {$e->getMessage()}\n";
                }
            }
        }

        if (!$this->connections) {
            throw new RuntimeException('Unable to connect to any relay.');
        }
    }

    public function subscribe(array $filters): void
    {
        if (!$this->connections) {
            throw new RuntimeException('Relay client is not connected.');
        }

        if ($filters === []) {
            if ($this->verbose) {
                echo "[relay] no filters to subscribe\n";
            }
            return;
        }

        $this->subscriptionId = 'noxtr_' . substr(md5(uniqid('', true)), 0, 12);

        $request = array_merge(
            ['REQ', $this->subscriptionId],
            array_values($filters)
        );

        $payload = json_encode($request, JSON_UNESCAPED_SLASHES);
        if ($payload === false) {
            throw new RuntimeException('Unable to encode Nostr REQ payload.');
        }

        foreach ($this->connections as $entry) {
            /** @var WebSocketClient $client */
            $client = $entry['client'];
            $client->sendText($payload);

            if ($this->verbose) {
                echo "[relay] subscribed -> {$this->subscriptionId} @ {$entry['url']}\n";
                echo "[relay] filters -> " . $payload . "\n";
            }
        }
    }

    public function run(callable $onMessage, ?callable $shouldStop = null): void
    {
        if (!$this->connections) {
            throw new RuntimeException('Relay client is not connected.');
        }

        while (true) {
            if ($shouldStop !== null && $shouldStop()) {
                return;
            }

            $read = [];
            foreach ($this->connections as $entry) {
                if (is_resource($entry['stream'])) {
                    $read[] = $entry['stream'];
                }
            }

            if (!$read) {
                return;
            }

            $write = null;
            $except = null;
            $selected = @stream_select($read, $write, $except, 1, 0);

            if ($selected === false) {
                throw new RuntimeException('stream_select failed while reading relays.');
            }

            if ($selected === 0) {
                continue;
            }

            foreach ($read as $readyStream) {
                $entry = $this->findConnectionByStream($readyStream);
                if ($entry === null) {
                    continue;
                }

                /** @var WebSocketClient $client */
                $client = $entry['client'];
                $payload = $client->receiveText();
                if ($payload === null || trim($payload) === '') {
                    continue;
                }

                $messages = $this->decodePayloadMessages($payload);
                if ($messages === []) {
                    if ($this->verbose) {
                        $preview = substr(str_replace(["\r", "\n"], ['\\r', '\\n'], $payload), 0, 240);
                        echo "[relay] invalid json @ {$entry['url']} <- {$preview}\n";
                    }
                    continue;
                }

                foreach ($messages as $decoded) {
                    if ($this->verbose && isset($decoded[0])) {
                        if ($decoded[0] === 'EVENT') {
                            $eventId = '';
                            if (isset($decoded[2]) && is_array($decoded[2]) && !empty($decoded[2]['id'])) {
                                $eventId = substr((string)$decoded[2]['id'], 0, 12);
                            }
                            echo '[relay] EVENT' . ($eventId !== '' ? ' ' . $eventId : '') . ' @ ' . $entry['url'] . "\n";
                        } else {
                            echo '[relay] ' . (string)$decoded[0] . ' @ ' . $entry['url'] . "\n";
                        }
                    }

                    $onMessage($decoded);
                }
            }
        }
    }

    public function publishEvent(array $event): int
    {
        if (!$this->connections) {
            return 0;
        }

        $payload = json_encode(['EVENT', $event], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (!is_string($payload) || $payload === '') {
            return 0;
        }

        $sent = 0;
        foreach ($this->connections as $entry) {
            try {
                /** @var WebSocketClient $client */
                $client = $entry['client'];
                $client->sendText($payload);
                $sent++;
            } catch (Exception $e) {
                if ($this->verbose) {
                    echo '[relay] publish failed -> ' . $entry['url'] . ' | ' . $e->getMessage() . "\n";
                }
            }
        }

        if ($this->verbose && $sent > 0) {
            echo '[relay] event published -> ' . $sent . '/' . count($this->connections) . "\n";
        }

        return $sent;
    }

    public function disconnect(): void
    {
        foreach ($this->connections as $entry) {
            /** @var WebSocketClient $client */
            $client = $entry['client'];
            $client->close();
        }

        $this->connections = [];
        $this->subscriptionId = null;
    }

    /**
     * @return string[]
     */
    public function getConnectedRelayUrls(): array
    {
        $urls = [];
        foreach ($this->connections as $entry) {
            if (!empty($entry['url'])) {
                $urls[] = (string)$entry['url'];
            }
        }
        return array_values(array_unique($urls));
    }

    /**
     * @param resource $stream
     * @return array<string,mixed>|null
     */
    private function findConnectionByStream($stream)
    {
        foreach ($this->connections as $entry) {
            if ($entry['stream'] === $stream) {
                return $entry;
            }
        }

        return null;
    }

    /**
     * Some relays send one JSON message per websocket frame, others may batch
     * several JSON arrays separated by newlines in the same text payload.
     *
     * @return array<int,array<int|string,mixed>>
     */
    private function decodePayloadMessages(string $payload): array
    {
        $messages = [];

        $decoded = json_decode($payload, true);
        if (is_array($decoded)) {
            $messages[] = $decoded;
            return $messages;
        }

        $chunks = preg_split("/\r\n|\n|\r/", $payload);
        if (!is_array($chunks)) {
            return [];
        }

        foreach ($chunks as $chunk) {
            $chunk = trim($chunk);
            if ($chunk === '') {
                continue;
            }
            $decoded = json_decode($chunk, true);
            if (is_array($decoded)) {
                $messages[] = $decoded;
            }
        }

        return $messages;
    }
}
