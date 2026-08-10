<?php
namespace App\Core;

class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start([
                'cookie_httponly' => true,
                'cookie_samesite' => 'Lax',
            ]);
        }
    }

    public static function set(string $key, $value): void { $_SESSION[$key] = $value; }
    public static function get(string $key)               { return $_SESSION[$key] ?? null; }
    public static function remove(string $key): void      { unset($_SESSION[$key]); }

    public static function destroy(): void
    {
        $_SESSION = [];
        session_destroy();
    }
}
