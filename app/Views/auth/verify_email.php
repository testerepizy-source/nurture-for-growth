<div class="auth-form">
  <div class="mb-4">
    <h4 class="card-title mb-1">Verifikasi Email</h4>
    <p class="section-sub mb-0">Masukkan kode OTP 6 digit yang kami kirimkan ke alamat email Anda.</p>
  </div>

  <form method="POST" action="<?= base_url('/verify-email') ?>">
    <?= \Core\Csrf::field() ?>
    <div class="mb-3">
      <label class="form-label" for="email">Email</label>
      <input id="email" type="email" name="email" class="form-control" value="<?= e($email ?? '') ?>" required autofocus>
    </div>
    <div class="mb-3">
      <label class="form-label" for="otp">Kode OTP</label>
      <input id="otp" type="text" name="otp" class="form-control text-center fw-bold" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" placeholder="000000" required>
      <div class="form-text">Kode berlaku selama 10 menit.</div>
    </div>
    <button class="btn btn-primary w-100" type="submit">Verifikasi Email</button>
  </form>

  <form method="POST" action="<?= base_url('/verify-email/resend') ?>" class="mt-3 text-center">
    <?= \Core\Csrf::field() ?>
    <input type="hidden" name="email" value="<?= e($email ?? '') ?>">
    <button class="btn btn-link btn-sm" type="submit">Kirim ulang kode OTP</button>
  </form>

  <div class="form-footer mt-3 small text-muted">
    Sudah terverifikasi? <a href="<?= base_url('/login') ?>">Masuk di sini</a>
  </div>
</div>
