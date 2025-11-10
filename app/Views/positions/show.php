<?php
/** @var array $pos */
$title = 'Position #'.$pos['id'];
$tpl   = 'positions/show';
$ongoing = ($pos['status'] === 'ongoing');
?>
<a class="underline text-sm" href="/app/positions">&larr; Back</a>
<h1 class="text-2xl font-bold mb-4">Position #<?= (int)$pos['id'] ?> — <?= e($pos['symbol']) ?> (<?= e($pos['side']) ?>)</h1>

<div class="grid lg:grid-cols-3 gap-6">
  <!-- LEFT: info + manage -->
  <div class="lg:col-span-2 space-y-4">

    <!-- Snapshot -->
    <section class="border border-zinc-800 rounded p-4">
      <h2 class="font-semibold text-lg mb-3">Snapshot</h2>
      <div class="grid sm:grid-cols-3 gap-4 text-sm">
        <div><div class="text-zinc-400">Status</div><div class="font-semibold"><?= e(ucfirst($pos['status'])) ?></div></div>
        <div><div class="text-zinc-400">Qty</div><div class="font-semibold"><?= e($pos['qty']) ?></div></div>
        <div><div class="text-zinc-400">Executed At</div><div class="font-semibold"><?= e($pos['executed_at']) ?></div></div>
        <div><div class="text-zinc-400">Entry</div><div class="font-semibold"><?= e($pos['entry_price']) ?></div></div>
        <div><div class="text-zinc-400">SL</div><div class="font-semibold"><?= e($pos['stop_loss']) ?></div></div>
        <div><div class="text-zinc-400">TP</div><div class="font-semibold"><?= e($pos['target_price']) ?></div></div>
        <div><div class="text-zinc-400">Capital Used</div><div class="font-semibold">₹<?= e(number_format((float)$pos['capital_used'],2)) ?></div></div>
        <div><div class="text-zinc-400">RR @ Entry</div><div class="font-semibold"><?= e(number_format((float)$pos['rr_at_entry'],2)) ?></div></div>
        <div><div class="text-zinc-400">Avg Exit</div><div class="font-semibold"><?= isset($pos['avg_exit_price']) ? e($pos['avg_exit_price']) : '—' ?></div></div>
      </div>
    </section>

    <?php if (!empty($legs)): 
  $partialLegs = array_values(array_filter($legs, fn($l) => ($l['leg_type'] ?? '') === 'partial'));
  if (!empty($partialLegs)): ?>
  <section class="border border-zinc-800 rounded p-4">
    <h2 class="font-semibold text-lg mb-3">Partial closes</h2>
    <div class="overflow-x-auto">
      <table class="w-full text-xs border border-zinc-800 rounded overflow-hidden">
        <thead class="bg-zinc-900">
          <tr>
            <th class="p-1 text-left">#</th>
            <th class="p-1 text-left">Qty</th>
            <th class="p-1 text-left">Exit</th>
            <th class="p-1 text-left">Fees</th>
            <th class="p-1 text-left">Realized P/L</th>
            <th class="p-1 text-left">Exited At</th>
            <th class="p-1 text-left">Note</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($partialLegs as $i => $l): 
            $plClass = ((float)$l['realized_pl'] > 0) ? 'text-emerald-400' : (((float)$l['realized_pl'] < 0) ? 'text-red-400' : 'text-zinc-300'); ?>
            <tr class="border-t border-zinc-800">
              <td class="p-1"><?= $i+1 ?></td>
              <td class="p-1"><?= e($l['qty']) ?></td>
              <td class="p-1"><?= e($l['exit_price']) ?></td>
              <td class="p-1"><?= e($l['fees']) ?></td>
              <td class="p-1 <?= $plClass ?>">₹<?= e(number_format((float)$l['realized_pl'],2)) ?></td>
              <td class="p-1 whitespace-nowrap"><?= e($l['exited_at']) ?></td>
              <td class="p-1"><?= e($l['note'] ?? '') ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
