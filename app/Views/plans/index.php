<?php
/** @var array $plans */
$title = 'Plans';
$tpl   = 'plans/index';

// small helpers (view-only)
function badge($status){
  $map = [
    'planned'  => 'bg-emerald-900/40 text-emerald-300 ring-1 ring-emerald-700/50',
    'executed' => 'bg-blue-900/40 text-blue-300 ring-1 ring-blue-700/50',
    'canceled' => 'bg-red-900/40 text-red-300 ring-1 ring-red-700/50',
    'expired'  => 'bg-zinc-800 text-zinc-300 ring-1 ring-zinc-700/60',
  ];
  $cls = $map[$status] ?? 'bg-zinc-800 text-zinc-300 ring-1 ring-zinc-700/60';
  return "<span class=\"px-2 py-0.5 rounded-full text-xs font-semibold $cls\">".ucfirst($status)."</span>";
}
function bias_icon($bias){
  return [
    'very_bullish' => '📈',
    'mild_bullish' => '📈',
    'sideways'     => '➡️',
    'bearish'      => '📉',
  ][$bias] ?? '➡️';
}
function ready_to_execute($p){
  // Simple readiness: planned + has entry/stop/target + (optional) today-or-older
  if (($p['status'] ?? '') !== 'planned') return false;
  if ((float)$p['entry_price'] <= 0 || (float)$p['stop_loss'] <= 0 || (float)$p['target_price'] <= 0) return false;
  $pd = !empty($p['plan_date']) ? strtotime($p['plan_date']) : 0;
  return $pd > 0 && $pd <= strtotime('today');
}
function rr_ratio($p){
  $risk  = abs((float)$p['entry_price'] - (float)$p['stop_loss']);
  $reward= abs((float)$p['target_price'] - (float)$p['entry_price']);
  return $risk > 0 ? $reward / $risk : 0.0;
}
function est_profit($p){
  $qtyEst = (float)($p['position_size'] ?? 0); // if you store calc; else derive from capital_used/entry_price
  if ($qtyEst <= 0 && !empty($p['capital_used']) && (float)$p['entry_price'] > 0) {
    $qtyEst = (float)$p['capital_used'] / (float)$p['entry_price'];
  }
  return $qtyEst * ((float)$p['target_price'] - (float)$p['entry_price']);
}
function percent($num, $den){
  if ($den <= 0) return '—';
  return number_format(($num / $den) * 100, 1) . '%';
}

// Capital utilization baseline: sum of capital_allocated for all planned (fallback: sum for all)
$totalCap = 0.0;
foreach ($plans as $pp) {
  if (($pp['status'] ?? '') === 'planned') $totalCap += (float)($pp['capital_allocated'] ?? 0);
}
if ($totalCap <= 0) {
  foreach ($plans as $pp) $totalCap += (float)($pp['capital_allocated'] ?? 0);
}
?>
<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
  <h1 class="text-2xl font-bold">Plans</h1>

  <div class="flex flex-wrap gap-2">
    <a href="/app/plans/create" class="px-3 py-2 bg-emerald-700 hover:bg-emerald-600 rounded text-sm">+ New Plan</a>
    <a href="/app/positions" class="px-3 py-2 bg-blue-700 hover:bg-blue-600 rounded text-sm">Positions</a>
    <a href="/app/positions/completed" class="px-3 py-2 bg-zinc-800 hover:bg-zinc-700 rounded text-sm">Completed</a>
  </div>
</div>

<!-- Filters -->
<div class="flex flex-wrap items-center gap-2 mb-3 text-sm">
  <button data-filter="all" class="filter-btn px-3 py-1.5 rounded bg-zinc-800 hover:bg-zinc-700">All</button>
  <button data-filter="planned" class="filter-btn px-3 py-1.5 rounded bg-emerald-900/40 hover:bg-emerald-900/60">Planned</button>
  <button data-filter="executed" class="filter-btn px-3 py-1.5 rounded bg-blue-900/40 hover:bg-blue-900/60">Executed</button>
  <button data-filter="canceled" class="filter-btn px-3 py-1.5 rounded bg-red-900/40 hover:bg-red-900/60">Canceled</button>
  <button data-filter="expired" class="filter-btn px-3 py-1.5 rounded bg-zinc-800 hover:bg-zinc-700">Expired</button>

  <div class="ml-auto relative">
    <input id="search" type="text" placeholder="Search symbol / notes..."
           class="pl-9 pr-3 py-1.5 rounded bg-zinc-900 border border-zinc-800 focus:border-zinc-600 outline-none">
    <span class="absolute left-2 top-1.5 text-zinc-500">🔎</span>
  </div>
