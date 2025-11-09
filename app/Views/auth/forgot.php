<?php $title='Forgot Password'; $tpl='auth/forgot'; ?>
<h1 class="text-2xl font-bold mb-4">Forgot Password</h1>
<form method="post" class="space-y-3 max-w-md">
  <?= csrf_field($this->app ?? $app ?? null) ?>
  <div>
    <label class="block">Email</label>
    <input class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2" type="email" name="email" required>
  </div>
  <button class="px-4 py-2 rounded bg-blue-600 hover:bg-blue-500">Send reset link</button>
</form>
<p class="mt-3 text-sm text-gray-400">In dev, reset links are written to <code>storage/mail/</code>.</p>