<?php endif; endif; ?>


    <!-- Manage (only when ongoing) -->
    <?php if ($ongoing): ?>
    <section class="border border-zinc-800 rounded p-4 space-y-4">
      <h2 class="font-semibold text-lg">Manage</h2>

      <details class="rounded border border-zinc-800">
        <summary class="px-3 py-2 cursor-pointer">Adjust SL / TP & Toggles</summary>
        <form method="post" action="/app/positions/<?= (int)$pos['id'] ?>/adjust" class="p-3 grid sm:grid-cols-3 gap-3">
          <?= csrf_field($this->app ?? $app ?? null) ?>
          <div>
            <label class="block text-sm text-zinc-400">Stop Loss</label>
            <input class="w-full bg-zinc-900 border border-zinc-700 rounded px-3 py-2" name="stop_loss" type="number" step="0.01" value="<?= e($pos['stop_loss']) ?>">
          </div>
          <div>
            <label class="block text-sm text-zinc-400">Target Price</label>
            <input class="w-full bg-zinc-900 border border-zinc-700 rounded px-3 py-2" name="target_price" type="number" step="0.01" value="<?= e($pos['target_price']) ?>">
          </div>
          <div class="sm:col-span-3 grid sm:grid-cols-4 gap-3 mt-2">
            <label class="inline-flex items-center gap-2">
              <input type="checkbox" name="trailing_enabled" <?= !empty($pos['trailing_enabled']) ? 'checked' : '' ?>>
              <span class="text-sm">Trailing enabled</span>
            </label>
            <select name="trailing_type" class="bg-zinc-900 border border-zinc-700 rounded px-3 py-2">
              <option value="">Type</option>
              <?php foreach (['fixed','percent','ATR'] as $tt): ?>
                <option value="<?= e($tt) ?>" <?= ($pos['trailing_type'] ?? '')===$tt ? 'selected':'' ?>><?= e($tt) ?></option>
              <?php endforeach; ?>
            </select>
            <input name="trailing_value" type="number" step="0.0001"
                   class="bg-zinc-900 border border-zinc-700 rounded px-3 py-2"
                   placeholder="Trailing value" value="<?= e($pos['trailing_value'] ?? '') ?>">
            <label class="inline-flex items-center gap-2">
              <input type="checkbox" name="breakeven_enabled" <?= !empty($pos['breakeven_enabled']) ? 'checked' : '' ?>>
              <span class="text-sm">Breakeven rule</span>
            </label>
          </div>
          <div class="sm:col-span-3">
            <button class="px-3 py-2 rounded bg-amber-600 hover:bg-amber-500">Save Risk Controls</button>
          </div>
        </form>
      </details>

      <details class="rounded border border-zinc-800">
        <summary class="px-3 py-2 cursor-pointer">Partial Close</summary>
        <form method="post" action="/app/positions/<?= (int)$pos['id'] ?>/partial-close" class="p-3 grid sm:grid-cols-5 gap-3">
          <?= csrf_field($this->app ?? $app ?? null) ?>
          <input class="bg-zinc-900 border border-zinc-700 rounded px-3 py-2" name="qty"   type="number" step="0.0001" placeholder="Qty">
          <input class="bg-zinc-900 border border-zinc-700 rounded px-3 py-2" name="price" type="number" step="0.01"   placeholder="Price">
          <input class="bg-zinc-900 border border-zinc-700 rounded px-3 py-2" name="fees"  type="number" step="0.01"   placeholder="Fees (optional)">
          <input class="bg-zinc-900 border border-zinc-700 rounded px-3 py-2 sm:col-span-2" name="note"  placeholder="Note (optional)">
          <div class="sm:col-span-5">
            <button class="px-3 py-2 rounded bg-blue-600 hover:bg-blue-500">Record Partial</button>
          </div>
        </form>
      </details>

      <details class="rounded border border-zinc-800">
        <summary class="px-3 py-2 cursor-pointer">Full Close</summary>
        <form method="post" action="/app/positions/<?= (int)$pos['id'] ?>/close" class="p-3 grid sm:grid-cols-4 gap-3">
          <?= csrf_field($this->app ?? $app ?? null) ?>
          <input class="bg-zinc-900 border border-zinc-700 rounded px-3 py-2" name="price" type="number" step="0.01" placeholder="Close Price">
          <input class="bg-zinc-900 border border-zinc-700 rounded px-3 py-2" name="fees"  type="number" step="0.01" placeholder="Fees (optional)">
          <input class="bg-zinc-900 border border-zinc-700 rounded px-3 py-2 sm:col-span-2" name="note"  placeholder="Note (optional)">
          <div class="sm:col-span-4">
            <button class="px-3 py-2 rounded bg-red-600 hover:bg-red-500">Close Position</button>
          </div>
        </form>
      </details>
    </section>
    <?php endif; ?>

    <?php if (!$ongoing): ?>
    <section class="border border-zinc-800 rounded p-4">
      <h2 class="font-semibold text-lg mb-2">Completed Summary</h2>
      <div class="grid sm:grid-cols-3 gap-4 text-sm">
        <div><div class="text-zinc-400">Completed At</div><div class="font-semibold"><?= e($pos['completed_at'] ?? '—') ?></div></div>
        <div><div class="text-zinc-400">Avg Exit Price</div><div class="font-semibold"><?= e($pos['avg_exit_price'] ?? '—') ?></div></div>
        <div><div class="text-zinc-400">Net Realized P/L</div><div class="font-semibold">₹<?= e(number_format((float)($pos['realized_pl_total'] ?? 0),2)) ?></div></div>
      </div>
      <p class="text-xs text-zinc-400 mt-2">Detailed legs are available on the Completed page.</p>
    </section>
    <?php endif; ?>

  </div>

  <!-- RIGHT: helpful notes -->
  <aside class="space-y-3">
    <?php if (!empty($events)): ?>
