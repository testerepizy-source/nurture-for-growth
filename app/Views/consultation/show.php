<?php use Core\Auth; use App\Services\CommunicationService; $role = Auth::roleName(); $isProvider = in_array($role, ['psikolog','psikiater'], true); ?>
<?php $payMethodLabel = fn(?string $m): string => match ($m) { 'qris' => 'QRIS', 'midtrans' => 'Midtrans', 'card' => 'Kartu Kredit/Debit', 'e_wallet' => 'E-Wallet', 'cash' => 'Tunai', default => 'Transfer Bank' }; ?>
<?php $statusLabels = ['pending' => 'Menunggu Konfirmasi', 'approved' => 'Terjadwal', 'rescheduled' => 'Dijadwalkan Ulang', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan', 'rejected' => 'Ditolak', 'no_show' => 'Tidak Hadir']; ?>
<?php $statusIcons = ['pending' => 'fa-hourglass-half', 'approved' => 'fa-calendar-check', 'rescheduled' => 'fa-rotate', 'completed' => 'fa-flag-checkered', 'cancelled' => 'fa-circle-xmark', 'rejected' => 'fa-ban', 'no_show' => 'fa-user-xmark']; ?>
<?php $statusKey = in_array($consultation['status'], array_keys($statusLabels), true) ? $consultation['status'] : 'pending'; ?>

<?php $providerContact = $providerProfile['whatsapp_number'] ?? $providerUser['whatsapp_number'] ?? $providerUser['phone'] ?? null; ?>
<?php $patientContact = $patientUser['whatsapp_number'] ?? $patientUser['phone'] ?? null; ?>
<?php $paymentProofUrl = !empty($consultation['payment_proof_path']) ? CommunicationService::resolvePublicUrl($consultation['payment_proof_path']) : null; ?>

<link rel="stylesheet" href="<?= asset('css/consultation.css') ?>">

<div class="disclaimer-box mb-3">
  <i class="fas fa-circle-info me-1"></i>
  Sesi konseling ini dikelola oleh tenaga profesional dan bukan merupakan layanan diagnosis otomatis.
</div>
<?php if ($completedMessage = \Core\Session::flash('complete_alert')): ?>
<div class="alert alert-success rounded-4 border-0 shadow-sm mb-3" role="alert">
  <i class="fas fa-check-circle me-2"></i><?= e($completedMessage) ?>
</div>
<?php endif; ?>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.getElementById('editLinkToggleBtn');
    const form = document.getElementById('editLinkForm');
    if (toggle && form) {
      toggle.addEventListener('click', function () {
        form.style.display = form.style.display === 'none' || form.style.display === '' ? 'block' : 'none';
        if (form.style.display === 'block') {
          const input = form.querySelector('input[name="meeting_link"]');
          if (input) input.focus();
        }
      });
    }

    const completeForm = document.getElementById('completeSessionForm');
    if (completeForm) {
      completeForm.addEventListener('submit', function (event) {
        event.preventDefault();
        Swal.fire({
          title: 'Tandai sesi ini selesai?',
          text: 'Status sesi akan berubah menjadi selesai dan peserta tidak lagi dapat mengedit booking.',
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Ya, Tandai Selesai',
          cancelButtonText: 'Batal'
        }).then(function (result) {
          if (result.isConfirmed) {
            completeForm.submit();
          }
        });
      });
    }

    document.querySelectorAll('form.approve-form').forEach(function (f) {
      f.addEventListener('submit', function (event) {
        event.preventDefault();
        const autoVerify = f.dataset.autoVerify === '1';
        Swal.fire({
          title: 'Terima permintaan booking ini?',
          html: autoVerify
            ? '<p>Sesi akan dikonfirmasi dan pembayaran otomatis diverifikasi (Lunas) serta invoice dibuat.</p>'
            : '<p>Booking akan disetujui dan client mendapat notifikasi konfirmasi.</p>',
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Ya, Setujui Booking',
          cancelButtonText: 'Batal'
        }).then(function (result) {
          if (result.isConfirmed) {
            f.submit();
          }
        });
      });
    });

    document.querySelectorAll('form.verify-payment-form').forEach(function (f) {
      f.addEventListener('submit', function (event) {
        event.preventDefault();
        Swal.fire({
          title: 'Verifikasi pembayaran ini?',
          text: 'Status menjadi Lunas dan invoice dibuat untuk client.',
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Ya, Verifikasi',
          cancelButtonText: 'Batal'
        }).then(function (result) {
          if (result.isConfirmed) f.submit();
        });
      });
    });

    });
