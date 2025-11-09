<?php $title='Position #'.$pos['id']; $tpl='positions/show'; ?>
<a class="underline text-sm" href="/app/positions">&larr; Back</a>
<h1 class="text-2xl font-bold mb-4">Position #<?= (int)$pos['id'] ?> — <?= e($pos['symbol']) ?> (<?= e($pos['side']) ?>)</h1>

<div class="grid lg:grid-cols-3 gap-6">
  <div class="lg:col-span-2 space-y-4">
    <section class="border border-gray-800 rounded p-4">
      <h2 class="font-semibold text-lg mb-3">Overview</h2>
      <div class="grid sm:grid-cols-3 gap-4 text-sm">
        <div><div class="text-gray-400">Status</div><div class="font-semibold"><?= e($pos['status']) ?></div></div>
        <div><div class="text-gray-400">Qty</div><div class="font-semibold"><?= e($pos['qty']) ?></div></div>
        <div><div class="text-gray-400">Executed At</div><div class="font-semibold"><?= e($pos['executed_at']) ?></div></div>
        <div><div class="text-gray-400">Entry</div><div class="font-semibold"><?= e($pos['entry_price']) ?></div></div>
        <div><div class="text-gray-400">SL</div><div class="font-semibold"><?= e($pos['stop_loss']) ?></div></div>
        <div><div class="text-gray-400">TP</div><div class="font-semibold"><?= e($pos['target_price']) ?></div></div>
        <div><div class="text-gray-400">Capital Used</div><div class="font-semibold"><?= e($pos['capital_used']) ?></div></div>
        <div><div class="text-gray-400">RR at Entry</div><div class="font-semibold"><?= e($pos['rr_at_entry']) ?></div></div>
        <div><div class="text-gray-400">Est. Profit (Entry)</div><div class="font-semibold"><?= e($pos['est_profit_at_entry']) ?></div></div>
      </div>
    </section>

    <?php if ($pos['status'] === 'ongoing'): ?>
    <section class="border border-gray-800 rounded p-4 space-y-4">
      <h2 class="font-semibold text-lg">Manage</h2>

      <details class="rounded border border-gray-800">
        <summary class="px-3 py-2 cursor-pointer">Adjust SL/TP</summary>
        <form method="post" action="/app/positions/<?= (int)$pos['id'] ?>/adjust" class="p-3 grid sm:grid-cols-3 gap-3">
          <?= csrf_field($this->app ?? $app ?? null) ?>
          <input class="bg-gray-900 border border-gray-700 rounded px-3 py-2" name="stop_loss" type="number" step="0.01" value="<?= e($pos['stop_loss']) ?>">
          <input class="bg-gray-900 border border-gray-700 rounded px-3 py-2" name="target_price" type="number" step="0.01" value="<?= e($pos['target_price']) ?>">
          <button class="px-3 py-2 rounded bg-yellow-600 hover:bg-yellow-500">Save</button>
        </form>
      </details>

      <details class="rounded border border-gray-800">
        <summary class="px-3 py-2 cursor-pointer">Partial Close</summary>
        <form method="post" action="/app/positions/<?= (int)$pos['id'] ?>/partial-close" class="p-3 grid sm:grid-cols-4 gap-3">
          <?= csrf_field($this->app ?? $app ?? null) ?>
          <input class="bg-gray-900 border border-gray-700 rounded px-3 py-2" name="qty"   type="number" step="0.0001" placeholder="Qty">
          <input class="bg-gray-900 border border-gray-700 rounded px-3 py-2" name="price" type="number" step="0.01"   placeholder="Price">
          <input class="bg-gray-900 border border-gray-700 rounded px-3 py-2" name="fees"  type="number" step="0.01"   placeholder="Fees">
          <button class="px-3 py-2 rounded bg-blue-600 hover:bg-blue-500">Record</button>
        </form>
      </details>

      <details class="rounded border border-gray-800">
        <summary class="px-3 py-2 cursor-pointer">Full Close</summary>
        <form method="post" action="/app/positions/<?= (int)$pos['id'] ?>/close" class="p-3 grid sm:grid-cols-3 gap-3">
          <?= csrf_field($this->app ?? $app ?? null) ?>
          <input class="bg-gray-900 border border-gray-700 rounded px-3 py-2" name="price" type="number" step="0.01" placeholder="Close Price">
          <input class="bg-gray-900 border border-gray-700 rounded px-3 py-2" name="fees"  type="number" step="0.01" placeholder="Fees">
          <button class="px-3 py-2 rounded bg-red-600 hover:bg-red-500">Close Position</button>
        </form>
      </details>
    </section>
    <?php endif; ?>
  </div>

  <aside>
    <!-- You can add a small event log or upcoming tasks here later -->
  </aside>
</div>
