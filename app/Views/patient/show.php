<?php use Core\Auth; $isPsychiatrist = Auth::roleName() === 'psikiater'; ?>

<div class="patient-record-head d-flex flex-wrap align-items-center gap-3">
  <div class="patient-record-identity d-flex align-items-center gap-3">
    <?php if (!empty($patient['photo'])): ?>
      <img src="<?= base_url($patient['photo']) ?>" alt="<?= e($patient['name']) ?>" class="patient-record-avatar patient-record-avatar-lg">
    <?php else: ?>
      <span class="patient-record-avatar patient-record-avatar-fallback patient-record-avatar-lg"><?= e(mb_substr($patient['name'], 0, 1)) ?></span>
    <?php endif; ?>
    <div>
      <div class="d-flex align-items-center flex-wrap gap-2">
        <h4 class="fw-bold mb-0"><?= e($patient['name']) ?></h4>
        <span class="patient-record-rm-badge"><i class="fas fa-folder-open me-1"></i>RM <?= e($patient['medical_record_number']) ?></span>
      </div>
      <p class="text-muted small mb-0">
        <i class="fas fa-envelope me-1 text-teal"></i><?= e($patient['email'] ?: '-') ?>
        <span class="mx-2">|</span>
        <i class="fas fa-phone me-1 text-teal"></i><?= e($patient['phone'] ?: '-') ?>
      </p>
    </div>
  </div>
  <span class="patient-record-privacy ms-auto <?= $patient['privacy_pref'] === 'public' ? 'patient-record-privacy--public' : 'patient-record-privacy--private' ?>">
    <?php if ($patient['privacy_pref'] === 'public'): ?>
      <i class="fas fa-eye me-1"></i>Privasi: Publik (anonim)
    <?php else: ?>
      <i class="fas fa-lock me-1"></i>Privasi: Privat
    <?php endif; ?>
  </span>
</div>

<div class="patient-record-summary card p-4 mb-3">
  <div class="d-flex align-items-center gap-3">
    <span class="patient-record-head-icon"><i class="fas fa-id-card-clip"></i></span>
    <div>
      <h6 class="fw-bold mb-1">Data Lengkap Client</h6>
      <p class="small text-muted mb-0">Informasi identitas dan kontak client yang terhubung dengan rekam ini.</p>
    </div>
  </div>

  <hr class="record-divider">

  <div class="record-group-grid">
    <div class="record-group">
      <div class="record-group-title"><span class="record-group-icon"><i class="fas fa-user"></i></span>Identitas</div>
      <div class="record-group-body">
        <div class="record-item"><span class="record-label">Jenis Kelamin</span><strong><?= e($patient['gender'] === 'L' ? 'Laki-laki' : ($patient['gender'] === 'P' ? 'Perempuan' : '-')) ?></strong></div>
        <div class="record-item"><span class="record-label">Tanggal Lahir</span><strong><?= e($patient['birth_date'] ?: '-') ?></strong></div>
        <div class="record-item"><span class="record-label">Pekerjaan</span><strong><?= e($patient['occupation'] ?: '-') ?></strong></div>
        <div class="record-item"><span class="record-label">Pendidikan</span><strong><?= e($patient['education'] ?: '-') ?></strong></div>
      </div>
    </div>

    <div class="record-group">
      <div class="record-group-title"><span class="record-group-icon"><i class="fas fa-address-book"></i></span>Kontak</div>
      <div class="record-group-body">
        <div class="record-item"><span class="record-label">Email</span><strong><?= e($patient['email'] ?: '-') ?></strong></div>
        <div class="record-item"><span class="record-label">No. HP / WhatsApp</span><strong><?= e($patient['phone'] ?: '-') ?></strong></div>
        <div class="record-item"><span class="record-label">Alamat</span><strong><?= e($patient['address'] ?: '-') ?></strong></div>
        <div class="record-item">
          <span class="record-label">Kontak Darurat</span>
          <strong><?= e($patient['emergency_contact_name'] ?: '-') ?></strong>
          <span class="record-item-sub"><?= e($patient['emergency_contact_phone'] ?: '-') ?><?php if ($patient['emergency_contact_relation']): ?> &middot; <?= e($patient['emergency_contact_relation']) ?><?php endif; ?></span>
        </div>
      </div>
    </div>

    <div class="record-group">
      <div class="record-group-title"><span class="record-group-icon"><i class="fas fa-notes-medical"></i></span>Rekam Medis</div>
      <div class="record-group-body">
        <div class="record-item"><span class="record-label">Riwayat Penyakit</span><strong><?= e($patient['medical_history'] ?: '-') ?></strong></div>
        <div class="record-item"><span class="record-label">Alergi</span><strong><?= e($patient['allergy_history'] ?: '-') ?></strong></div>
        <div class="record-item"><span class="record-label">Referensi</span><strong><?= e($patient['referred_by'] ?: '-') ?></strong></div>
      </div>
    </div>
  </div>
