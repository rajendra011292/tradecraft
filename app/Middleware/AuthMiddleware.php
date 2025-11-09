<?php
namespace App\Middleware;

use App\Core\App;

class AuthMiddleware
{
    private App $app;
    public function __construct(App $app){ $this->app = $app; }
    public function handle(): void
    {
        if (!$this->app->session->get('user_id')) {
            header('Location: /auth/login'); exit;
        }
    }
}
