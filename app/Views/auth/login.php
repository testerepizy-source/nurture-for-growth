<div class="auth-form">
  <div class="mb-4">
    <h4 class="card-title mb-1">Masuk ke Akun Anda</h4>
    <p class="section-sub mb-0">Gunakan email dan password akun Anda untuk mengelola sesi dan catatan kesehatan mental.</p>
  </div>

  <form method="POST" action="<?= base_url('/login') ?>">
    <?= \Core\Csrf::field() ?>

    <div class="mb-3">
      <label class="form-label" for="email">Email</label>
      <input id="email" type="email" name="email" class="form-control" required autofocus>
    </div>

    <div class="mb-3">
      <label class="form-label" for="password">Password</label>
      <input id="password" type="password" name="password" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label" for="captcha_answer">Captcha</label>
      <div class="input-group">
        <span class="input-group-text bg-surface-muted border-end-0" id="captchaQuestion">memuat...</span>
        <input id="captcha_answer" type="number" name="captcha_answer" class="form-control border-start-0" placeholder="Jawaban" required>
      </div>
    </div>

    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3 mb-3">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
        <label class="form-check-label" for="remember">Ingat saya</label>
      </div>
      <a href="<?= base_url('/forgot-password') ?>" class="small text-primary">Lupa password?</a>
    </div>

    <button class="btn btn-primary w-100" type="submit">Masuk</button>
  </form>

  <div class="form-footer mt-4 small text-muted">
    Belum punya akun? <a href="<?= base_url('/register') ?>">Daftar sekarang</a>
  </div>
</div>
