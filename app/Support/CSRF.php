<?php
namespace App\Support;

class CSRF
{
    private Session $session;
    private string $key;
    public function __construct(Session $session, string $key)
    {
        $this->session = $session;
        $this->key = $key;
        if (!$this->session->get('_csrf')) {
            $this->regenerate();
        }
    }
    public function token(): string { return $this->session->get('_csrf'); }
    public function regenerate(): void {
        $this->session->set('_csrf', bin2hex(random_bytes(32)));
    }
    public function check(?string $token): bool {
        return is_string($token) && hash_equals($this->token(), $token);
    }
}
