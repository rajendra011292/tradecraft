<?php

namespace App\Controllers;

use App\Models\Position;
use App\Models\PositionEvent;

class PositionsController extends Controller
{
    /** Ensure only ongoing positions can be changed */
    private function ensureOngoingOrBounce(array $pos, int $id): void
    {
        if (($pos['status'] ?? '') !== 'ongoing') {
            $this->app->flash->error('Only ongoing positions can be modified.');
            $this->redirect('/app/positions/' . $id);
        }
    }

    /** List ongoing positions */
    public function index(): void
    {
        $uid = (int)$this->app->session->get('user_id');
        $positions = (new Position($this->app))->allOngoing($uid);
        $this->view('positions/index', compact('positions'));
    }

    /** List completed positions with legs */
    public function completed(): void
    {
        $uid = (int)$this->app->session->get('user_id');
        $positions = $this->app->db->query(
            "SELECT * FROM trade_positions WHERE user_id=? AND status='completed' ORDER BY updated_at DESC",
            [$uid]
        )->fetchAll();

        $ids = array_column($positions, 'id');
        $legsByPos = [];
        if ($ids) {
            $in = implode(',', array_fill(0, count($ids), '?'));
            $rows = $this->app->db->query("SELECT * FROM position_legs WHERE position_id IN ($in) ORDER BY exited_at ASC", $ids)->fetchAll();
            foreach ($rows as $r) {
                $legsByPos[$r['position_id']][] = $r;
            }
        }

        $this->view('positions/completed', compact('positions', 'legsByPos'));
    }

    /** Show single position */
    public function show($id): void
    {
        $uid = (int)$this->app->session->get('user_id');
        $id  = (int)$id;

        $pos = (new \App\Models\Position($this->app))->find($id, $uid);
        if (!$pos) {
            http_response_code(404);
            echo 'Not Found';
            return;
        }

        // NEW: load legs for this position (partial + full; we’ll show partials in the view)
        $legs = $this->app->db->query(
            "SELECT * FROM position_legs WHERE position_id=? ORDER BY exited_at ASC",
            [$id]
        )->fetchAll();

        // Load position events
        $positionEvent = new PositionEvent($this->app);
        $events = $positionEvent->forPosition($id, $uid);

        // Debug: Log the events to see what's being fetched
        error_log('Position events: ' . print_r($events, true));

        $this->view('positions/show', compact('pos', 'legs', 'events'));
    }


