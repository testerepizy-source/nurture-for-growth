<style>
  .jobs-form-card{ background:#fff; border-radius:18px; box-shadow:var(--shadow-sm); padding:22px; }
  .jobs-form-card h6{ font-weight:800; }
  .form-label-jv{ font-size:.78rem; font-weight:600; color:var(--ink-soft); margin-bottom:4px; }
  .jv-hint{ font-size:.72rem; color:var(--ink-faint); margin-top:3px; }
  .job-status-badge{ font-size:.72rem; font-weight:700; border-radius:999px; padding:4px 12px; }
  .job-status-badge.published{ background:#e5f6ec; color:#0a7d33; }
  .job-status-badge.draft{ background:#f1f1f1; color:#666; }
  .job-status-badge.closed{ background:#fdecec; color:#a03030; }
</style>

<h4 class="fw-bold mb-1">Lowongan Pekerjaan</h4>
<p class="text-muted small">Buat dan kelola lowongan yang tampil di halaman publik <a href="<?= base_url('/karier') ?>" target="_blank">Karier</a>.</p>

<div class="row g-3">
  <div class="col-xl-5">
    <div class="jobs-form-card">
      <h6 class="mb-3"><?= $editing ? 'Edit Lowongan' : 'Tambah Lowongan' ?></h6>
      <form method="POST" action="<?= $editing ? base_url('/admin/jobs/' . $job['id'] . '/update') : base_url('/admin/jobs') ?>">
        <?= \Core\Csrf::field() ?>
        <div class="row g-2">
          <div class="col-12">
            <label class="form-label-jv">Judul Posisi *</label>
            <input type="text" name="title" class="form-control form-control-sm" value="<?= e($job['title'] ?? '') ?>" placeholder="cth: Psikolog Junior" required>
          </div>
          <div class="col-md-6">
            <label class="form-label-jv">Departemen</label>
            <input type="text" name="department" class="form-control form-control-sm" value="<?= e($job['department'] ?? '') ?>" placeholder="cth: Psikologi">
          </div>
          <div class="col-md-6">
            <label class="form-label-jv">Lokasi</label>
            <input type="text" name="location" class="form-control form-control-sm" value="<?= e($job['location'] ?? '') ?>" placeholder="cth: Jakarta Barat">
          </div>
          <div class="col-md-6">
            <label class="form-label-jv">Tipe Kerja</label>
            <select name="employment_type" class="form-select form-select-sm">
              <?php foreach ($types as $tk => $tl): ?>
                <option value="<?= e($tk) ?>" <?= (($job['employment_type'] ?? '') === $tk) ? 'selected' : '' ?>><?= e($tl) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label-jv">Sistem Kerja</label>
            <select name="work_mode" class="form-select form-select-sm">
              <?php foreach ($modes as $mk => $ml): ?>
                <option value="<?= e($mk) ?>" <?= (($job['work_mode'] ?? '') === $mk) ? 'selected' : '' ?>><?= e($ml) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label-jv">Gaji Min (Rp)</label>
            <input type="number" name="salary_min" min="0" step="100000" class="form-control form-control-sm" value="<?= e($job['salary_min'] ?? '') ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label-jv">Gaji Maks (Rp)</label>
            <input type="number" name="salary_max" min="0" step="100000" class="form-control form-control-sm" value="<?= e($job['salary_max'] ?? '') ?>">
          </div>
          <div class="col-md-4 d-flex align-items-end pb-1">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="salary_visible" value="1" id="salaryVisibleChk" <?= ($editing && !(int)$job['salary_visible']) ? '' : 'checked' ?>>
              <label class="form-check-label small" for="salaryVisibleChk">Tampilkan gaji</label>
            </div>
          </div>
          <div class="col-12">
            <label class="form-label-jv">Deskripsi Singkat <span class="text-muted">(tampil di kartu lowongan)</span></label>
            <input type="text" name="short_description" maxlength="255" class="form-control form-control-sm" value="<?= e($job['short_description'] ?? '') ?>">
          </div>
          <div class="col-12">
            <label class="form-label-jv">Deskripsi Pekerjaan</label>
            <textarea name="description" rows="4" class="form-control form-control-sm"><?= e($job['description'] ?? '') ?></textarea>
          </div>
          <div class="col-12">
            <label class="form-label-jv">Kualifikasi / Persyaratan</label>
            <textarea name="requirements" rows="4" class="form-control form-control-sm" placeholder="Satu poin per baris"><?= e($job['requirements'] ?? '') ?></textarea>
            <div class="jv-hint"><i class="fas fa-circle-info me-1"></i>Tiap baris otomatis menjadi satu poin dengan ikon centang.</div>
          </div>
          <div class="col-12">
            <label class="form-label-jv">Benefit</label>
            <textarea name="benefits" rows="3" class="form-control form-control-sm" placeholder="Satu poin per baris"><?= e($job['benefits'] ?? '') ?></textarea>
          </div>
          <div class="col-md-6">
            <label class="form-label-jv">Email Penerima Lamaran</label>
            <input type="email" name="contact_email" class="form-control form-control-sm" value="<?= e($job['contact_email'] ?? '') ?>" placeholder="default email klinik">
          </div>
          <div class="col-md-6">
            <label class="form-label-jv">Batas Waktu Lamaran</label>
            <input type="date" name="deadline_date" min="<?= date('Y-m-d') ?>" class="form-control form-control-sm" value="<?= e($job['deadline_date'] ?? '') ?>">
            <div class="jv-hint">Kosongkan jika diterima terus-menerus.</div>
          </div>
          <div class="col-md-8">
            <label class="form-label-jv">Catatan Cara Melamar</label>
            <input type="text" name="apply_note" maxlength="255" class="form-control form-control-sm" value="<?= e($job['apply_note'] ?? '') ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label-jv">Urutan Tampil</label>
            <input type="number" name="sort_order" min="0" class="form-control form-control-sm" value="<?= e($job['sort_order'] ?? '0') ?>">
          </div>
          <div class="col-12">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" role="switch" name="is_published" value="1" id="publishedSwitch" <?= (!$editing || (int)($job['is_published'] ?? 0) === 1) ? 'checked' : '' ?>>
              <label class="form-check-label small fw-semibold" for="publishedSwitch">Terbitkan ke halaman Karier</label>
            </div>
          </div>
        </div>
        <div class="mt-3 d-flex gap-2">
          <button class="btn btn-primary btn-sm px-3" type="submit"><?= $editing ? 'Perbarui' : 'Simpan Lowongan' ?></button>
          <?php if ($editing): ?>
            <a href="<?= base_url('/admin/jobs') ?>" class="btn btn-outline-secondary btn-sm">Batal</a>
          <?php endif; ?>
        </div>
      </form>
    </div>
  </div>

  <div class="col-xl-7">
    <div class="card p-3">
      <table class="table table-sm align-middle">
        <thead><tr><th>Posisi</th><th>Detail</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php if (empty($jobs)): ?>
          <tr><td colspan="4" class="text-center text-muted small py-4">Belum ada lowongan. Buat lowongan pertama melalui formulir.</td></tr>
        <?php endif; ?>
        <?php foreach ($jobs as $j):
          if ((int) $j['is_published'] === 0) { $st = ['draft', 'Draft']; }
          elseif (!empty($j['deadline_date']) && $j['deadline_date'] < date('Y-m-d')) { $st = ['closed', 'Ditutup']; }
          else { $st = ['published', 'Terbit']; }
        ?>
          <tr>
            <td class="small">
              <span class="fw-semibold"><?= e($j['title']) ?></span><br>
              <span class="text-muted"><?= e($j['department'] ?: '-') ?> &middot; <?= e(\App\Models\JobVacancy::EMPLOYMENT_TYPES[$j['employment_type']]) ?></span>
            </td>
            <td class="small text-muted">
              <?= e($j['location'] ?: 'Jakarta') ?><br>
              Deadline: <?= !empty($j['deadline_date']) ? format_date($j['deadline_date'], 'd M Y') : '-' ?>
            </td>
            <td><span class="job-status-badge <?= $st[0] ?>"><?= $st[1] ?></span></td>
            <td class="text-end text-nowrap">
              <a href="<?= base_url('/karier/' . $j['slug']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary me-1" title="Lihat halaman"><i class="fas fa-eye"></i></a>
              <a href="<?= base_url('/admin/jobs?edit=' . (int) $j['id']) ?>" class="btn btn-sm btn-outline-primary me-1" title="Edit"><i class="fas fa-edit"></i></a>
              <a href="<?= base_url('/admin/jobs/' . $j['id'] . '/toggle') ?>" class="btn btn-sm btn-outline-warning me-1" title="<?= (int) $j['is_published'] ? 'Jadikan draft' : 'Terbitkan' ?>"><i class="fas fa-power-off"></i></a>
              <a href="<?= base_url('/admin/jobs/' . $j['id'] . '/delete') ?>" class="btn btn-sm btn-outline-danger" title="Hapus" onclick="return confirm('Hapus lowongan ini?')"><i class="fas fa-trash"></i></a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
