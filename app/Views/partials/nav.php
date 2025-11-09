<header class="border-b border-gray-800">
    <div class="max-w-5xl mx-auto p-4 flex items-center justify-between">
      <nav class="bg-gray-900 border-b border-gray-800 text-sm text-gray-300">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center h-12">
    <div class="flex items-center gap-4">
      <a href="/app/dashboard" class="font-bold text-white hover:text-blue-400">TradeSmart</a>
      <a href="/app/plans" class="hover:text-white <?= str_contains($_SERVER['REQUEST_URI'], '/plans') ? 'text-blue-400' : '' ?>">Plans</a>
      <a href="/app/positions" class="hover:text-white <?= str_contains($_SERVER['REQUEST_URI'], '/positions') && !str_contains($_SERVER['REQUEST_URI'], 'completed') ? 'text-blue-400' : '' ?>">Positions</a>
      <a href="/app/positions/completed" class="hover:text-white <?= str_contains($_SERVER['REQUEST_URI'], 'completed') ? 'text-blue-400' : '' ?>">Completed</a>
    </div>
    <div class="flex items-center gap-4">
      <span class="hidden sm:inline text-gray-400"><?= e($_SESSION['user_email'] ?? '') ?></span>
      <a href="/logout" class="text-red-400 hover:text-red-300">Logout</a>
    </div>
  </div>
</nav>
    </div>
  </header>