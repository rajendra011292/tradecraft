<?php
use App\Support\CSRF;
function csrf_field(\App\Core\App $app): string {
    $csrf = new CSRF($app->session, $app->env->get('CSRF_KEY','key'));
    $token = $csrf->token();
    return '<input type="hidden" name="_token" value="'.htmlspecialchars($token, ENT_QUOTES).'">';
}
function e(string $v): string { return htmlspecialchars($v, ENT_QUOTES);}
function now(): string { return date('Y-m-d H:i:s'); }
