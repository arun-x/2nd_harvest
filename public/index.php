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
require __DIR__ . '/../app/Controllers/HomeController.php';
require __DIR__ . '/../app/Controllers/AuthController.php';
require __DIR__ . '/../app/Controllers/EmployeeController.php';
 
Session::start();
 
$router = new Router();
 
// Public
$router->get('/', [HomeController::class, 'index']);
 
// Auth
$router->get('/register', [AuthController::class, 'registerRoleSelect']);
$router->get('/register/supermarket', [AuthController::class, 'registerSupermarket']);
$router->get('/register/charity', [AuthController::class, 'registerCharity']);
$router->get('/register/consumer', [AuthController::class, 'registerConsumer']);
$router->post('/register', [AuthController::class, 'store']);
$router->get('/login', [AuthController::class, 'login']);
$router->post('/login', [AuthController::class, 'authenticate']);
$router->post('/logout', [AuthController::class, 'logout']);
 
// Supermarket Staff routes
$router->get('/employee/dashboard', [EmployeeController::class, 'dashboard']);
$router->get('/employee/listings/create', [EmployeeController::class, 'createListing']);
$router->post('/employee/listings', [EmployeeController::class, 'storeListing']);
$router->get('/employee/listings/published', [EmployeeController::class, 'listingPublished']);
$router->get('/employee/pickups/verify', [EmployeeController::class, 'pickupVerification']);
$router->post('/employee/pickups/verify/token', [EmployeeController::class, 'verifyPickupToken']);
$router->post('/employee/pickups/verify/reset', [EmployeeController::class, 'resetPickupVerification']);
$router->post('/employee/pickups/complete', [EmployeeController::class, 'completePickup']);
 
$router->dispatch($_SERVER['REQUEST_METHOD'], parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
 