<?php $isEdit = $channel !== null; $type = $channel['type'] ?? 'bank'; ?>
<h4 class="fw-bold mb-3"><?= $isEdit ? 'Edit Kanal Pembayaran' : 'Tambah Kanal Pembayaran' ?></h4>

<div class="card p-3" style="max-width:720px">
  <form method="POST"
        action="<?= $isEdit ? base_url('/admin/payment-channels/' . $channel['id'] . '/update') : base_url('/admin/payment-channels') ?>"
        enctype="multipart/form-data" id="paymentChannelForm">
    <?= \Core\Csrf::field() ?>

    <div class="mb-3">
      <label class="form-label small d-block">Jenis Kanal <span class="text-danger">*</span></label>
      <div class="btn-group" role="group">
        <input type="radio" class="btn-check" name="type" id="typeBank" value="bank" <?= $type === 'bank' ? 'checked' : '' ?>>
        <label class="btn btn-outline-secondary btn-sm" for="typeBank"><i class="fas fa-building-columns me-1"></i>Transfer Bank</label>
        <input type="radio" class="btn-check" name="type" id="typeQris" value="qris" <?= $type === 'qris' ? 'checked' : '' ?>>
        <label class="btn btn-outline-secondary btn-sm" for="typeQris"><i class="fas fa-qrcode me-1"></i>QRIS</label>
      </div>
    </div>

    <div class="mb-2">
      <label class="form-label small">Nama/Label <span class="text-danger">*</span>
        <span class="text-muted">(tampil ke client, cth: "BCA — Utama" atau "QRIS Klinik")</span></label>
      <input type="text" name="label" class="form-control form-control-sm" required maxlength="100" value="<?= e($channel['label'] ?? '') ?>">
    </div>

    <div id="bankFields" style="<?= $type === 'bank' ? '' : 'display:none' ?>">
      <div class="row g-2 mb-2">
        <div class="col-md-6">
          <label class="form-label small">Nama Bank <span class="text-danger">*</span></label>
          <input type="text" name="bank_name" class="form-control form-control-sm" maxlength="80" value="<?= e($channel['bank_name'] ?? '') ?>" placeholder="cth: Bank Central Asia (BCA)">
        </div>
        <div class="col-md-6">
          <label class="form-label small">Nomor Rekening <span class="text-danger">*</span></label>
          <input type="text" name="account_number" class="form-control form-control-sm font-monospace" maxlength="60" value="<?= e($channel['account_number'] ?? '') ?>" placeholder="cth: 3730062752">
        </div>
      </div>
    </div>

    <div class="mb-2">
      <label class="form-label small">Atas Nama</label>
      <input type="text" name="account_name" class="form-control form-control-sm" maxlength="100" value="<?= e($channel['account_name'] ?? '') ?>" placeholder="cth: PT Tumbuh Anak Bangsa">
    </div>

    <div class="mb-3">
      <label class="form-label small d-block">
        Gambar <span class="text-muted">(wajib untuk QRIS; opsional logo bank)</span>
      </label>
      <?php if (!empty($channel['image_path'])): ?>
        <img src="<?= base_url($channel['image_path']) ?>" alt="" style="width:120px;height:120px;object-fit:contain;border:1px solid var(--nfg-border);border-radius:.6rem;background:#fff;" class="mb-2 d-block">
        <p class="text-muted small mb-2">Gambar saat ini. Pilih file baru untuk mengganti.</p>
      <?php endif; ?>
      <input type="file" name="image_file" class="form-control form-control-sm" accept="image/jpeg,image/png,image/webp" style="max-width:340px;">
      <p class="text-muted small mt-1 mb-0">JPG/PNG/WebP maks 2MB. Untuk QRIS gunakan gambar kode QR resolusi baik.</p>
    </div>

    <div class="mb-3">
      <label class="form-label small">Instruksi Tambahan <span class="text-muted">(opsional, tampil di halaman pembayaran)</span></label>
      <textarea name="instructions" class="form-control form-control-sm" rows="2"><?= e($channel['instructions'] ?? '') ?></textarea>
    </div>

    <div class="form-check mb-3">
      <input type="checkbox" name="is_active" value="1" class="form-check-input" id="isActive" <?= ($channel['is_active'] ?? 1) ? 'checked' : '' ?>>
      <label class="form-check-label small" for="isActive">Aktif (tampil ke client)</label>
    </div>

    <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Simpan Perubahan' : 'Tambah Kanal' ?></button>
    <a href="<?= base_url('/admin/payment-channels') ?>" class="btn btn-outline-secondary">Batal</a>
  </form>
</div>

<script>
const bankFields = document.getElementById('bankFields');
document.querySelectorAll('input[name="type"]').forEach(r => {
  r.addEventListener('change', () => {
    bankFields.style.display = document.getElementById('typeBank').checked ? '' : 'none';
  });
});
document.getElementById('paymentChannelForm').addEventListener('submit', function (e) {
  const isQris = document.getElementById('typeQris').checked;
  const hasImage = this.elements.image_file.files.length > 0;
  const existingImage = <?= !empty($channel['image_path']) ? 'true' : 'false' ?>;
  if (isQris && !hasImage && !existingImage) {
    e.preventDefault();
    alert('Unggah gambar QRIS terlebih dahulu.');
  }
});
</script>
