<?php
namespace App\Services;

use App\Core\App;
use App\Models\PlanEvent;

class PlanExpiry
{
    /** Run expiry once per $throttleSeconds using session key */
    public static function run(App $app, int $throttleSeconds = 3600): void
    {
        // Throttle (once per hour by default)
        $key = 'plan_expiry_last_run';
        $last = (int)($app->session->get($key) ?? 0);
        if (time() - $last < $throttleSeconds) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        // Find all planned plans that crossed expires_at
        $expired = $app->db->query(
            "SELECT id, user_id FROM plans
             WHERE status='planned' AND expires_at IS NOT NULL AND expires_at <= ?",
            [$now]
        )->fetchAll();

        if (!$expired) {
            $app->session->set($key, time());
            return;
        }

        // Mark them canceled with reason
        $ids = array_column($expired, 'id');
        $in  = implode(',', array_fill(0, count($ids), '?'));
        $params = array_merge(["Expired after 30 days", $now], $ids);

        // status, cancel_reason, updated_at in one shot
        $app->db->query(
            "UPDATE plans SET status='canceled', cancel_reason=?, updated_at=?
             WHERE id IN ($in)",
            $params
        );

        // Event log per plan
        $event = new PlanEvent($app);
        foreach ($expired as $p) {
            $event->log((int)$p['id'], (int)$p['user_id'], 'auto_cancel', 'Expired after 30 days', []);
        }

        $app->session->set($key, time());
    }
}
