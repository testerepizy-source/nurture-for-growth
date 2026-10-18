<h4 class="fw-bold mb-1">Mental Health Screening</h4>
<p class="text-muted small mb-3">Pilih kuesioner skrining di bawah ini.</p>

<div class="disclaimer-box mb-4">
  <i class="fas fa-circle-info me-1"></i>
  <strong>Disclaimer:</strong> Hasil assessment ini merupakan <strong>skrining awal</strong> dan bukan diagnosis medis.
  Untuk penanganan lebih lanjut, silakan berkonsultasi dengan psikolog profesional.
</div>

<div class="row g-3 mb-4">
  <?php if (empty($assessments)): ?>
    <p class="text-muted">Belum ada assessment yang tersedia.</p>
  <?php endif; ?>
  <?php foreach ($assessments as $a): ?>
    <div class="col-md-6 col-lg-4">
      <div class="card p-3 h-100">
        <span class="badge bg-primary-soft text-primary mb-2 align-self-start"><?= e($a['category_name']) ?></span>
        <h6 class="fw-bold"><?= e($a['title']) ?></h6>
        <p class="text-muted small flex-grow-1"><?= e($a['description']) ?></p>
        <div class="small text-muted mb-2"><i class="fas fa-clock me-1"></i><?= (int) $a['time_limit_minutes'] ?> menit</div>
        <a href="<?= base_url('/assessment/' . $a['id'] . '/take') ?>" class="btn btn-primary btn-sm">Mulai Assessment</a>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="card p-3">
  <h6 class="fw-bold mb-3">Riwayat Pengerjaan</h6>
  <div class="table-responsive">
    <table class="table table-sm align-middle">
      <!-- <thead><tr><th>Tanggal</th><th>Assessment</th><th>Skor</th><th>Kategori</th><th></th></tr></thead> -->
      <thead><tr><th>Tanggal</th><th>Assessment</th><th></th></tr></thead>
      <tbody>
      <?php if (empty($history)): ?>
        <tr><td colspan="5" class="text-center text-muted py-3">Belum ada riwayat assessment.</td></tr>
      <?php endif; ?>
      <?php foreach ($history as $h): ?>
        <tr>
          <td><?= format_date($h['created_at'], 'd M Y H:i') ?></td>
          <td><?= e($h['assessment_title']) ?></td>
          <!-- <td><?= (int) $h['total_score'] ?></td> -->
          <!-- <td><span class="badge <?= $h['is_high_risk'] ? 'badge-risk-high' : 'badge-risk-low' ?>"><?= e($h['category_label'] ?? '-') ?></span></td> -->
          <td><a href="<?= base_url('/assessment/result/' . $h['id']) ?>" class="btn btn-sm btn-outline-primary">Lihat</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
