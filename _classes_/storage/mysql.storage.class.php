<?php



/******
 * 
        CREATE TABLE cache (
            key_name VARCHAR(255) PRIMARY KEY,
            value TEXT NOT NULL,
            expires_at DATETIME DEFAULT NULL
        );

 * 
 * 
 * 
 
// Primero configuramos la conexión PDO
$dsn = "mysql:host=localhost;dbname=test;charset=utf8mb4";
$username = "root";
$password = "password";

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Instanciamos nuestro storage
    $storage = new MySQLStorage($pdo);
    //or in extFW
    $storage = new MySQLStorage(MySql_PDO::singleton());
    
    // Ejemplo 1: Guardar un array
    $userPreferences = [
        'theme' => 'dark',
        'language' => 'es',
        'notifications' => true
    ];
    $storage->set('user_123_preferences', $userPreferences, 3600); // TTL de 1 hora
    
    // Ejemplo 2: Guardar un objeto
    $user = new stdClass();
    $user->name = "Juan";
    $user->email = "juan@example.com";
    $storage->set('user_123', $user, 7200); // TTL de 2 horas
    
    // Ejemplo 3: Recuperar datos
    $preferences = $storage->get('user_123_preferences');
    if ($preferences) {
        echo "Theme: " . $preferences['theme'] . "\n";
        echo "Language: " . $preferences['language'] . "\n";
    }
    
    // Ejemplo 4: Actualizar un valor existente
    $preferences['theme'] = 'light';
    $storage->set('user_123_preferences', $preferences, 3600);
    
    // Ejemplo 5: Eliminar datos
    $storage->delete('user_123');
    
    // Ejemplo 6: Intentar recuperar un valor eliminado
    $deletedUser = $storage->get('user_123');
    var_dump($deletedUser); // Mostrará NULL
    
} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error general: " . $e->getMessage() . "\n";
}

 * 
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 */
class MySQLStorage implements StorageInterface {

    private $pdo;
    private $tablename;

    public function __construct(PDO $pdo,String $tablename= 'CFG_CACHE') 
    {
        $this->pdo = $pdo;
        $this->tablename = $tablename;
    }
    
    public function get(string $key) 
    {
        try{
            
            $stmt = $this->pdo->prepare('SELECT value FROM '.$this->tablename.' WHERE key_name = ? AND (expires_at > NOW() OR expires_at IS NULL)');
            $stmt->execute([$key]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? unserialize($result['value']) : null;

        } catch (PDOException $e) {
            if($e->getCode() == '42S02'){           
                $this->pdo->exec( 'CREATE TABLE '.$this->tablename.' ( key_name VARCHAR(255) PRIMARY KEY, value TEXT NOT NULL, expires_at DATETIME DEFAULT NULL )' );
            }
            return null;
        }

    }
    
    public function set(string $key, $value, int $ttl) 
    {
        $serialized = serialize($value);
        $expires = date('Y-m-d H:i:s', time() + $ttl);
        
        $stmt = $this->pdo->prepare('INSERT INTO '.$this->tablename.' (key_name, value, expires_at) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE value = ?, expires_at = ?');
        return $stmt->execute([$key, $serialized, $expires, $serialized, $expires]);
    }
    
    public function delete(string $key) 
    {
        $stmt = $this->pdo->prepare('DELETE FROM '.$this->tablename.' WHERE key_name = ?');
        return $stmt->execute([$key]);
    }
}