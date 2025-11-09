<?php
namespace App\Middleware;

use App\Core\App;

class ThrottleMiddleware
{
    private App $app;
    private int $maxAttempts = 5;
    private int $decaySeconds = 60;

    public function __construct(App $app){ $this->app = $app; }

    public function handle(): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'cli';
        $key = 'throttle_' . md5($ip . ($_SERVER['REQUEST_URI'] ?? ''));
        $bucket = $this->app->session->get($key, ['count'=>0, 'reset'=>time()+$this->decaySeconds]);
        if (time() > $bucket['reset']) {
            $bucket = ['count'=>0,'reset'=>time()+$this->decaySeconds];
        }
        $bucket['count']++;
        $this->app->session->set($key, $bucket);
        if ($bucket['count'] > $this->maxAttempts) {
            http_response_code(429);
            exit('Too Many Attempts. Try again later.');
        }
    }
}
