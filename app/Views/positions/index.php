<?php $title='Positions'; $tpl='positions/index'; ?>
<h1 class="text-2xl font-bold mb-4">Ongoing Positions</h1>
<table class="w-full text-left border border-gray-800 rounded overflow-hidden text-sm">
  <thead class="bg-gray-900">
    <tr><th class="p-2">#</th><th class="p-2">Symbol</th><th class="p-2">Side</th><th class="p-2">Qty</th><th class="p-2">Entry</th><th class="p-2">SL</th><th class="p-2">TP</th><th class="p-2">Actions</th></tr>
  </thead>
  <tbody>
    <?php foreach ($positions as $p): ?>
      <tr class="border-t border-gray-800 hover:bg-gray-900">
        <td class="p-2"><?= (int)$p['id'] ?></td>
        <td class="p-2"><?= e($p['symbol']) ?></td>
        <td class="p-2"><?= e($p['side']) ?></td>
        <td class="p-2"><?= e($p['qty']) ?></td>
        <td class="p-2"><?= e($p['entry_price']) ?></td>
        <td class="p-2"><?= e($p['stop_loss']) ?></td>
        <td class="p-2"><?= e($p['target_price']) ?></td>
        <td class="p-2"><a class="underline" href="/app/positions/<?= (int)$p['id'] ?>">View</a></td>
      </tr>
    <?php endforeach; ?>
    <?php if (empty($positions)): ?>
      <tr><td class="p-2 text-gray-400" colspan="8">No ongoing positions.</td></tr>
    <?php endif; ?>
  </tbody>
</table>
