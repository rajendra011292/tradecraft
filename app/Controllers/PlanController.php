<?php

namespace App\Controllers;

use App\Models\Plan;
use App\Models\PlanEvent;

class PlanController extends Controller
{
    public function index(): void
    {
        $uid = (int)$this->app->session->get('user_id');
        $plans = (new Plan($this->app))->allForUser($uid);
        $this->view('plans/index', compact('plans'));
    }

    public function create(): void
    {
        $this->view('plans/create');
    }

    public function store(): void
    {
        $uid = (int)$this->app->session->get('user_id');

        // Raw inputs
        $symbol            = trim($_POST['symbol'] ?? '');
        $sector            = trim($_POST['sector'] ?? null);
        $side              = ($_POST['side'] ?? 'long') === 'short' ? 'short' : 'long';
        $plan_date         = $_POST['plan_date'] ?? date('Y-m-d');
        $timeframe         = $_POST['timeframe'] ?? 'swing';
        $market_bias       = $_POST['market_bias'] ?? 'sideways';
        $setup_type        = $_POST['setup_type'] ?? 'breakout';
        $entry_price       = (float)($_POST['entry_price'] ?? 0);
        $stop_loss         = (float)($_POST['stop_loss'] ?? 0);
        $target_price      = (float)($_POST['target_price'] ?? 0);
        $capital_allocated = (float)($_POST['capital_allocated'] ?? 0);
        $notes             = trim($_POST['notes'] ?? '');
        $confidence        = isset($_POST['confidence']) ? (int)$_POST['confidence'] : null;
        $emotion           = isset($_POST['emotion']) ? (int)$_POST['emotion'] : null;

        if ($symbol === '' || $entry_price <= 0 || $stop_loss <= 0 || $target_price <= 0) {
            $this->app->flash->error('Please fill symbol, entry, stop, target with valid numbers.');
            $this->redirect('/app/plans/create');
        }

        // Server-side calculations (ignore client-side computed values)
        $biasMap = [
            'very_bullish' => 3.0,
            'mild_bullish' => 2.0,
            'sideways'     => 1.5,
            'bearish'      => 1.0,
        ];
        $risk_percent   = $biasMap[$market_bias] ?? 1.0;
        $risk_per_share = abs($entry_price - $stop_loss);
        $reward_per_sh  = abs($target_price - $entry_price);

        $position_size = 0.0;
        $rr_ratio      = 0.0;
        $est_profit    = 0.0;

        if ($risk_per_share > 0) {
            $position_size = ($capital_allocated * ($risk_percent / 100.0)) / $risk_per_share;
            $rr_ratio      = $reward_per_sh / $risk_per_share;
            $est_profit    = $position_size * $reward_per_sh;
        }

        $capital_used = $position_size * $entry_price;

        $payload = [
            'symbol'            => $symbol,
            'sector'            => $sector,
            'side'              => $side,
            'plan_date'         => $plan_date,
            'timeframe'         => $timeframe,
            'market_bias'       => $market_bias,
            'setup_type'        => $setup_type,
            'entry_price'       => $entry_price,
            'stop_loss'         => $stop_loss,
            'target_price'      => $target_price,
            'capital_allocated' => $capital_allocated,
            'risk_percent'      => $risk_percent,
            'position_size'     => $position_size,
            'capital_used'      => $capital_used,
            'rr_ratio'          => $rr_ratio,
            'est_profit'        => $est_profit,
            'notes'             => $notes,
            'confidence'        => $confidence,
            'emotion'           => $emotion,
        ];

        $id = (new Plan($this->app))->create($uid, $payload);

        (new PlanEvent($this->app))->log(
            $id,
            $uid,
            'create',
            $_POST['reason'] ?? 'initial create',
            $payload
        );

        $this->app->flash->success('Plan created.');
        $this->redirect('/app/plans/' . $id);
    }

    public function show(int $id): void
    {
        $uid = (int)$this->app->session->get('user_id');
        $plan = (new Plan($this->app))->find($id, $uid);
        if (!$plan) {
            http_response_code(404);
            echo 'Not Found';
            return;
        }
        $events = (new PlanEvent($this->app))->forPlan($id, $uid);
        $this->view('plans/show', compact('plan', 'events'));
    }