    /** Adjust SL/TP + flags */
    public function adjust($id): void
    {
        $uid = (int)$this->app->session->get('user_id');
        $id = (int)$id;
        $posModel = new Position($this->app);
        $pos = $posModel->find($id, $uid);
        if (!$pos) {
            http_response_code(404);
            echo 'Not Found';
            return;
        }

        $this->ensureOngoingOrBounce($pos, $id);

        $sl = (float)($_POST['stop_loss'] ?? 0);
        $tp = (float)($_POST['target_price'] ?? 0);

        // Optional trailing/breakeven toggles
        // ---- BEFORE updating DB ----
        $rawType  = isset($_POST['trailing_type']) ? trim((string)$_POST['trailing_type']) : '';
        $rawValue = isset($_POST['trailing_value']) ? trim((string)$_POST['trailing_value']) : '';

        $trailingEnabled = !empty($_POST['trailing_enabled']) ? 1 : 0;

        // Normalize trailing_type to match ENUM exactly or NULL
        // Accept common variants and map to canonical values
        $map = [
            'fixed'   => 'fixed',
            'percent' => 'percent',
            'atr'     => 'ATR',
            'ATR'     => 'ATR',
        ];
        $trailingType  = ($rawType === '' ? null : ($map[$rawType] ?? null));

        // Normalize value: null if empty
        $trailingValue = ($rawValue === '' ? null : (float)$rawValue);

        // Breakeven flag
        $breakeven = !empty($_POST['breakeven_enabled']) ? 1 : 0;

        // Optional: if trailing is disabled, force type/value to NULL
        if ($trailingEnabled === 0) {
            $trailingType  = null;
            $trailingValue = null;
        }


        $this->app->db->query(
            "UPDATE trade_positions
             SET stop_loss=?, target_price=?, trailing_enabled=?, trailing_type=?, trailing_value=?, breakeven_enabled=?, updated_at=?
             WHERE id=? AND user_id=?",
            [$sl, $tp, $trailingEnabled, $trailingType, $trailingValue, $breakeven, date('Y-m-d H:i:s'), $id, $uid]
        );

        // (Optional) log position_events here
        $ev = new \App\Models\PositionEvent($this->app);
        if ((float)$pos['stop_loss'] !== $sl)    $ev->log($id, $uid, 'adjust_sl', 'risk_update', ['stop_loss' => $sl]);
        if ((float)$pos['target_price'] !== $tp) $ev->log($id, $uid, 'adjust_tp', 'risk_update', ['target_price' => $tp]);
        if ((int)$pos['trailing_enabled'] !== $trailingEnabled)
            $ev->log($id, $uid, $trailingEnabled ? 'set_trailing' : 'unset_trailing', 'risk_update', [
                'type' => $trailingType,
                'value' => $trailingValue
            ]);
        if ((int)$pos['breakeven_enabled'] !== $breakeven)
            $ev->log($id, $uid, $breakeven ? 'enable_breakeven' : 'disable_breakeven', 'risk_update', []);


        $events = (new \App\Models\PositionEvent($this->app))->forPosition($id, $uid);
        $this->view('positions/show', compact('pos', 'events')); // ensure the view expects $events

    }

    /** Partial close: add leg, reduce qty; if qty hits 0 => complete */
    public function partial($id): void
    {
        $uid = (int)$this->app->session->get('user_id');
        $id  = (int)$id;
        $posModel = new Position($this->app);
        $pos = $posModel->find($id, $uid);
        if (!$pos) {
            http_response_code(404);
            echo 'Not Found';
            return;
        }

        $this->ensureOngoingOrBounce($pos, $id);

        $qty   = max(0, (float)($_POST['qty'] ?? 0));
        $price = (float)($_POST['price'] ?? 0);
        $fees  = max(0, (float)($_POST['fees'] ?? 0));
        $note  = trim($_POST['note'] ?? '');

        if ($qty <= 0 || $price <= 0) {
            $this->app->flash->error('Quantity and price are required.');
            $this->redirect('/app/positions/' . $id);
        }
        if ($qty > (float)$pos['qty']) {
            $this->app->flash->error('Cannot close more than remaining quantity.');
            $this->redirect('/app/positions/' . $id);
        }

        $sideSign = ($pos['side'] === 'short') ? -1 : 1;
        $realized = (($price - (float)$pos['entry_price']) * $qty * $sideSign) - $fees;

        // Insert leg
        $this->app->db->query(
            "INSERT INTO position_legs (position_id, user_id, leg_type, qty, exit_price, fees, realized_pl, exited_at, note, created_at)
             VALUES (?,?,?,?,?,?,?,?,?,?)",
            [$id, $uid, 'partial', $qty, $price, $fees, $realized, date('Y-m-d H:i:s'), $note ?: null, date('Y-m-d H:i:s')]
        );

        // Reduce qty
        $newQty = (float)$pos['qty'] - $qty;
        $this->app->db->query(
            "UPDATE trade_positions SET qty=?, updated_at=? WHERE id=? AND user_id=?",
            [$newQty, date('Y-m-d H:i:s'), $id, $uid]
        );

        // If zero -> finalize completed stats
        if ($newQty <= 0.0000001) {
            $this->finalizeCompleted($pos['id'], $uid);
        }

        (new \App\Models\PositionEvent($this->app))->log(
            $id,
            $uid,
            'partial_close',
            $note ?: null,
            ['qty' => $qty, 'price' => $price, 'fees' => $fees, 'realized_pl' => $realized, 'remaining_qty' => $newQty]
        );


        $this->app->flash->success('Partial close recorded.');
        $this->redirect('/app/positions/' . $id);
    }

