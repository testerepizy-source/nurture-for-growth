<?php $isEdit = $article !== null; ?>
<h4 class="fw-bold mb-3"><?= $isEdit ? 'Edit Artikel' : 'Tambah Artikel' ?></h4>
<div class="card p-3" style="max-width:720px">
  <form method="POST" action="<?= $isEdit ? base_url('/admin/articles/' . $article['id'] . '/update') : base_url('/admin/articles') ?>" enctype="multipart/form-data">
    <?= \Core\Csrf::field() ?>
    <div class="mb-3">
      <label class="form-label small d-block">Foto/Thumbnail Artikel</label>
      <?php if (!empty($article['thumbnail'])): ?>
        <img src="<?= base_url($article['thumbnail']) ?>" alt="" style="width:120px;height:80px;object-fit:cover;border-radius:.5rem;" class="mb-2 d-block">
      <?php endif; ?>
      <input type="file" name="thumbnail" class="form-control form-control-sm" accept="image/jpeg,image/png,image/webp">
      <p class="text-muted small mt-1 mb-0">JPG, PNG, atau WEBP. Maks 2MB. <?= !empty($article['thumbnail']) ? 'Kosongkan jika tidak ingin mengganti foto.' : '' ?></p>
    </div>
    <div class="row g-2 mb-2">
      <div class="col-md-6">
        <label class="form-label small">Kategori</label>
        <select name="category_id" class="form-select form-select-sm">
          <option value="">-</option>
          <?php foreach ($categories as $c): ?>
            <option value="<?= $c['id'] ?>" <?= ($article['category_id'] ?? null) == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label small">Tipe</label>
        <select name="type" class="form-select form-select-sm">
          <?php foreach (['article'=>'Artikel','video'=>'Video','podcast'=>'Podcast','meditation'=>'Meditasi','infographic'=>'Infografis'] as $val => $lbl): ?>
            <option value="<?= $val ?>" <?= ($article['type'] ?? '') === $val ? 'selected' : '' ?>><?= $lbl ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="mb-2">
      <label class="form-label small">Judul</label>
      <input type="text" name="title" class="form-control form-control-sm" required value="<?= e($article['title'] ?? '') ?>">
    </div>
    <div class="mb-2">
      <label class="form-label small">Ringkasan (Excerpt)</label>
      <textarea name="excerpt" class="form-control form-control-sm" rows="2"><?= e($article['excerpt'] ?? '') ?></textarea>
    </div>
    <div class="mb-2">
      <label class="form-label small">Konten</label>
      <textarea name="content" class="form-control form-control-sm" rows="6"><?= e($article['content'] ?? '') ?></textarea>
    </div>
    <div class="mb-2">
      <label class="form-label small">URL Media (video/podcast, opsional)</label>
      <input type="text" name="media_url" class="form-control form-control-sm" value="<?= e($article['media_url'] ?? '') ?>">
    </div>
    <div class="mb-2">
      <label class="form-label small">Tag</label>
      <input type="text" name="tag" class="form-control form-control-sm" value="<?= e($article['tag'] ?? '') ?>">
    </div>
    <div class="form-check mb-3">
      <input class="form-check-input" type="checkbox" name="is_published" value="1" id="pub" <?= ($article['is_published'] ?? 1) ? 'checked' : '' ?>>
      <label class="form-check-label small" for="pub">Publikasikan</label>
    </div>
    <button class="btn btn-primary btn-sm" type="submit">Simpan</button>
    <a href="<?= base_url('/admin/articles') ?>" class="btn btn-outline-secondary btn-sm">Batal</a>
  </form>
</div>