</div>

<div class="disclaimer-box mb-3">
  <i class="fas fa-circle-info me-1"></i> Data pada halaman ini bersifat rahasia medis. Seluruh hasil assessment adalah skrining awal, bukan diagnosis.
</div>

<div class="card consent-record-card mb-3">
  <div class="consent-record-header">
    <div class="consent-record-title"><span class="consent-record-icon"><i class="fas fa-file-signature"></i></span><div><h6>Informed Consent Layanan Psikologi</h6><p>Dokumen persetujuan client yang tersimpan.</p></div></div>
    <?php if ($informedConsent): ?><span class="consent-status-badge"><i class="fas fa-check me-1"></i>Disetujui</span><?php endif; ?>
  </div>
  <?php if ($informedConsent): ?>
    <p class="consent-record-meta">Disetujui pada <?= format_date($informedConsent['signed_at'], 'd M Y H:i') ?> oleh <strong><?= e($informedConsent['signature_name']) ?></strong>.</p>
    <details class="consent-record-details">
      <summary>Lihat data persetujuan</summary>
      <div class="consent-record-grid">
        <div><span class="record-label">Nama Klien</span><strong><?= e($informedConsent['full_name']) ?></strong></div>
        <div><span class="record-label">Tanggal Lahir</span><strong><?= e($informedConsent['birth_date'] ?: '-') ?></strong></div>
        <div><span class="record-label">Orang Tua/Wali</span><strong><?= e($informedConsent['guardian_name'] ?: '-') ?></strong></div>
        <div class="consent-record-wide"><span class="record-label">Pernyataan</span><strong>Seluruh 8 poin informed consent disetujui secara elektronik.</strong></div>
        <div><span class="record-label">Tanda Tangan Elektronik</span><strong><?= e($informedConsent['signature_name']) ?></strong></div>
        <div><span class="record-label">Waktu Persetujuan</span><strong><?= format_date($informedConsent['signed_at'], 'd M Y H:i') ?></strong></div>
      </div>
    </details>
  <?php else: ?>
    <p class="consent-record-empty">Client belum menyimpan informed consent.</p>
  <?php endif; ?>
</div>

<ul class="nav nav-tabs mb-3" role="tablist">
  <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabBookings">Booking Client</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabAssessment">Assessment</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabMood">Mood</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabJournal">Journal</button></li>
  <?php if ($isPsychiatrist): ?>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabMedical">Catatan Medis</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabTherapy">Riwayat Terapi</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabControl">Rencana Kontrol</button></li>
  <?php endif; ?>
</ul>

