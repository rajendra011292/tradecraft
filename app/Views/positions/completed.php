<?php
/** @var array $positions */
/** @var array $legsByPos */
$title = 'Completed Positions';
$tpl   = 'positions/completed';
?>
<div class="flex items-center justify-between mb-4">
  <h1 class="text-2xl font-bold">Completed Trades</h1>
  <a href="/app/positions" class="px-3 py-2 bg-zinc-800 hover:bg-zinc-700 rounded text-sm">Back to Ongoing</a>
</div>

<div class="overflow-x-auto border border-zinc-800 rounded">
  <table class="min-w-full text-sm">
    <thead class="bg-zinc-900">
      <tr>
        <th class="p-2 text-left">#</th>
        <th class="p-2 text-left">Symbol</th>
        <th class="p-2 text-left">Side</th>
        <th class="p-2 text-left">Entry</th>
        <th class="p-2 text-left">Avg Exit</th>
        <th class="p-2 text-left">Net Realized P/L</th>
        <th class="p-2 text-left">Executed</th>
        <th class="p-2 text-left">Completed</th>
        <th class="p-2 text-left">Legs</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-zinc-800">
      <?php foreach ($positions as $p):
        $legs = $legsByPos[$p['id']] ?? [];
        // compute fallback totals if columns not filled
        $sumPL = 0.0; $sumQty=0.0; $wExit=0.0;
        foreach ($legs as $l) {
          $sumPL += (float)$l['realized_pl'];
          $sumQty += (float)$l['qty'];
          $wExit += (float)$l['qty'] * (float)$l['exit_price'];
        }
        $avgExit = $sumQty > 0 ? $wExit / $sumQty : null;
        $netPL   = isset($p['realized_pl_total']) ? (float)$p['realized_pl_total'] : $sumPL;
        $avgExitShown = isset($p['avg_exit_price']) && $p['avg_exit_price'] ? $p['avg_exit_price'] : $avgExit;
        $plClass = $netPL > 0 ? 'text-emerald-400' : ($netPL < 0 ? 'text-red-400' : 'text-zinc-300');
      ?>
      <tr class="hover:bg-zinc-900 align-top">
        <td class="p-2"><?= (int)$p['id'] ?></td>
        <td class="p-2 font-semibold"><?= e($p['symbol']) ?></td>
        <td class="p-2"><?= e(ucfirst($p['side'])) ?></td>
        <td class="p-2"><?= e($p['entry_price']) ?></td>
        <td class="p-2"><?= $avgExitShown ? e(number_format((float)$avgExitShown,2)) : '—' ?></td>
        <td class="p-2 <?= $plClass ?>">₹<?= e(number_format($netPL,2)) ?></td>
        <td class="p-2 whitespace-nowrap"><?= e(date('Y-m-d H:i', strtotime($p['executed_at']))) ?></td>
        <td class="p-2 whitespace-nowrap"><?= e(!empty($p['completed_at']) ? date('Y-m-d H:i', strtotime($p['completed_at'])) : '—') ?></td>
        <td class="p-2">
          <?php if (!empty($legs)): ?>
          <details class="rounded border border-zinc-800">
            <summary class="px-2 py-1 cursor-pointer">View legs (<?= count($legs) ?>)</summary>
            <div class="p-2">
              <table class="w-full text-xs border border-zinc-800 rounded overflow-hidden">
                <thead class="bg-zinc-900">
                  <tr>
                    <th class="p-1 text-left">#</th>
                    <th class="p-1 text-left">Type</th>
                    <th class="p-1 text-left">Qty</th>
                    <th class="p-1 text-left">Exit</th>
                    <th class="p-1 text-left">Fees</th>
                    <th class="p-1 text-left">Realized P/L</th>
                    <th class="p-1 text-left">Exited At</th>
                    <th class="p-1 text-left">Note</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($legs as $i => $l): 
                    $c = ((float)$l['realized_pl'] > 0) ? 'text-emerald-400' : (((float)$l['realized_pl'] < 0) ? 'text-red-400' : 'text-zinc-300'); ?>
                  <tr class="border-t border-zinc-800">
                    <td class="p-1"><?= $i+1 ?></td>
                    <td class="p-1"><?= e($l['leg_type']) ?></td>
                    <td class="p-1"><?= e($l['qty']) ?></td>
                    <td class="p-1"><?= e($l['exit_price']) ?></td>
                    <td class="p-1"><?= e($l['fees']) ?></td>
                    <td class="p-1 <?= $c ?>">₹<?= e(number_format((float)$l['realized_pl'],2)) ?></td>
                    <td class="p-1 whitespace-nowrap"><?= e($l['exited_at']) ?></td>
                    <td class="p-1"><?= e($l['note'] ?? '') ?></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
                <tfoot class="bg-zinc-900">
                  <tr>
                    <td class="p-1 font-semibold" colspan="5">Totals</td>
                    <td class="p-1 font-semibold <?= $plClass ?>">₹<?= e(number_format($netPL,2)) ?></td>
                    <td class="p-1" colspan="2">Avg Exit: <?= $avgExitShown ? e(number_format((float)$avgExitShown,2)) : '—' ?></td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </details>
          <?php else: ?>
            <span class="text-zinc-400">No legs recorded</span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($positions)): ?>
      <tr><td class="p-3 text-zinc-400" colspan="9">No completed trades.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
