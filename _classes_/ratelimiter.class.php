<?php


/**
 * Class RateLimiter
 * Token Bucket Algorithm
 * Code from https://tech.jotform.com/implementing-rate-limiter-with-php-307334598974
 */

class RateLimiter
{
    private /*string*/ $prefix;    // Prefix for the bucket keys
    private /*int*/ $maxCapacity;  // Maximum number of tokens in the bucket
    private /*int*/ $refillPeriod; // Time in seconds to refill the bucket
    private /*StorageInterface*/ $storage;
    private /*array*/ $headers = [
        'X-RateLimit-Limit' => '{{maxCapacity}}',
        'X-RateLimit-Remaining' => '{{currentAmmount}}',
        'X-RateLimit-Reset' => '{{reset}}',
    ];

    public function __construct(array $options, StorageInterface $storage)
    {
        $this->prefix = $options['prefix'];
        $this->maxCapacity = $options['maxCapacity'];
        $this->refillPeriod = $options['refillPeriod'];
        $this->headers = $options['headers'] ?? $this->headers;
        $this->storage = $storage;
    }

    public function check(string $identifier): bool
    {
        $key = $this->prefix . $identifier;
        // if the bucket does not exist, create it
        if (!$this->hasBucket($key)) {
            $this->createBucket($key);
        }

        $currentTime = time();
        $lastCheck = $this->storage->get($key . 'last_check');
        $tokensToAdd = ($currentTime - $lastCheck) * ($this->maxCapacity / $this->refillPeriod);
        $currentAmmount = $this->storage->get($key);
        // optimization of adding a token every rate ÷ per seconds
        $bucket = $currentAmmount + $tokensToAdd;
        // if is greater than max ammount, set it to max ammount
        $bucket = $bucket > $this->maxCapacity ? $this->maxCapacity : $bucket;
        // set last check time
        $this->storage->set($key . 'last_check', $currentTime, $this->refillPeriod);

        if ($bucket < 1) {
            return false;
        }

        $this->storage->set($key, $bucket - 1, $this->refillPeriod);
        return true;
    }

    private function createBucket(string $key)
    {
        $this->storage->set($key . 'last_check', time(), $this->refillPeriod);
        $this->storage->set($key, $this->maxCapacity - 1, $this->refillPeriod);
    }

    private function hasBucket(string $key): bool
    {
        return $this->storage->get($key) !== null;
    }

    public function get(string $identifier): int
    {
        $key = $this->prefix . $identifier;
        return $this->storage->get($key);
    }

    public function delete(string $identifier): void
    {
        $key = $this->prefix . $identifier;
        $this->storage->delete($key);
    }

    public function headers(string $identifier): array
    {
        $key = $this->prefix . $identifier;
        $lastCheck = $this->storage->get($key . 'last_check');
        $headers = [];
        foreach ($this->headers as $key => $value) {
            $headers[$key] = str_replace('{{maxCapacity}}', $this->maxCapacity, $value);
            $headers[$key] = str_replace('{{currentAmmount}}', $this->get($identifier), $headers[$key]);
            $headers[$key] = str_replace('{{reset}}', $lastCheck + $this->refillPeriod, $headers[$key]);
        }

        return $headers;
    }
}


/*

//Copilot Samples 

//REDIS
$redis = new Redis();
$redis->connect('127.0.0.1', 6379);

$ip = $_SERVER['REMOTE_ADDR'];
$limit = 100; // Number of allowed requests
$timeFrame = 3600; // Time frame in seconds (1 hour)

$currentRequests = $redis->get($ip);

if ($currentRequests === false) {
    $redis->set($ip, 1, $timeFrame);
} elseif ($currentRequests < $limit) {
    $redis->incr($ip);
} else {
    header('HTTP/1.1 429 Too Many Requests');
    echo 'Rate limit exceeded. Try again later.';
    exit();
}



//MYSQL
$pdo = new PDO('mysql:host=localhost;dbname=rate_limit', 'username', 'password');

$ip = $_SERVER['REMOTE_ADDR'];
$limit = 100; // Number of allowed requests
$timeFrame = 3600; // Time frame in seconds (1 hour)

$stmt = $pdo->prepare("SELECT COUNT(*) FROM requests WHERE ip = :ip AND timestamp > NOW() - INTERVAL :timeFrame SECOND");
$stmt->execute(['ip' => $ip, 'timeFrame' => $timeFrame]);
$requestCount = $stmt->fetchColumn();

if ($requestCount < $limit) {
    $stmt = $pdo->prepare("INSERT INTO requests (ip, timestamp) VALUES (:ip, NOW())");
    $stmt->execute(['ip' => $ip]);
} else {
    header('HTTP/1.1 429 Too Many Requests');
    echo 'Rate limit exceeded. Try again later.';
    exit();
}


//SESSIONS
session_start();

$limit = 100; // Number of allowed requests
$timeFrame = 3600; // Time frame in seconds (1 hour)

if (!isset($_SESSION['requests'])) {
    $_SESSION['requests'] = [];
}

$currentTime = time();
$_SESSION['requests'] = array_filter($_SESSION['requests'], function($timestamp) use ($currentTime, $timeFrame) {
    return ($timestamp > $currentTime - $timeFrame);
});

if (count($_SESSION['requests']) < $limit) {
    $_SESSION['requests'][] = $currentTime;
} else {
    header('HTTP/1.1 429 Too Many Requests');
    echo 'Rate limit exceeded. Try again later.';
    exit();
}


**/