    /** Full close: close remaining qty, add leg, set completed */
    public function close($id): void
    {
        $uid = (int)$this->app->session->get('user_id');
        $id  = (int)$id;
        $posModel = new Position($this->app);
        $pos = $posModel->find($id, $uid);
        if (!$pos) {
            http_response_code(404);
            echo 'Not Found';
            return;
        }

        $this->ensureOngoingOrBounce($pos, $id);

        $remaining = (float)$pos['qty'];
        if ($remaining <= 0) {
            $this->app->flash->error('No quantity left to close.');
            $this->redirect('/app/positions/' . $id);
        }

        $price = (float)($_POST['price'] ?? 0);
        $fees  = max(0, (float)($_POST['fees'] ?? 0));
        $note  = trim($_POST['note'] ?? '');

        if ($price <= 0) {
            $this->app->flash->error('Close price is required.');
            $this->redirect('/app/positions/' . $id);
        }

        $sideSign = ($pos['side'] === 'short') ? -1 : 1;
        $realized = (($price - (float)$pos['entry_price']) * $remaining * $sideSign) - $fees;

        // Insert leg
        $this->app->db->query(
            "INSERT INTO position_legs (position_id, user_id, leg_type, qty, exit_price, fees, realized_pl, exited_at, note, created_at)
             VALUES (?,?,?,?,?,?,?,?,?,?)",
            [$id, $uid, 'full', $remaining, $price, $fees, $realized, date('Y-m-d H:i:s'), $note ?: null, date('Y-m-d H:i:s')]
        );

        // Zero out qty & mark completed
        $this->app->db->query(
            "UPDATE trade_positions SET qty=0, status='completed', completed_at=?, updated_at=? WHERE id=? AND user_id=?",
            [date('Y-m-d H:i:s'), date('Y-m-d H:i:s'), $id, $uid]
        );

        // Compute realized_pl_total + avg_exit_price
        $this->finalizeCompleted($pos['id'], $uid);

        // (Optional) log position_events
        (new \App\Models\PositionEvent($this->app))->log(
            $id,
            $uid,
            'full_close',
            $note ?: null,
            ['qty' => $remaining, 'price' => $price, 'fees' => $fees, 'realized_pl' => $realized]
        );
        $this->app->flash->success('Position closed.');
        $this->redirect('/app/positions/' . $id);
    }

    /** Compute realized_pl_total & avg_exit_price after completion */
    private function finalizeCompleted(int $positionId, int $userId): void
    {
        // Guard: load current state
        $pos = $this->app->db->query("SELECT * FROM trade_positions WHERE id=? AND user_id=?", [$positionId, $userId])->fetch();
        if (!$pos) return;

        // Pull all legs
        $legs = $this->app->db->query("SELECT qty, exit_price, realized_pl FROM position_legs WHERE position_id=? ORDER BY exited_at ASC", [$positionId])->fetchAll();

        $sumPL = 0.0;
        $sumQty = 0.0;
        $wSumExit = 0.0;
        foreach ($legs as $l) {
            $sumPL += (float)$l['realized_pl'];
            $q = (float)$l['qty'];
            $sumQty += $q;
            $wSumExit += $q * (float)$l['exit_price'];
        }
        $avgExit = $sumQty > 0 ? $wSumExit / $sumQty : null;

        $this->app->db->query(
            "UPDATE trade_positions SET realized_pl_total=?, avg_exit_price=?, status='completed', completed_at=?, updated_at=? WHERE id=? AND user_id=?",
            [$sumPL, $avgExit, $pos['completed_at'] ?: date('Y-m-d H:i:s'), date('Y-m-d H:i:s'), $positionId, $userId]
        );
    }
}
