<?php
/**
 * public/index.php — combined front controller for the 2nd Harvest
 * merged project (Supermarket Staff + Consumer modules).
 *
 * Loading order:
 *   1) Namespaced autoloader (App\ ...) for Arun's Consumer module
 *   2) Manual requires for Malinka's non-namespaced classes
 *   3) Routes for BOTH modules against the same Router instance
 */

// 1) namespaced autoloader — resolves App\* to /app_ns/*
require __DIR__ . '/../vendor_autoload.php';

// BASE_URL — the URL path this app is served from, computed at runtime so
// the project works regardless of where it's cloned (XAMPP htdocs subdir,
// virtual host, etc.). Example: /GitTest/Arun/public on this machine.
if (!defined('BASE_URL')) {
    $__base = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    define('BASE_URL', rtrim($__base, '/'));
}

// 2) Malinka's non-namespaced core + auth stack
require __DIR__ . '/../app/Core/Router.php';
require __DIR__ . '/../app/Core/Session.php';
require __DIR__ . '/../app/Core/Database.php';
require __DIR__ . '/../app/Models/BaseModel.php';
require __DIR__ . '/../app/Models/User.php';
require __DIR__ . '/../app/Models/Outlet.php';
require __DIR__ . '/../app/Models/Charity.php';
require __DIR__ . '/../app/Models/Listing.php';
require __DIR__ . '/../app/Models/Reservation.php';
require __DIR__ . '/../app/Models/Pickup.php';
require __DIR__ . '/../app/Models/Notification.php';
require __DIR__ . '/../app/Models/AuditLog.php';
require __DIR__ . '/../app/Models/Dispute.php';
require __DIR__ . '/../app/Models/Report.php';
require __DIR__ . '/../app/Core/Auth.php';
require __DIR__ . '/../app/Controllers/BaseController.php';
require __DIR__ . '/../app/Controllers/HomeController.php';
require __DIR__ . '/../app/Controllers/AuthController.php';
require __DIR__ . '/../app/Controllers/EmployeeController.php';
require __DIR__ . '/../app/Controllers/AdminController.php';

date_default_timezone_set('Asia/Colombo');

Session::start();

$router = new Router();
$router->setBasePath(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])));

// -------------------------------------------------------------
// Public
// -------------------------------------------------------------
$router->get('/', [HomeController::class, 'index']);

// -------------------------------------------------------------
// Auth (Malinka's AuthController — on success it populates BOTH
// session shapes so the Consumer module also sees the user)
// -------------------------------------------------------------
$router->get ('/register',              [AuthController::class, 'registerRoleSelect']);
$router->get ('/register/supermarket',  [AuthController::class, 'registerSupermarket']);
$router->get ('/register/charity',      [AuthController::class, 'registerCharity']);
$router->get ('/register/consumer',     [AuthController::class, 'registerConsumer']);
$router->post('/register',              [AuthController::class, 'store']);
$router->get ('/login',                 [AuthController::class, 'login']);
$router->get ('/terms',                 [AuthController::class, 'terms']);
$router->get ('/privacy',               [AuthController::class, 'privacy']);
$router->post('/login',                 [AuthController::class, 'authenticate']);
$router->post('/logout',                [AuthController::class, 'logout']);

