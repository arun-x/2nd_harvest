<?php
// Both modules read this file. Malinka's Database::connection() uses
// $cfg['db']['pass'] while Arun's App\Core\Database::connect() uses
// $cfg['db']['password'] — keep BOTH keys with the same value.

// Compute base_url dynamically so the app works from any install location
// (XAMPP htdocs subdir, virtual host, production, etc.). BASE_URL is
// defined in public/index.php; fall back to a computed value if this file
// is loaded outside the front controller (e.g. CLI scripts).
if (defined('BASE_URL')) {
    $__basePath = BASE_URL;
} else {
    $__basePath = isset($_SERVER['SCRIPT_NAME'])
        ? rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/')
        : '';
}
$__scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$__host   = $_SERVER['HTTP_HOST'] ?? 'localhost';

return [
    'db' => [
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'name'     => 'second_harvest',
        'user'     => 'root',
        'pass'     => '',
        'password' => '',
        'charset'  => 'utf8mb4',
    ],
    'app' => [
        'base_url'     => $__scheme . '://' . $__host . $__basePath,
        'session_name' => 'harvest_session',
    ],
];
