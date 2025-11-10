<?php
namespace App\Models;

use App\Core\App;

class PositionEvent
{
    private App $app;
    public function __construct(App $app){ $this->app = $app; }

    public function log(int $positionId, int $userId, string $action, ?string $reason = null, array $snapshot = []): bool
    {
        try {
            $snap = $snapshot ? json_encode($snapshot, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) : null;
            
            $result = $this->app->db->query(
                "INSERT INTO position_events (position_id, user_id, action, reason, snapshot, created_at)
                 VALUES (?, ?, ?, ?, ?, ?)",
                [$positionId, $userId, $action, $reason, $snap, date('Y-m-d H:i:s')]
            );
            
            if ($result === false) {
                error_log("Failed to log position event for position #$positionId, action: $action");
                return false;
            }
            
            return true;
        } catch (\Exception $e) {
            error_log("Error in PositionEvent::log: " . $e->getMessage());
            return false;
        }
    }

    public function forPosition(int $positionId, int $userId): array
    {
        return $this->app->db->query(
            "SELECT * FROM position_events WHERE position_id=? AND user_id=? ORDER BY created_at DESC",
            [$positionId, $userId]
        )->fetchAll();
    }
}
