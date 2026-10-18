<?php $pageTitle = 'Daftar Konseling'; ?>
<h4 class="fw-bold mb-3">Konsultasi Konseling</h4>
<p class="text-muted small">Ringkasan seluruh sesi konseling klinik. Gunakan filter tanggal atau status untuk mempersempit daftar.</p>

<div class="card p-3 mb-3">
  <form method="GET" class="row g-2 align-items-end">
    <div class="col-sm-4 col-md-3">
      <label class="record-label">Tanggal</label>
      <input type="date" name="date" class="form-control" value="<?= e($date) ?>">
    </div>
    <div class="col-sm-4 col-md-3">
      <label class="record-label">Status</label>
      <select name="status" class="form-select">
        <option value="">Semua status</option>
        <?php $statuses = ['pending', 'approved', 'rejected', 'rescheduled', 'cancelled', 'completed', 'no_show']; ?>
        <?php foreach ($statuses as $st): ?>
          <option value="<?= e($st) ?>" <?= $status === $st ? 'selected' : '' ?>><?= e(ucfirst($st)) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-auto">
      <button class="btn btn-primary"><i class="fas fa-filter me-1"></i>Terapkan</button>
      <a href="<?= base_url('/admin/consultations') ?>" class="btn btn-outline-secondary">Reset</a>
    </div>
  </form>
</div>

<div class="card p-3">
  <div class="table-responsive">
    <table class="table table-sm align-middle">
      <thead>
        <tr><th>Waktu</th><th>Client</th><th>Penyedia</th><th>Status</th><th>Pembayaran</th><th>Biaya</th></tr>
      </thead>
      <tbody>
      <?php foreach ($list as $c): ?>
        <tr>
          <td class="small"><?= format_date($c['schedule_at'], 'd M Y H:i') ?></td>
          <td><?= e($c['patient_name']) ?></td>
          <td class="small"><?= e($c['provider_name']) ?></td>
          <td>
            <?php $statusBadge = [
              'pending' => 'bg-warning-subtle text-warning-emphasis',
              'approved' => 'bg-primary-subtle text-primary-emphasis',
              'completed' => 'bg-success-subtle text-success-emphasis',
              'no_show' => 'bg-danger-subtle text-danger-emphasis',
              'cancelled' => 'bg-danger-subtle text-danger-emphasis',
              'rejected' => 'bg-danger-subtle text-danger-emphasis',
              'rescheduled' => 'bg-secondary-subtle text-secondary-emphasis',
            ][$c['status']] ?? 'bg-secondary-subtle text-secondary-emphasis'; ?>
            <span class="badge <?= $statusBadge ?> text-uppercase"><?= e($c['status']) ?></span>
          </td>
          <td>
            <?php $payBadge = [
              'paid' => 'bg-success-subtle text-success-emphasis',
              'pending_verification' => 'bg-warning-subtle text-warning-emphasis',
              'unpaid' => 'bg-secondary-subtle text-secondary-emphasis',
            ][$c['payment_status']] ?? 'bg-secondary-subtle text-secondary-emphasis'; ?>
            <span class="badge <?= $payBadge ?> text-uppercase"><?= e(str_replace('_', ' ', $c['payment_status'])) ?></span>
          </td>
          <td class="small"><?= $c['price_amount'] !== null ? 'Rp' . number_format($c['price_amount'], 0, ',', '.') : '-' ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($list)): ?><tr><td colspan="6" class="text-center text-muted py-3">Tidak ada konsultasi yang cocok.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>