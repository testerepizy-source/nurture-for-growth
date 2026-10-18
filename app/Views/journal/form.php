<?php $isEdit = $journal !== null; ?>
<h4 class="fw-bold mb-3"><?= $isEdit ? 'Edit Jurnal' : 'Tulis Jurnal Baru' ?></h4>

<div class="card p-3" style="max-width:720px">
  <form method="POST" action="<?= $isEdit ? base_url('/journal/' . $journal['id'] . '/update') : base_url('/journal') ?>">
    <?= \Core\Csrf::field() ?>
    <div class="mb-3">
      <label class="form-label small">Judul</label>
      <input type="text" name="title" class="form-control" required value="<?= e($journal['title'] ?? '') ?>">
    </div>
    <div class="row g-2 mb-3">
      <div class="col-md-6">
        <label class="form-label small">Tanggal</label>
        <input type="date" name="journal_date" class="form-control" value="<?= e($journal['journal_date'] ?? date('Y-m-d')) ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label small">Kategori</label>
        <input type="text" name="category" class="form-control" value="<?= e($journal['category'] ?? '') ?>" placeholder="Misal: Refleksi, Syukur">
      </div>
    </div>
    <div class="mb-3">
      <label class="form-label small">Tag</label>
      <input type="text" name="tag" class="form-control" value="<?= e($journal['tag'] ?? '') ?>" placeholder="pisahkan dengan koma">
    </div>
    <div class="mb-3">
      <label class="form-label small">Isi Jurnal</label>
      <textarea name="content" rows="8" class="form-control" required><?= e($journal['content'] ?? '') ?></textarea>
    </div>
    <div class="form-check mb-2">
      <input class="form-check-input" type="checkbox" name="is_favorite" value="1" id="fav" <?= !empty($journal['is_favorite']) ? 'checked' : '' ?>>
      <label class="form-check-label small" for="fav">Tandai sebagai favorit</label>
    </div>
    <div class="form-check mb-3">
      <input class="form-check-input" type="checkbox" name="grant_access" value="1" id="grant" <?= isset($journal['is_private']) && !$journal['is_private'] ? 'checked' : '' ?>>
      <label class="form-check-label small" for="grant">Izinkan psikolog saya yang menangani untuk melihat jurnal ini</label>
    </div>
    <p class="text-muted small"><i class="fas fa-circle-info me-1"></i>Secara default jurnal bersifat privat. Lampiran foto/voice memerlukan konfigurasi storage tambahan dan belum tersedia pada versi starter ini.</p>
    <button class="btn btn-primary" type="submit"><?= $isEdit ? 'Simpan Perubahan' : 'Simpan Jurnal' ?></button>
    <a href="<?= base_url('/journal') ?>" class="btn btn-outline-secondary">Batal</a>
  </form>
</div>
