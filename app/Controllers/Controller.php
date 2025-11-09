<?php
namespace App\Controllers;

use App\Core\App;

abstract class Controller
{
    protected App $app;
    public function __construct(App $app){ $this->app = $app; }
    protected function view(string $tpl, array $data=[]): void {
        extract($data);
        $flashSuccess = $this->app->flash->get('success');
        $flashError = $this->app->flash->get('error');
        include __DIR__ . '/../Views/layouts/app.php';
    }
    protected function redirect(string $to): void { header('Location: ' . $to); exit; }
}