</div>

<!-- Table -->
<div class="overflow-x-auto border border-zinc-800 rounded">
  <table id="plansTable" class="min-w-full text-sm">
    <thead class="bg-zinc-900">
      <tr>
        <th class="p-2 text-left">#</th>
        <th class="p-2 text-left sortable" data-key="symbol">Symbol</th>
        <th class="p-2 text-left">Bias</th>
        <th class="p-2 text-left sortable" data-key="rr">RR</th>
        <th class="p-2 text-left sortable" data-key="estp">Est. ₹</th>
        <th class="p-2 text-left sortable" data-key="cap">Capital</th>
        <th class="p-2 text-left">Util%</th>
        <th class="p-2 text-left">Conf.</th>
        <th class="p-2 text-left">Emotion</th>
        <th class="p-2 text-left sortable" data-key="status">Status</th>
        <th class="p-2 text-left">Setup</th>
        <th class="p-2 text-left sortable" data-key="created">Created</th>
        <th class="p-2 text-left sortable" data-key="updated">Updated</th>
        <th class="p-2 text-left">Links</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-zinc-800">
      <?php foreach ($plans as $p):
        $rr  = rr_ratio($p);
        $est = est_profit($p);
        $cap = (float)($p['capital_allocated'] ?? 0);
        $utilPct = $totalCap > 0 ? ($cap / $totalCap) * 100 : 0;
        $ready = ready_to_execute($p);
        $rowCls = $ready ? 'bg-emerald-950/30 hover:bg-emerald-950/50' : 'hover:bg-zinc-900';
        $setupTip = "Entry: ".number_format((float)$p['entry_price'],2)
                  ." | SL: ".number_format((float)$p['stop_loss'],2)
                  ." | Tgt: ".number_format((float)$p['target_price'],2);
      ?>
      <tr class="<?= $rowCls ?>"
          data-status="<?= e($p['status']) ?>"
          data-symbol="<?= e(strtolower($p['symbol'])) ?>"
          data-notes="<?= e(strtolower($p['notes'] ?? '')) ?>"
          data-rr="<?= number_format($rr, 4, '.', '') ?>"
          data-estp="<?= number_format($est, 4, '.', '') ?>"
          data-cap="<?= number_format($cap, 4, '.', '') ?>"
          data-created="<?= e($p['created_at']) ?>"
          data-updated="<?= e($p['updated_at']) ?>">
        <td class="p-2 align-top"><?= (int)$p['id'] ?></td>

        <td class="p-2 align-top">
          <div class="flex items-center gap-2">
            <a href="/app/plans/<?= (int)$p['id'] ?>" class="font-semibold underline"><?= e($p['symbol']) ?></a>
            <?php if ($ready): ?>
              <span class="text-emerald-400 text-xs" title="Checklist ready / date reached">✔︎</span>
            <?php endif; ?>
          </div>
        </td>

        <td class="p-2 align-top" title="<?= e($p['market_bias']) ?>">
          <?= bias_icon($p['market_bias']) ?>
        </td>

        <td class="p-2 align-top">
          <span class="font-medium"><?= number_format($rr, 2) ?></span>
          <?php if ($rr >= 2): ?>
            <span class="ml-1 text-emerald-400 text-xs">▲</span>
          <?php endif; ?>
        </td>

        <td class="p-2 align-top"><?= number_format($est, 2) ?></td>

        <td class="p-2 align-top">₹<?= number_format($cap, 2) ?></td>

        <td class="p-2 align-top">
          <?php if ($totalCap > 0): ?>
            <div class="flex items-center gap-2">
              <div class="w-16 h-2 bg-zinc-800 rounded">
                <div class="h-2 bg-sky-600 rounded" style="width: <?= max(0,min(100,$utilPct)) ?>%"></div>
              </div>
              <span><?= number_format($utilPct,1) ?>%</span>
            </div>
          <?php else: ?>—<?php endif; ?>
        </td>

        <td class="p-2 align-top">
          <?php $c = (int)($p['confidence'] ?? 0); ?>
          <div class="w-20 h-2 bg-zinc-800 rounded" title="Confidence: <?= $c ?>/10">
            <div class="h-2 rounded <?= $c>=7?'bg-emerald-500':($c>=4?'bg-amber-500':'bg-red-500') ?>" style="width: <?= max(0,min(100,$c*10)) ?>%"></div>
          </div>
        </td>

        <td class="p-2 align-top">
          <?php $e = (int)($p['emotion'] ?? 0); ?>
          <div class="w-20 h-2 bg-zinc-800 rounded" title="Emotion: <?= $e ?>/10">
            <div class="h-2 rounded <?= $e<=4?'bg-emerald-500':($e<=7?'bg-amber-500':'bg-red-500') ?>" style="width: <?= max(0,min(100,$e*10)) ?>%"></div>
          </div>
        </td>

        <td class="p-2 align-top"><?= badge($p['status']) ?></td>

        <td class="p-2 align-top">
          <span class="underline decoration-dotted" title="<?= e($setupTip) ?>">View setup</span>
        </td>

        <td class="p-2 align-top whitespace-nowrap"><?= e(date('Y-m-d H:i', strtotime($p['created_at']))) ?></td>
        <td class="p-2 align-top whitespace-nowrap"><?= e(date('Y-m-d H:i', strtotime($p['updated_at']))) ?></td>

        <td class="p-2 align-top">
          <div class="flex flex-wrap gap-2">
            <?php if (($p['status'] ?? '') === 'executed'): ?>
              <!-- If you have plan->position mapping, link directly; else index with query -->
              <a class="text-blue-400 underline" href="/app/positions?plan=<?= (int)$p['id'] ?>">Position</a>
            <?php endif; ?>
            <a class="text-zinc-400 underline" href="/app/plans/<?= (int)$p['id'] ?>">Details</a>
            <?php if (($p['status'] ?? '') === 'planned'): ?>
              <a class="text-emerald-400 underline" href="/app/plans/<?= (int)$p['id'] ?>">Execute</a>
            <?php endif; ?>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- Legend / Hints -->
