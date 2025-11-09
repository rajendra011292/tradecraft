<?php $title = 'Create Trade Plan';
$tpl = 'plans/create'; ?>
<h1 class="text-2xl font-bold mb-4">Create Trade Plan</h1>

<form method="post" action="/app/plans" id="planForm" class="space-y-8">
  <?= csrf_field($this->app ?? $app ?? null) ?>

  <!-- A. Basic Info -->
  <section class="border border-gray-800 rounded p-4 space-y-4">
    <h2 class="font-semibold text-lg">A. Basic Info</h2>

    <div class="grid sm:grid-cols-2 gap-4">
      <div>
        <label class="block">Symbol</label>
        <input name="symbol" class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2" required>
      </div>

      <div>
        <label class="block">Sector</label>
        <select name="sector" class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2">
          <option value="auto">Auto</option>
          <option value="banking">Banking</option>
          <option value="it">IT</option>
          <option value="energy">Energy</option>
          <option value="metal">Metal</option>
        </select>
      </div>

      <input type="hidden" name="plan_date" value="<?= date('Y-m-d') ?>">

      <div>
        <label class="block">Timeframe</label>
        <select name="timeframe" class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2">
          <option value="intraday">Intraday</option>
          <option value="swing">Swing</option>
          <option value="positional">Positional</option>
        </select>
      </div>
      <div>
        <label class="block">Side</label>
        <select name="side" class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2">
          <option value="long">Long</option>
          <option value="short">Short</option>
        </select>
      </div>

      <div class="sm:col-span-2">
        <label class="block">Market Condition (Bias)</label>
        <select id="marketBias" name="market_bias" class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2">
          <option value="very_bullish">Very Bullish</option>
          <option value="mild_bullish">Mild Bullish</option>
          <option value="sideways">Sideways</option>
          <option value="bearish">Bearish</option>
        </select>
        <p class="text-xs text-gray-400 mt-1">Auto-sets allowed risk %</p>
      </div>
    </div>
  </section>

  <!-- B. Entry Setup -->
  <section class="border border-gray-800 rounded p-4 space-y-4">
    <h2 class="font-semibold text-lg">B. Entry Setup</h2>

    <div class="grid sm:grid-cols-2 gap-4">
      <div>
        <label class="block">Setup Type</label>
        <select name="setup_type" class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2">
          <option value="breakout">Breakout</option>
          <option value="pullback">Pullback</option>
          <option value="reversal">Reversal</option>
          <option value="range_play">Range Play</option>
        </select>
      </div>

      <div>
        <label class="block">Entry Price</label>
        <input type="number" step="0.01" name="entry_price" id="entryPrice" class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2" required>
      </div>

      <div>
        <label class="block">Target Price</label>
        <input type="number" step="0.01" name="target_price" id="targetPrice" class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2" required>
      </div>

      <div>
        <label class="block">Stop Loss</label>
        <input type="number" step="0.01" name="stop_loss" id="stopLoss" class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2" required>
      </div>

      <div class="sm:col-span-2">
        <label class="block">Capital Allocated</label>
        <input type="number" step="0.01" name="capital_allocated" id="capitalAllocated" class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2">
      </div>
    </div>
  </section>

  <!-- C. Risk Management -->
  <section class="border border-gray-800 rounded p-4 space-y-4">
    <h2 class="font-semibold text-lg">C. Risk Management</h2>

    <div class="grid sm:grid-cols-2 gap-4">
      <div>
        <label class="block">Risk %</label>
        <input type="number" step="0.1" name="risk_percent" id="riskPercent" class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2" readonly>
      </div>

      <div>
        <label class="block">Position Size</label>
        <input type="number" step="0.01" name="position_size" id="positionSize" class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2" readonly>
      </div>

      <div>
        <label class="block">Risk-Reward Ratio</label>
        <input type="number" step="0.01" name="rr_ratio" id="rrRatio" class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2" readonly>
      </div>

      <div>
        <label class="block">Estimated Profit / Loss</label>
        <input type="number" step="0.01" name="est_profit" id="estProfit" class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2" readonly>
      </div>
      <div class="sm:col-span-2">
        <label class="block">Capital Used (auto)</label>
        <input type="number" step="0.01" id="capitalUsed" class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2" readonly>
      </div>
    </div>
  </section>

  <!-- D. Trade Thesis -->
  <section class="border border-gray-800 rounded p-4 space-y-4">
    <h2 class="font-semibold text-lg">D. Trade Thesis</h2>

    <div class="grid sm:grid-cols-2 gap-4">
      <div class="sm:col-span-2">
        <label class="block">Trade Rationale / Notes</label>
        <textarea name="notes" class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2" rows="3"></textarea>
      </div>

      <div>
        <label class="block">Confidence (1-10)</label>
        <input type="number" name="confidence" min="1" max="10" class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2">
      </div>

      <div>
        <label class="block">Emotion Level (1-10)</label>
        <input type="number" name="emotion" min="1" max="10" class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2">
      </div>
    </div>
  </section>

  <!-- E. Submit -->
  <section class="border border-gray-800 rounded p-4">
    <label class="block mb-2">Reason (for event log)</label>
    <input name="reason" class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2 mb-4" placeholder="Why this trade plan?">
    <button class="px-4 py-2 rounded bg-green-600 hover:bg-green-500">Save Plan</button>
  </section>
</form>

<!-- JS: Auto risk calculation -->
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const biasMap = {
      very_bullish: 3,
      mild_bullish: 2,
      sideways: 1.5,
      bearish: 1
    };

    const marketBias = document.getElementById('marketBias');
    const riskPercent = document.getElementById('riskPercent');
    const entryPrice = document.getElementById('entryPrice');
    const stopLoss = document.getElementById('stopLoss');
    const targetPrice = document.getElementById('targetPrice');
    const capitalAllocated = document.getElementById('capitalAllocated');
    const positionSize = document.getElementById('positionSize');
    const rrRatio = document.getElementById('rrRatio');
    const estProfit = document.getElementById('estProfit');
    const capitalUsed = document.getElementById('capitalUsed');

    function recalc() {
      const riskPct = biasMap[marketBias.value] || 1;
      riskPercent.value = riskPct.toFixed(2);

      const entry = parseFloat(entryPrice.value) || 0;
      const stop = parseFloat(stopLoss.value) || 0;
      const target = parseFloat(targetPrice.value) || 0;
      const capital = parseFloat(capitalAllocated.value) || 0;

      const riskPerShare = Math.abs(entry - stop);
      const rewardPerShare = Math.abs(target - entry);

      let posSize = 0,
        rr = 0,
        profit = 0;

      if (riskPerShare > 0) {
        posSize = (capital * (riskPct / 100)) / riskPerShare;
        rr = rewardPerShare / riskPerShare;
        profit = posSize * rewardPerShare;
      }

      positionSize.value = posSize.toFixed(2);
      rrRatio.value = rr.toFixed(2);
      estProfit.value = profit.toFixed(2);
      const used = posSize * entry;
      capitalUsed.value = used.toFixed(2);
    }

    [marketBias, entryPrice, stopLoss, targetPrice, capitalAllocated].forEach(el => {
      el.addEventListener('input', recalc);
      el.addEventListener('change', recalc);
    });

    recalc();
  });
</script>