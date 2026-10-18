<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h4 class="fw-bold mb-1">Slide Utama Landing Page</h4>
    <p class="text-muted small mb-0">Maksimal 4 slide aktif. Urutan menentukan tampilan slider di halaman utama.</p>
  </div>
  <?php if ($canAddMore): ?>
    <a href="<?= base_url('/admin/hero-slides/create') ?>" class="btn btn-primary btn-sm">+ Tambah Slide</a>
  <?php else: ?>
    <span class="badge bg-secondary">4/4 slide terpakai</span>
  <?php endif; ?>
</div>

<?php if (empty($slides)): ?>
  <p class="text-muted">Belum ada hero slide. Slider akan kosong di landing page sampai Anda menambahkan minimal 1 slide.</p>
<?php endif; ?>

<div class="row g-3">
  <?php foreach ($slides as $i => $s): ?>
    <div class="col-md-6">
      <div class="card p-3 h-100">
        <div class="d-flex gap-3">
          <div style="width:100px;height:70px;border-radius:.5rem;overflow:hidden;flex-shrink:0;background:linear-gradient(135deg,var(--nfg-teal-500),var(--nfg-teal-700));display:flex;align-items:center;justify-content:center;">
            <?php if (!empty($s['image_path'])): ?>
              <img src="<?= base_url($s['image_path']) ?>" alt="" style="width:100%;height:100%;object-fit:cover;">
            <?php else: ?>
              <i class="fas fa-image text-white"></i>
            <?php endif; ?>
          </div>
          <div class="flex-grow-1">
            <div class="d-flex justify-content-between align-items-start">
              <h6 class="fw-bold mb-1"><?= strip_tags($s['title']) ?></h6>
              <span class="badge <?= $s['is_active'] ? 'bg-success' : 'bg-secondary' ?>"><?= $s['is_active'] ? 'Aktif' : 'Nonaktif' ?></span>
            </div>
            <p class="small text-muted mb-2"><?= e($s['badge_text']) ?></p>
            <div class="d-flex gap-1 flex-wrap">
              <a href="<?= base_url('/admin/hero-slides/' . $s['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary">Ubah</a>
              <?php if ($i > 0): ?>
                <form method="POST" action="<?= base_url('/admin/hero-slides/' . $s['id'] . '/move-up') ?>" class="d-inline"><?= \Core\Csrf::field() ?><button class="btn btn-sm btn-outline-secondary" title="Naikkan urutan"><i class="fas fa-arrow-up"></i></button></form>
              <?php endif; ?>
              <?php if ($i < count($slides) - 1): ?>
                <form method="POST" action="<?= base_url('/admin/hero-slides/' . $s['id'] . '/move-down') ?>" class="d-inline"><?= \Core\Csrf::field() ?><button class="btn btn-sm btn-outline-secondary" title="Turunkan urutan"><i class="fas fa-arrow-down"></i></button></form>
              <?php endif; ?>
              <form method="POST" action="<?= base_url('/admin/hero-slides/' . $s['id'] . '/delete') ?>" class="d-inline" onsubmit="return confirm('Hapus slide ini?')"><?= \Core\Csrf::field() ?><button class="btn btn-sm btn-outline-danger">Hapus</button></form>
            </div>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>
