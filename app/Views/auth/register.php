<?php $title='Register'; $tpl='auth/register'; ?>
<h1 class="text-2xl font-bold mb-4">Create account</h1>
<form method="post" class="space-y-3 max-w-md">
  <?= csrf_field($this->app ?? $app ?? null) ?>
  <div>
    <label class="block">Name</label>
    <input class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2" type="text" name="name" required>
  </div>
  <div>
    <label class="block">Email</label>
    <input class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2" type="email" name="email" required>
  </div>
  <div>
    <label class="block">Password</label>
    <input class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2" type="password" name="password" minlength="8" required>
  </div>
  <button class="px-4 py-2 rounded bg-blue-600 hover:bg-blue-500">Register</button>
</form>
<p class="mt-3 text-sm text-gray-400">After registration, open <code>storage/mail/*.log</code> to get your verification link.</p>
