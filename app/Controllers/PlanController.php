<?php

namespace App\Controllers;

use App\Core\App;
use App\Models\Plan;
use App\Models\PlanEvent;
use App\Models\Position;
use App\Support\Math;

class PlanController extends Controller
{

    public function create(): void
    {
        // If you need dropdown data, load here and pass to view.
        $this->view('plans/create', [
            'today' => date('Y-m-d'),
        ]);
    }

    public function store(): void
    {
        $uid = (int)$this->app->session->get('user_id');

        // Read posted fields (server is canonical; ignore client computed fields)
        $d = [
            'symbol'            => trim($_POST['symbol'] ?? ''),
            'sector'            => trim($_POST['sector'] ?? null),
            'side'              => $_POST['side'] ?? 'long',
            'plan_date'         => $_POST['plan_date'] ?? date('Y-m-d'),
            'timeframe'         => $_POST['timeframe'] ?? 'swing',
            'market_bias'       => $_POST['market_bias'] ?? 'sideways',
            'setup_type'        => $_POST['setup_type'] ?? 'breakout',
            'entry_price'       => (float)($_POST['entry_price'] ?? 0),
            'stop_loss'         => (float)($_POST['stop_loss'] ?? 0),
            'target_price'      => (float)($_POST['target_price'] ?? 0),
            'capital_allocated' => (float)($_POST['capital_allocated'] ?? 0),
            'notes'             => trim($_POST['notes'] ?? ''),
            'confidence'        => (int)($_POST['confidence'] ?? 0),
            'emotion'           => (int)($_POST['emotion'] ?? 0),
        ];

        // Server-side calculations
        $biasMap = ['very_bullish' => 3.0, 'mild_bullish' => 2.0, 'sideways' => 1.5, 'bearish' => 1.0];
        $riskPercent   = $biasMap[$d['market_bias']] ?? 1.0;
        $riskPerUnit   = abs($d['entry_price'] - $d['stop_loss']);
        $rewardPerUnit = abs($d['target_price'] - $d['entry_price']);

        $positionSize  = $riskPerUnit > 0 ? (($d['capital_allocated'] * ($riskPercent / 100.0)) / $riskPerUnit) : 0;
        $positionSize = Math::roundDownQty($positionSize, 1.0);
        $capitalUsed   = $positionSize * $d['entry_price'];
        $rrRatio       = $riskPerUnit > 0 ? ($rewardPerUnit / $riskPerUnit) : 0;
        $estProfit     = $positionSize * ($d['target_price'] - $d['entry_price']);

        // Attach computed values expected by Plan::create()
        $d['risk_percent']  = $riskPercent;
        $d['position_size'] = $positionSize;
        $d['capital_used']  = $capitalUsed;
        $d['rr_ratio']      = $rrRatio;
        $d['est_profit']    = $estProfit;

        // Basic validation
        if ($d['symbol'] === '' || $d['entry_price'] <= 0 || $d['stop_loss'] <= 0 || $d['target_price'] <= 0) {
            $this->app->flash->error('Please fill symbol, entry, stop, and target correctly.');
            $this->redirect('/app/plans/create');
        }

        // Persist
        $planId = (new \App\Models\Plan($this->app))->create($uid, $d);

        $this->app->flash->success('Plan created.');
        $this->redirect('/app/plans/' . $planId);
    }

    /** Only planned plans may be acted on */
    private function ensurePlannedOrBounce(array $plan, int $id): void
    {
        if (($plan['status'] ?? '') !== 'planned') {
            $this->app->flash->error('Actions are only allowed on planned plans.');
            $this->redirect('/app/plans/' . $id);
        }
    }

    /** List all plans (you likely already have this) */
    public function index(): void
    {
        // Optional: run expiry service here if you integrated it
        // \App\Services\PlanExpiry::run($this->app);

        $uid = (int)$this->app->session->get('user_id');
        $plans = (new Plan($this->app))->allForUser($uid);



        $this->view('plans/index', compact('plans'));
    }

    /** Show single plan (you likely already have this) */
    public function show(int $id): void
    {
        $uid = (int)$this->app->session->get('user_id');
        $plan = (new Plan($this->app))->find($id, $uid);
        if (!$plan) {
            http_response_code(404);
            echo 'Not Found';
            return;
        }

        $events = (new \App\Models\PlanEvent($this->app))->forPlan($id, $uid);
        $this->view('plans/show', compact('plan', 'events'));
    }

