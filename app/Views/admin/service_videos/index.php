<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h4 class="fw-bold mb-1">Video Layanan Landing Page</h4>
    <p class="text-muted small mb-0">Maksimal 12 video. Urutan menentukan tampilan di bagian "Video Layanan" halaman utama.</p>
  </div>
  <?php if ($canAddMore): ?>
    <a href="<?= base_url('/admin/service-videos/create') ?>" class="btn btn-primary btn-sm">+ Tambah Video</a>
  <?php else: ?>
    <span class="badge bg-secondary">12/12 slot terpakai</span>
  <?php endif; ?>
</div>

<?php if (empty($videos)): ?>
  <div class="card p-4 text-center">
    <i class="fas fa-circle-play fa-2x text-muted mb-2"></i>
    <p class="text-muted mb-0">Belum ada video layanan. Tambahkan minimal satu video agar bagian ini tampil di landing page.</p>
  </div>
<?php endif; ?>

<div class="row g-3">
  <?php foreach ($videos as $i => $v): ?>
    <?php
      $thumb = \App\Models\ServiceVideo::thumbUrl($v);
      $sourceLabel = 'File';
      if (!empty($v['video_url'])) {
          $sourceLabel = \App\Models\ServiceVideo::youtubeId($v['video_url']) ? 'YouTube'
              : (\App\Models\ServiceVideo::vimeoId($v['video_url']) ? 'Vimeo' : 'URL Video');
      } elseif (!empty($v['video_path'])) {
          $sourceLabel = 'Unggahan';
      }
    ?>
    <div class="col-md-6 col-xl-4">
      <div class="card p-3 h-100 d-flex flex-column">
        <div style="position:relative;aspect-ratio:16/9;border-radius:.5rem;overflow:hidden;background:#12241d;margin-bottom:10px;">
          <?php if ($thumb): ?>
            <img src="<?= e($thumb) ?>" alt="" style="width:100%;height:100%;object-fit:cover;" loading="lazy">
            <span style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;">
              <span style="width:38px;height:38px;border-radius:50%;background:rgba(255,255,255,.92);display:flex;align-items:center;justify-content:center;box-shadow:0 4px 14px rgba(0,0,0,.35);">
                <i class="fas fa-play ms-1" style="color:#12241d;font-size:.8rem;"></i>
              </span>
            </span>
          <?php else: ?>
            <div style="height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;color:#7fb8a8;gap:6px;">
              <i class="fas fa-film fa-lg"></i><small>Pratinjau tidak tersedia</small>
            </div>
          <?php endif; ?>
        </div>
        <div class="flex-grow-1">
          <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
            <h6 class="fw-bold mb-0"><?= strip_tags($v['title']) ?></h6>
            <span class="badge <?= $v['is_active'] ? 'bg-success' : 'bg-secondary' ?> flex-shrink-0"><?= $v['is_active'] ? 'Aktif' : 'Nonaktif' ?></span>
          </div>
          <?php if (!empty($v['badge_text'])): ?>
            <span class="badge bg-light text-dark border mb-1"><?= e($v['badge_text']) ?></span>
          <?php endif; ?>
          <p class="small text-muted mb-2">
            <span class="badge text-bg-light border"><i class="fas fa-video me-1"></i><?= $sourceLabel ?></span>
            Urutan <?= $v['order_no'] ?>
          </p>
        </div>
        <div class="d-flex gap-1 flex-wrap">
          <a href="<?= base_url('/admin/service-videos/' . $v['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary">Ubah</a>
          <form method="POST" action="<?= base_url('/admin/service-videos/' . $v['id'] . '/toggle') ?>" class="d-inline"><?= \Core\Csrf::field() ?><button class="btn btn-sm btn-outline-<?= $v['is_active'] ? 'warning' : 'success' ?>"><?= $v['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?></button></form>
          <?php if ($i > 0): ?>
            <form method="POST" action="<?= base_url('/admin/service-videos/' . $v['id'] . '/move-up') ?>" class="d-inline"><?= \Core\Csrf::field() ?><button class="btn btn-sm btn-outline-secondary" title="Naikkan urutan"><i class="fas fa-arrow-up"></i></button></form>
          <?php endif; ?>
          <?php if ($i < count($videos) - 1): ?>
            <form method="POST" action="<?= base_url('/admin/service-videos/' . $v['id'] . '/move-down') ?>" class="d-inline"><?= \Core\Csrf::field() ?><button class="btn btn-sm btn-outline-secondary" title="Turunkan urutan"><i class="fas fa-arrow-down"></i></button></form>
          <?php endif; ?>
          <form method="POST" action="<?= base_url('/admin/service-videos/' . $v['id'] . '/delete') ?>" class="d-inline" onsubmit="return confirm('Hapus video ini?')"><?= \Core\Csrf::field() ?><button class="btn btn-sm btn-outline-danger">Hapus</button></form>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>
