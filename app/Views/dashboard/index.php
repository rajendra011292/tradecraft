<?php $title='Dashboard'; $tpl='dashboard/index'; ?>
<h1 class="text-2xl font-bold mb-4">Dashboard</h1>
<div class="mb-4">
  <a class="px-3 py-2 rounded bg-green-600 hover:bg-green-500" href="/app/plans/create">Create Plan</a>
</div>
<div class="grid sm:grid-cols-2 gap-4">
  <?php foreach ($plans as $p): ?>
    <a href="/app/plans/<?= (int)$p['id'] ?>" class="block rounded border border-gray-800 p-4 hover:bg-gray-900">
      <div class="font-semibold"><?= e($p['symbol']) ?> (<?= e($p['side']) ?>)</div>
      <div class="text-sm text-gray-400">Status: <?= e($p['status']) ?> • Target <?= e($p['target_price']) ?> • SL <?= e($p['stop_loss']) ?></div>
    </a>
  <?php endforeach; ?>
  <?php if (empty($plans)): ?>
    <div class="text-gray-400">No plans yet.</div>
  <?php endif; ?>
</div>
