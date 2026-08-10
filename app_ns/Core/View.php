<?php
namespace App\Core;

class View
{
    public static function render(string $viewPath, array $data = []): void
    {
        extract($data);
        $viewFile = __DIR__ . "/../Views/{$viewPath}.php";

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/main.php';
    }
}
