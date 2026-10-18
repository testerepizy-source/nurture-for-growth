<h4 class="fw-bold mb-3">Data Asesmen</h4>

<div class="row g-3 mb-4">
  <div class="col-md-5">
    <div class="card p-3">
      <h6 class="fw-bold mb-2">Tambah Kategori</h6>
      <form method="POST" action="<?= base_url('/admin/assessments/category') ?>">
        <?= \Core\Csrf::field() ?>
        <div class="mb-2">
          <label class="form-label small">Nama Kategori</label>
          <input type="text" name="name" class="form-control form-control-sm" required>
        </div>
        <div class="mb-2">
          <label class="form-label small">Deskripsi</label>
          <textarea name="description" class="form-control form-control-sm" rows="2"></textarea>
        </div>
        <button class="btn btn-primary btn-sm" type="submit">Simpan Kategori</button>
      </form>
      <hr>
      <div class="small">
        <?php foreach ($categories as $c): ?>
          <div class="d-flex justify-content-between border-bottom py-1">
            <span><?= e($c['name']) ?></span>
            <span class="text-muted"><?= e($c['slug']) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <div class="col-md-7">
    <div class="card p-3">
      <h6 class="fw-bold mb-2">Tambah Assessment / Kuesioner</h6>
      <form method="POST" action="<?= base_url('/admin/assessments') ?>">
        <?= \Core\Csrf::field() ?>
        <div class="row g-2 mb-2">
          <div class="col-md-6">
            <label class="form-label small">Kategori</label>
            <select name="category_id" class="form-select form-select-sm" required>
              <?php foreach ($categories as $c): ?>
                <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label small">Waktu Pengerjaan (menit)</label>
            <input type="number" name="time_limit_minutes" class="form-control form-control-sm" value="10">
          </div>
        </div>
        <div class="mb-2">
          <label class="form-label small">Judul Assessment</label>
          <input type="text" name="title" class="form-control form-control-sm" required>
        </div>
        <div class="mb-2">
          <label class="form-label small">Deskripsi</label>
          <textarea name="description" class="form-control form-control-sm" rows="2"></textarea>
        </div>
        <button class="btn btn-primary btn-sm" type="submit">Simpan Assessment</button>
      </form>
    </div>
  </div>
</div>

<div class="assessment-list-shell">
  <div class="assessment-list-heading">
    <div><span class="admin-eyebrow">Katalog Asesmen</span><h6 class="fw-bold mb-1">Daftar Assessment</h6><p>Kelola kuesioner, waktu pengerjaan, dan pertanyaan dari satu tempat.</p></div>
    <span class="assessment-count"><?= count($assessments) ?> assessment</span>
  </div>
  <div class="assessment-card-list">
  <?php foreach ($assessments as $a): $formId = 'assessmentUpdate' . (int) $a['id']; ?>
    <article class="assessment-admin-card">
      <div class="assessment-card-top">
        <div class="assessment-card-title"><span class="assessment-card-icon"><i class="fas fa-clipboard-check"></i></span><div><span class="assessment-card-label">Assessment #<?= (int) $a['id'] ?></span><h5><?= e($a['title']) ?></h5></div></div>
        <span class="assessment-status <?= $a['is_active'] ? 'is-active' : 'is-inactive' ?>"><i class="fas fa-circle"></i><?= $a['is_active'] ? 'Aktif' : 'Nonaktif' ?></span>
      </div>
      <form id="<?= $formId ?>" method="POST" action="<?= base_url('/admin/assessments/' . $a['id'] . '/update') ?>">
        <?= \Core\Csrf::field() ?>
        <div class="assessment-edit-grid">
          <div class="assessment-edit-field assessment-edit-field-wide"><label for="title<?= (int) $a['id'] ?>">Judul</label><input id="title<?= (int) $a['id'] ?>" type="text" name="title" class="form-control" value="<?= e($a['title']) ?>" required></div>
          <div class="assessment-edit-field"><label for="category<?= (int) $a['id'] ?>">Kategori</label><select id="category<?= (int) $a['id'] ?>" name="category_id" class="form-select" required><?php foreach ($categories as $c): ?><option value="<?= (int) $c['id'] ?>" <?= (int) $c['id'] === (int) $a['category_id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></div>
          <div class="assessment-edit-field"><label for="minutes<?= (int) $a['id'] ?>">Waktu (menit)</label><input id="minutes<?= (int) $a['id'] ?>" type="number" name="time_limit_minutes" class="form-control" min="1" value="<?= max(1, (int) $a['time_limit_minutes']) ?>" required></div>
          <div class="assessment-edit-field assessment-edit-field-wide"><label for="description<?= (int) $a['id'] ?>">Deskripsi</label><textarea id="description<?= (int) $a['id'] ?>" name="description" class="form-control" rows="2" placeholder="Deskripsi singkat assessment (opsional)"><?= e($a['description'] ?? '') ?></textarea></div>
        </div>
      </form>
      <div class="assessment-card-actions">
        <button form="<?= $formId ?>" type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Simpan Perubahan</button>
        <a href="<?= base_url('/admin/assessments/' . $a['id'] . '/questions') ?>" class="btn btn-outline-primary"><i class="fas fa-list-check me-1"></i>Kelola Pertanyaan</a>
        <a href="<?= base_url('/admin/assessments/' . $a['id'] . '/toggle') ?>" class="btn btn-outline-secondary"><?= $a['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?></a>
        <form method="POST" action="<?= base_url('/admin/assessments/' . $a['id'] . '/delete') ?>" onsubmit="return confirm('Hapus assessment ini beserta pertanyaan dan rentang skornya?')">
          <?= \Core\Csrf::field() ?><button type="submit" class="btn btn-outline-danger"><i class="fas fa-trash me-1"></i>Hapus</button>
        </form>
      </div>
    </article>
  <?php endforeach; ?>
  <?php if (empty($assessments)): ?><div class="assessment-empty">Belum ada assessment.</div><?php endif; ?>
  </div>
</div>