<section class="border border-zinc-800 rounded p-4 text-sm">
  <h2 class="font-semibold text-lg mb-2">Event Log</h2>
  <ol class="space-y-2">
    <?php foreach ($events as $ev): ?>
      <li class="border border-zinc-800 rounded p-2">
        <div class="flex items-center justify-between">
          <span class="font-semibold uppercase"><?= e($ev['action']) ?></span>
          <span class="text-xs text-zinc-400"><?= e($ev['created_at']) ?></span>
        </div>
        <?php if (!empty($ev['reason'])): ?>
          <div class="text-zinc-300">Reason: <?= e($ev['reason']) ?></div>
        <?php endif; ?>
        <?php if (!empty($ev['snapshot'])): ?>
          <details class="mt-1">
            <summary>Snapshot</summary>
            <pre class="text-xs bg-zinc-900 p-2 rounded overflow-x-auto"><?= e($ev['snapshot']) ?></pre>
          </details>
        <?php endif; ?>
      </li>
    <?php endforeach; ?>
  </ol>
</section>
<?php else: ?>
<section class="border border-zinc-800 rounded p-4 text-sm">
  <h2 class="font-semibold text-lg mb-2">Event Log</h2>
  <p class="text-zinc-400">No events yet.</p>
</section>
<?php endif; ?>

    <div class="border border-zinc-800 rounded p-4 text-sm">
      <h3 class="font-semibold mb-2">Hints</h3>
      <ul class="list-disc list-inside space-y-1 text-zinc-300">
        <li>Adjust only SL/TP and toggles; entry/symbol/side are locked.</li>
        <li>Use Partial Close to scale out; Full Close completes the trade.</li>
        <li>Trailing rules are applied by your discretion (server stores flags).</li>
      </ul>
    </div>
  </aside>
</div>
