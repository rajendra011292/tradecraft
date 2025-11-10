<?php
require __DIR__ . '/../../vendor/autoload.php';

use App\Core\App;
use App\Models\Plan;
use App\Models\PlanEvent;

$app = new App();
$now = date('Y-m-d H:i:s');

// Find expired planned plans
$expired = $app->db->query("SELECT id, user_id FROM plans WHERE status='planned' AND expires_at <= ?", [$now])->fetchAll();

foreach ($expired as $p) {
    // Mark plan as canceled with reason 'Expired after 30 days'
    $app->db->query("UPDATE plans SET status='canceled', cancel_reason='Expired after 30 days', updated_at=? WHERE id=?", [now(), $p['id']]);

    // Log event
    (new PlanEvent($app))->log($p['id'], $p['user_id'], 'auto_cancel', 'Expired after 30 days', []);
}

echo count($expired) . " plans expired.\n";