   public function adjust(int $id): void
{
    $uid = (int)$this->app->session->get('user_id');
    $planModel = new Plan($this->app);
    $plan = $planModel->find($id, $uid);
    if (!$plan) { http_response_code(404); echo 'Not Found'; return; }
$this->ensurePlannedOrBounce($plan, $id);
    if ($plan['status'] === 'executed') {
        $this->app->flash->error('Executed plans cannot be adjusted.');
        $this->redirect('/app/plans/' . $id);
    }

        $symbol            = trim($_POST['symbol'] ?? $plan['symbol']);
        $sector            = trim($_POST['sector'] ?? ($plan['sector'] ?? ''));
        $side              = $_POST['side'] ?? $plan['side'];
        $plan_date         = $_POST['plan_date'] ?? $plan['plan_date'];
        $timeframe         = $_POST['timeframe'] ?? $plan['timeframe'];
        $market_bias       = $_POST['market_bias'] ?? $plan['market_bias'];
        $setup_type        = $_POST['setup_type'] ?? $plan['setup_type'];
        $entry_price       = (float)($_POST['entry_price'] ?? $plan['entry_price']);
        $stop_loss         = (float)($_POST['stop_loss'] ?? $plan['stop_loss']);
        $target_price      = (float)($_POST['target_price'] ?? $plan['target_price']);
        $capital_allocated = (float)($_POST['capital_allocated'] ?? ($plan['capital_allocated'] ?? 0));
        $notes             = trim($_POST['notes'] ?? ($plan['notes'] ?? ''));
        $confidence        = isset($_POST['confidence']) ? (int)$_POST['confidence'] : ($plan['confidence'] ?? null);
        $emotion           = isset($_POST['emotion']) ? (int)$_POST['emotion'] : ($plan['emotion'] ?? null);

        $biasMap = [
            'very_bullish' => 3.0,
            'mild_bullish' => 2.0,
            'sideways'     => 1.5,
            'bearish'      => 1.0,
        ];
        $risk_percent   = $biasMap[$market_bias] ?? 1.0;
        $risk_per_share = abs($entry_price - $stop_loss);
        $reward_per_sh  = abs($target_price - $entry_price);

        $position_size = 0.0;
        $rr_ratio      = 0.0;
        $est_profit    = 0.0;

        if ($risk_per_share > 0) {
            $position_size = ($capital_allocated * ($risk_percent / 100.0)) / $risk_per_share;
            $rr_ratio      = $reward_per_sh / $risk_per_share;
            $est_profit    = $position_size * $reward_per_sh;
        }

        $capital_used = $position_size * $entry_price;

        $data = [
            'symbol'            => $symbol,
            'sector'            => $sector,
            'side'              => $side,
            'plan_date'         => $plan_date,
            'timeframe'         => $timeframe,
            'market_bias'       => $market_bias,
            'setup_type'        => $setup_type,
            'entry_price'       => $entry_price,
            'stop_loss'         => $stop_loss,
            'target_price'      => $target_price,
            'capital_allocated' => $capital_allocated,
            'risk_percent'      => $risk_percent,
            'position_size'     => $position_size,
            'capital_used'      => $capital_used,
            'rr_ratio'          => $rr_ratio,
            'est_profit'        => $est_profit,
            'notes'             => $notes,
            'confidence'        => $confidence,
            'emotion'           => $emotion,
        ];

        $planModel->updateFields($id, $uid, $data);

        (new PlanEvent($this->app))->log($id, $uid, 'adjust', $_POST['reason'] ?? 'adjust', $data);

        $this->app->flash->success('Plan adjusted.');
        $this->redirect('/app/plans/' . $id);
    }

