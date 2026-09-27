<?php
namespace App\Controllers;

use App\Core\View;
use App\Core\Session;

abstract class BaseController
{
    /* Render a view file through the shared layout. */
    protected function render(string $view, array $data = []): void
    {
        View::render($view, $data);
    }

    /* Redirect + terminate. */
    protected function redirect(string $path): void
    {
        header("Location: {$path}");
        exit;
    }

    /* Role gate: call at the top of any protected method. */
    protected function requireRole(string ...$allowedRoles): array
    {
        $user = Session::get('user');
        if (!$user) {
            $this->redirect(BASE_URL . '/login');
        }
        if (!in_array($user['role'], $allowedRoles, true)) {
            // Signed in with another role (e.g. supermarket staff opening a
            // consumer link): send them to their own dashboard rather than
            // showing this module's sidebar with a 403.
            $home = [
                'consumer' => '/consumer/dashboard',
                'charity'  => '/charity/dashboard',
                'employee' => '/employee/dashboard',
                'admin'    => '/admin/dashboard',
            ][$user['role']] ?? '/';
            $this->redirect(BASE_URL . $home);
        }
        return $user;
    }

    protected function flash(string $type, string $message): void
    {
        Session::set('flash', ['type' => $type, 'message' => $message]);
    }

    /* Generate a CSRF token, cached in the session. */
    protected function csrfToken(): string
    {
        $token = Session::get('csrf_token');
        if (!$token) {
            $token = bin2hex(random_bytes(32));
            Session::set('csrf_token', $token);
        }
        return $token;
    }

    /* Abort if the posted csrf_token doesn't match. */
    protected function verifyCsrf(): void
    {
        $expected = Session::get('csrf_token') ?? '';
        $given    = $_POST['csrf_token']       ?? '';
        if ($expected === '' || !hash_equals($expected, $given)) {
            http_response_code(419);
            exit('Invalid or expired form submission. Please refresh the page.');
        }
    }

    // Mirrors EmployeeController::imageFor so marketplace cards (consumer
    // and charity) show the same picture the employee sees for the same item.
    protected function imageFor(string $itemName, string $category): string
    {
        $base = BASE_URL . '/assets/images/';
        $name = strtolower($itemName);

        $keywordMap = [
            'banana'      => 'bananas.jpg',
            'plantain'    => 'bananas.jpg',
            'bell pepper' => 'bell-peppers.jpg',
            'capsicum'    => 'bell-peppers.jpg',
            'pepper'      => 'bell-peppers.jpg',
            'broccoli'    => 'broccoli.jpg',
            'carrot'      => 'carrots.jpg',
            'strawberr'   => 'strawberries.jpg',
            'berry'       => 'strawberries.jpg',
        ];
        foreach ($keywordMap as $needle => $file) {
            if (strpos($name, $needle) !== false) {
                return $base . $file;
            }
        }

        return $base . 'produce-crate.jpg';
    }
}
