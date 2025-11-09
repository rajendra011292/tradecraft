<?php
namespace App\Models;

use App\Core\App;

class Position
{
    private App $app;
    public function __construct(App $app){ $this->app = $app; }

    public function create(array $d): int
{
    $sql = "INSERT INTO trade_positions
        (plan_id, user_id, symbol, side, entry_price, stop_loss, target_price, qty,
         capital_used, rr_at_entry, est_profit_at_entry, executed_at, status, created_at, updated_at)
        VALUES (?,?,?,?,?,?,?,?, ?,?,?,?, 'ongoing', ?,?)";

    $this->app->db->query($sql, [
        $d['plan_id'], $d['user_id'], $d['symbol'], $d['side'],
        $d['entry_price'], $d['stop_loss'], $d['target_price'], $d['qty'],
        $d['capital_used'], $d['rr_at_entry'], $d['est_profit'], $d['executed_at'],
        now(), now()
    ]);
    return $this->app->db->lastInsertId();
}

    public function allOngoing(int $userId): array
    {
        return $this->app->db->query("SELECT * FROM trade_positions WHERE user_id=? AND status='ongoing' ORDER BY executed_at DESC", [$userId])->fetchAll();
    }

    public function find(int $id, int $userId): ?array
    {
        $p = $this->app->db->query("SELECT * FROM trade_positions WHERE id=? AND user_id=?", [$id,$userId])->fetch();
        return $p ?: null;
    }

    public function updateStops(int $id, int $userId, float $sl, float $tp): void
    {
        $this->app->db->query("UPDATE trade_positions SET stop_loss=?, target_price=?, updated_at=? WHERE id=? AND user_id=?", [$sl,$tp,now(),$id,$userId]);
    }

    public function partialClose(int $id, int $userId, float $qty, float $price, float $fees): void
    {
        // reduce qty, keep simple (you can extend to a fills table later)
        $pos = $this->find($id, $userId);
        if (!$pos) return;
        $newQty = max(0, (float)$pos['qty'] - $qty);
        $this->app->db->query("UPDATE trade_positions SET qty=?, updated_at=? WHERE id=? AND user_id=?", [$newQty, now(), $id, $userId]);
        if ($newQty <= 0) {
            $this->app->db->query("UPDATE trade_positions SET status='completed', updated_at=? WHERE id=? AND user_id=?", [now(), $id, $userId]);
        }
    }

    public function fullClose(int $id, int $userId, float $price, float $fees): void
    {
        $this->app->db->query("UPDATE trade_positions SET qty=0, status='completed', updated_at=? WHERE id=? AND user_id=?", [now(), $id, $userId]);
    }
}