</script>
<div class="row g-3">
  <div class="col-12">
    <div class="consultation-header-card mb-3">
      <?php
        $rescheduleNewScheduleAt = is_array($completedReschedule) && !empty($completedReschedule['new_schedule_at'])
            ? $completedReschedule['new_schedule_at']
            : null;
        $displayScheduleAt = $rescheduleNewScheduleAt ?? $consultation['schedule_at'];
        $methodLabels = ['video' => 'Video Call', 'voice' => 'Voice Call', 'chat' => 'Chat', 'tatap_muka' => 'Tatap Muka'];
        $methodIcons = ['video' => 'fas fa-video', 'voice' => 'fas fa-phone', 'chat' => 'fas fa-comments', 'tatap_muka' => 'fas fa-handshake'];
        $sessionTypeLabels = ['individual' => 'Individu', 'couple' => 'Suami & Istri', 'family' => 'Ayah & Ibu'];
        $initials = fn(?string $name): string => strtoupper(substr(trim((string) $name), 0, 1));
      ?>
      <div class="consultation-header-top">
        <span class="consultation-eyebrow">Ringkasan Konsultasi</span>
        <div class="consultation-header-badges">
          <span class="badge consultation-status consultation-status-<?= e($statusKey) ?>">
            <i class="fas <?= e($statusIcons[$statusKey]) ?>"></i><?= e($statusLabels[$statusKey]) ?>
          </span>
          <span class="badge bg-secondary text-uppercase">ID: <?= e($consultation['id']) ?></span>
        </div>
      </div>

      <div class="consultation-participants">
        <div class="participant">
          <span class="participant-avatar"><?= e($initials($consultation['patient_name'])) ?></span>
          <span class="participant-text">
            <span class="participant-role">Client</span>
            <span class="participant-name"><?= e($consultation['patient_name']) ?></span>
          </span>
        </div>
        <span class="participant-divider"><i class="fas fa-arrow-right-long"></i></span>
        <div class="participant">
          <span class="participant-avatar participant-avatar--accent"><?= e($initials($consultation['provider_name'])) ?></span>
          <span class="participant-text">
            <span class="participant-role"><?= $isProvider ? 'Anda' : 'Psikolog' ?></span>
            <span class="participant-name"><?= e($consultation['provider_name']) ?></span>
          </span>
        </div>
      </div>

      <div class="consultation-fact-grid">
        <div class="consultation-fact">
          <span class="fact-icon"><i class="far fa-calendar-days"></i></span>
          <span class="fact-body">
            <span class="fact-label">Jadwal</span>
            <span class="fact-value"><?= format_date($displayScheduleAt, 'd M Y') ?>, <?= format_date($displayScheduleAt, 'H:i') ?></span>
          </span>
        </div>
        <div class="consultation-fact">
          <span class="fact-icon"><i class="<?= e($methodIcons[$consultation['method']] ?? 'fas fa-headset') ?>"></i></span>
          <span class="fact-body">
            <span class="fact-label">Metode</span>
            <span class="fact-value"><?= e($methodLabels[$consultation['method']] ?? $consultation['method']) ?></span>
          </span>
        </div>
        <div class="consultation-fact">
          <span class="fact-icon"><i class="far fa-clock"></i></span>
          <span class="fact-body">
            <span class="fact-label">Durasi</span>
            <span class="fact-value"><?= (int) $consultation['duration_minutes'] ?> menit</span>
          </span>
        </div>
        <div class="consultation-fact">
          <span class="fact-icon"><i class="fas fa-user-group"></i></span>
          <span class="fact-body">
            <span class="fact-label">Tipe Sesi</span>
            <span class="fact-value"><?= e($sessionTypeLabels[$consultation['session_type'] ?? 'individual'] ?? 'Individu') ?></span>
          </span>
        </div>
      </div>

      <?php if ($rescheduleNewScheduleAt && $rescheduleNewScheduleAt !== $consultation['schedule_at']): ?>
      <div class="schedule-update-note mt-3">
        <strong>Dijadwalkan ulang:</strong> dari <?= format_date($consultation['schedule_at'], 'd M Y H:i') ?> ke
        <?= format_date($rescheduleNewScheduleAt, 'd M Y H:i') ?>.
      </div>
      <?php endif; ?>

      <?php if (in_array($consultation['status'], ['pending', 'approved', 'rescheduled', 'completed'], true)): ?>
      <?php
        $stepIndex = match ($consultation['status']) {
          'approved', 'rescheduled' => 2,
          'completed' => 3,
          default => 1,
        };
        $steps = [
          ['label' => 'Booking Dibuat', 'icon' => 'fa-file-circle-check'],
          ['label' => 'Sesi Dikonfirmasi', 'icon' => 'fa-calendar-check'],
          ['label' => 'Sesi Selesai', 'icon' => 'fa-flag-checkered'],
        ];
      ?>
      <div class="consultation-progress mt-4">
        <?php foreach ($steps as $i => $st): $n = $i + 1; ?>
        <div class="progress-step <?= $n < $stepIndex ? 'done' : ($n === $stepIndex ? 'active' : 'upcoming') ?>">
          <span class="progress-dot">
            <i class="fas <?= $n < $stepIndex ? 'fa-check' : $st['icon'] ?>"></i>
          </span>
          <span class="progress-label"><?= e($st['label']) ?></span>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <?php if ($consultation['method'] === 'video'): ?>
      <?php $roomUrl = base_url('/consultation/' . $consultation['id'] . '/room'); ?>
      <?php $meetingLink = !empty($consultation['meeting_link']) && !str_contains((string) $consultation['meeting_link'], '/room') ? $consultation['meeting_link'] : null; ?>
      <?php $isRoomEnded = !empty($videoSession) && strtolower((string) ($videoSession['status'] ?? '')) === 'ended'; ?>
      <div class="meeting-summary-card">
        <div class="meeting-summary-inner">
          <div class="meeting-info">
            <div class="meeting-info-title"><i class="fas fa-door-open me-1"></i>Ruang Tunggu &amp; Meeting</div>
            <p class="small text-muted mb-0">Sesi online ini berjalan di ruang tunggu internal NFG. Tekan tombol
              di samping untuk membuka ruang tunggu dan bergabung ke sesi.</p>
            <?php if ($meetingLink): ?>
            <div class="meeting-link-chip"><i class="fas fa-video me-1"></i><span
                class="small">Link:</span> <code><?= e($meetingLink) ?></code></div>
            <?php endif; ?>
          </div>
          <div class="meeting-card-actions">
            <?php if ($isRoomEnded): ?>
            <button type="button" class="btn btn-secondary btn-lg" disabled aria-disabled="true" title="Sesi sudah selesai">
              <i class="fas fa-lock me-1"></i>Ruang Tunggu Ditutup
            </button>
            <?php else: ?>
            <a href="<?= $roomUrl ?>" class="btn btn-primary btn-lg">
              <i class="fas fa-door-open me-1"></i>Buka Ruang Tunggu
            </a>
            <?php endif; ?>
          </div>
        </div>
        <?php if ($videoSession): ?>
        <div class="meeting-status-summary">
          <span class="meeting-status-label">Status ruang tunggu</span>
          <span
            class="badge <?= $videoSession['status'] === 'waiting' ? 'bg-warning text-dark' : ($videoSession['status'] === 'ongoing' ? 'bg-success' : 'bg-secondary') ?> text-uppercase">
            <?= e($videoSession['status']) ?>
          </span>
          <span class="meeting-status-hint">
            <?php if ($videoSession['status'] === 'waiting'): ?>Tunggu sampai sesi dimulai untuk
            bergabung.<?php endif; ?>
            <?php if ($videoSession['status'] === 'ongoing'): ?>Sesi berjalan; silakan bergabung
            sekarang.<?php endif; ?>
            <?php if ($videoSession['status'] === 'ended'): ?>Sesi telah selesai atau ditutup.<?php endif; ?>
          </span>
        </div>
        <?php endif; ?>
      </div>
      <?php endif; ?>

      <?php if (!$isProvider && !empty($providerContact)): ?>
      <?php $providerWhatsAppUrl = CommunicationService::createWhatsAppChatUrl($providerContact, "Halo " . $providerUser['name'] . ",\n\nBooking konseling baru telah dibuat.\n- Client: " . $consultation['patient_name'] . "\n- Tanggal & Jam: " . format_date($displayScheduleAt, 'd M Y H:i') . "\n- Metode: " . ($consultation['method'] ?? 'Video') . "\n- Durasi: " . (int) $consultation['duration_minutes'] . " menit\n- Biaya: Rp" . number_format($consultation['price_amount'] ?? 0, 0, ',', '.') . "\n- Metode Pembayaran: " . $payMethodLabel($consultation['payment_method'] ?? 'transfer') . ($paymentProofUrl ? "\n- Bukti Transfer: " . $paymentProofUrl : '') . "\n\nSilakan verifikasi booking di aplikasi."); ?>
      <!-- <div class="whatsapp-box mt-3">
        <div class="whatsapp-panel">
          <div class="whatsapp-copy">
            <div class="small text-uppercase fw-bold mb-1">Kirim WhatsApp ke Provider</div>
            <p class="small text-muted mb-0">Buka chat WhatsApp dengan <?= e($providerUser['name']) ?> untuk konfirmasi
              booking dan detail sesi.</p>
          </div>
          <a href="<?= e($providerWhatsAppUrl) ?>" target="_blank" class="btn btn-sm btn-whatsapp"><i
              class="fab fa-whatsapp me-1"></i>Chat Provider</a>
        </div>
      </div> -->


      <div class="whatsapp-box mt-3">
        <div class="d-flex align-items-center justify-content-between flex-column flex-md-row gap-3">
          <div>
            <div class="small text-uppercase fw-bold mb-1">Kirim WhatsApp ke Psikolog</div>
            <p class="small text-muted mb-0">Buka chat WhatsApp dengan <?= e($providerUser['name']) ?> untuk konfirmasi
              rincian booking secara cepat.</p>
          </div>
          <a href="<?= e($providerWhatsAppUrl) ?>" target="_blank" class="btn btn-whatsapp btn-sm px-4"><i
              class="fab fa-whatsapp me-2"></i>Chat Psikolog</a>
        </div>
      </div>



      <?php elseif ($isProvider && !empty($patientContact)): ?>
      <?php $patientWhatsAppUrl = CommunicationService::createWhatsAppChatUrl($patientContact, "Halo " . $patientUser['name'] . ",\n\nBooking konseling baru telah dibuat untuk Anda.\n- Psikolog: " . $providerUser['name'] . "\n- Tanggal & Jam: " . format_date($displayScheduleAt, 'd M Y H:i') . "\n- Metode: " . ($consultation['method'] ?? 'Video') . "\n- Durasi: " . (int) $consultation['duration_minutes'] . " menit\n- Biaya: Rp" . number_format($consultation['price_amount'] ?? 0, 0, ',', '.') . "\n- Metode Pembayaran: " . $payMethodLabel($consultation['payment_method'] ?? 'transfer') . ($paymentProofUrl ? "\n- Bukti Transfer: " . $paymentProofUrl : '') . "\n\nSilakan cek detail booking di aplikasi."); ?>
      <div class="whatsapp-box mt-3">
        <div class="d-flex align-items-center justify-content-between flex-column flex-md-row gap-3">
          <div>
            <div class="small text-uppercase fw-bold mb-1">Kirim WhatsApp ke Client</div>
            <p class="small text-muted mb-0">Buka chat WhatsApp dengan <?= e($patientUser['name']) ?> untuk mengirim
              rincian booking secara cepat.</p>
          </div>
          <a href="<?= e($patientWhatsAppUrl) ?>" target="_blank" class="btn btn-whatsapp btn-sm px-4"><i
              class="fab fa-whatsapp me-2"></i>Chat Client</a>
        </div>
      </div>
      <?php elseif ($isProvider && empty($patientContact)): ?>
      <div class="alert alert-warning mt-3">
        <h6 class="fw-bold mb-1">Nomor WhatsApp client tidak tersedia</h6>
        <p class="small mb-0">Tidak dapat membuat tombol WhatsApp karena nomor client belum terdaftar di profil mereka.
        </p>
      </div>
      <?php elseif (!$isProvider && empty($providerContact)): ?>
      <div class="alert alert-warning mt-3">
        <h6 class="fw-bold mb-1">Nomor WhatsApp psikolog tidak tersedia</h6>
        <p class="small mb-0">Tidak dapat membuat tombol WhatsApp karena nomor psikolog belum terdaftar. Silakan minta
          psikolog untuk memperbarui profilnya.</p>
      </div>
      <?php endif; ?>

      <?php if ($consultation['reason']): ?>
      <div class="consultation-reason-card mt-3">
        <div class="d-flex gap-3">
          <span class="consultation-reason-icon"><i class="fas fa-comment-lines"></i></span>
          <div>
            <div class="consultation-reason-title">Tujuan / Keluhan Client</div>
            <p class="consultation-reason-text"><?= e($consultation['reason']) ?></p>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <?php if ($isProvider && !empty($pendingReschedule)): ?>
      <div class="alert alert-warning mt-3">
        <h6 class="fw-bold mb-2">Permintaan Reschedule Masuk</h6>
        <p class="mb-1">Jadwal baru: <strong><?= format_datetime($pendingReschedule['new_schedule_at']) ?></strong></p>
        <p class="mb-1">Biaya tambahan: <strong>Rp
            <?= number_format($pendingReschedule['rescheduling_fee'], 0, ',', '.') ?></strong></p>
        <p class="mb-1">Status pembayaran:
          <strong><?= ucfirst(str_replace('_', ' ', $pendingReschedule['payment_status'])) ?></strong></p>
        <?php if ($pendingReschedule['payment_status'] === 'pending_verification'): ?>
        <form method="POST" action="<?= base_url('/payment/reschedule/' . $pendingReschedule['id'] . '/verify') ?>"
          class="d-inline-block me-2">
          <?= \Core\Csrf::field() ?>
          <button class="btn btn-sm btn-success">Verifikasi Pembayaran</button>
        </form>
        <?php endif; ?>
        <?php if (!empty($pendingReschedule['payment_proof_path'])): ?>
        <div class="mt-2">
          <a href="<?= e(CommunicationService::resolvePublicUrl($pendingReschedule['payment_proof_path'])) ?>"
            target="_blank" class="small text-primary d-inline-flex align-items-center">
            <i class="fas fa-receipt me-1"></i>Lihat Bukti Transfer
          </a>
        </div>
        <?php endif; ?>
        <?php $pendingRescheduleInvoice = \App\Models\Invoice::forConsultation($consultation['id'], (int)$consultation['price_amount']); ?>
        <?php if (!empty($pendingRescheduleInvoice)): ?>
        <div class="mt-2">
          <a href="<?= base_url('/invoice/' . $pendingRescheduleInvoice['id']) ?>" target="_blank"
            class="small text-primary d-inline-flex align-items-center">
            <i class="fas fa-file-invoice me-1"></i>Unduh Invoice/Kwitansi
          </a>
        </div>
        <?php endif; ?>
        <form method="POST"
          action="<?= base_url('/consultation/reschedule/' . $pendingReschedule['id'] . '/approve') ?>"
          class="d-inline-block me-2">
          <?= \Core\Csrf::field() ?>
          <button class="btn btn-sm btn-primary">Setujui Reschedule</button>
        </form>
        <form method="POST" action="<?= base_url('/consultation/reschedule/' . $pendingReschedule['id'] . '/reject') ?>"
          class="d-inline-block">
          <?= \Core\Csrf::field() ?>
          <button class="btn btn-sm btn-outline-danger">Tolak Reschedule</button>
        </form>
      </div>
      <?php endif; ?>

      <?php if (!empty($consultation['price_amount'])): ?>
      <div class="consultation-info-panel mb-3">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start gap-4">
          <div>
            <div class="small text-uppercase text-muted mb-2">Biaya Sesi</div>
            <?php $showDiscount = !empty($consultation['discount_amount']) && (int) $consultation['discount_amount'] > 0; ?>
            <?php if ($showDiscount): ?>
            <div class="text-muted text-decoration-line-through small">Rp<?= number_format((int) ($consultation['original_price_amount'] ?? 0) + (int) $consultation['discount_amount'], 0, ',', '.') ?></div>
            <?php endif; ?>
            <div class="fw-bold fs-4" <?= $showDiscount ? 'style="color:#0a7d33"' : '' ?>>Rp<?= number_format($consultation['price_amount'], 0, ',', '.') ?></div>
            <?php if ($showDiscount): ?>
            <?php $promoLabel = \App\Models\ServicePromo::find((int) $consultation['promo_id'])['name'] ?? null; ?>
            <div class="badge bg-success-subtle text-success-emphasis mt-1">
              <i class="fas fa-tag me-1"></i>Promo<?= $promoLabel ? ': ' . e($promoLabel) : '' ?> &minus;Rp<?= number_format((int) $consultation['discount_amount'], 0, ',', '.') ?>
            </div>
            <?php endif; ?>
            <div class="small text-muted mt-1">
              (<?= e($sessionTypeLabels[$consultation['session_type'] ?? 'individual'] ?? 'Individu') ?>)
            </div>
            <?php if (!empty($consultation['patient_role']) && $consultation['patient_role'] !== 'self'): ?>
            <?php $patientRoleLabels = ['husband' => 'Suami', 'wife' => 'Istri', 'father' => 'Ayah', 'mother' => 'Ibu', 'child' => 'Anak']; ?>
            <div class="small text-muted mt-2">Peran Client:
              <?= e($patientRoleLabels[$consultation['patient_role']] ?? 'Individu') ?></div>
            <?php endif; ?>
            <?php if (!empty($consultation['payment_method'])): ?>
            <div class="small text-muted mt-1">Metode Pembayaran:
              <?= e($payMethodLabel($consultation['payment_method'])) ?></div>
            <?php endif; ?>
          </div>
          <div class="text-end">
            <span
              class="badge <?= $consultation['payment_status'] === 'paid' ? 'bg-success' : ($consultation['payment_status'] === 'pending_verification' ? 'bg-warning text-dark' : 'bg-secondary') ?> text-uppercase"><?= $consultation['payment_status'] === 'paid' ? 'Lunas' : ($consultation['payment_status'] === 'pending_verification' ? 'Menunggu Verifikasi' : 'Belum Bayar') ?></span>
          </div>
        </div>

        <?php if (is_array($completedReschedule) && !empty($completedReschedule['rescheduling_fee'])): ?>
        <div class="consultation-fee-panel">
          <div class="fee-summary-card fee-summary-card--secondary">
            <span class="label">Biaya Reschedule</span>
            <div class="amount">Rp<?= number_format($completedReschedule['rescheduling_fee'], 0, ',', '.') ?></div>
          </div>
          <div class="fee-total-card">
            <span class="label">Total Biaya</span>
            <div class="amount">
              Rp<?= number_format((int)$consultation['price_amount'] + (int)$completedReschedule['rescheduling_fee'], 0, ',', '.') ?>
            </div>
          </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($consultation['payment_proof_path'])): ?>
        <div class="mt-3">
          <a href="<?= base_url($consultation['payment_proof_path']) ?>" target="_blank" class="small link-accent"><i
              class="fas fa-receipt me-1"></i>Lihat Bukti Transfer</a>
        </div>
        <?php endif; ?>
        <?php if ($isProvider && $consultation['payment_status'] === 'pending_verification' && $consultation['status'] !== 'pending'): ?>
        <form method="POST" action="<?= base_url('/consultation/' . $consultation['id'] . '/verify-payment') ?>"
          class="mt-3 verify-payment-form">
          <?= \Core\Csrf::field() ?>
          <button class="btn btn-sm btn-success">Verifikasi Pembayaran</button>
        </form>
        <?php endif; ?>
        <?php if (!$isProvider && $consultation['payment_status'] === 'unpaid'): ?>
        <p class="small text-muted mt-3 mb-0">Belum ada bukti transfer diunggah. Hubungi klinik jika Anda sudah membayar
          namun status belum berubah.</p>
        <?php endif; ?>
        <?php if ($consultation['payment_status'] === 'paid'): ?>
        <?php $invoice = \App\Models\Invoice::forConsultation($consultation['id']); ?>
        <?php if ($invoice): ?>
        <div class="mt-3"><a href="<?= base_url('/invoice/' . $invoice['id']) ?>" target="_blank"
            class="small link-accent"><i class="fas fa-file-invoice me-1"></i>Unduh Invoice/Kwitansi</a></div>
        <?php endif; ?>
        <?php endif; ?>
      </div>

      <?php if (!$isProvider && !empty($pendingReschedule)): ?>
      <div class="alert alert-warning mt-3">
        <h6 class="fw-bold mb-2">Permintaan Penjadwalan Ulang Sedang Berjalan</h6>
        <p class="mb-1">Jadwal baru: <strong><?= format_datetime($pendingReschedule['new_schedule_at']) ?></strong></p>
        <p class="mb-1">Biaya: <strong>Rp
            <?= number_format($pendingReschedule['rescheduling_fee'], 0, ',', '.') ?></strong></p>
        <p class="mb-1">Status pembayaran:
          <strong><?= ucfirst(str_replace('_', ' ', $pendingReschedule['payment_status'])) ?></strong></p>
        <?php if (!empty($pendingReschedule['payment_proof_path'])): ?>
        <div class="mt-2">
          <a href="<?= e(CommunicationService::resolvePublicUrl($pendingReschedule['payment_proof_path'])) ?>"
            target="_blank" class="small link-accent d-inline-flex align-items-center">
            <i class="fas fa-receipt me-1"></i>Lihat Bukti Transfer</a>
        </div>
        <?php endif; ?>
        <?php $pendingRescheduleInvoice = \App\Models\Invoice::forConsultation($consultation['id'], (int)$consultation['price_amount']); ?>
        <?php if (!empty($pendingRescheduleInvoice)): ?>
        <div class="mt-2">
          <a href="<?= base_url('/invoice/' . $pendingRescheduleInvoice['id']) ?>" target="_blank"
            class="small link-accent d-inline-flex align-items-center">
            <i class="fas fa-file-invoice me-1"></i>Unduh Invoice/Kwitansi</a>
        </div>
        <?php endif; ?>
        <?php if ($pendingReschedule['payment_status'] === 'unpaid'): ?>
        <a href="<?= base_url('/payment/reschedule/' . $pendingReschedule['id']) ?>"
          class="btn btn-sm btn-success mt-2">Lanjutkan Pembayaran Reschedule</a>
        <?php elseif ($pendingReschedule['payment_status'] === 'pending_verification'): ?>
        <div class="small text-muted mt-2"><i class="fas fa-hourglass-half me-1"></i>Menunggu verifikasi pembayaran oleh tim NFG.</div>
        <?php endif; ?>
      </div>
      <?php endif; ?>

      <?php if (!$isProvider && !empty($pendingExtension)): ?>
      <div class="alert alert-info mt-3">
        <h6 class="fw-bold mb-2">Permintaan Perpanjangan Durasi Sedang Berjalan</h6>
        <p class="mb-1">Tambahan: <strong><?= (int)$pendingExtension['extension_minutes'] ?> menit</strong></p>
        <p class="mb-1">Biaya: <strong>Rp
            <?= number_format($pendingExtension['extension_price'], 0, ',', '.') ?></strong></p>
        <p class="mb-1">Status pembayaran:
          <strong><?= ucfirst(str_replace('_', ' ', $pendingExtension['payment_status'])) ?></strong></p>
        <?php if ($pendingExtension['payment_status'] === 'unpaid'): ?>
        <a href="<?= base_url('/payment/extension/' . $pendingExtension['id']) ?>"
          class="btn btn-sm btn-success mt-2">Lanjutkan Pembayaran Perpanjangan</a>
        <?php elseif ($pendingExtension['payment_status'] === 'pending_verification'): ?>
        <div class="small text-muted mt-2"><i class="fas fa-hourglass-half me-1"></i>Menunggu verifikasi pembayaran oleh psikolog.</div>
        <?php endif; ?>
      </div>
      <?php endif; ?>
      <?php endif; ?>

      <div class="consultation-action-bar">
        <?php if ($isProvider && $consultation['status'] === 'pending'): ?>
        <div class="action-card action-card--wide">
          <div class="action-card-header">
            <span class="action-card-icon"><i class="fas fa-check-circle"></i></span>
            <div>
              <div class="action-card-title">Terima Permintaan</div>
              <div class="action-card-note">Konfirmasi sesi untuk client.
                <?php if ($consultation['method'] === 'video'): ?>Link meeting sudah disiapkan otomatis dan bisa diubah lewat kartu <strong>Ubah Link Meeting</strong> bila diperlukan.<?php endif; ?></div>
              <?php if ($consultation['payment_status'] === 'pending_verification'): ?>
              <div class="small text-success mt-1"><i class="fas fa-circle-check me-1"></i>Bukti pembayaran sudah diunggah &mdash; saat Anda menekan <strong>Setujui</strong>, pembayaran otomatis diverifikasi (Lunas) dan invoice dibuat.</div>
              <?php endif; ?>
            </div>
          </div>
          <form method="POST" action="<?= base_url('/consultation/' . $consultation['id'] . '/approve') ?>"
            class="approve-form" data-auto-verify="<?= $consultation['payment_status'] === 'pending_verification' ? '1' : '0' ?>">
            <?= \Core\Csrf::field() ?>
            <button class="btn btn-success" type="submit"><i class="fas fa-check me-1"></i>Setujui Booking</button>
          </form>
        </div>
        <?php endif; ?>

        <?php if ($isProvider && in_array($consultation['status'], ['approved', 'rescheduled'], true)): ?>
        <div class="action-card">
          <div class="action-card-header">
            <span class="action-card-icon"><i class="fas fa-flag-checkered"></i></span>
            <div>
              <div class="action-card-title">Tandai Selesai</div>
              <div class="action-card-note">Gunakan setelah sesi selesai untuk memperbarui status dan menyelesaikan
                administrasi.</div>
            </div>
          </div>
          <form id="completeSessionForm" method="POST"
            action="<?= base_url('/consultation/' . $consultation['id'] . '/complete') ?>">
            <?= \Core\Csrf::field() ?>
            <button class="btn btn-sm btn-outline-primary w-100"><i class="fas fa-check me-1"></i>Tandai
              Selesai</button>
          </form>
        </div>
        <?php endif; ?>

        <?php if ($isProvider && $consultation['method'] === 'video' && in_array($consultation['status'], ['pending', 'approved', 'rescheduled'], true)): ?>
        <div class="action-card">
          <div class="action-card-header">
            <span class="action-card-icon"><i class="fas fa-pencil-alt"></i></span>
            <div>
              <div class="action-card-title">
                <?= !empty($consultation['meeting_link']) ? 'Ubah Link Meeting' : 'Tambah Link Meeting' ?></div>
              <div class="action-card-note">Gunakan jika Google API otomatis belum tersedia atau jika Anda ingin
                mengirim link eksternal.</div>
            </div>
          </div>
          <button type="button" id="editLinkToggleBtn" class="btn btn-sm btn-outline-secondary w-100"><i
              class="fas fa-edit me-1"></i><?= !empty($consultation['meeting_link']) ? 'Ubah Link' : 'Tambah Link' ?></button>
        </div>
        <?php endif; ?>

        <?php if (!$isProvider && in_array($consultation['status'], ['pending', 'approved'])): ?>
        <?php if ($consultation['payment_status'] === 'unpaid'): ?>
        <div class="action-card">
          <div class="action-card-header">
            <span class="action-card-icon"><i class="fas fa-credit-card"></i></span>
            <div>
              <div class="action-card-title">Bayar Sekarang</div>
              <div class="action-card-note">Lengkapi pembayaran agar booking dapat diproses lebih cepat.</div>
            </div>
          </div>
          <a href="<?= base_url('/payment/consultation/' . $consultation['id']) ?>"
            class="btn btn-sm btn-success w-100"><i class="fas fa-wallet me-1"></i>Bayar Sekarang</a>
        </div>


        <?php endif; ?>
        <div class="action-card">
          <div class="action-card-header">
            <span class="action-card-icon"><i class="fas fa-calendar-alt"></i></span>
            <div>
              <div class="action-card-title">Jadwal Ulang</div>
              <div class="action-card-note">Ajukan perubahan jadwal jika waktu ini sudah tidak cocok.</div>
            </div>
          </div>
          <a href="<?= base_url('/consultation/' . $consultation['id'] . '/reschedule') ?>"
            class="btn btn-sm btn-warning w-100"><i class="fas fa-calendar-plus me-1"></i>Jadwal Ulang</a>
        </div>
        <?php endif; ?>





        <?php if ($isProvider && in_array($consultation['status'], ['pending', 'approved', 'rescheduled'], true)): ?>
        <div class="action-card">
          <div class="action-card-header">
            <span class="action-card-icon"><i class="fas fa-hourglass-half"></i></span>
            <div>
              <div class="action-card-title">Perpanjang Durasi</div>
              <div class="action-card-note">Minta perpanjangan sesi jika perlu lebih banyak waktu dalam konsultasi.
              </div>
            </div>
          </div>
          <a href="<?= base_url('/consultation/' . $consultation['id'] . '/extend') ?>"
            class="btn btn-sm btn-outline-success"><i class="fas fa-plus me-1"></i>Ajukan Perpanjangan Durasi</a>
        </div>
        <?php endif; ?>

      </div>

      <?php if ($isProvider && $consultation['method'] === 'video' && in_array($consultation['status'], ['pending', 'approved', 'rescheduled'], true)): ?>
      <form id="editLinkForm" method="POST"
        action="<?= base_url('/consultation/' . $consultation['id'] . '/update-link') ?>" class="edit-link-form"
        style="display: none;">
        <?= \Core\Csrf::field() ?>
        <div class="input-group input-group-sm">
          <input type="url" name="meeting_link" class="form-control form-control-sm"
            placeholder="Masukkan link meeting (contoh: https://zoom.us/...)"
            value="<?= e($consultation['meeting_link'] ?? '') ?>" pattern="https?://.+">
          <button class="btn btn-primary"
            type="submit"><?= !empty($consultation['meeting_link']) ? 'Perbarui Link' : 'Simpan Link' ?></button>
        </div>
        <div class="form-text mt-2">Link akan tersimpan langsung dan dapat dikirim ke client via WhatsApp prefilling.
        </div>
      </form>
      <?php endif; ?>
    </div>



    <?php if ($isProvider && !empty($pendingExtension)): ?>
    <div class="alert alert-info mt-3">
      <h6 class="fw-bold mb-2">Permintaan Perpanjangan Masuk</h6>
      <p class="mb-1">Tambahan durasi: <strong><?= (int)$pendingExtension['extension_minutes'] ?> menit</strong></p>
      <p class="mb-1">Biaya: <strong>Rp <?= number_format($pendingExtension['extension_price'], 0, ',', '.') ?></strong>
      </p>
      <p class="mb-1">Status pembayaran:
        <strong><?= ucfirst(str_replace('_', ' ', $pendingExtension['payment_status'])) ?></strong></p>
      <?php if ($pendingExtension['payment_status'] === 'pending_verification'): ?>
      <form method="POST" action="<?= base_url('/payment/extension/' . $pendingExtension['id'] . '/verify') ?>"
        class="d-inline-block me-2">
        <?= \Core\Csrf::field() ?>
        <button class="btn btn-sm btn-success">Verifikasi &amp; Setujui Perpanjangan</button>
      </form>
      <?php elseif ($pendingExtension['payment_status'] === 'paid'): ?>
      <form method="POST" action="<?= base_url('/consultation/extension/' . $pendingExtension['id'] . '/approve') ?>"
        class="d-inline-block me-2">
        <?= \Core\Csrf::field() ?>
        <button class="btn btn-sm btn-primary">Setujui Perpanjangan</button>
      </form>
      <form method="POST" action="<?= base_url('/consultation/extension/' . $pendingExtension['id'] . '/reject') ?>"
        class="d-inline-block">
        <?= \Core\Csrf::field() ?>
        <button class="btn btn-sm btn-outline-danger">Tolak Perpanjangan</button>
      </form>
      <?php else: ?>
      <div class="small text-muted">Menunggu client menyelesaikan pembayaran tambahan.</div>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>
  <div class="col-12">
    <?php if (!$isProvider && $consultation['status'] === 'completed'): ?>
    <div class="row g-3 consultation-columns-layout">
      <div class="col-12 col-lg-6 consultation-column-left">
        <?php endif; ?>

        <?php if (!$isProvider && $consultation['status'] === 'completed'): ?>
        <?php if ($existingReview): ?>
        <div class="card border-0 shadow-sm rounded-4 p-3 p-lg-4 mb-3 review-card review-card--enhanced">
          <div class="review-header mb-3">
            <div class="review-icon"><i class="fas fa-star"></i></div>
            <div>
              <h6 class="fw-bold mb-1">Ulasan Anda</h6>
              <p class="small text-muted mb-0">Terima kasih telah berbagi pengalaman Anda.</p>
            </div>
          </div>
          <div class="mb-3">
            <?php for ($i = 1; $i <= 5; $i++): ?>
            <i class="fa<?= $i <= $existingReview['rating'] ? 's' : 'r' ?> fa-star review-star"></i>
            <?php endfor; ?>
          </div>
          <?php if (!empty($existingReview['comment'])): ?>
          <p class="small text-muted mb-0 review-summary"><?= e($existingReview['comment']) ?></p>
          <?php else: ?>
          <p class="small text-muted mb-0 review-summary">Tidak ada komentar tambahan.</p>
          <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="card border-0 shadow-sm rounded-4 p-3 p-lg-4 mb-3 review-card review-card--enhanced">
          <div class="review-header mb-3">
            <div class="review-icon"><i class="fas fa-smile"></i></div>
            <div>
              <h6 class="fw-bold mb-1">Beri Ulasan untuk <?= e($consultation['provider_name']) ?></h6>
              <p class="small text-muted mb-0">Bagaimana pengalaman Anda selama sesi konseling?</p>
            </div>
          </div>
          <form method="POST" action="<?= base_url('/consultation/' . $consultation['id'] . '/review') ?>"
            class="review-form">
            <?= \Core\Csrf::field() ?>
            <div class="mb-3">
              <div class="small fw-semibold text-muted mb-2">Pilih rating Anda</div>
              <div class="rating-group" role="radiogroup" aria-label="Pilih rating bintang">
                <?php for ($i = 5; $i >= 1; $i--): ?>
                <input type="radio" name="rating" id="star<?= $i ?>" value="<?= $i ?>" <?= $i == 5 ? 'checked' : '' ?>>
                <label for="star<?= $i ?>" title="<?= $i ?> bintang"><i class="fas fa-star"></i></label>
                <?php endfor; ?>
              </div>
              <div class="small text-muted mt-2">Bintang dari kiri ke kanan menunjukkan rating dari 1 hingga 5.</div>
            </div>
            <textarea name="comment" class="form-control mb-3 review-textarea" rows="4"
              placeholder="Ceritakan pengalaman Anda (opsional)"></textarea>
            <button type="submit" class="btn btn-primary review-submit-btn"><i class="fas fa-paper-plane me-1"></i>Kirim
              Ulasan</button>
          </form>
        </div>
        <?php endif; ?>
        <?php endif; ?>

        <?php if (!$isProvider && $consultation['status'] === 'completed'): ?>
      </div>
      <div class="col-12 col-lg-6 consultation-column-right">
        <?php $selfCareAssessment = \App\Models\Assessment::rawOne("SELECT id FROM assessments WHERE title = ? LIMIT 1", ['Self-Care Assessment']); ?>
        <?php if ($selfCareAssessment): ?>
        <div class="card p-3 p-lg-4 mb-3 assessment-card">
          <div
            class="assessment-card-inner d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
            <div class="assessment-card-copy">
              <h6 class="fw-bold mb-2"><i class="fas fa-clipboard-list me-1"></i>Assessment Pasca Sesi</h6>
              <p class="small text-muted mb-0">Silakan isi assessment self-care untuk membantu memantau kesejahteraan
                Anda setelah sesi konseling selesai.</p>
            </div>

          </div>
          <div class="assessment-card-actions">
            <a href="<?= base_url('/assessment/' . $selfCareAssessment['id'] . '/take') ?>"
              class="btn btn-sm btn-primary assessment-btn">
              <i class="fas fa-pen-to-square me-1"></i>Isi Assessment Self-Care
            </a>
          </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>

        <?php if (!$isProvider && !empty($documents)): ?>
        <div class="card p-3 mb-3">
          <h6 class="fw-bold mb-2"><i class="fas fa-file-check me-1"></i>Dokumen Hasil Konseling</h6>
          <p class="small text-muted mb-2">Dokumen berikut telah disiapkan oleh <?= e($consultation['provider_name']) ?>
            untuk Anda.</p>
          <?php foreach ($documents as $doc): ?>
          <div class="document-item d-flex justify-content-between align-items-start gap-3">
            <div class="flex-grow-1">
              <div class="fw-bold"><i
                  class="fas fa-file me-1"></i><?= e(ucfirst(str_replace('_', ' ', $doc['document_type']))) ?></div>
              <div class="file-meta"><?= e($doc['file_name']) ?> • <?= number_format($doc['file_size'] / 1024, 0) ?> KB
              </div>
              <?php if ($doc['description']): ?><div class="file-meta"><?= e($doc['description']) ?></div>
              <?php endif; ?>
              <div class="file-meta">Disediakan: <?= format_date($doc['created_at'], 'd M Y H:i') ?></div>
            </div>
            <a href="<?= base_url('/consultation/' . $consultation['id'] . '/download-document/' . $doc['id']) ?>"
              class="btn btn-sm btn-primary" title="Download"><i class="fas fa-download"></i></a>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <?php if (!$isProvider && $consultation['status'] === 'completed'): ?>
      </div>
    </div>
    <?php endif; ?>

    <?php if ($isProvider): ?>
    <!-- <div class="card p-3 mb-3">
      <h6 class="fw-bold mb-2">Catatan Konseling</h6>
      <form method="POST" action="<?= base_url('/consultation/' . $consultation['id'] . '/note') ?>" class="mb-3">
        <?= \Core\Csrf::field() ?>
        <textarea name="note" class="form-control form-control-sm mb-2" rows="2" placeholder="Tulis catatan sesi..." required></textarea>
        <button class="btn btn-sm btn-primary">Simpan Catatan</button>
      </form>
      <?php foreach ($notes as $n): ?>
        <div class="border-bottom py-2 small">
          <strong><?= e($n['author_name']) ?></strong> &middot; <span class="text-muted"><?= format_date($n['created_at'], 'd M Y H:i') ?></span>
          <p class="mb-0"><?= e($n['note']) ?></p>
        </div>
      <?php endforeach; ?>
      <?php if (empty($notes)): ?><p class="text-muted small mb-0">Belum ada catatan.</p><?php endif; ?>
    </div> -->


    <div class="card p-3 mb-3">
      <h6 class="fw-bold mb-2">RIWAYAT CONSULTATION</h6>
      <?php if (isset($consultation['previous_session'])): ?>
      <p class="small"><strong>Pernah sesi sebelumnya:</strong>
        <?= $consultation['previous_session'] === 'yes' ? 'Ya' : 'Tidak' ?></p>
      <?php if ($consultation['previous_session'] === 'yes' && !empty($consultation['previous_session_document_path'])): ?>
      <a href="<?= base_url($consultation['previous_session_document_path']) ?>" target="_blank"
        class="small link-accent d-inline-block mb-2"><i class="fas fa-file-alt me-1"></i>Lihat
        Dokumen Riwayat Sesi Sebelumnya</a>
      <?php endif; ?>
      <?php endif; ?>
    </div>




    <div class="card p-3 mb-3">
      <h6 class="fw-bold mb-2">Rencana Tindak Lanjut</h6>
      <form method="POST" action="<?= base_url('/consultation/' . $consultation['id'] . '/followup') ?>" class="mb-3">
        <?= \Core\Csrf::field() ?>
        <textarea name="plan_text" class="form-control form-control-sm mb-2" rows="2"
          placeholder="Rencana tindak lanjut..." required></textarea>
        <input type="date" name="target_date" class="form-control form-control-sm mb-2 max-w-200">
        <button class="btn btn-sm btn-primary">Simpan Rencana</button>
      </form>
      <?php foreach ($followUps as $f): ?>
      <div class="border-bottom py-2 small">
        <span class="badge bg-secondary text-uppercase"><?= e($f['status']) ?></span>
        <?= e($f['plan_text']) ?> <?php if ($f['target_date']): ?><span class="text-muted">(target:
          <?= format_date($f['target_date']) ?>)</span><?php endif; ?>
      </div>
      <?php endforeach; ?>
      <?php if (empty($followUps)): ?><p class="text-muted small mb-0">Belum ada rencana tindak lanjut.</p>
      <?php endif; ?>
    </div>

    <?php if ($isProvider && $consultation['status'] === 'completed'): ?>
    <div class="card p-3 mb-3">
      <h6 class="fw-bold mb-2"><i class="fas fa-file-upload me-1"></i>Unggah Dokumen Hasil Konseling</h6>
      <form method="POST" action="<?= base_url('/consultation/' . $consultation['id'] . '/upload-document') ?>"
        enctype="multipart/form-data" class="mb-3">
        <?= \Core\Csrf::field() ?>
        <div class="mb-2">
          <label class="form-label small fw-bold">Jenis Dokumen *</label>
          <select name="document_type" class="form-select form-select-sm" required>
            <option value="">-- Pilih Jenis Dokumen --</option>
            <option value="surat_keterangan">Surat Keterangan</option>
            <option value="hasil_konseling">Hasil Konseling</option>
            <option value="surat_rekomendasi">Surat Rekomendasi</option>
            <option value="surat_referensi">Surat Referensi</option>
            <option value="lainnya">Lainnya</option>
          </select>
        </div>
        <div class="mb-2">
          <label class="form-label small fw-bold">File Dokumen *</label>
          <input type="file" name="document" class="form-control form-control-sm"
            accept=".pdf,.doc,.docx,.xls,.xlsx,.txt,.jpg,.png" required>
          <small class="text-muted d-block mt-1">Format: PDF, DOC, DOCX, XLS, XLSX, TXT, JPG, PNG (Maks 10MB)</small>
        </div>
        <div class="mb-2">
          <label class="form-label small fw-bold">Deskripsi (Opsional)</label>
          <textarea name="description" class="form-control form-control-sm" rows="2"
            placeholder="Catatan singkat tentang dokumen ini..."></textarea>
        </div>
        <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-upload me-1"></i>Unggah Dokumen</button>
      </form>

      <?php if (!empty($documents)): ?>
      <div class="mt-3 pt-3 border-top">
        <h6 class="small fw-bold mb-2">Dokumen yang Diunggah:</h6>
        <?php foreach ($documents as $doc): ?>
        <div class="d-flex justify-content-between align-items-start p-2 bg-light rounded mb-2 small">
          <div class="flex-grow-1">
            <div class="fw-bold"><?= e(ucfirst(str_replace('_', ' ', $doc['document_type']))) ?></div>
            <div class="text-muted" style="font-size:.85rem"><?= e($doc['file_name']) ?> •
              <?= number_format($doc['file_size'] / 1024, 0) ?> KB</div>
            <?php if ($doc['description']): ?><div class="text-muted text-xs">
              <?= e($doc['description']) ?></div><?php endif; ?>
            <div class="text-muted text-xs">Diupload: <?= format_date($doc['created_at'], 'd M Y H:i') ?></div>
          </div>
          <div class="d-flex gap-1">
            <form method="POST"
              action="<?= base_url('/consultation/' . $consultation['id'] . '/delete-document/' . $doc['id']) ?>"
              class="d-inline" onsubmit="return confirm('Hapus dokumen ini?');">
              <?= \Core\Csrf::field() ?>
              <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus"><i
                  class="fas fa-trash"></i></button>
            </form>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
      <p class="text-muted small mb-0">Belum ada dokumen yang diunggah.</p>
      <?php endif; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
  </div>
