<?php
namespace App\Models;

use App\Core\App;

class User
{
    private App $app;
    public function __construct(App $app){ $this->app = $app; }

    public function create(string $name, string $email, string $password): int
    {
        $hash = password_hash($password, PASSWORD_ARGON2ID);
        $this->app->db->query("INSERT INTO users (name,email,password,created_at,updated_at) VALUES (?,?,?,?,?)",
            [$name,$email,$hash, now(), now()]);
        return $this->app->db->lastInsertId();
    }

    public function findByEmail(string $email): ?array
    {
        $u = $this->app->db->query("SELECT * FROM users WHERE email=?",[$email])->fetch();
        return $u ?: null;
    }

    public function find(int $id): ?array
    {
        $u = $this->app->db->query("SELECT * FROM users WHERE id=?",[$id])->fetch();
        return $u ?: null;
    }

    public function markVerified(int $id): void
    {
        $this->app->db->query("UPDATE users SET email_verified_at=?, updated_at=? WHERE id=?", [now(), now(), $id]);
    }

    public function updatePassword(int $id, string $password): void
    {
        $hash = password_hash($password, PASSWORD_ARGON2ID);
        $this->app->db->query("UPDATE users SET password=?, updated_at=? WHERE id=?", [$hash, now(), $id]);
    }
}
