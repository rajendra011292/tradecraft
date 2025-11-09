<?php
namespace App\Core;

use App\Support\Env;
use App\Support\Session;
use App\Support\View;
use App\Support\DB;
use App\Support\Flash;

class App
{
    public Env $env;
    public Session $session;
    public View $view;
    public DB $db;
    public Flash $flash;

    /** @var array<class-string> */
    public array $globalMiddleware = [];

    public function __construct()
    {
        $this->env = new Env(__DIR__ . '/../../.env');
        $this->session = new Session($this->env->get('SESSION_NAME','tradecraft_session'));
        $this->view = new View();
        $this->db = new DB([
            'driver' => $this->env->get('DB_DRIVER','mysql'),
            'host' => $this->env->get('DB_HOST','127.0.0.1'),
            'port' => (int)$this->env->get('DB_PORT', 3306),
            'name' => $this->env->get('DB_NAME','tradecraft'),
            'user' => $this->env->get('DB_USER','root'),
            'pass' => $this->env->get('DB_PASS',''),
        ]);
        $this->flash = new Flash($this->session);
    }

    /** Register global middleware to run on every request */
    public function middleware(array $classes): void
    {
        $this->globalMiddleware = array_merge($this->globalMiddleware, $classes);
    }
}
