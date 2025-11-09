<?php

declare(strict_types=1);

use App\Core\App;
use App\Core\Router;
use App\Middleware\CsrfMiddleware;
use App\Middleware\AuthMiddleware;
use App\Middleware\ThrottleMiddleware;

require __DIR__ . '/../vendor/autoload.php';

// Bootstrap
$app = new App();

// Global middleware
$app->middleware([
    CsrfMiddleware::class,
]);

$router = new Router($app);

// Public routes
$router->get('/', [\App\Controllers\DashboardController::class, 'welcome']);
$router->get('/auth/login', [\App\Controllers\AuthController::class, 'showLogin']);
$router->post('/auth/login', [\App\Controllers\AuthController::class, 'login'], [ThrottleMiddleware::class]);
$router->get('/auth/register', [\App\Controllers\AuthController::class, 'showRegister']);
$router->post('/auth/register', [\App\Controllers\AuthController::class, 'register'], [ThrottleMiddleware::class]);
$router->get('/auth/verify', [\App\Controllers\AuthController::class, 'verifyEmail']);
$router->get('/auth/forgot', [\App\Controllers\AuthController::class, 'showForgot']);
$router->post('/auth/forgot', [\App\Controllers\AuthController::class, 'sendReset']);
$router->get('/auth/reset', [\App\Controllers\AuthController::class, 'showReset']);
$router->post('/auth/reset', [\App\Controllers\AuthController::class, 'resetPassword']);
$router->get('/logout', [\App\Controllers\AuthController::class, 'logout']);

// Protected routes (require auth)
$router->group('/app', [AuthMiddleware::class], function ($r) {
    $r->get('/dashboard', [\App\Controllers\DashboardController::class, 'dashboard']);
    // Plans
    $r->get('/plans', [\App\Controllers\PlanController::class, 'index']);
    $r->get('/plans/create', [\App\Controllers\PlanController::class, 'create']);
    $r->post('/plans', [\App\Controllers\PlanController::class, 'store']);
    $r->get('/plans/{id}', [\App\Controllers\PlanController::class, 'show']);
    $r->post('/plans/{id}/adjust', [\App\Controllers\PlanController::class, 'adjust']);
    $r->post('/plans/{id}/execute', [\App\Controllers\PlanController::class, 'execute']);
    $r->post('/plans/{id}/cancel', [\App\Controllers\PlanController::class, 'cancel']);
    $r->get('/positions', [\App\Controllers\PositionsController::class, 'index']);
    $r->get('/positions/{id}', [\App\Controllers\PositionsController::class, 'show']);
    $r->post('/positions/{id}/adjust', [\App\Controllers\PositionsController::class, 'adjust']);        // SL/TP only
    $r->post('/positions/{id}/partial-close', [\App\Controllers\PositionsController::class, 'partial']);
    $r->post('/positions/{id}/close', [\App\Controllers\PositionsController::class, 'close']);          // full close

});

$router->dispatch();
