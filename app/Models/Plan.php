<?php

namespace App\Models;

use App\Core\App;

class Plan
{
    private App $app;
    public function __construct(App $app)
    {
        $this->app = $app;
    }

    public function allForUser(int $userId): array
    {
        return $this->app->db->query("SELECT * FROM plans WHERE user_id=? ORDER BY created_at DESC", [$userId])->fetchAll();
    }

    public function find(int $id, int $userId): ?array
    {
        $p = $this->app->db->query("SELECT * FROM plans WHERE id=? AND user_id=?", [$id, $userId])->fetch();
        return $p ?: null;
    }

    public function create(int $userId, array $d): int
{
    // 23 columns ⇒ 23 placeholders
    $sql = "INSERT INTO plans
        (user_id, symbol, sector, side, plan_date, timeframe, market_bias, setup_type,
         entry_price, stop_loss, target_price, capital_allocated,
         risk_percent, position_size, capital_used, rr_ratio, est_profit,
         notes, confidence, emotion, status, created_at, updated_at)
        VALUES
        (?,?,?,?,?,?,?,?, ?,?,?,?, ?,?,?,?, ?,?,?,?, ?,?,?)";

    $this->app->db->query($sql, [
        $userId,
        $d['symbol'],
        $d['sector'],
        $d['side'],
        $d['plan_date'],
        $d['timeframe'],
        $d['market_bias'],
        $d['setup_type'],
        $d['entry_price'],
        $d['stop_loss'],
        $d['target_price'],
        $d['capital_allocated'],
        $d['risk_percent'],
        $d['position_size'],
        $d['capital_used'],
        $d['rr_ratio'],
        $d['est_profit'],
        $d['notes'],
        $d['confidence'],
        $d['emotion'],
        'planned',          // bound as a value (keeps placeholder count correct)
        now(),
        now(),
    ]);

    return $this->app->db->lastInsertId();
}

public function updateFields(int $id, int $userId, array $d): void
{
    // 20 SET placeholders + 2 WHERE placeholders = 22 total
    $sql = "UPDATE plans SET
        symbol=?, sector=?, side=?, plan_date=?, timeframe=?, market_bias=?, setup_type=?,
        entry_price=?, stop_loss=?, target_price=?, capital_allocated=?,
        risk_percent=?, position_size=?, capital_used=?, rr_ratio=?, est_profit=?,
        notes=?, confidence=?, emotion=?, updated_at=?
        WHERE id=? AND user_id=?";

    $this->app->db->query($sql, [
        $d['symbol'],
        $d['sector'],
        $d['side'],
        $d['plan_date'],
        $d['timeframe'],
        $d['market_bias'],
        $d['setup_type'],
        $d['entry_price'],
        $d['stop_loss'],
        $d['target_price'],
        $d['capital_allocated'],
        $d['risk_percent'],
        $d['position_size'],
        $d['capital_used'],
        $d['rr_ratio'],
        $d['est_profit'],
        $d['notes'],
        $d['confidence'],
        $d['emotion'],
        now(),
        $id,
        $userId,
    ]);
}



    public function setStatus(int $id, int $userId, string $status): void
    {
        $this->app->db->query("UPDATE plans SET status=?, updated_at=? WHERE id=? AND user_id=?", [$status, now(), $id, $userId]);
    }
}
