<?php
// app/Models/Position.php
namespace App\Models;

use App\Core\App;

class Position
{

    private App $app;
    public function __construct(App $app)
    {
        $this->app = $app;
    }

    public function create(array $d): int
    {
        $sql = "INSERT INTO trade_positions
                (plan_id,user_id,symbol,side,entry_price,stop_loss,target_price,qty,
                 capital_used,rr_at_entry,est_profit_at_entry,executed_at,status,created_at,updated_at)
                VALUES (?,?,?,?,?,?,?,?, ?,?,?,?,'ongoing',?,?)";
        $this->app->db->query($sql, [
            $d['plan_id'],
            $d['user_id'],
            $d['symbol'],
            $d['side'],
            $d['entry_price'],
            $d['stop_loss'],
            $d['target_price'],
            $d['qty'],
            $d['capital_used'],
            $d['rr_at_entry'],
            $d['est_profit'],
            $d['executed_at'],
            date('Y-m-d H:i:s'),
            date('Y-m-d H:i:s')
        ]);
        return (int)$this->app->db->lastInsertId();
    }

    public function find(int $id, int $userId): ?array
    {
        $p = $this->app->db->query("SELECT * FROM trade_positions WHERE id=? AND user_id=?", [$id, $userId])->fetch();
        return $p ?: null;
    }

    public function allOngoing(int $userId): array
    {
        return $this->app->db->query("SELECT * FROM trade_positions WHERE user_id=? AND status='ongoing' ORDER BY executed_at DESC", [$userId])->fetchAll();
    }
}
