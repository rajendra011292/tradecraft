<?php
namespace App\Models;

use App\Core\App;

class PlanEvent
{
    private App $app;
    public function __construct(App $app){ $this->app = $app; }

    public function log(int $planId, int $userId, string $action, string $reason, array $snapshot=[]): void
    {
        $this->app->db->query("INSERT INTO plan_events (plan_id, user_id, action, reason, snapshot, created_at) VALUES (?,?,?,?,?,?)",
            [$planId, $userId, $action, $reason, json_encode($snapshot, JSON_UNESCAPED_UNICODE), now()]);
    }

    public function forPlan(int $planId, int $userId): array
    {
        return $this->app->db->query("SELECT * FROM plan_events WHERE plan_id=? AND user_id=? ORDER BY created_at DESC", [$planId, $userId])->fetchAll();
    }
}