<div class="mt-3 text-xs text-zinc-400 space-y-1">
  <div>📈 bullish / ➡️ sideways / 📉 bearish • ✔︎ = “ready to execute” (meets date & setup fields).</div>
  <div>Click column headers with underline to sort. Use the status chips above to filter. Search filters symbol & notes.</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  // client-side filters
  const rows = Array.from(document.querySelectorAll('#plansTable tbody tr'));
  const search = document.getElementById('search');
  document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const f = btn.getAttribute('data-filter');
      rows.forEach(r => {
        const st = r.getAttribute('data-status');
        r.style.display = (f === 'all' || st === f) ? '' : 'none';
      });
    });
  });
  search.addEventListener('input', () => {
    const q = search.value.trim().toLowerCase();
    rows.forEach(r => {
      const sym = r.getAttribute('data-symbol') || '';
      const notes = r.getAttribute('data-notes') || '';
      r.style.display = (sym.includes(q) || notes.includes(q)) ? '' : 'none';
    });
  });

  // client-side sort
  const table = document.getElementById('plansTable');
  let sortKey = null, sortDir = 1;
  function cmp(a,b){
    if (!isNaN(a) && !isNaN(b)) return (parseFloat(a) - parseFloat(b)) * sortDir;
    return a.localeCompare(b) * sortDir;
  }
  function sortBy(key){
    const tbody = table.querySelector('tbody');
    const arr = Array.from(tbody.querySelectorAll('tr'));
    arr.sort((ra, rb) => {
      let va = '', vb = '';
      if (key === 'symbol' || key === 'status') {
        va = (key === 'symbol') ? ra.getAttribute('data-symbol') : ra.getAttribute('data-status');
        vb = (key === 'symbol') ? rb.getAttribute('data-symbol') : rb.getAttribute('data-status');
      } else if (key === 'rr') {
        va = ra.getAttribute('data-rr'); vb = rb.getAttribute('data-rr');
      } else if (key === 'estp') {
        va = ra.getAttribute('data-estp'); vb = rb.getAttribute('data-estp');
      } else if (key === 'cap') {
        va = ra.getAttribute('data-cap'); vb = rb.getAttribute('data-cap');
      } else if (key === 'created') {
        va = ra.getAttribute('data-created'); vb = rb.getAttribute('data-created');
      } else if (key === 'updated') {
        va = ra.getAttribute('data-updated'); vb = rb.getAttribute('data-updated');
      }
      return cmp(va ?? '', vb ?? '');
    });
    tbody.innerHTML = '';
    arr.forEach(tr => tbody.appendChild(tr));
  }
  document.querySelectorAll('th.sortable').forEach(th => {
    th.classList.add('underline','decoration-dotted','cursor-pointer');
    th.addEventListener('click', () => {
      const key = th.getAttribute('data-key');
      if (sortKey === key) sortDir *= -1; else { sortKey = key; sortDir = 1; }
      sortBy(key);
    });
  });
});
</script>
