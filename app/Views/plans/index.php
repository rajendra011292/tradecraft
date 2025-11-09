<?php $title='Plans'; $tpl='plans/index'; ?>
<h1 class="text-2xl font-bold mb-4">Plans</h1>
<table class="w-full text-left border border-gray-800 rounded overflow-hidden">
  <thead class="bg-gray-900">
    <tr>
      <th class="p-2">#</th><th class="p-2">Symbol</th><th class="p-2">Side</th><th class="p-2">Status</th><th class="p-2">Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($plans as $p): ?>
      <tr class="border-t border-gray-800 hover:bg-gray-900">
        <td class="p-2"><?= (int)$p['id'] ?></td>
        <td class="p-2"><?= e($p['symbol']) ?></td>
        <td class="p-2"><?= e($p['side']) ?></td>
        <td class="p-2"><?= e($p['status']) ?></td>
        <td class="p-2"><a class="underline" href="/app/plans/<?= (int)$p['id'] ?>">View</a></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
