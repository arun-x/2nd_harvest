<?php
/**
 * Autoloader for the namespaced App\ classes used by Arun's Consumer module.
 * Maps App\Foo\Bar  →  app_ns/Foo/Bar.php
 *
 * Malinka's non-namespaced global classes (User, Auth, Session, Database,
 * Router, ...) live under app/ and are still loaded manually in index.php.
 */
spl_autoload_register(function (string $class) {
    $prefix = 'App\\';
    if (strncmp($prefix, $class, strlen($prefix)) !== 0) return;
    $relative = substr($class, strlen($prefix));
    $file = __DIR__ . '/app_ns/' . str_replace('\\', '/', $relative) . '.php';
    if (file_exists($file)) require $file;
});
