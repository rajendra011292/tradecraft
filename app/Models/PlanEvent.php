<?php
namespace App\Models;

use App\Core\App;

class PlanEvent
{
    private App $app;
    public function __construct(App $app){ $this->app = $app; }

    public function log(int $planId, int $userId, string $action, ?string $reason = null, array $snapshot = []): bool
    {
        try {
            $snap = $snapshot ? json_encode($snapshot, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) : null;
            
            $result = $this->app->db->query(
                "INSERT INTO plan_events (plan_id, user_id, action, reason, snapshot, created_at)
                 VALUES (?, ?, ?, ?, ?, ?)",
                [$planId, $userId, $action, $reason, $snap, date('Y-m-d H:i:s')]
            );
            
            if ($result === false) {
                error_log("Failed to log plan event");
                return false;
            }
            
            return true;
        } catch (\Exception $e) {
            error_log("Error in PlanEvent::log: " . $e->getMessage());
            return false;
        }
    }

    public function forPlan(int $planId, int $userId): array
    {
        return $this->app->db->query(
            "SELECT * FROM plan_events WHERE plan_id=? AND user_id=? ORDER BY created_at DESC",
            [$planId, $userId]
        )->fetchAll();
    }
}
