<div class="card p-4">
  <div class="text-center mb-4">
    <h5 class="text-muted mb-1">Hasil Assessment</h5>
    <h3 class="fw-bold"><?= e($result['assessment_title']) ?></h3>
  </div>

  <!-- <div class="row text-center g-3 mb-4">
    <div class="col-md-6">
      <div class="p-3 rounded bg-primary-soft">
        <div class="text-muted small">Skor Total</div>
        <div class="display-6 fw-bold text-primary"><?= (int) $result['total_score'] ?></div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="p-3 rounded <?= $result['is_high_risk'] ? 'bg-danger bg-opacity-10' : 'bg-success bg-opacity-10' ?>">
        <div class="text-muted small">Kategori</div>
        <div class="display-6 fw-bold <?= $result['is_high_risk'] ? 'text-danger' : 'text-success' ?>"><?= e($result['category_label'] ?? '-') ?></div>
      </div>
    </div>
  </div> -->

  <h6 class="fw-bold">Penjelasan</h6>
  <p class="text-muted"><?= e($result['interpretation'] ?? 'Tidak ada interpretasi tersedia.') ?></p>

  <h6 class="fw-bold">Saran Umum</h6>
  <p class="text-muted"><?= e($result['suggestion'] ?? '-') ?></p>

  <?php if ($result['is_high_risk']): ?>
  <div class="alert alert-danger">
    <i class="fas fa-triangle-exclamation me-1"></i>
    Hasil menunjukkan indikasi risiko yang perlu perhatian segera. Kami sangat menyarankan Anda berkonsultasi dengan psikolog profesional sesegera mungkin.
  </div>
  <?php endif; ?>

  <div class="disclaimer-box">
    <i class="fas fa-circle-info me-1"></i>
    <strong>Disclaimer:</strong> Hasil ini adalah skrining awal berbasis jawaban self-report dan <strong>bukan diagnosis medis</strong>.
    Diagnosis resmi hanya dapat ditegakkan oleh psikolog melalui pemeriksaan langsung.
  </div>

  <div class="d-flex gap-2 mt-3">
    <a href="<?= base_url('/assessment') ?>" class="btn btn-outline-primary btn-sm">Kembali ke Daftar Assessment</a>
    <!-- <button class="btn btn-outline-secondary btn-sm" onclick="window.print()"><i class="fas fa-print me-1"></i>Export / Print PDF</button> -->
  </div>
</div>
