<h4 class="fw-bold mb-3">Detail Aplikasi &mdash; <?= e($application['name']) ?></h4>
<div class="card p-3 mb-3">
  <div class="row g-3">
    <div class="col-md-6"><p class="small text-muted mb-0">Role</p><p class="fw-bold"><?= e(ucfirst($application['role_type'])) ?></p></div>
    <div class="col-md-6"><p class="small text-muted mb-0">Email</p><p class="fw-bold"><?= e($application['email']) ?></p></div>
    <div class="col-md-6"><p class="small text-muted mb-0">Telepon</p><p class="fw-bold"><?= e($application['phone'] ?: '-') ?></p></div>
    <div class="col-md-6"><p class="small text-muted mb-0">Pengalaman</p><p class="fw-bold"><?= (int) $application['years_experience'] ?> tahun</p></div>
    <div class="col-md-6"><p class="small text-muted mb-0">No. STR</p><p class="fw-bold"><?= e($application['str_number'] ?: '-') ?></p></div>
    <div class="col-md-6"><p class="small text-muted mb-0">No. SIPP/SIP</p><p class="fw-bold"><?= e($application['license_number'] ?: '-') ?></p></div>
    <div class="col-12"><p class="small text-muted mb-0">Spesialisasi</p><p class="fw-bold"><?= e($application['specialization'] ?: '-') ?></p></div>
    <div class="col-12"><p class="small text-muted mb-0">Bio</p><p><?= e($application['bio'] ?: '-') ?></p></div>
    <?php if ($application['cv_file_path']): ?><div class="col-md-6"><a href="<?= base_url($application['cv_file_path']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">Lihat CV</a></div><?php endif; ?>
    <?php if ($application['certificate_file_path']): ?><div class="col-md-6"><a href="<?= base_url($application['certificate_file_path']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">Lihat Sertifikat</a></div><?php endif; ?>
  </div>
</div>

<?php if ($application['status'] === 'pending'): ?>
<div class="d-flex gap-2">
  <form method="POST" action="<?= base_url('/admin/provider-applications/' . $application['id'] . '/approve') ?>">
    <?= \Core\Csrf::field() ?><button class="btn btn-success">Setujui &amp; Buat Akun</button>
  </form>
  <form method="POST" action="<?= base_url('/admin/provider-applications/' . $application['id'] . '/reject') ?>">
    <?= \Core\Csrf::field() ?>
    <input type="text" name="admin_note" class="form-control form-control-sm mb-2" placeholder="Alasan penolakan (opsional)">
    <button class="btn btn-outline-danger">Tolak</button>
  </form>
</div>
<?php else: ?>
  <div class="alert alert-info small">Status: <strong><?= e(ucfirst($application['status'])) ?></strong><?php if ($application['admin_note']): ?><br><?= e($application['admin_note']) ?><?php endif; ?></div>
<?php endif; ?>
