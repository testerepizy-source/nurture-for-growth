<?php require __DIR__ . '/icons.php'; ?>

<section class="public-hero">
  <div class="public-hero-inner">
    <div class="public-hero-copy">
      <span class="badge badge-primary">Kesehatan Mental Modern</span>
      <?php
        // If hero title contains the word 'Empati', split so 'Empati' becomes the emphasized word
        if (strpos($heroTitle, 'Empati') !== false) {
          $parts = explode('Empati', $heroTitle);
          $left = trim($parts[0]);
          echo '<h1 class="section-title"><span class="hero-title-regular">' . e($left) . '</span> <span class="hero-title-accent">Empati</span></h1>';
        } else {
          echo '<h1 class="section-title">' . e($heroTitle) . '</h1>';
        }
      ?>
      <p class="section-sub">NurtureForGrowth menghadirkan perawatan berbasis bukti untuk stres, kecemasan, hubungan, dan tantangan emosional dengan pendekatan klinis yang manusiawi.</p>

      <p class="section-sub"><?= e($heroSubtitle) ?></p>

      <div class="hero-actions">
        <a href="<?= $heroCtaLink ?>" class="btn btn-primary btn-pill"><?= e($heroCtaText) ?></a>
        <a href="<?= base_url('/education') ?>" class="btn btn-secondary btn-pill">Lihat Layanan</a>
      </div>

      <div class="hero-stat-grid">
        <div class="hero-stat">
          <strong><?= !empty($stats['providers']) ? (int) $stats['providers'] : '120' ?>+</strong>
          <span>Psikolog Berlisensi</span>
        </div>
        <div class="hero-stat">
          <strong><?= !empty($stats['patients']) ? (int) $stats['patients'] : '1800' ?>+</strong>
          <span>Klien yang terbantu</span>
        </div>
        <div class="hero-stat">
          <strong><?= !empty($stats['sessions']) ? (int) $stats['sessions'] : '4600' ?>+</strong>
          <span>Sesi pendampingan terselesaikan</span>
        </div>
      </div>
    </div>

    <div class="public-hero-media">
      <div class="public-hero-media-frame visual-card" role="img" aria-label="Ilustrasi pendampingan psikologis">
        <?php if (!empty($hero['image_path'])): ?>
          <img src="<?= $heroImage ?>" srcset="<?= $heroImage ?> 1x" alt="<?= e(strip_tags($heroTitle)) ?>" loading="lazy" decoding="async" style="object-position: var(--hero-object-position, center right);">
        <?php else: ?>
          <div class="public-hero-media-fallback">
            <h3>Perjalanan mental Anda dimulai di sini.</h3>
          </div>
        <?php endif; ?>

        <div class="visual-chip visual-chip-top" tabindex="0" role="button" aria-hidden="true">
          <div class="chip-avatar" aria-hidden="true">
            <?= svg_icon('lock', 18) ?>
          </div>
          <div class="chip-text"><strong>Rahasia</strong><small>&amp; aman terjaga</small></div>
        </div>

        <div class="visual-chip visual-chip-bottom" tabindex="0" role="button" aria-hidden="true">
          <div class="chip-avatar" aria-hidden="true">
            <?= svg_icon('chart', 18) ?>
          </div>
          <div class="chip-text"><strong><?= !empty($stats['sessions']) ? (int)$stats['sessions'] . '+' : '5400+' ?></strong><small>Sesi terlaksana</small></div>
        </div>

        <div class="hero-blobs" aria-hidden="true">
          <span class="blob blob-top"></span>
          <span class="blob blob-bottom"></span>
        </div>
      </div>
    </div>
  </div>
</section>
