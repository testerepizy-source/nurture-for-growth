<h4 class="fw-bold mb-3">Data Peran</h4>
<div class="card p-3">
  <div class="table-responsive">
    <table class="table table-sm align-middle">
      <thead><tr><th>Role</th><th>Deskripsi</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php foreach ($roles as $r): ?>
        <tr>
          <td><span class="badge bg-primary-soft text-primary"><?= e($r['label']) ?></span></td>
          <td class="small text-muted"><?= e($r['description']) ?></td>
          <td><a href="<?= base_url('/admin/roles/' . $r['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-key me-1"></i>Kelola Permission</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
