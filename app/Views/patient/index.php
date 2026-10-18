<h4 class="fw-bold mb-3">Daftar Client Saya</h4>
<div class="card p-3">
  <div class="table-responsive d-none d-md-block">
    <table class="table table-sm align-middle mb-0">
      <thead><tr><th>No. RM</th><th>Nama</th><th>Email</th><th>Telp</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($patients as $p): ?>
        <tr>
          <td><?= e($p['medical_record_number']) ?></td>
          <td><?= e($p['name']) ?></td>
          <td><?= e($p['email'] ?? '-') ?></td>
          <td><?= e($p['phone'] ?? '-') ?></td>
          <td><a href="<?= base_url('/patients/' . $p['id']) ?>" class="btn btn-sm btn-outline-primary">Lihat Rekam</a></td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($patients)): ?><tr><td colspan="5" class="text-center text-muted py-3">Belum ada client yang ditugaskan kepada Anda.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="d-md-none">
    <?php if (empty($patients)): ?>
      <div class="text-center text-muted py-3">Belum ada client yang ditugaskan kepada Anda.</div>
    <?php else: ?>
      <?php foreach ($patients as $p): ?>
        <div class="border rounded-3 p-3 mb-2 shadow-sm">
          <div class="d-flex justify-content-between align-items-start gap-2">
            <div>
              <div class="fw-semibold"><?= e($p['name']) ?></div>
              <div class="small text-muted">No. RM: <?= e($p['medical_record_number']) ?></div>
            </div>
            <a href="<?= base_url('/patients/' . $p['id']) ?>" class="btn btn-sm btn-outline-primary flex-shrink-0">Lihat</a>
          </div>
          <div class="small text-muted mt-2">
            <div class="mb-1"><i class="fas fa-envelope me-2"></i><?= e($p['email'] ?? '-') ?></div>
            <div><i class="fas fa-phone me-2"></i><?= e($p['phone'] ?? '-') ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>
