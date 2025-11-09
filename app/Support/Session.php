<?php
namespace App\Support;

class Session
{
    public function __construct(string $name)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_name($name);
            session_start([
                'cookie_httponly' => true,
                'cookie_samesite' => 'Lax',
            ]);
        }
    }
    public function get(string $key, $default=null){ return $_SESSION[$key] ?? $default; }
    public function set(string $key, $value): void { $_SESSION[$key] = $value; }
    public function flash(string $key, $value): void { $_SESSION['_flash'][$key] = $value; }
    public function getFlash(string $key){ $v = $_SESSION['_flash'][$key] ?? null; unset($_SESSION['_flash'][$key]); return $v;}
    public function destroy(): void { session_destroy(); }
}
