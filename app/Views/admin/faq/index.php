<h4 class="fw-bold mb-3">Data FAQ</h4>
<div class="row g-3">
  <div class="col-md-5">
    <div class="card p-3">
      <h6 class="fw-bold mb-2"><?= isset($faq) ? 'Edit FAQ' : 'Tambah FAQ' ?></h6>
      <form method="POST" action="<?= isset($faq) ? base_url('/admin/faq/' . $faq['id'] . '/update') : base_url('/admin/faq') ?>">
        <?= \Core\Csrf::field() ?>
        <div class="mb-2"><label class="form-label small">Pertanyaan</label><input type="text" name="question" class="form-control form-control-sm" value="<?= isset($faq) ? e($faq['question']) : '' ?>" required></div>
        <div class="mb-2"><label class="form-label small">Jawaban</label><textarea name="answer" class="form-control form-control-sm" rows="3" required><?= isset($faq) ? e($faq['answer']) : '' ?></textarea></div>
        <div class="mb-2"><label class="form-label small">Kategori</label><input type="text" name="category" class="form-control form-control-sm" value="<?= isset($faq) ? e($faq['category']) : '' ?>"></div>
        <div class="mb-2"><label class="form-label small">Urutan</label><input type="number" name="order_no" class="form-control form-control-sm" value="<?= isset($faq) ? e($faq['order_no']) : '0' ?>"></div>
        <button class="btn btn-primary btn-sm" type="submit"><?= isset($faq) ? 'Perbarui' : 'Simpan' ?></button>
        <?php if (isset($faq)): ?>
          <a href="<?= base_url('/admin/faq') ?>" class="btn btn-outline-secondary btn-sm ms-2">Batal</a>
        <?php endif; ?>
      </form>
    </div>
  </div>
  <div class="col-md-7">
    <div class="card p-3">
      <table class="table table-sm">
        <thead><tr><th>Pertanyaan</th><th>Kategori</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($faqs as $f): ?>
          <tr>
            <td class="small"><?= e($f['question']) ?></td>
            <td class="small"><?= e($f['category']) ?></td>
            <td>
              <a href="<?= base_url('/admin/faq/' . $f['id'] . '/edit') ?>" class="btn btn-sm btn-outline-secondary me-1"><i class="fas fa-edit"></i></a>
              <a href="<?= base_url('/admin/faq/' . $f['id'] . '/delete') ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus FAQ ini?')"><i class="fas fa-trash"></i></a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
