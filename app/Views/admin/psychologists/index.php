<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="fw-bold mb-0">Data Psikolog</h4>
  <a href="<?= base_url('/admin/psychologists/create') ?>" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Tambah Psikolog</a>
</div>
<div class="row row-cols-1 row-cols-md-2 g-3">
  <?php foreach ($psychologists as $p): ?>
    <div class="col">
      <div class="admin-card shadow-sm p-3 rounded-4 d-flex align-items-start gap-3">
        <div class="admin-avatar">
          <?php if (!empty($p['photo'])): ?>
            <img src="<?= base_url($p['photo']) ?>" alt="<?= e($p['name']) ?>">
          <?php else: ?>
            <span><?= e(mb_substr($p['name'],0,1)) ?></span>
          <?php endif; ?>
        </div>
        <div class="flex-grow-1">
          <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2 mb-2">
            <div>
              <h5 class="mb-1"><?= e($p['name']) ?></h5>
              <p class="mb-1 text-muted small"><?= e($p['email']) ?></p>
            </div>
            <div class="d-flex flex-wrap gap-2">
              <span class="badge bg-teal text-white">STR: <?= e($p['str_number']) ?></span>
              <span class="badge bg-sand text-dark">SIPP: <?= e($p['sipp_number']) ?></span>
            </div>
          </div>

          <p class="mb-2 text-muted small"><strong>Spesialisasi:</strong> <?= e($p['specialization']) ?></p>
          <div class="d-flex flex-wrap gap-2">
            <a href="<?= base_url('/admin/psychologists/' . $p['id'] . '/edit') ?>" class="btn btn-sm btn-outline-secondary">Ubah</a>
            <a href="<?= base_url('/admin/provider-tags/' . $p['user_id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary">Kelola Tag</a>
            <form method="POST" action="<?= base_url('/admin/psychologists/' . $p['id'] . '/delete') ?>" class="d-inline" onsubmit="return confirm('Hapus psikolog ini?');">
              <?= \Core\Csrf::field() ?>
              <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>
