<h4 class="fw-bold mb-1">Kelola Pertanyaan: <?= e($assessment['title']) ?></h4>
<p class="text-muted small mb-3">Setiap pertanyaan dapat memiliki beberapa pilihan jawaban dengan bobot skor masing-masing.</p>

<div class="row g-3 mb-4">
  <div class="col-md-6">
    <div class="card p-3">
      <h6 class="fw-bold mb-2">Tambah Pertanyaan</h6>
      <form method="POST" action="<?= base_url('/admin/assessments/' . $assessment['id'] . '/questions') ?>">
        <?= \Core\Csrf::field() ?>
        <div class="mb-2">
          <label class="form-label small">Teks Pertanyaan</label>
          <textarea name="question_text" class="form-control form-control-sm" rows="2" required></textarea>
        </div>
        <div class="mb-2">
          <label class="form-label small">Urutan</label>
          <input type="number" name="order_no" class="form-control form-control-sm" value="0">
        </div>
        <div id="optionsWrap">
          <label class="form-label small">Pilihan Jawaban &amp; Skor</label>
          <?php for ($i = 0; $i < 4; $i++): ?>
            <div class="input-group input-group-sm mb-1">
              <input type="text" name="option_text[]" class="form-control" placeholder="Teks pilihan">
              <input type="number" name="option_score[]" class="form-control" placeholder="Skor" style="max-width:90px">
            </div>
          <?php endfor; ?>
        </div>
        <button class="btn btn-primary btn-sm mt-2" type="submit">Simpan Pertanyaan</button>
      </form>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card p-3">
      <h6 class="fw-bold mb-2">Tambah Rentang Skor / Interpretasi</h6>
      <form method="POST" action="<?= base_url('/admin/assessments/' . $assessment['id'] . '/ranges') ?>">
        <?= \Core\Csrf::field() ?>
        <div class="row g-2 mb-2">
          <div class="col-6">
            <label class="form-label small">Skor Min</label>
            <input type="number" name="min_score" class="form-control form-control-sm" required>
          </div>
          <div class="col-6">
            <label class="form-label small">Skor Max</label>
            <input type="number" name="max_score" class="form-control form-control-sm" required>
          </div>
        </div>
        <div class="mb-2">
          <label class="form-label small">Label Kategori</label>
          <input type="text" name="category_label" class="form-control form-control-sm" required placeholder="Misal: Ringan, Sedang, Berat">
        </div>
        <div class="mb-2">
          <label class="form-label small">Interpretasi</label>
          <textarea name="interpretation" class="form-control form-control-sm" rows="2"></textarea>
        </div>
        <div class="mb-2">
          <label class="form-label small">Saran</label>
          <textarea name="suggestion" class="form-control form-control-sm" rows="2"></textarea>
        </div>
        <div class="form-check mb-2">
          <input class="form-check-input" type="checkbox" name="is_high_risk" value="1" id="hr">
          <label class="form-check-label small" for="hr">Tandai sebagai risiko tinggi (memicu alert)</label>
        </div>
        <button class="btn btn-primary btn-sm" type="submit">Simpan Rentang Skor</button>
      </form>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-md-7">
    <div class="card p-3">
      <h6 class="fw-bold mb-2">Daftar Pertanyaan</h6>
      <?php foreach ($questions as $i => $q): ?>
        <div class="border-bottom py-2">
          <div class="d-flex justify-content-between">
            <strong class="small"><?= ($i + 1) ?>. <?= e($q['question_text']) ?></strong>
            <a href="<?= base_url('/admin/assessments/' . $assessment['id'] . '/questions/' . $q['id'] . '/delete') ?>" class="text-danger small"><i class="fas fa-trash"></i></a>
          </div>
          <ul class="small text-muted mb-0">
            <?php foreach ($q['options'] as $opt): ?>
              <li><?= e($opt['option_text']) ?> (skor: <?= (int) $opt['score'] ?>)</li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endforeach; ?>
      <?php if (empty($questions)): ?><p class="text-muted small">Belum ada pertanyaan.</p><?php endif; ?>
    </div>
  </div>
  <div class="col-md-5">
    <div class="card p-3">
      <h6 class="fw-bold mb-2">Rentang Skor Terdaftar</h6>
      <table class="table table-sm">
        <thead><tr><th>Range</th><th>Label</th><th>Risiko</th></tr></thead>
        <tbody>
        <?php foreach ($ranges as $r): ?>
          <tr>
            <td><?= (int) $r['min_score'] ?>-<?= (int) $r['max_score'] ?></td>
            <td><?= e($r['category_label']) ?></td>
            <td><?= $r['is_high_risk'] ? '<span class="badge badge-risk-high">Tinggi</span>' : '<span class="badge badge-risk-low">Rendah</span>' ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
