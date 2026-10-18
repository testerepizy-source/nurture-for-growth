<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="fw-bold mb-0">Data Client</h4>
  <a href="<?= base_url('/admin/patients/create') ?>" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Tambah Client</a>
</div>
<div class="card p-3">
  <div class="table-responsive">
    <table class="table table-sm align-middle">
      <thead><tr><th>No. RM</th><th>Nama</th><th>Email</th><th>Status</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($patients as $p): ?>
        <tr>
          <td><?= e($p['medical_record_number']) ?></td>
          <td><?= e($p['name']) ?></td>
          <td><?= e($p['email']) ?></td>
          <td><span class="badge <?= $p['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>"><?= e($p['status']) ?></span></td>
          <td class="text-end">
            <a href="<?= base_url('/admin/patients/' . $p['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary">Edit</a>
            <form method="POST" action="<?= base_url('/admin/patients/' . $p['id'] . '/delete') ?>" class="d-inline" onsubmit="return confirm('Hapus client ini? Data terkait akan tetap tersimpan.');">
              <?= \Core\Csrf::field() ?>
              <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>