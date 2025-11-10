<?php
/** @var array $positions */
$title = 'Ongoing Positions';
$tpl   = 'positions/index';
?>
<div class="flex items-center justify-between mb-4">
  <h1 class="text-2xl font-bold">Ongoing Positions</h1>
  <div class="flex items-center gap-2">
    <a href="/app/positions/completed" class="px-3 py-2 bg-zinc-800 hover:bg-zinc-700 rounded text-sm">Completed</a>
    <a href="/app/plans" class="px-3 py-2 bg-zinc-800 hover:bg-zinc-700 rounded text-sm">Plans</a>
  </div>
</div>

<div class="flex flex-wrap items-center gap-2 mb-3 text-sm">
  <div class="relative">
    <input id="search" type="text" placeholder="Search symbol..."
           class="pl-8 pr-3 py-1.5 rounded bg-zinc-900 border border-zinc-800 focus:border-zinc-600 outline-none">
    <span class="absolute left-2 top-1.5 text-zinc-500">🔎</span>
  </div>
  <select id="sideFilter" class="px-2 py-1.5 rounded bg-zinc-900 border border-zinc-800">
    <option value="">All sides</option>
    <option value="long">Long</option>
    <option value="short">Short</option>
  </select>
</div>

<div class="overflow-x-auto border border-zinc-800 rounded">
  <table id="posTable" class="min-w-full text-sm">
    <thead class="bg-zinc-900">
      <tr>
        <th class="p-2 text-left">#</th>
        <th class="p-2 text-left">Symbol</th>
        <th class="p-2 text-left">Side</th>
        <th class="p-2 text-left">Qty</th>
        <th class="p-2 text-left">Entry</th>
        <th class="p-2 text-left">SL</th>
        <th class="p-2 text-left">TP</th>
        <th class="p-2 text-left">RR@Entry</th>
        <th class="p-2 text-left">Capital</th>
        <th class="p-2 text-left">Executed</th>
        <th class="p-2 text-left">Actions</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-zinc-800">
      <?php foreach ($positions as $p): ?>
      <tr class="hover:bg-zinc-900"
          data-symbol="<?= e(strtolower($p['symbol'])) ?>"
          data-side="<?= e($p['side']) ?>">
        <td class="p-2"><?= (int)$p['id'] ?></td>
        <td class="p-2 font-semibold"><?= e($p['symbol']) ?></td>
        <td class="p-2"><?= e(ucfirst($p['side'])) ?></td>
        <td class="p-2"><?= e($p['qty']) ?></td>
        <td class="p-2"><?= e($p['entry_price']) ?></td>
        <td class="p-2"><?= e($p['stop_loss']) ?></td>
        <td class="p-2"><?= e($p['target_price']) ?></td>
        <td class="p-2"><?= e(number_format((float)$p['rr_at_entry'],2)) ?></td>
        <td class="p-2">₹<?= e(number_format((float)$p['capital_used'],2)) ?></td>
        <td class="p-2 whitespace-nowrap"><?= e(date('Y-m-d H:i', strtotime($p['executed_at']))) ?></td>
        <td class="p-2"><a class="underline" href="/app/positions/<?= (int)$p['id'] ?>">View</a></td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($positions)): ?>
      <tr><td class="p-3 text-zinc-400" colspan="11">No ongoing positions.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const rows = Array.from(document.querySelectorAll('#posTable tbody tr'));
  const search = document.getElementById('search');
  const side = document.getElementById('sideFilter');
  function apply() {
    const q = (search.value || '').toLowerCase().trim();
    const s = side.value;
    rows.forEach(r => {
      const sym = r.getAttribute('data-symbol') || '';
      const sd  = r.getAttribute('data-side') || '';
      const okQ = !q || sym.includes(q);
      const okS = !s || sd === s;
      r.style.display = (okQ && okS) ? '' : 'none';
    });
  }
  search.addEventListener('input', apply);
  side.addEventListener('change', apply);
});
</script>
