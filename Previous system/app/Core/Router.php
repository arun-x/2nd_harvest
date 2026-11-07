<?php
/**
 * app/Core/Router.php
 * Deliberately minimal — exact-path matching only, no {params} yet.
 * Enough to get pages rendering; extend with regex/param support later.
 */

class Router
{
    private array $routes = [];
    private string $basePath = '';

    public function setBasePath(string $basePath): void
    {
        $this->basePath = rtrim($basePath, '/');
    }

    public function get(string $path, array $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, array $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(string $method, string $path): void
    {
        if ($this->basePath !== '' && str_starts_with($path, $this->basePath)) {
            $path = substr($path, strlen($this->basePath));
        }
        $path = rtrim($path, '/') ?: '/';

        $handler = $this->routes[$method][$path] ?? null;
        $params = [];

        if (!$handler) {
            foreach ($this->routes[$method] ?? [] as $pattern => $h) {
                if (strpos($pattern, '{') === false) continue;
                $regex = '#^' . preg_replace('#\{[a-zA-Z_]+\}#', '([^/]+)', $pattern) . '$#';
                if (preg_match($regex, $path, $matches)) {
                    array_shift($matches);
                    $handler = $h;
                    $params = $matches;
                    break;
                }
            }
        }

        if (!$handler) {
            http_response_code(404);
            echo "404 Not Found: {$method} {$path}";
            return;
        }

        [$controllerClass, $action] = $handler;
        $controller = new $controllerClass();
        call_user_func_array([$controller, $action], $params);
    }
}
