<?php $title = $title ?? 'TradeSmart'; ?>
<!doctype html>
<html lang="en" class="h-full" data-theme="dark">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title) ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-950 text-gray-100">
  <header class="border-b border-gray-800">
    <div class="max-w-5xl mx-auto p-4 flex items-center justify-between">
      <a href="/" class="font-bold">TradeSmart</a>
      <nav class="space-x-4">
        <?php if (!empty($_SESSION['user_id'])): ?>
          <a class="hover:underline" href="/app/dashboard">Dashboard</a>
          <a class="hover:underline" href="/app/plans">Plans</a>
          <a class="hover:underline" href="/logout">Logout</a>
        <?php else: ?>
          <a class="hover:underline" href="/auth/login">Login</a>
          <a class="hover:underline" href="/auth/register">Register</a>
        <?php endif; ?>
      </nav>
    </div>
  </header>

  <main class="max-w-5xl mx-auto p-4">
    <?php if (!empty($flashSuccess)): ?>
      <div class="bg-green-900/40 border border-green-700 text-green-200 px-4 py-2 rounded mb-4"><?= e($flashSuccess) ?></div>
    <?php endif; ?>
    <?php if (!empty($flashError)): ?>
      <div class="bg-red-900/40 border border-red-700 text-red-200 px-4 py-2 rounded mb-4"><?= e($flashError) ?></div>
    <?php endif; ?>

    <?php include __DIR__ . '/../' . $tpl . '.php'; ?>
  </main>

  <footer class="max-w-5xl mx-auto p-4 text-sm text-gray-400 border-t border-gray-800 mt-10">
    Built for dev: PHP 8.3, clean MVC, CSRF, throttle, Argon2id.
  </footer>
</body>
</html>
