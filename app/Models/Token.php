<?php
namespace App\Models;

use App\Core\App;

class Token
{
    private App $app;
    public function __construct(App $app){ $this->app = $app; }

    public function create(int $userId, string $type): string
    {
        $token = bin2hex(random_bytes(32));
        $this->app->db->query("INSERT INTO tokens (user_id, token, type, created_at) VALUES (?,?,?,?)",
            [$userId, $token, $type, now()]);
        return $token;
    }

    public function consume(string $token, string $type): ?int
    {
        $row = $this->app->db->query("SELECT * FROM tokens WHERE token=? AND type=? LIMIT 1", [$token, $type])->fetch();
        if (!$row) return null;
        $this->app->db->query("DELETE FROM tokens WHERE id=?", [$row['id']]);
        return (int)$row['user_id'];
    }
}
