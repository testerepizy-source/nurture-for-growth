<?php $isEdit = $slide !== null; ?>
<h4 class="fw-bold mb-3"><?= $isEdit ? 'Edit Slide Utama' : 'Tambah Slide Utama' ?></h4>

<div class="card p-3" style="max-width:760px">
  <form method="POST" action="<?= $isEdit ? base_url('/admin/hero-slides/' . $slide['id'] . '/update') : base_url('/admin/hero-slides') ?>" id="heroSlideForm">
    <?= \Core\Csrf::field() ?>
    <input type="hidden" name="cropped_image" id="croppedImageInput">

    <div class="mb-3">
      <label class="form-label small d-block">Foto Slide <span class="text-muted">(disarankan rasio 4:3, minimal 800x600px)</span></label>
      <?php if ($isEdit && !empty($slide['image_path'])): ?>
        <img src="<?= base_url($slide['image_path']) ?>" alt="" style="width:160px;height:120px;object-fit:cover;border-radius:.5rem;" class="mb-2 d-block">
        <p class="text-muted small mb-2">Foto saat ini. Pilih file baru di bawah untuk mengganti.</p>
      <?php endif; ?>
      <input type="file" id="imageInput" class="form-control form-control-sm" accept="image/jpeg,image/png,image/webp">
      <p class="text-muted small mt-1 mb-0">JPG, PNG, atau WEBP. Setelah memilih file, Anda bisa atur crop-nya di bawah sebelum simpan.</p>
    </div>

    <div id="cropperContainer" class="mb-3" style="display:none;">
      <div style="max-width:100%;max-height:400px;overflow:hidden;">
        <img id="cropperImage" style="max-width:100%;display:block;">
      </div>
      <div class="d-flex gap-2 mt-2">
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="cropper.rotate(-90)"><i class="fas fa-rotate-left"></i></button>
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="cropper.rotate(90)"><i class="fas fa-rotate-right"></i></button>
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="cropper.zoom(0.1)"><i class="fas fa-magnifying-glass-plus"></i></button>
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="cropper.zoom(-0.1)"><i class="fas fa-magnifying-glass-minus"></i></button>
        <button type="button" class="btn btn-sm btn-primary ms-auto" onclick="applyCrop()"><i class="fas fa-check me-1"></i>Terapkan Crop</button>
      </div>
      <p class="text-muted small mt-2">Geser &amp; perbesar/perkecil kotak crop sesuai kebutuhan, lalu klik "Terapkan Crop".</p>
    </div>

    <div id="cropPreviewWrap" class="mb-3" style="display:none;">
      <label class="form-label small d-block">Preview Hasil Crop</label>
      <img id="cropPreview" style="width:200px;height:150px;object-fit:cover;border-radius:.5rem;border:1px solid var(--nfg-border);">
    </div>

    <div class="mb-2">
      <label class="form-label small">Badge/Label Kecil <span class="text-muted">(tampil di atas judul, mis. "Konseling")</span></label>
      <input type="text" name="badge_text" class="form-control form-control-sm" value="<?= e($slide['badge_text'] ?? '') ?>">
    </div>
    <div class="mb-2">
      <label class="form-label small">Judul <span class="text-muted">(boleh pakai &lt;em&gt;teks&lt;/em&gt; untuk bagian miring aksen warna)</span></label>
      <input type="text" name="title" class="form-control form-control-sm" required value="<?= e($slide['title'] ?? '') ?>">
    </div>
    <div class="mb-2">
      <label class="form-label small">Sub-judul / Deskripsi Singkat</label>
      <textarea name="subtitle" class="form-control form-control-sm" rows="2"><?= e($slide['subtitle'] ?? '') ?></textarea>
    </div>
    <div class="row g-2 mb-2">
      <div class="col-md-6">
        <label class="form-label small">Teks Tombol</label>
        <input type="text" name="cta_text" class="form-control form-control-sm" value="<?= e($slide['cta_text'] ?? 'Mulai Sekarang') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label small">Link Tombol</label>
        <input type="text" name="cta_link" class="form-control form-control-sm" value="<?= e($slide['cta_link'] ?? '/register') ?>" placeholder="/register">
      </div>
    </div>
    <div class="mb-3">
      <label class="form-label small d-block">Warna Aksen <span class="text-muted">(dipakai jika foto belum diunggah)</span></label>
      <?php $theme = $slide['color_theme'] ?? 'teal'; ?>
      <div class="btn-group" role="group">
        <input type="radio" class="btn-check" name="color_theme" id="themeTeal" value="teal" <?= $theme === 'teal' ? 'checked' : '' ?>>
        <label class="btn btn-outline-secondary btn-sm" for="themeTeal" style="border-color:#00c79c">Teal</label>
        <input type="radio" class="btn-check" name="color_theme" id="themeOrange" value="orange" <?= $theme === 'orange' ? 'checked' : '' ?>>
        <label class="btn btn-outline-secondary btn-sm" for="themeOrange" style="border-color:#f68c12">Orange</label>
        <input type="radio" class="btn-check" name="color_theme" id="themePink" value="pink" <?= $theme === 'pink' ? 'checked' : '' ?>>
        <label class="btn btn-outline-secondary btn-sm" for="themePink" style="border-color:#ee156e">Pink</label>
      </div>
    </div>
    <div class="form-check mb-3">
      <input type="checkbox" name="is_active" value="1" class="form-check-input" id="isActive" <?= ($slide['is_active'] ?? 1) ? 'checked' : '' ?>>
      <label class="form-check-label small" for="isActive">Aktif (tampil di landing page)</label>
    </div>

    <button type="submit" class="btn btn-primary">Simpan Slide</button>
    <a href="<?= base_url('/admin/hero-slides') ?>" class="btn btn-outline-secondary">Batal</a>
  </form>
</div>

<link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
<script>
let cropper = null;

document.getElementById('imageInput').addEventListener('change', function (e) {
  const file = e.target.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = function (evt) {
    const img = document.getElementById('cropperImage');
    img.src = evt.target.result;
    document.getElementById('cropperContainer').style.display = 'block';
    if (cropper) cropper.destroy();
    cropper = new Cropper(img, {
      aspectRatio: 4 / 3,
      viewMode: 1,
      autoCropArea: 1,
      background: false,
    });
  };
  reader.readAsDataURL(file);
});

function applyCrop() {
  if (!cropper) return;
  const canvas = cropper.getCroppedCanvas({ width: 1000, height: 750 });
  const dataUrl = canvas.toDataURL('image/jpeg', 0.85);
  document.getElementById('croppedImageInput').value = dataUrl;
  document.getElementById('cropPreview').src = dataUrl;
  document.getElementById('cropPreviewWrap').style.display = 'block';
}

document.getElementById('heroSlideForm').addEventListener('submit', function (e) {
  const fileChosen = document.getElementById('imageInput').files.length > 0;
  const cropped = document.getElementById('croppedImageInput').value;
  if (fileChosen && !cropped) {
    e.preventDefault();
    alert('Anda memilih foto baru, tapi belum klik "Terapkan Crop". Silakan terapkan crop terlebih dahulu.');
  }
});
</script>
