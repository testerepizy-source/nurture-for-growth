<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="fw-bold mb-0">Data Artikel</h4>
  <a href="<?= base_url('/admin/articles/create') ?>" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Tambah Artikel</a>
</div>
<div class="card p-3">
  <div class="table-responsive">
    <table class="table table-sm align-middle">
      <thead><tr><th>Judul</th><th>Tipe</th><th>Kategori</th><th>Status</th><th>Views</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php foreach ($articles as $a): ?>
        <tr>
          <td><?= e($a['title']) ?></td>
          <td class="text-uppercase small"><?= e($a['type']) ?></td>
          <td><?= e($a['category_name'] ?? '-') ?></td>
          <td><span class="badge <?= $a['is_published'] ? 'bg-success' : 'bg-secondary' ?>"><?= $a['is_published'] ? 'Published' : 'Draft' ?></span></td>
          <td><?= (int) $a['views'] ?></td>
          <td>
            <a href="<?= base_url('/admin/articles/' . $a['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-pen"></i></a>
            <form method="POST" action="<?= base_url('/admin/articles/' . $a['id'] . '/delete') ?>" class="d-inline" id="delArt<?= $a['id'] ?>">
              <?= \Core\Csrf::field() ?>
              <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete('delArt<?= $a['id'] ?>')"><i class="fas fa-trash"></i></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
