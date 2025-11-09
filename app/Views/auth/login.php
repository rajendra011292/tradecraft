<?php $title='Login'; $tpl='auth/login'; ?>
<h1 class="text-2xl font-bold mb-4">Login</h1>
<form method="post" class="space-y-3 max-w-md">
  <?= csrf_field($this->app ?? $app ?? null) ?>
  <div>
    <label class="block">Email</label>
    <input class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2" type="email" name="email" required>
  </div>
  <div>
    <label class="block">Password</label>
    <input class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2" type="password" name="password" required>
  </div>
  <button class="px-4 py-2 rounded bg-blue-600 hover:bg-blue-500">Login</button>
  <a class="ml-3 underline" href="/auth/forgot">Forgot password?</a>
</form>