<div class="tab-content">
  <div class="tab-pane fade show active" id="tabBookings">
    <div class="card p-3">
      <div class="table-responsive">
        <table class="table table-sm align-middle">
          <thead><tr><th>Jadwal</th><th>Psikolog</th><th>Metode</th><th>Durasi</th><th>Status</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($consultations as $c): ?>
            <?php
              $bookingStatus = match ($c['status']) {
                'approved' => ['Disetujui', 'consultation-status-approved'],
                'pending' => ['Pending', 'consultation-status-pending'],
                'cancelled', 'rejected', 'no_show' => ['Dibatalkan', 'consultation-status-cancelled'],
                'completed' => ['Selesai', 'consultation-status-completed'],
                default => [ucfirst($c['status']), 'consultation-status-approved'],
              };
            ?>
            <tr>
              <td><?= format_date($c['schedule_at'], 'd M Y H:i') ?></td>
              <td><?= e($c['provider_name']) ?></td>
              <td class="text-uppercase small"><?= e($c['method']) ?></td>
              <td><?= (int) $c['duration_minutes'] ?> menit</td>
              <td><span class="badge consultation-status <?= $bookingStatus[1] ?>"><?= e($bookingStatus[0]) ?></span></td>
              <td><a href="<?= base_url('/consultation/' . $c['id']) ?>" class="btn btn-sm btn-outline-primary">Detail</a></td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($consultations)): ?><tr><td colspan="6" class="text-center text-muted py-3">Belum ada data booking.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="tab-pane fade" id="tabAssessment">
    <div class="card p-3">
      <table class="table table-sm">
        <thead><tr><th>Tanggal</th><th>Assessment</th><th>Skor</th><th>Kategori</th><th>Interpretasi</th></tr></thead>
        <tbody>
        <?php foreach ($assessments as $a): ?>
          <tr>
            <td><?= format_date($a['created_at'], 'd M Y') ?></td>
            <td><?= e($a['assessment_title']) ?></td>
            <td><?= (int) $a['total_score'] ?></td>
            <td><span class="badge <?= $a['is_high_risk'] ? 'badge-risk-high' : 'badge-risk-low' ?>"><?= e($a['category_label'] ?? '-') ?></span></td>
            <td class="small"><?= e($a['interpretation'] ?? ($a['suggestion'] ?? '-')) ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($assessments)): ?><tr><td colspan="5" class="text-center text-muted py-3">Belum ada data.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="tab-pane fade" id="tabMood">
    <div class="card p-3">
      <table class="table table-sm">
        <thead><tr><th>Tanggal</th><th>Skala</th><th>Penyebab</th><th>Aktivitas</th></tr></thead>
        <tbody>
        <?php foreach ($moods as $m): ?>
          <tr><td><?= format_date($m['mood_date']) ?></td><td><?= (int) $m['mood_scale'] ?>/10</td><td><?= e($m['cause']) ?></td><td><?= e($m['activity']) ?></td></tr>
        <?php endforeach; ?>
        <?php if (empty($moods)): ?><tr><td colspan="4" class="text-center text-muted py-3">Belum ada data.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="tab-pane fade" id="tabJournal">
    <div class="card p-3">
      <p class="text-muted small"><i class="fas fa-lock me-1"></i>Hanya jurnal yang diizinkan secara eksplisit oleh client yang ditampilkan di sini.</p>
      <?php foreach ($journals as $j): ?>
        <div class="border-bottom py-2">
          <strong class="small"><?= e($j['title']) ?></strong> <span class="text-muted small">(<?= format_date($j['journal_date']) ?>)</span>
          <p class="small mb-0"><?= nl2br(e($j['content'])) ?></p>
        </div>
      <?php endforeach; ?>
      <?php if (empty($journals)): ?><p class="text-muted small mb-0">Tidak ada jurnal yang dapat diakses.</p><?php endif; ?>
    </div>
  </div>

  <?php if ($isPsychiatrist): ?>
  <div class="tab-pane fade" id="tabMedical">
    <div class="card p-3">
      <form method="POST" action="<?= base_url('/patients/' . $patient['id'] . '/medical-note') ?>" class="mb-3">
        <?= \Core\Csrf::field() ?>
        <div class="mb-2"><label class="form-label small">Catatan Medis</label><textarea name="diagnosis_note" class="form-control form-control-sm" rows="2" required></textarea></div>
        <div class="mb-2"><label class="form-label small">Catatan Pengobatan</label><textarea name="medication_note" class="form-control form-control-sm" rows="2"></textarea></div>
        <button class="btn btn-sm btn-primary">Simpan Catatan Medis</button>
      </form>
      <?php foreach ($medicalNotes as $mn): ?>
        <div class="border-bottom py-2 small">
          <span class="text-muted"><?= format_date($mn['created_at'], 'd M Y H:i') ?> &middot; <?= e($mn['psychiatrist_name']) ?></span>
          <p class="mb-0"><?= e($mn['diagnosis_note']) ?></p>
          <?php if ($mn['medication_note']): ?><p class="mb-0 text-muted">Pengobatan: <?= e($mn['medication_note']) ?></p><?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="card p-3 mt-3">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="fw-bold mb-0">Resep</h6>
        <a href="<?= base_url('/patients/' . $patient['id'] . '/prescription') ?>" class="btn btn-sm btn-outline-primary">+ Buat Resep</a>
      </div>
      <?php if (empty($prescriptions)): ?>
        <p class="text-muted small mb-0">Belum ada resep untuk client ini.</p>
      <?php else: ?>
        <?php foreach ($prescriptions as $rx): ?>
          <div class="border-bottom py-2 small d-flex justify-content-between align-items-center">
            <span><?= e($rx['prescription_number']) ?> &middot; <?= format_date($rx['created_at']) ?> &middot; <?= e($rx['psychiatrist_name']) ?></span>
            <a href="<?= base_url('/prescription/' . $rx['id']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">Lihat</a>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <div class="tab-pane fade" id="tabTherapy">
    <div class="card p-3">
      <form method="POST" action="<?= base_url('/patients/' . $patient['id'] . '/therapy-history') ?>" class="row g-2 mb-3">
        <?= \Core\Csrf::field() ?>
        <div class="col-md-4"><input type="text" name="therapy_type" class="form-control form-control-sm" placeholder="Jenis terapi" required></div>
        <div class="col-md-3"><input type="date" name="started_at" class="form-control form-control-sm"></div>
        <div class="col-md-3"><input type="date" name="ended_at" class="form-control form-control-sm"></div>
        <div class="col-md-2"><button class="btn btn-sm btn-primary w-100">Simpan</button></div>
        <div class="col-12"><textarea name="description" class="form-control form-control-sm" rows="2" placeholder="Deskripsi/outcome"></textarea></div>
      </form>
      <?php foreach ($therapyHistory as $t): ?>
        <div class="border-bottom py-2 small">
          <strong><?= e($t['therapy_type']) ?></strong> <span class="text-muted">(<?= format_date($t['started_at']) ?> - <?= $t['ended_at'] ? format_date($t['ended_at']) : 'berjalan' ?>)</span>
          <p class="mb-0"><?= e($t['description']) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="tab-pane fade" id="tabControl">
    <div class="card p-3">
      <form method="POST" action="<?= base_url('/patients/' . $patient['id'] . '/control-plan') ?>" class="row g-2 mb-3">
        <?= \Core\Csrf::field() ?>
        <div class="col-md-4"><input type="date" name="next_control_date" class="form-control form-control-sm" required></div>
        <div class="col-md-6"><input type="text" name="note" class="form-control form-control-sm" placeholder="Catatan"></div>
        <div class="col-md-2"><button class="btn btn-sm btn-primary w-100">Simpan</button></div>
      </form>
      <table class="table table-sm">
        <thead><tr><th>Tanggal Kontrol</th><th>Catatan</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($controlPlans as $c): ?>
          <tr><td><?= format_date($c['next_control_date']) ?></td><td><?= e($c['note']) ?></td><td><span class="badge bg-secondary text-uppercase"><?= e($c['status']) ?></span></td></tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>
</div>
