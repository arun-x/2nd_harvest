<?php
/**
 * app/Core/Session.php
 * Minimal wrapper. Flash messages are stored under $_SESSION['_flash'] and
 * cleared the moment getFlash() reads them (true one-time flash behavior).
 */
 
class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
 
    public static function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }
 
    public static function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }
 
    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }
 
    public static function flash(string $type, string $message): void
    {
        $_SESSION['_flash'][$type] = $message;
    }
 
    /** @return array<string,string> e.g. ['success' => 'Listing created.'] */
    public static function getFlash(): array
    {
        $flash = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $flash;
    }
}