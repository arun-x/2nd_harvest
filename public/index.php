<?php
/**
 * public/index.php
 * The ONE entry point. Every request (via .htaccess rewrite, or PHP's
 * built-in server with -t public) lands here.
 */

require __DIR__ . '/../app/Core/Router.php';
require __DIR__ . '/../app/Core/Session.php';
require __DIR__ . '/../app/Core/Auth.php';
require __DIR__ . '/../app/Controllers/BaseController.php';
require __DIR__ . '/../app/Controllers/EmployeeController.php';

Session::start();

$router = new Router();

// Supermarket Staff routes
$router->get('/employee/dashboard', [EmployeeController::class, 'dashboard']);
$router->get('/employee/listings/create', [EmployeeController::class, 'createListing']);
$router->get('/employee/pickups/verify', [EmployeeController::class, 'pickupVerification']);

// Fallback root -> dashboard, just so "/" shows something during dev
$router->get('/', [EmployeeController::class, 'dashboard']);

$router->dispatch($_SERVER['REQUEST_METHOD'], parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
