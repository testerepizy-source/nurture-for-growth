<?php
$brandName = trim(preg_replace('/^[\p{P}\p{S}\s]+|[\p{P}\p{S}\s]+$/u', '', config_get('app_name')));
?>
<header class="public-navbar">
  <div class="container public-navbar-grid">
    <a class="navbar-brand" href="<?= base_url('/') ?>">
      <img src="<?= asset('img/brand/nfg-logo.jpg') ?>" alt="<?= e($brandName) ?>">
      <div class="brand-copy">
        <span class="brand-name"><?= e($brandName) ?></span>
        <small class="brand-tag"><?= __('app.tagline') ?></small>
      </div>
    </a>

    <button class="public-navbar-toggle" type="button" aria-expanded="false" aria-controls="publicNav" aria-label="Toggle navigation">
      <?= svg_icon('menu',20) ?>
    </button>

    <nav id="publicNav" class="public-nav" aria-label="Primary navigation">
      <a class="nav-link <?= publicNavActive('/', $relativePath) ?>" href="<?= base_url('/') ?>"><?= __('page.home') ?></a>
      <a class="nav-link <?= publicNavActive('/education', $relativePath) ?>" href="<?= base_url('/education') ?>"><?= __('page.articles') ?></a>
      <a class="nav-link <?= publicNavActive('/faq', $relativePath) ?>" href="<?= base_url('/faq') ?>"><?= __('page.faq') ?></a>
      <a class="nav-link <?= publicNavActive('/contact', $relativePath) ?>" href="<?= base_url('/contact') ?>"><?= __('page.contact') ?></a>
    </nav>

    <div class="public-navbar-actions">
      <a class="btn btn-outline-primary btn-sm public-cta" href="<?= base_url('/join-us') ?>"><?= __('page.join_us_short') ?></a>
      <?php if (\Core\Auth::check()): ?>
        <a class="btn btn-secondary btn-sm" href="<?= base_url('/dashboard') ?>"><?= __('nav.dashboard') ?></a>
      <?php else: ?>
        <a class="btn btn-primary btn-sm" href="<?= base_url('/register') ?>"><?= __('auth.register') ?></a>
        <a class="btn btn-outline-secondary btn-sm" href="<?= base_url('/login') ?>"><?= __('auth.login') ?></a>
      <?php endif; ?>
      <?php require __DIR__ . '/../partials/header_language_switcher.php'; ?>
    </div>
  </div>
</header>
