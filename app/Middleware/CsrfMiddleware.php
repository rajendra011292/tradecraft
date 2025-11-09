<?php
namespace App\Middleware;

use App\Core\App;
use App\Support\CSRF;

class CsrfMiddleware
{
    private App $app;
    public function __construct(App $app){ $this->app = $app; }
    public function handle(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $csrf = new CSRF($this->app->session, $this->app->env->get('CSRF_KEY','key'));
            $token = $_POST['_token'] ?? null;
            if (!$csrf->check($token)) {
                http_response_code(419);
                exit('CSRF token mismatch.');
            }
        }
    }
}