// -------------------------------------------------------------
// Admin portal — only reachable via /admin (not linked from /login)
// -------------------------------------------------------------
$router->get ('/admin',                 [AdminController::class, 'login']);
$router->post('/admin/login',           [AdminController::class, 'authenticate']);
$router->get ('/admin/dashboard',                     [AdminController::class, 'dashboard']);
$router->get ('/admin/registrations',                 [AdminController::class, 'registrations']);
$router->post('/admin/registrations/{id}/approve',    [AdminController::class, 'approveRegistration']);
$router->post('/admin/registrations/{id}/reject',     [AdminController::class, 'rejectRegistration']);
$router->get ('/admin/listings',                      [AdminController::class, 'listings']);
$router->post('/admin/listings/{id}/remove',          [AdminController::class, 'removeListing']);
$router->post('/admin/listings/{id}/restore',         [AdminController::class, 'restoreListing']);
$router->get ('/admin/disputes',                      [AdminController::class, 'disputes']);
$router->post('/admin/disputes/{id}/request-info',    [AdminController::class, 'requestDisputeInfo']);
$router->post('/admin/disputes/{id}/resolve',         [AdminController::class, 'resolveDispute']);
$router->get ('/admin/reports',                       [AdminController::class, 'reports']);
$router->get ('/admin/reports/export',                [AdminController::class, 'exportReport']);
$router->get ('/admin/users',                         [AdminController::class, 'users']);
$router->post('/admin/users/{id}/lock',               [AdminController::class, 'lockUser']);
$router->post('/admin/users/{id}/unlock',             [AdminController::class, 'unlockUser']);
$router->get ('/admin/audit-log',                     [AdminController::class, 'auditLog']);
$router->get ('/admin/audit-log/export',              [AdminController::class, 'exportAuditLog']);

// -------------------------------------------------------------
// Supermarket Staff (Malinka's Employee module)
// -------------------------------------------------------------
$router->get ('/employee/dashboard',              [EmployeeController::class, 'dashboard']);
$router->get ('/employee/listings/create',        [EmployeeController::class, 'createListing']);
$router->post('/employee/listings',               [EmployeeController::class, 'storeListing']);
$router->get ('/employee/listings/published',     [EmployeeController::class, 'listingPublished']);
$router->get ('/employee/listings/{id}/edit',     [EmployeeController::class, 'editListing']);
$router->post('/employee/listings/{id}',          [EmployeeController::class, 'updateListing']);
$router->post('/employee/listings/{id}/delete',   [EmployeeController::class, 'deleteListing']);
$router->get ('/employee/pickups/verify',         [EmployeeController::class, 'pickupVerification']);
$router->post('/employee/pickups/verify/token',   [EmployeeController::class, 'verifyPickupToken']);
$router->post('/employee/pickups/verify/reset',   [EmployeeController::class, 'resetPickupVerification']);
$router->post('/employee/pickups/complete',       [EmployeeController::class, 'completePickup']);
$router->get ('/employee/profile',                [EmployeeController::class, 'profile']);
$router->post('/employee/profile',                [EmployeeController::class, 'updateProfile']);
$router->post('/employee/profile/password',       [EmployeeController::class, 'changePassword']);

// -------------------------------------------------------------
// Consumer (Arun's namespaced module)
// -------------------------------------------------------------
$router->get ('/consumer/dashboard',              [\App\Controllers\ConsumerController::class, 'dashboard']);
$router->get ('/consumer/listings',               [\App\Controllers\ConsumerController::class, 'browse']);
$router->get ('/consumer/listings/{id}/checkout', [\App\Controllers\ConsumerController::class, 'checkout']);
$router->post('/consumer/listings/{id}/checkout', [\App\Controllers\ConsumerController::class, 'confirmCheckout']);
$router->get ('/consumer/orders',                 [\App\Controllers\ConsumerController::class, 'orders']);
$router->post('/consumer/orders/{id}/confirm',    [\App\Controllers\ConsumerController::class, 'confirmPickup']);
$router->post('/consumer/orders/{id}/cancel',     [\App\Controllers\ConsumerController::class, 'cancelReservation']);
$router->get ('/consumer/profile',                [\App\Controllers\ConsumerController::class, 'profile']);
$router->post('/consumer/profile',                [\App\Controllers\ConsumerController::class, 'updateProfile']);
$router->post('/consumer/profile/password',       [\App\Controllers\ConsumerController::class, 'changePassword']);
$router->get ('/consumer/notifications',          [\App\Controllers\ConsumerController::class, 'notifications']);

$router->dispatch($_SERVER['REQUEST_METHOD'], parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
