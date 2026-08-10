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
            $this->redirect('/Deployment/2nd-harvest/public/login');
        }
        if (!in_array($user['role'], $allowedRoles, true)) {
            http_response_code(403);
            $this->render('errors/403');
            exit;
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
}
