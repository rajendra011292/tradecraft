<?php $title='Reset Password'; $tpl='auth/reset'; $token = $_GET['token'] ?? ''; ?>
<h1 class="text-2xl font-bold mb-4">Reset Password</h1>
<form method="post" class="space-y-3 max-w-md">
  <?= csrf_field($this->app ?? $app ?? null) ?>
  <input type="hidden" name="token" value="<?= e($token) ?>">
  <div>
    <label class="block">New Password</label>
    <input class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2" type="password" name="password" minlength="8" required>
  </div>
  <button class="px-4 py-2 rounded bg-blue-600 hover:bg-blue-500">Update</button>
</form>
