<?php
/** @var array $plan */
/** @var array $events */
$title = 'Plan #'.$plan['id'];
$tpl   = 'plans/show';
?>
<a class="underline text-sm" href="/app/plans">&larr; Back</a>
<h1 class="text-2xl font-bold mb-4">Plan #<?= (int)$plan['id'] ?> — <?= e($plan['symbol']) ?> (<?= e($plan['side']) ?>)</h1>

<div class="grid xl:grid-cols-3 gap-6">
  <!-- LEFT: INFORMATION -->
  <section class="xl:col-span-2 space-y-6">
    <!-- Overview -->
    <div class="border border-gray-800 rounded p-4">
      <h2 class="font-semibold text-lg mb-3">Overview</h2>
      <div class="grid sm:grid-cols-2 gap-4 text-sm">
        <div><div class="text-gray-400">Status</div><div class="font-semibold"><?= e($plan['status']) ?></div></div>
        <div><div class="text-gray-400">Plan Date</div><div class="font-semibold"><?= e($plan['plan_date']) ?></div></div>
        <div><div class="text-gray-400">Symbol</div><div class="font-semibold"><?= e($plan['symbol']) ?></div></div>
        <div><div class="text-gray-400">Sector</div><div class="font-semibold"><?= e($plan['sector'] ?? '—') ?></div></div>
        <div><div class="text-gray-400">Side</div><div class="font-semibold"><?= e($plan['side']) ?></div></div>
        <div><div class="text-gray-400">Timeframe</div><div class="font-semibold"><?= e($plan['timeframe']) ?></div></div>
        <div><div class="text-gray-400">Market Bias</div><div class="font-semibold"><?= e($plan['market_bias']) ?></div></div>
        <div><div class="text-gray-400">Setup</div><div class="font-semibold"><?= e($plan['setup_type']) ?></div></div>
      </div>
      <?php if (!empty($plan['notes'])): ?>
        <div class="mt-4">
          <div class="text-gray-400 text-sm">Notes</div>
          <div class="font-medium whitespace-pre-wrap"><?= e($plan['notes']) ?></div>
        </div>
      <?php endif; ?>
    </div>

    <!-- Setup -->
    <div class="border border-gray-800 rounded p-4">
      <h2 class="font-semibold text-lg mb-3">Setup</h2>
      <div class="grid sm:grid-cols-3 gap-4 text-sm">
        <div><div class="text-gray-400">Entry</div><div class="font-semibold"><?= e($plan['entry_price']) ?></div></div>
        <div><div class="text-gray-400">Stop Loss</div><div class="font-semibold"><?= e($plan['stop_loss']) ?></div></div>
        <div><div class="text-gray-400">Target</div><div class="font-semibold"><?= e($plan['target_price']) ?></div></div>
      </div>
    </div>

    <!-- Risk & Sizing -->
    <div class="border border-gray-800 rounded p-4">
      <h2 class="font-semibold text-lg mb-3">Risk & Sizing</h2>
      <div class="grid sm:grid-cols-3 gap-4 text-sm">
        <div><div class="text-gray-400">Capital Allocated</div><div class="font-semibold"><?= e(number_format((float)($plan['capital_allocated'] ?? 0),2)) ?></div></div>
        <div><div class="text-gray-400">Risk %</div><div class="font-semibold"><?= e(number_format((float)$plan['risk_percent'],2)) ?>%</div></div>
        <div><div class="text-gray-400">Position Size</div><div class="font-semibold"><?= e(number_format((float)$plan['position_size'],2)) ?></div></div>
        <div><div class="text-gray-400">Capital Used</div><div class="font-semibold"><?= e(number_format((float)$plan['capital_used'],2)) ?></div></div>
        <div><div class="text-gray-400">RR Ratio</div><div class="font-semibold"><?= e(number_format((float)$plan['rr_ratio'],2)) ?></div></div>
        <div><div class="text-gray-400">Est. Profit</div><div class="font-semibold"><?= e(number_format((float)$plan['est_profit'],2)) ?></div></div>
      </div>
    </div>

    <!-- Psychology -->
    <div class="border border-gray-800 rounded p-4">
      <h2 class="font-semibold text-lg mb-3">Psychology</h2>
      <div class="grid sm:grid-cols-2 gap-4 text-sm">
        <div><div class="text-gray-400">Confidence</div><div class="font-semibold"><?= e($plan['confidence'] ?? '—') ?></div></div>
        <div><div class="text-gray-400">Emotion</div><div class="font-semibold"><?= e($plan['emotion'] ?? '—') ?></div></div>
      </div>
    </div>

    <!-- Timeline -->
    <div class="border border-gray-800 rounded p-4">
      <h2 class="font-semibold text-lg mb-3">Timeline</h2>
      <div class="grid sm:grid-cols-2 gap-4 text-sm">
        <div><div class="text-gray-400">Created At</div><div class="font-semibold"><?= e($plan['created_at']) ?></div></div>
        <div><div class="text-gray-400">Updated At</div><div class="font-semibold"><?= e($plan['updated_at']) ?></div></div>
      </div>
    </div>

    <!-- Actions: only when status == planned -->
    <?php if ($plan['status'] === 'planned'): ?>
      <div class="border border-gray-800 rounded p-4 space-y-3">
        <h2 class="font-semibold text-lg">Actions</h2>

        <div class="flex flex-wrap gap-2">
          <button type="button" data-open="#adjustPanel"  class="px-3 py-2 rounded bg-yellow-600 hover:bg-yellow-500">Adjust</button>
          <button type="button" data-open="#executePanel" class="px-3 py-2 rounded bg-blue-600 hover:bg-blue-500">Execute</button>
          <button type="button" data-open="#cancelPanel"  class="px-3 py-2 rounded bg-red-600 hover:bg-red-500">Cancel</button>
        </div>

        <!-- Adjust (hidden by default) -->
        <div id="adjustPanel" class="hidden mt-3 border border-gray-800 rounded p-4">
          <h3 class="font-semibold mb-3">Adjust Plan</h3>
          <form method="post" action="/app/plans/<?= (int)$plan['id'] ?>/adjust" class="grid sm:grid-cols-2 gap-3">
            <?= csrf_field($this->app ?? $app ?? null) ?>
            <input class="bg-gray-900 border border-gray-700 rounded px-3 py-2" name="symbol" value="<?= e($plan['symbol']) ?>">
            <select class="bg-gray-900 border border-gray-700 rounded px-3 py-2" name="side">
              <option <?= $plan['side']==='long'?'selected':'' ?> value="long">Long</option>
              <option <?= $plan['side']==='short'?'selected':'' ?> value="short">Short</option>
            </select>
            <input class="bg-gray-900 border border-gray-700 rounded px-3 py-2" name="sector" value="<?= e($plan['sector'] ?? '') ?>" placeholder="Sector">
            <select class="bg-gray-900 border border-gray-700 rounded px-3 py-2" name="timeframe">
              <?php foreach (['intraday','swing','positional'] as $tf): ?>
                <option value="<?= e($tf) ?>" <?= $plan['timeframe']===$tf?'selected':'' ?>><?= ucfirst($tf) ?></option>
              <?php endforeach; ?>
            </select>
            <select class="bg-gray-900 border border-gray-700 rounded px-3 py-2" name="market_bias" id="adjMarketBias">
              <?php foreach (['very_bullish','mild_bullish','sideways','bearish'] as $mb): ?>
                <option value="<?= e($mb) ?>" <?= $plan['market_bias']===$mb?'selected':'' ?>><?= str_replace('_',' ',ucfirst($mb)) ?></option>
              <?php endforeach; ?>
            </select>
            <select class="bg-gray-900 border border-gray-700 rounded px-3 py-2" name="setup_type">
              <?php foreach (['breakout','pullback','reversal','range_play'] as $st): ?>
                <option value="<?= e($st) ?>" <?= $plan['setup_type']===$st?'selected':'' ?>><?= ucwords(str_replace('_',' ',$st)) ?></option>
              <?php endforeach; ?>
            </select>
            <input type="date" class="bg-gray-900 border border-gray-700 rounded px-3 py-2" name="plan_date" value="<?= e($plan['plan_date']) ?>">
            <input type="number" step="0.01" class="bg-gray-900 border border-gray-700 rounded px-3 py-2" id="adjEntry"  name="entry_price"  value="<?= e($plan['entry_price']) ?>">
            <input type="number" step="0.01" class="bg-gray-900 border border-gray-700 rounded px-3 py-2" id="adjStop"   name="stop_loss"    value="<?= e($plan['stop_loss']) ?>">
            <input type="number" step="0.01" class="bg-gray-900 border border-gray-700 rounded px-3 py-2" id="adjTarget" name="target_price" value="<?= e($plan['target_price']) ?>">
            <input type="number" step="0.01" class="bg-gray-900 border border-gray-700 rounded px-3 py-2" id="adjCap"    name="capital_allocated" value="<?= e($plan['capital_allocated'] ?? 0) ?>">

            <!-- Calculated (display-only) -->
            <input type="number" step="0.01" class="bg-gray-900 border border-gray-700 rounded px-3 py-2" id="adjRiskPct"  placeholder="Risk % (auto)" readonly>
            <input type="number" step="0.01" class="bg-gray-900 border border-gray-700 rounded px-3 py-2" id="adjPosSize" placeholder="Position Size (auto)" readonly>
            <input type="number" step="0.01" class="bg-gray-900 border border-gray-700 rounded px-3 py-2" id="adjCapUsed" placeholder="Capital Used (auto)" readonly>
            <input type="number" step="0.01" class="bg-gray-900 border border-gray-700 rounded px-3 py-2" id="adjRR"      placeholder="RR Ratio (auto)" readonly>
            <input type="number" step="0.01" class="bg-gray-900 border border-gray-700 rounded px-3 py-2" id="adjProfit"  placeholder="Est. Profit (auto)" readonly>

            <input class="bg-gray-900 border border-gray-700 rounded px-3 py-2 sm:col-span-2" name="notes" value="<?= e($plan['notes'] ?? '') ?>" placeholder="Notes (optional)">
            <input type="number" min="1" max="10" class="bg-gray-900 border border-gray-700 rounded px-3 py-2" name="confidence" value="<?= e($plan['confidence'] ?? '') ?>" placeholder="Confidence (1-10)">
            <input type="number" min="1" max="10" class="bg-gray-900 border border-gray-700 rounded px-3 py-2" name="emotion"    value="<?= e($plan['emotion'] ?? '') ?>" placeholder="Emotion (1-10)">
            <input class="bg-gray-900 border border-gray-700 rounded px-3 py-2 sm:col-span-2" name="reason" placeholder="Reason for adjustment">
            <button class="px-3 py-2 rounded bg-yellow-600 hover:bg-yellow-500 sm:col-span-2">Save Adjustments</button>
          </form>
        </div>

        <!-- Execute (hidden by default) -->
        <div id="executePanel" class="hidden mt-3 border border-gray-800 rounded p-4">
          <h3 class="font-semibold mb-3">Execute Plan — Actual Fill</h3>
          <form method="post" action="/app/plans/<?= (int)$plan['id'] ?>/execute" id="executeForm" class="space-y-3">
            <?= csrf_field($this->app ?? $app ?? null) ?>

            <div class="grid sm:grid-cols-3 gap-3">
              <div>
                <label class="block text-sm text-gray-400">Actual Entry Price</label>
                <input type="number" step="0.01" name="entry_price_actual"
                       class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2" required>
              </div>
              <div>
                <label class="block text-sm text-gray-400">Quantity (leave blank = auto by risk)</label>
                <input type="number" step="0.0001" name="qty"
                       class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2">
              </div>
              <div>
                <label class="block text-sm text-gray-400">Execution Date & Time</label>
                <input type="datetime-local" name="executed_at"
                       class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2"
                       value="<?= date('Y-m-d\TH:i') ?>">
              </div>
              <div class="sm:col-span-3">
                <label class="block text-sm text-gray-400">Market Bias for Sizing (if different today)</label>
                <select name="market_bias_execute" class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2">
                  <?php foreach (['very_bullish','mild_bullish','sideways','bearish'] as $mb): ?>
                    <option value="<?= e($mb) ?>" <?= $plan['market_bias']===$mb?'selected':'' ?>>
                      <?= str_replace('_',' ',ucfirst($mb)) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <p class="text-xs text-gray-400 mt-1">Server will size using this bias (3% / 2% / 1.5% / 1%).</p>
              </div>
            </div>

            <h4 class="font-semibold mt-2">Pre-Trade Checklist</h4>
            <ul class="space-y-2 text-sm">
              <li><label class="inline-flex items-start gap-2"><input type="checkbox" class="chk"> Risk within allowed %</label></li>
              <li><label class="inline-flex items-start gap-2"><input type="checkbox" class="chk"> Levels revalidated on current chart</label></li>
              <li><label class="inline-flex items-start gap-2"><input type="checkbox" class="chk"> No impactful news/earnings</label></li>
              <li><label class="inline-flex items-start gap-2"><input type="checkbox" class="chk"> Position size verified</label></li>
              <li><label class="inline-flex items-start gap-2"><input type="checkbox" class="chk"> Stop-loss order ready</label></li>
              <li><label class="inline-flex items-start gap-2"><input type="checkbox" class="chk"> Entry & invalidation defined</label></li>
            </ul>

            <input type="hidden" name="precheck_confirm" id="precheckConfirm" value="no">

            <div class="flex items-center gap-2">
              <input class="bg-gray-900 border border-gray-700 rounded px-3 py-2 flex-1" name="reason" placeholder="Reason (optional)">
              <button id="executeBtn" class="px-3 py-2 rounded bg-blue-600 opacity-50 cursor-not-allowed" disabled>Execute</button>
            </div>
            <p class="text-xs text-gray-400">Check all items to enable Execute.</p>
          </form>
        </div>

        <!-- Cancel (hidden by default) -->
        <div id="cancelPanel" class="hidden mt-3 border border-gray-800 rounded p-4">
          <h3 class="font-semibold mb-3">Cancel Plan</h3>
          <form method="post" action="/app/plans/<?= (int)$plan['id'] ?>/cancel" class="flex items-center gap-2">
            <?= csrf_field($this->app ?? $app ?? null) ?>
            <input class="bg-gray-900 border border-gray-700 rounded px-3 py-2 flex-1" name="reason" placeholder="Reason for cancel">
            <button class="px-3 py-2 rounded bg-red-600 hover:bg-red-500">Cancel Plan</button>
          </form>
        </div>
      </div>
    <?php endif; ?>
  </section>

  <!-- RIGHT: EVENT LOG -->
  <aside class="space-y-3">
    <div class="border border-gray-800 rounded p-4">
      <h2 class="font-semibold mb-2">Event Log</h2>
      <ol class="space-y-2 text-sm">
        <?php foreach ($events as $ev): ?>
          <li class="border border-gray-800 rounded p-2">
            <div><span class="font-semibold uppercase"><?= e($ev['action']) ?></span> — <?= e($ev['created_at']) ?></div>
            <div class="text-gray-300">Reason: <?= e($ev['reason']) ?></div>
            <?php if (!empty($ev['snapshot'])): ?>
              <details class="mt-1">
                <summary>Snapshot</summary>
                <pre class="text-xs bg-gray-900 p-2 rounded overflow-x-auto"><?= e($ev['snapshot']) ?></pre>
              </details>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
        <?php if (empty($events)): ?>
          <li class="text-gray-400">No events yet.</li>
        <?php endif; ?>
      </ol>
    </div>
  </aside>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  // Allow only one panel open at a time
  const panels = ['#adjustPanel', '#executePanel', '#cancelPanel'].map(s => document.querySelector(s)).filter(Boolean);
  document.querySelectorAll('[data-open]').forEach(btn => {
    btn.addEventListener('click', () => {
      const target = document.querySelector(btn.getAttribute('data-open'));
      panels.forEach(p => { if (p && p !== target) p.classList.add('hidden'); });
      if (target) target.classList.toggle('hidden');
    });
  });

  // Adjust panel live calcs
  const biasMap = { very_bullish:3, mild_bullish:2, sideways:1.5, bearish:1 };
  const el = id => document.getElementById(id);
  const adjMarketBias = el('adjMarketBias');
  const adjEntry  = el('adjEntry');
  const adjStop   = el('adjStop');
  const adjTarget = el('adjTarget');
  const adjCap    = el('adjCap');
  const adjRiskPct = el('adjRiskPct');
  const adjPosSize = el('adjPosSize');
  const adjCapUsed = el('adjCapUsed');
  const adjRR      = el('adjRR');
  const adjProfit  = el('adjProfit');

  function recalcAdj(){
    const riskPct = biasMap[adjMarketBias?.value] || 1;
    const entry   = parseFloat(adjEntry?.value)  || 0;
    const stop    = parseFloat(adjStop?.value)   || 0;
    const target  = parseFloat(adjTarget?.value) || 0;
    const cap     = parseFloat(adjCap?.value)    || 0;
    const riskPerShare = Math.abs(entry - stop);
    const rewardPerShare = Math.abs(target - entry);
    let posSize=0, rr=0, profit=0;
    if (riskPerShare > 0){
      posSize = (cap * (riskPct/100)) / riskPerShare;
      rr = rewardPerShare / riskPerShare;
      profit = posSize * rewardPerShare;
    }
    adjRiskPct && (adjRiskPct.value = riskPct.toFixed(2));
    adjPosSize && (adjPosSize.value = posSize.toFixed(2));
    adjCapUsed && (adjCapUsed.value = (posSize * entry).toFixed(2));
    adjRR && (adjRR.value = rr.toFixed(2));
    adjProfit && (adjProfit.value = profit.toFixed(2));
  }
  [adjMarketBias, adjEntry, adjStop, adjTarget, adjCap].forEach(x=>{
    if(x){ x.addEventListener('input', recalcAdj); x.addEventListener('change', recalcAdj); }
  });
  recalcAdj();

  // Execute checklist gating
  const execForm = document.getElementById('executeForm');
  if (execForm){
    const execBtn  = document.getElementById('executeBtn');
    const confirmField = document.getElementById('precheckConfirm');
    function checkExecReady(){
      const allChecked = Array.from(execForm.querySelectorAll('.chk')).every(c => c.checked);
      execBtn.disabled = !allChecked;
      execBtn.classList.toggle('opacity-50', !allChecked);
      execBtn.classList.toggle('cursor-not-allowed', !allChecked);
      confirmField.value = allChecked ? 'yes' : 'no';
    }
    execForm.querySelectorAll('.chk').forEach(c => c.addEventListener('change', checkExecReady));
    checkExecReady();
  }
});
</script>
