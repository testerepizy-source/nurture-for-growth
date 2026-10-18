<div class="auth-form">
  <div class="mb-4">
    <h4 class="card-title mb-1">Buat Akun Baru</h4>
    <p class="section-sub mb-0">Segera buka akses ke sesi, asesmen, dan ringkasan kesehatan mental Anda.</p>
  </div>

  <form method="POST" action="<?= base_url('/register') ?>">
    <?= \Core\Csrf::field() ?>

    <div class="mb-3">
      <label class="form-label" for="name">Nama Lengkap</label>
      <input id="name" type="text" name="name" class="form-control" value="<?= old('name') ?>" required autofocus>
    </div>

    <div class="mb-3">
      <label class="form-label" for="email">Email</label>
      <input id="email" type="email" name="email" class="form-control" value="<?= old('email') ?>" required>
    </div>

    <div class="mb-3">
      <label class="form-label" for="password">Password</label>
      <input id="password" type="password" name="password" class="form-control" required minlength="8">
      <div class="form-text">Gunakan minimal 8 karakter untuk keamanan akun.</div>
    </div>

    <div class="mb-3">
      <label class="form-label" for="password_confirmation">Konfirmasi Password</label>
      <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" required minlength="8">
    </div>

    <button class="btn btn-primary w-100" type="submit">Daftar</button>
  </form>

  <div class="form-footer mt-4 small text-muted">
    Sudah punya akun? <a href="<?= base_url('/login') ?>">Masuk di sini</a>
  </div>
</div>
