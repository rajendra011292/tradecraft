<?php $title = 'Completed Positions'; $tpl='positions/completed'; ?>
<h1 class="text-2xl font-bold mb-4">Completed Trades</h1>
<table class="w-full text-left border border-gray-800 rounded overflow-hidden text-sm">
  <thead class="bg-gray-900">
    <tr><th class="p-2">#</th><th class="p-2">Symbol</th><th class="p-2">Side</th><th class="p-2">Qty</th><th class="p-2">Entry</th><th class="p-2">Exit</th><th class="p-2">RR</th><th class="p-2">Capital Used</th></tr>
  </thead>
  <tbody>
    <?php foreach ($positions as $p): ?>
      <tr class="border-t border-gray-800 hover:bg-gray-900">
        <td class="p-2"><?= (int)$p['id'] ?></td>
        <td class="p-2"><?= e($p['symbol']) ?></td>
        <td class="p-2"><?= e($p['side']) ?></td>
        <td class="p-2"><?= e($p['qty']) ?></td>
        <td class="p-2"><?= e($p['entry_price']) ?></td>
        <td class="p-2"><?= e($p['updated_at']) ?></td>
        <td class="p-2"><?= e($p['rr_at_entry']) ?></td>
        <td class="p-2"><?= e($p['capital_used']) ?></td>
      </tr>
    <?php endforeach; ?>
    <?php if (empty($positions)): ?>
      <tr><td colspan="8" class="p-2 text-gray-400">No completed trades yet.</td></tr>
    <?php endif; ?>
  </tbody>
</table>
