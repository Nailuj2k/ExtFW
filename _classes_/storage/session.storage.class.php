<?php

class SessionStorage implements StorageInterface
{

    public function __construct()
    {
        // Solo iniciar sesión si no está ya iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function get(string $key)
    {
        return $_SESSION[$key] ?? null;
    }

    public function set(string $key, $value, int $ttl)
    {
        $_SESSION[$key] = $value;
    }

    public function delete(string $key)
    {
        unset($_SESSION[$key]);
    }
}