<h4 class="fw-bold mb-3">Aplikasi Psikolog</h4>
<?php if (empty($applications)): ?>
  <p class="text-muted">Belum ada aplikasi.</p>
<?php else: ?>
<div class="table-responsive">
<table class="table">
  <thead><tr><th>Nama</th><th>Role</th><th>Email</th><th>Pengalaman</th><th>Status</th><th>Aksi</th></tr></thead>
  <tbody>
  <?php foreach ($applications as $a): ?>
    <tr>
      <td><?= e($a['name']) ?></td>
      <td><?= e(ucfirst($a['role_type'])) ?></td>
      <td><?= e($a['email']) ?></td>
      <td><?= (int) $a['years_experience'] ?> tahun</td>
      <td><span class="badge bg-<?= $a['status'] === 'approved' ? 'success' : ($a['status'] === 'rejected' ? 'danger' : 'warning text-dark') ?>"><?= e(ucfirst($a['status'])) ?></span></td>
      <td><a href="<?= base_url('/admin/provider-applications/' . $a['id']) ?>" class="btn btn-sm btn-outline-primary">Detail</a></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php endif; ?>