</div>
</div>
</div>
</div>

<!-- <div class="col-lg-5">
    <div class="card p-3" style="max-height:600px;display:flex;flex-direction:column">
      <h6 class="fw-bold mb-2"><i class="fas fa-comments me-1"></i>Chat</h6>
      <div class="flex-grow-1 overflow-auto mb-2" style="max-height:420px">
        <?php foreach ($chats as $c): ?>
          <div class="mb-2 small">
            <strong><?= e($c['sender_name']) ?>:</strong> <?= e($c['message']) ?>
            <div class="text-muted" style="font-size:.7rem"><?= format_date($c['created_at'], 'd M H:i') ?></div>
          </div>
        <?php endforeach; ?>
        <?php if (empty($chats)): ?><p class="text-muted small">Belum ada pesan.</p><?php endif; ?>
      </div>
      <form method="POST" action="<?= base_url('/consultation/' . $consultation['id'] . '/chat') ?>" class="d-flex gap-2">
        <?= \Core\Csrf::field() ?>
        <input type="text" name="message" class="form-control form-control-sm" placeholder="Tulis pesan..." required>
        <button class="btn btn-sm btn-primary"><i class="fas fa-paper-plane"></i></button>
      </form>
      <p class="text-muted small mt-2 mb-0"><i class="fas fa-circle-info me-1"></i>Chat ini menggunakan refresh manual (bukan WebSocket real-time) pada versi starter.</p>
    </div>
  </div> -->

</div>