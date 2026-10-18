<h4 class="fw-bold mb-3">Data Tag Keluhan (Sistem Pencocokan Cerdas)</h4>
<p class="text-muted small mb-4">Tag ini dipakai client untuk memilih keluhan saat intake, dan dicocokkan ke tag spesialisasi psikolog. Kelola juga kata kunci di bawah agar keluhan dalam bentuk teks bebas bisa otomatis disarankan ke tag yang sesuai.</p>

<div class="row g-3">
  <div class="col-md-5">
    <div class="card p-3 mb-3">
      <h6 class="fw-bold mb-2">Tambah Tag Keluhan</h6>
      <form method="POST" action="<?= base_url('/admin/concern-tags') ?>">
        <?= \Core\Csrf::field() ?>
        <div class="mb-2"><label class="form-label small">Nama Tag</label><input type="text" name="name" class="form-control form-control-sm" required></div>
        <div class="mb-2"><label class="form-label small">Deskripsi</label><input type="text" name="description" class="form-control form-control-sm"></div>
        <button class="btn btn-primary btn-sm">Simpan Tag</button>
      </form>
    </div>
    <div class="card p-3">
      <h6 class="fw-bold mb-2">Tambah Kata Kunci</h6>
      <form method="POST" action="<?= base_url('/admin/concern-tags/keyword') ?>">
        <?= \Core\Csrf::field() ?>
        <div class="mb-2">
          <label class="form-label small">Tag</label>
          <select name="tag_id" class="form-select form-select-sm" required>
            <?php foreach ($tags as $t): ?><option value="<?= $t['id'] ?>"><?= e($t['name']) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="mb-2"><label class="form-label small">Kata Kunci</label><input type="text" name="keyword" class="form-control form-control-sm" placeholder="mis. 'sulit tidur'" required></div>
        <button class="btn btn-primary btn-sm">Simpan Kata Kunci</button>
      </form>
    </div>
  </div>

  <div class="col-md-7">
    <div class="card p-3 mb-3">
      <h6 class="fw-bold mb-2">Daftar Tag</h6>
      <table class="table table-sm">
        <thead><tr><th>Nama</th><th>Deskripsi</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php foreach ($tags as $t): ?>
          <tr id="tagRow<?= (int) $t['id'] ?>">
            <td><?= e($t['name']) ?></td>
            <td class="small text-muted"><?= e($t['description']) ?></td>
            <td>
              <button type="button" class="btn btn-sm btn-outline-primary" onclick="toggleTagEdit(<?= (int) $t['id'] ?>)"><i class="fas fa-pen me-1"></i>Ubah</button>
              <a href="<?= base_url('/admin/concern-tags/' . $t['id'] . '/delete') ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus tag ini?')"><i class="fas fa-trash"></i></a>
            </td>
          </tr>
          <tr id="tagEditRow<?= (int) $t['id'] ?>" class="tag-edit-row" hidden>
            <td colspan="3">
              <form method="POST" action="<?= base_url('/admin/concern-tags/' . $t['id'] . '/update') ?>" class="row g-2 align-items-end">
                <?= \Core\Csrf::field() ?>
                <div class="col-md-3"><label class="form-label small mb-1">Nama Tag</label><input type="text" name="name" class="form-control form-control-sm" value="<?= e($t['name']) ?>" required></div>
                <div class="col-md-6"><label class="form-label small mb-1">Deskripsi</label><input type="text" name="description" class="form-control form-control-sm" value="<?= e($t['description']) ?>"></div>
                <div class="col-md-3 d-flex gap-1"><button class="btn btn-primary btn-sm" type="submit"><i class="fas fa-save me-1"></i>Simpan</button><button class="btn btn-outline-secondary btn-sm" type="button" onclick="toggleTagEdit(<?= (int) $t['id'] ?>)">Batal</button></div>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="card p-3">
      <h6 class="fw-bold mb-2">Daftar Kata Kunci</h6>
      <table class="table table-sm">
        <thead><tr><th>Kata Kunci</th><th>Tag</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($keywords as $k): ?>
          <tr>
            <td><?= e($k['keyword']) ?></td>
            <td><span class="badge bg-primary-soft text-primary"><?= e($k['tag_name']) ?></span></td>
            <td><a href="<?= base_url('/admin/concern-tags/keyword/' . $k['id'] . '/delete') ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus kata kunci ini?')"><i class="fas fa-trash"></i></a></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
function toggleTagEdit(tagId) {
  const row = document.getElementById('tagEditRow' + tagId);
  if (row) row.hidden = !row.hidden;
}
</script>
