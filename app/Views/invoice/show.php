<div class="d-flex justify-content-between align-items-start mb-4">
  <div>
    <h3 class="fw-bold mb-0" style="font-family:'Plus Jakarta Sans',sans-serif;">Nurture For Growth</h3>
    <p class="text-muted small mb-0">Jl. Tomang Utara No. 17, Jakarta Barat 11440</p>
    <p class="text-muted small mb-0">nurtureforgrowth@gmail.com &middot; 0857 1603 0407</p>
  </div>
  <div class="text-end">
    <h5 class="fw-bold mb-0">TAGIHAN</h5>
    <p class="text-muted small mb-0"><?= e($invoice['invoice_number']) ?></p>
    <p class="text-muted small mb-0"><?= format_date($invoice['issued_at']) ?></p>
  </div>
</div>
<hr>
<div class="row mb-4">
  <div class="col-6">
    <p class="small text-muted mb-1">DITAGIHKAN KEPADA</p>
    <p class="fw-bold mb-0"><?= e($invoice['patient_name']) ?></p>
    <p class="small text-muted mb-0">No. RM: <?= e($invoice['medical_record_number']) ?></p>
  </div>
  <div class="col-6 text-end">
    <p class="small text-muted mb-1">TENAGA PROFESIONAL</p>
    <p class="fw-bold mb-0"><?= e($invoice['provider_name']) ?></p>
  </div>
</div>
<table class="table">
  <thead><tr><th>Deskripsi</th><th class="text-end">Jumlah</th></tr></thead>
  <tbody>
    <?php
      $baseConsultationAmount = (int)($invoice['consultation_price_amount'] ?? $invoice['amount']);
      $totalAmount = $baseConsultationAmount;
      $rescheduleTotal = 0;
      $hasDiscount = !empty($invoice['discount_amount']) && (int) $invoice['discount_amount'] > 0;
      $originalLineAmount = (int) ($invoice['original_price_amount'] ?? 0);
      if (!$originalLineAmount && $hasDiscount) {
          $originalLineAmount = $baseConsultationAmount + (int) $invoice['discount_amount'];
      }
    ?>
    <tr>
      <td>
        Sesi Konseling &mdash; <?= e(ucfirst($invoice['session_type'] ?? 'individual')) ?><br>
        <span class="small text-muted"><?= format_date($invoice['schedule_at']) ?> &middot; <?= (int) $invoice['duration_minutes'] ?> menit &middot; <?= e(str_replace('_',' ',ucfirst($invoice['method']))) ?></span>
      </td>
      <td class="text-end">Rp<?= number_format($hasDiscount ? $originalLineAmount : $baseConsultationAmount, 0, ',', '.') ?></td>
    </tr>
    <?php if ($hasDiscount): ?>
    <tr>
      <td>
        Diskon Promo<?= !empty($invoice['promo_name']) ? ' &mdash; ' . e($invoice['promo_name']) : '' ?>
      </td>
      <td class="text-end text-success">&minus;Rp<?= number_format((int) $invoice['discount_amount'], 0, ',', '.') ?></td>
    </tr>
    <?php endif; ?>
    <?php if (!empty($reschedules)): ?>
      <?php foreach ($reschedules as $reschedule): ?>
        <?php $rescheduleTotal += (int) $reschedule['rescheduling_fee']; ?>
        <tr>
          <td>
            Reschedule<br>
            <span class="small text-muted">Biaya penjadwalan ulang untuk <?= format_date($reschedule['new_schedule_at']) ?></span>
          </td>
          <td class="text-end">Rp<?= number_format($reschedule['rescheduling_fee'], 0, ',', '.') ?></td>
        </tr>
      <?php endforeach; ?>
    <?php endif; ?>
    <?php
      $totalAmount += $rescheduleTotal;
      if (!empty($extensions)):
        foreach ($extensions as $extension):
          $totalAmount += $extension['extension_price'];
    ?>
    <tr>
      <td>
        Perpanjangan Durasi (Extension)<br>
        <span class="small text-muted">Tambahan <?= (int) $extension['extension_minutes'] ?> menit &mdash; Total durasi menjadi <?= (int) $extension['total_new_duration'] ?> menit</span>
      </td>
      <td class="text-end">Rp<?= number_format($extension['extension_price'], 0, ',', '.') ?></td>
    </tr>
    <?php endforeach; endif; ?>
  </tbody>
  <tfoot>
    <tr><th>Total</th><th class="text-end"  style="font-weight:bold; font-size:1.25rem;">Rp<?= number_format($totalAmount, 0, ',', '.') ?></th></tr>
  </tfoot>
</table>
<?php
  $transferProofUrl = null;
  if (!empty($invoice['payment_method']) && $invoice['payment_method'] === 'transfer' && !empty($invoice['payment_proof_path'])) {
      $transferProofUrl = \App\Services\CommunicationService::resolvePublicUrl($invoice['payment_proof_path']);
  }
  if (empty($transferProofUrl) && !empty($reschedules)) {
      foreach ($reschedules as $reschedule) {
          if (!empty($reschedule['payment_proof_path'])) {
              $transferProofUrl = \App\Services\CommunicationService::resolvePublicUrl($reschedule['payment_proof_path']);
              break;
          }
      }
  }
?>

<!-- <?php if (!empty($transferProofUrl)): ?>
  <div class="mb-3">
    <p class="small text-muted mb-1">Bukti Transfer</p>
    <a href="<?= e($transferProofUrl) ?>" target="_blank" class="d-inline-flex align-items-center small text-primary">
      <i class="fas fa-receipt me-1"></i>Lihat Bukti Transfer
    </a>
  </div>
<?php endif; ?> -->
<p class="small text-muted mt-4">Invoice ini adalah bukti pembayaran sah yang sudah diverifikasi oleh tim NFG. Simpan untuk keperluan administrasi/reimbursement Anda.</p>
