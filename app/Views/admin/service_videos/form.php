<?php $isEdit = $video !== null; ?>
<h4 class="fw-bold mb-3"><?= $isEdit ? 'Edit Video Layanan' : 'Tambah Video Layanan' ?></h4>

<div class="card p-3" style="max-width:760px">
  <form method="POST"
        action="<?= $isEdit ? base_url('/admin/service-videos/' . $video['id'] . '/update') : base_url('/admin/service-videos') ?>"
        enctype="multipart/form-data" id="serviceVideoForm">
    <?= \Core\Csrf::field() ?>

    <div class="mb-2">
      <label class="form-label small">Judul <span class="text-danger">*</span></label>
      <input type="text" name="title" class="form-control form-control-sm" required maxlength="150"
             value="<?= e($video['title'] ?? '') ?>" placeholder="cth: Kenalan dengan Layanan Konseling NFG">
    </div>
    <div class="mb-2">
      <label class="form-label small">Badge/Label Kecil <span class="text-muted">(mis. "Telekonseling", "Asesmen")</span></label>
      <input type="text" name="badge_text" class="form-control form-control-sm" maxlength="50" value="<?= e($video['badge_text'] ?? '') ?>">
    </div>
    <div class="mb-3">
      <label class="form-label small">Deskripsi Singkat</label>
      <textarea name="description" class="form-control form-control-sm" rows="2" maxlength="500"><?= e($video['description'] ?? '') ?></textarea>
    </div>

    <div class="border rounded p-3 mb-3 bg-light">
      <label class="form-label small fw-semibold mb-1"><i class="fas fa-video me-1"></i>Sumber Video <span class="text-danger">*</span></label>
      <p class="text-muted small mb-2">Isi salah satu: URL YouTube/Vimeo, URL file video, atau unggah file langsung. Jika keduanya diisi saat mengedit, unggahan yang dipakai.</p>
      <?php if ($isEdit): ?>
        <p class="small mb-2">
          <?php if (!empty($video['video_url'])): ?>
            <span class="badge bg-info-subtle text-dark me-1">URL aktif:</span>
            <code class="small text-truncate d-inline-block align-middle" style="max-width:420px;vertical-align:middle;"><?= e($video['video_url']) ?></code>
          <?php elseif (!empty($video['video_path'])): ?>
            <span class="badge bg-info-subtle text-dark me-1">File aktif:</span>
            <code><?= e(basename($video['video_path'])) ?></code>
          <?php endif; ?>
        </p>
      <?php endif; ?>
      <div class="row g-2">
        <div class="col-md-7">
          <input type="url" name="video_url" class="form-control form-control-sm" maxlength="255"
                 value="<?= e($video['video_url'] ?? '') ?>" placeholder="https://www.youtube.com/watch?v=...">
          <p class="text-muted small mt-1 mb-0">URL YouTube, Vimeo, atau tautan langsung .mp4/.webm</p>
        </div>
        <div class="col-md-5">
          <input type="file" name="video_file" class="form-control form-control-sm <?= $isEdit ? '' : 'js-video-source' ?>" accept="video/mp4,video/webm">
          <p class="text-muted small mt-1 mb-0">MP4/WebM, maks 32MB</p>
        </div>
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label small d-block">Thumbnail <span class="text-muted">(opsional — otomatis dari YouTube jika kosong)</span></label>
      <?php $thumb = $isEdit ? \App\Models\ServiceVideo::thumbUrl($video) : null; ?>
      <?php if ($thumb): ?>
        <img src="<?= e($thumb) ?>" alt="" style="width:200px;height:112px;object-fit:cover;border-radius:.5rem;" class="mb-2 d-block">
        <p class="text-muted small mb-2">Thumbnail saat ini. Pilih file baru untuk mengganti.</p>
      <?php endif; ?>
      <input type="file" name="thumbnail_file" class="form-control form-control-sm" accept="image/jpeg,image/png,image/webp" style="max-width:340px;">
      <p class="text-muted small mt-1 mb-0">JPG/PNG/WEBP maks 3MB, rasio 16:9</p>
    </div>

    <div class="form-check mb-3">
      <input type="checkbox" name="is_active" value="1" class="form-check-input" id="isActive" <?= ($video['is_active'] ?? 1) ? 'checked' : '' ?>>
      <label class="form-check-label small" for="isActive">Aktif (tampil di landing page)</label>
    </div>

    <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Simpan Perubahan' : 'Tambah Video' ?></button>
    <a href="<?= base_url('/admin/service-videos') ?>" class="btn btn-outline-secondary">Batal</a>
  </form>
</div>

<script>
document.getElementById('serviceVideoForm').addEventListener('submit', function (e) {
  const hasUrl = this.elements.video_url.value.trim() !== '';
  const hasFile = this.elements.video_file.files.length > 0;
  const existingSource = <?= $isEdit && (!empty($video['video_url']) || !empty($video['video_path'])) ? 'true' : 'false' ?>;
  if (!hasUrl && !hasFile && !existingSource) {
    e.preventDefault();
    alert('Isi URL video atau unggah file video terlebih dahulu.');
  }
});
</script>
