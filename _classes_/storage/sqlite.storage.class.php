<?php

class SQLiteStorage implements StorageInterface {

    private $pdo;
    
    public function __construct(string $databasePath) {
        $this->pdo = new PDO("sqlite:$databasePath");
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->createTableIfNotExists();
    }
    
    private function createTableIfNotExists(): void  {
        $this->pdo->exec('
            CREATE TABLE IF NOT EXISTS cache (
                key_name TEXT PRIMARY KEY,
                value BLOB NOT NULL,
                expires_at INTEGER DEFAULT NULL
            )
        ');
    }
    
    public function get(string $key) {

        $stmt = $this->pdo->prepare('SELECT value FROM cache WHERE key_name = ? AND (expires_at > ? OR expires_at IS NULL) ');       
        $stmt->execute([$key, time()]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? unserialize($result['value']) : null;
    }
    
    public function set(string $key, $value, int $ttl) {
        $serialized = serialize($value);
        $expires = time() + $ttl;
        
        // SQLite no tiene UPSERT como MySQL, así que usamos un REPLACE
        $stmt = $this->pdo->prepare('INSERT OR REPLACE INTO cache (key_name, value, expires_at) VALUES (?, ?, ?) ');
        
        return $stmt->execute([$key, $serialized, $expires]);
    }
    
    public function delete(string $key) {
        $stmt = $this->pdo->prepare('DELETE FROM cache WHERE key_name = ?');
        return $stmt->execute([$key]);
    }
    
}