  public function execute(int $id): void
{
    $uid = (int)$this->app->session->get('user_id');
    $plan = (new \App\Models\Plan($this->app))->find($id, $uid);
    if (!$plan) { http_response_code(404); echo 'Not Found'; return; }
$this->ensurePlannedOrBounce($plan, $id);

if (($_POST['precheck_confirm'] ?? 'no') !== 'yes') {
        $this->app->flash->error('Please complete the pre-trade checklist before executing.');
        $this->redirect('/app/plans/' . $id);
    }
    // Guards
    if ($plan['status'] === 'executed') {
        $this->app->flash->error('Plan is already executed.');
        $this->redirect('/app/plans/' . $id);
    }
    if ($plan['status'] === 'canceled') {
        $this->app->flash->error('Canceled plans cannot be executed.');
        $this->redirect('/app/plans/' . $id);
    }
    if (($_POST['precheck_confirm'] ?? 'no') !== 'yes') {
        $this->app->flash->error('Please complete the pre-trade checklist before executing.');
        $this->redirect('/app/plans/' . $id);
    }

    // Inputs
    $entryActual = (float)($_POST['entry_price_actual'] ?? 0);
    $qtyInput    = $_POST['qty'] ?? null;
    $execAtRaw   = trim($_POST['executed_at'] ?? '');
$biasExec    = $_POST['market_bias_execute'] ?? $plan['market_bias'];
$executedAt  = $execAtRaw !== '' ? date('Y-m-d H:i:s', strtotime($execAtRaw)) : date('Y-m-d H:i:s');
    if ($entryActual <= 0) {
        $this->app->flash->error('Actual entry price is required.');
        $this->redirect('/app/plans/' . $id);
    }


    // Risk map (use same as create/adjust)
    $biasMap = ['very_bullish'=>3.0,'mild_bullish'=>2.0,'sideways'=>1.5,'bearish'=>1.0];
    $riskPercent   = $biasMap[$plan['market_bias']] ?? 1.0;
    $riskPerShare  = abs($entryActual - (float)$plan['stop_loss']);
    $rewardPerShare= abs((float)$plan['target_price'] - $entryActual);

    // Qty: posted or auto-calc by risk
    if ($qtyInput === null || $qtyInput === '') {
        $qty = $riskPerShare > 0 ? (($plan['capital_allocated'] * ($riskPercent/100.0)) / $riskPerShare) : 0;
        $qty = max(0, floor($qty * 10000) / 10000); // trim precision
    } else {
        $qty = max(0, (float)$qtyInput);
    }           

    $capitalUsed = $qty * $entryActual;
    $rrAtEntry   = $riskPerShare > 0 ? ($rewardPerShare / $riskPerShare) : 0;
    $estProfitAt = $qty * ((float)$plan['target_price'] - $entryActual);

    // Create position (status: ongoing)
    $posId = (new \App\Models\Position($this->app))->create([
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

    // Log and mark plan executed
    (new \App\Models\PlanEvent($this->app))->log($plan['id'], $uid, 'execute', $_POST['reason'] ?? 'execute', [
    'bias_used'   => $biasExec,
    'planned'     => ['entry_price'=>(float)$plan['entry_price'],'stop_loss'=>(float)$plan['stop_loss'],'target_price'=>(float)$plan['target_price'],'capital_allocated'=>(float)($plan['capital_allocated'] ?? 0)],
    'actual'      => ['entry_price'=>$entryActual,'qty'=>$qty,'capital_used'=>$capitalUsed,'rr_at_entry'=>$rrAtEntry,'est_profit'=>$estProfitAt,'executed_at'=>$executedAt]
]);

    (new \App\Models\Plan($this->app))->setStatus($plan['id'], $uid, 'executed');
$this->redirect('/app/positions');
}


    public function cancel(int $id): void
    {
        $uid = (int)$this->app->session->get('user_id');
        $plan = (new Plan($this->app))->find($id, $uid);
        if (!$plan) {
            http_response_code(404);
            echo 'Not Found';
            return;
        }
        $this->ensurePlannedOrBounce($plan, $id);

        if ($plan['status'] === 'executed') {
            $this->app->flash->error('Executed plans cannot be cancelled.');
            $this->redirect('/app/plans/' . $id);
        }

        (new Plan($this->app))->setStatus($id, $uid, 'canceled');
        (new PlanEvent($this->app))->log($id, $uid, 'cancel', $_POST['reason'] ?? 'cancel', $plan);

        $this->app->flash->success('Plan canceled.');
        $this->redirect('/app/plans/' . $id);
    }
    private function ensurePlannedOrBounce(array $plan, int $id): void
{
    if (($plan['status'] ?? '') !== 'planned') {
        $this->app->flash->error('Actions are only allowed on planned plans.');
        $this->redirect('/app/plans/' . $id);
    }
}
}