    /** Execute -> creates trade_positions row with ACTUAL fill; locks plan; stores plan->position_id */
    public function execute(int $id): void
    {
        $uid  = (int)$this->app->session->get('user_id');
        $planModel = new Plan($this->app);
        $plan = $planModel->find($id, $uid);
        if (!$plan) {
            http_response_code(404);
            echo 'Not Found';
            return;
        }

        $this->ensurePlannedOrBounce($plan, $id);

        if (($_POST['precheck_confirm'] ?? 'no') !== 'yes') {
            $this->app->flash->error('Please complete the pre-trade checklist before executing.');
            $this->redirect('/app/plans/' . $id);
        }

        $entryActual = (float)($_POST['entry_price_actual'] ?? 0);
        $qtyInput    = $_POST['qty'] ?? null;
        $execAtRaw   = trim($_POST['executed_at'] ?? '');
        $biasExec    = $_POST['market_bias_execute'] ?? $plan['market_bias'];

        if ($entryActual <= 0) {
            $this->app->flash->error('Actual entry price is required.');
            $this->redirect('/app/plans/' . $id);
        }

        $executedAt  = $execAtRaw !== '' ? date('Y-m-d H:i:s', strtotime($execAtRaw)) : date('Y-m-d H:i:s');

        // Risk map for sizing at execution
        $biasMap = ['very_bullish' => 3.0, 'mild_bullish' => 2.0, 'sideways' => 1.5, 'bearish' => 1.0];
        $riskPercent   = $biasMap[$biasExec] ?? 1.0;
        $riskPerUnit   = abs($entryActual - (float)$plan['stop_loss']);
        $rewardPerUnit = abs((float)$plan['target_price'] - $entryActual);

        if ($qtyInput === null || $qtyInput === '') {
    $rawQty = $riskPerUnit > 0 ? (($plan['capital_allocated'] * ($riskPercent/100.0)) / $riskPerUnit) : 0;

    // Decide the step (1 share for cash by default; change if you detect F&O)
    $step = 1.0; // or detect by symbol/segment and set to lot size, e.g., 25
    $qty  = Math::roundDownQty($rawQty, $step);
} else {
    $qty = max(0, (float)$qtyInput);
    // Optional: also floor user-provided qty to the step
    $step = 1.0;
    $qty  = Math::roundDownQty($qty, $step);
}

        $capitalUsed = $qty * $entryActual;
        $rrAtEntry   = $riskPerUnit > 0 ? ($rewardPerUnit / $riskPerUnit) : 0;
        $estProfitAt = $qty * ((float)$plan['target_price'] - $entryActual);

        // Create Position
        $positionId = (new Position($this->app))->create([
            'plan_id'      => (int)$plan['id'],
            'user_id'      => $uid,
            'symbol'       => $plan['symbol'],
            'side'         => $plan['side'],
            'entry_price'  => $entryActual,
            'stop_loss'    => (float)$plan['stop_loss'],
            'target_price' => (float)$plan['target_price'],
            'qty'          => $qty,
            'capital_used' => $capitalUsed,
            'rr_at_entry'  => $rrAtEntry,
            'est_profit'   => $estProfitAt,
            'executed_at'  => $executedAt,
        ]);

        // Link plan → position + mark plan executed
        $this->app->db->query(
            "UPDATE plans SET status='executed', position_id=?, updated_at=? WHERE id=? AND user_id=?",
            [$positionId, date('Y-m-d H:i:s'), $plan['id'], $uid]
        );

        // Log execute event
        // Plan event: execute
        (new \App\Models\PlanEvent($this->app))->log(
            (int)$plan['id'],
            $uid,
            'execute',
            $_POST['reason'] ?? null,
            [
                'bias_used' => $biasExec,
                'planned' => [
                    'entry_price' => (float)$plan['entry_price'],
                    'stop_loss'  => (float)$plan['stop_loss'],
                    'target_price' => (float)$plan['target_price'],
                    'capital_allocated' => (float)($plan['capital_allocated'] ?? 0),
                ],
                'actual' => [
                    'entry_price' => $entryActual,
                    'qty' => $qty,
                    'capital_used' => $capitalUsed,
                    'rr_at_entry' => $rrAtEntry,
                    'executed_at' => $executedAt
                ],
            ]
        );

        // Position event: open
        (new \App\Models\PositionEvent($this->app))->log(
            (int)$positionId,
            $uid,
            'open',
            $_POST['reason'] ?? null,
            [
                'entry_price' => $entryActual,
                'stop_loss' => (float)$plan['stop_loss'],
                'target_price' => (float)$plan['target_price'],
                'qty' => $qty,
                'executed_at' => $executedAt
            ]
        );


        $this->app->flash->success('Plan executed. Position opened.');
        $this->redirect('/app/positions');
    }

    /** Adjust planned values (only when planned) — you likely already have this */
    public function adjust(int $id): void
    {
        $uid  = (int)$this->app->session->get('user_id');
        $plan = (new Plan($this->app))->find($id, $uid);
        if (!$plan) {
            http_response_code(404);
            echo 'Not Found';
            return;
        }

        $this->ensurePlannedOrBounce($plan, $id);

        // ... your existing recalc + update + event log ...
        $this->app->flash->success('Plan adjusted.');
        (new \App\Models\PlanEvent($this->app))->log($id, $uid, 'adjust', $_POST['reason'] ?? null);


        $this->redirect('/app/plans/' . $id);
    }

    /** Cancel plan (only when planned) */
    public function cancel(int $id): void
    {
        $uid  = (int)$this->app->session->get('user_id');
        $plan = (new Plan($this->app))->find($id, $uid);
        if (!$plan) {
            http_response_code(404);
            echo 'Not Found';
            return;
        }

        $this->ensurePlannedOrBounce($plan, $id);

        $this->app->db->query(
            "UPDATE plans SET status='canceled', cancel_reason=?, updated_at=? WHERE id=? AND user_id=?",
            [($_POST['reason'] ?? 'cancel'), date('Y-m-d H:i:s'), $id, $uid]
        );
        (new \App\Models\PlanEvent($this->app))->log($id, $uid, 'cancel', $_POST['reason'] ?? 'cancel');


        $this->app->flash->success('Plan canceled.');
        $this->redirect('/app/plans/' . $id);
    }
}
