<h5 class="fw-bold mb-3">Lupa Password</h5>
<p class="text-muted small">Masukkan email Anda, instruksi reset password akan dikirimkan.</p>
<form method="POST" action="<?= base_url('/forgot-password') ?>">
  <?= \Core\Csrf::field() ?>
  <div class="mb-3">
    <label class="form-label small">Email</label>
    <input type="email" name="email" class="form-control" required autofocus>
  </div>
  <button class="btn btn-primary w-100" type="submit">Kirim Instruksi</button>
</form>
<div class="text-center mt-3 small">
  <a href="<?= base_url('/login') ?>">Kembali ke Login</a>
</div>
