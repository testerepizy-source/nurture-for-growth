<h4 class="fw-bold mb-1">Ajukan Pembatalan Sesi</h4>
<p class="text-muted small mb-4">Sesi: <?= e($consultation['patient_name']) ?> &amp; <?= e($consultation['provider_name']) ?> &mdash; <?= format_date($consultation['schedule_at']) ?></p>

<div class="card p-3 mb-3">
  <h6 class="fw-bold mb-2">Kebijakan Refund</h6>
  <?php foreach ($policies as $p): ?>
    <p class="small mb-1"><?= svg_icon('check-circle',16) ?> <?= e($p['description']) ?></p>
  <?php endforeach; ?>
</div>

<div class="alert alert-info small">
  Jadwal sesi Anda <?= number_format($hoursBefore, 1, ',', '.') ?> jam lagi. Berdasarkan kebijakan di atas, Anda berhak atas
  <strong><?= $refundPct ?>% refund</strong>
  <?php if (!empty($consultation['price_amount'])): ?>
    (&asymp; Rp<?= number_format($consultation['price_amount'] * $refundPct / 100, 0, ',', '.') ?> dari Rp<?= number_format($consultation['price_amount'], 0, ',', '.') ?>).
  <?php endif; ?>
</div>

<form method="POST" action="<?= base_url('/consultation/' . $consultation['id'] . '/cancel-request') ?>" class="card p-3">
  <?= \Core\Csrf::field() ?>
  <label class="form-label small">Alasan Pembatalan</label>
  <textarea name="reason" class="form-control mb-3" rows="3" required></textarea>
  <button class="btn btn-danger" type="submit">Kirim Permintaan Pembatalan</button>
  <a href="<?= base_url('/consultation/' . $consultation['id']) ?>" class="btn btn-outline-secondary">Batal</a>
</form>
