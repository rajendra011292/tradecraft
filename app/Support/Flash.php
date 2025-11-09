<?php
namespace App\Support;

class Flash {
    private Session $session;
    public function __construct(Session $s){ $this->session = $s; }
    public function success(string $m){ $this->session->flash('success', $m); }
    public function error(string $m){ $this->session->flash('error', $m); }
    public function get(string $k){ return $this->session->getFlash($k); }
}
