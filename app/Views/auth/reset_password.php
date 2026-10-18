<h5 class="fw-bold mb-3">Buat Password Baru</h5>
<p class="text-muted small">Masukkan password baru untuk akun Anda.</p>
<form method="POST" action="<?= base_url('/reset-password') ?>">
  <?= \Core\Csrf::field() ?>
  <input type="hidden" name="email" value="<?= e($email ?? '') ?>">
  <input type="hidden" name="token" value="<?= e($token ?? '') ?>">
  <div class="mb-3">
    <label class="form-label small">Password baru</label>
    <input type="password" name="password" class="form-control" minlength="8" required autofocus>
  </div>
  <div class="mb-3">
    <label class="form-label small">Konfirmasi password baru</label>
    <input type="password" name="password_confirmation" class="form-control" minlength="8" required>
  </div>
  <button class="btn btn-primary w-100" type="submit">Simpan Password Baru</button>
</form>
