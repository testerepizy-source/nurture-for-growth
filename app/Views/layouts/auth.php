<?php
$__uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$__isLogin = str_contains($__uri, '/login');
$__isRegister = str_contains($__uri, '/register');
?>
<!DOCTYPE html>
<html lang="<?= locale() ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $__isRegister ? 'Daftar Akun' : ($__isLogin ? 'Masuk' : 'Lupa Password') ?> · <?= e(config_get('app_name', 'NurtureForGrowth')) ?></title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,500;1,500;1,600&family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.2/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="<?= asset('css/style.css') ?>">
<link rel="icon" href="<?= asset('img/brand/nfg-logo.jpg') ?>">
</head>
<body>

<div class="auth-shell">
  <div class="auth-side">
    <div class="blot blot-teal blot-shape" style="width:280px;height:280px;top:-60px;right:-60px;opacity:.5"></div>
    <div class="blot blot-pink blot-shape-alt" style="width:220px;height:220px;bottom:60px;left:-60px;opacity:.4"></div>
    <div class="blot blot-orange blot-shape" style="width:180px;height:180px;bottom:-60px;right:80px;opacity:.35"></div>

    <a href="<?= base_url('/') ?>" class="brand-mark" style="position:relative;z-index:1">
      <img class="brand-logo-image" src="<?= asset('img/brand/nfg-logo.jpg') ?>" alt="Nurture for Growth">
    </a>

    <p class="auth-side-quote">&ldquo;Langkah kecil untuk bicara dengan seseorang hari ini bisa jadi awal perubahan besar besok.&rdquo;</p>

    <div class="auth-side-foot">Nurture For Growth &middot; Konseling, asesmen &amp; terapi psikologis</div>
  </div>

  <div class="auth-form-col">
    <div class="auth-card">
      <a href="<?= base_url('/') ?>" class="back-home"><i class="fas fa-arrow-left"></i> Kembali ke beranda</a>

      <?php if ($msg = \Core\Session::flash('success')): ?>
        <div class="alert alert-success rounded-4 border-0 small"><?= e($msg) ?></div>
      <?php endif; ?>
      <?php if ($msg = \Core\Session::flash('error')): ?>
        <div class="alert alert-danger rounded-4 border-0 small"><?= e($msg) ?></div>
      <?php endif; ?>

      <?= $content ?>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script>window.NFG_CAPTCHA_URL = "<?= base_url('/captcha') ?>";</script>
<script src="<?= asset('js/main.js') ?>"></script>
</body>
</html>
