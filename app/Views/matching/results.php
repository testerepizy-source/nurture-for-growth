<?php
$maxScore = 0;
foreach ($results as $r) { $maxScore = max($maxScore, (int) $r['score']); }
$roleLabel = ['psikolog' => 'Psikolog'];
?>
<div class="sme-wrap">
  <!-- Langkah alur -->
  <div class="sme-steps mx-auto">
    <span class="sme-step is-done"><i class="fas fa-check"></i>Ceritakan Keluhan</span>
    <span class="sme-step-line is-done"></span>
    <span class="sme-step is-active"><i>2</i>Terima Rekomendasi</span>
    <span class="sme-step-line"></span>
    <span class="sme-step"><i>3</i>Booking Sesi</span>
  </div>

  <div class="sme-hero">
    <div class="sme-hero-icon"><i class="fas fa-user-check"></i></div>
    <h2 class="mb-2">Rekomendasi untuk Anda</h2>
    <p class="text-muted mx-auto mb-0" style="max-width:560px">Diurutkan berdasarkan kecocokan keluhan, ketersediaan jadwal, dan beban kasus saat ini.</p>
    <?php if (!empty($results)): ?>
      <div class="sme-result-count mx-auto mt-3">
        <i class="fas fa-list-check me-1"></i><?= count($results) ?> tenaga profesional ditemukan
      </div>
    <?php endif; ?>
  </div>

  <?php if ($is_urgent): ?>
  <div class="sme-urgent-box mx-auto mb-4" style="max-width:680px" role="alert">
    <span class="sme-urgent-icon"><i class="fas fa-triangle-exclamation"></i></span>
    <div>
      <strong>Perhatian:</strong> Berdasarkan yang Anda ceritakan, kami menyarankan Anda segera terhubung dengan psikolog atau layanan darurat.
      Jika Anda dalam bahaya, hubungi layanan darurat sekarang juga.
      <a href="<?= base_url('/education') ?>" class="fw-bold">Lihat kontak bantuan darurat</a>.
    </div>
  </div>
  <?php endif; ?>

  <div class="mx-auto sme-results" style="max-width:820px">
    <?php if (empty($results)): ?>
      <div class="card sme-card text-center py-5 px-3">
        <div class="sme-empty-icon mx-auto mb-3"><i class="fas fa-user-slash"></i></div>
        <h6 class="fw-bold mb-2">Belum ada psikolog yang tersedia</h6>
        <p class="text-muted mb-0">Silakan hubungi admin klinik untuk bantuan pemilihan jadwal.</p>
      </div>
    <?php endif; ?>

    <?php foreach ($results as $i => $r): $p = $r['provider']; $pct = $maxScore > 0 ? max(12, (int) round(((int) $r['score'] / $maxScore) * 100)) : 12; ?>
      <article class="match-card mb-3 <?= $i === 0 ? 'is-top' : '' ?>">
        <?php if ($i === 0): ?><span class="match-top-ribbon"><i class="fas fa-star"></i>Paling Cocok</span><?php endif; ?>
        <div class="d-flex align-items-start gap-3 flex-wrap flex-md-nowrap">
          <div class="match-rank <?= $i === 0 ? 'is-top' : '' ?>"><?= $i + 1 ?></div>

          <div class="provider-avatar match-avatar">
            <?php if (!empty($p['photo'])): ?>
              <img src="<?= base_url($p['photo']) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
            <?php else: ?>
              <?= e(mb_substr($p['name'], 0, 1)) ?>
            <?php endif; ?>
          </div>

          <div class="flex-grow-1 min-w-0">
            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
              <h6 class="fw-bold mb-0"><?= e($p['name']) ?></h6>
              <span class="match-role-badge <?= $p['role'] ?>"><?= e($roleLabel[$p['role']] ?? $p['role']) ?></span>
            </div>

            <p class="small text-muted mb-2"><?= e($r['reason']) ?></p>

            <?php if (!empty($r['matched_tags'])): ?>
              <div class="match-tags mb-2">
                <?php foreach ($r['matched_tags'] as $mt): ?>
                  <span class="match-tag"><i class="fas fa-circle-check"></i><?= e($mt) ?></span>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

            <div class="match-score mb-2" title="Skor kecocokan: <?= (int) $r['score'] ?> poin">
              <div class="match-score-bar"><span style="width:<?= $pct ?>%"></span></div>
              <span class="match-score-label">Kecocokan <?= $pct ?>%</span>
            </div>

            <div class="match-meta">
              <span><i class="fas fa-calendar-check"></i>Jadwal terdekat: <strong><?= e($r['next_slot']) ?></strong></span>
              <span><i class="fas fa-users"></i>Beban kasus: <strong><?= (int) $r['active_caseload'] ?> aktif</strong></span>
            </div>
          </div>

          <div class="match-action ms-md-auto">
            <form method="GET" action="<?= base_url('/consultation/create') ?>">
              <input type="hidden" name="provider_id" value="<?= $p['user_id'] ?>">
              <input type="hidden" name="matched" value="1">
              <button class="match-btn" type="submit">Pilih &amp; Jadwalkan<i class="fas fa-arrow-right ms-1"></i></button>
            </form>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</div>
