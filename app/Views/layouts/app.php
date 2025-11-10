<?php include __DIR__ . '/../partials/header.php'; ?>
  <?php include __DIR__ . '/../partials/nav.php'; ?>

  <main class="max-w-5xl mx-auto p-4">
    <?php if (!empty($flashSuccess)): ?>
      <div class="bg-green-900/40 border border-green-700 text-green-200 px-4 py-2 rounded mb-4"><?= e($flashSuccess) ?></div>
    <?php endif; ?>
    <?php if (!empty($flashError)): ?>
      <div class="bg-red-900/40 border border-red-700 text-red-200 px-4 py-2 rounded mb-4"><?= e($flashError) ?></div>
    <?php endif; ?>

    <?php include __DIR__ . '/../' . $tpl . '.php'; ?>
  </main>

  <?php include __DIR__ . '/../partials/footer.php'; ?>
