<?php use App\Models\ConsultationReschedule; ?>

<div class="consultation-page-heading">
  <div>
    <span class="consultation-eyebrow"><i class="fas fa-calendar-check"></i> Jadwal Aktif</span>
    <h4 class="mb-1">Booking &amp; Approval Konseling</h4>
    <p class="mb-0 text-muted">Menampilkan jadwal hari ini dan yang akan datang.</p>
  </div>
</div>
<div class="mb-3">
  <div class="btn-group btn-group-sm">
    <a href="<?= base_url('/consultation') ?>" class="btn btn-outline-secondary <?= $status === '' ? 'active' : '' ?>">Semua</a>
    <a href="<?= base_url('/consultation?status=pending') ?>" class="btn btn-outline-secondary <?= $status === 'pending' ? 'active' : '' ?>">Menunggu</a>
    <a href="<?= base_url('/consultation?status=approved') ?>" class="btn btn-outline-secondary <?= $status === 'approved' ? 'active' : '' ?>">Disetujui</a>
    <a href="<?= base_url('/consultation?status=completed') ?>" class="btn btn-outline-secondary <?= $status === 'completed' ? 'active' : '' ?>">Selesai</a>
  </div>
</div>
<div class="card p-3">
  <div class="table-responsive">
    <table class="table table-sm align-middle">
      <thead><tr><th>Jadwal</th><th>Client</th><th>Metode</th><th>Status</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($list as $c): ?>
        <?php $displayScheduleAt = ConsultationReschedule::getEffectiveScheduleAt($c['id'], $c['schedule_at']); ?>
        <tr>
          <td><?= format_date($displayScheduleAt, 'd M Y H:i') ?></td>
          <td><?= e($c['patient_name']) ?> <span class="text-muted small">(<?= e($c['medical_record_number']) ?>)</span></td>
          <td class="text-uppercase small"><?= e($c['method']) ?></td>
          <?php
            $statusClass = match ($c['status']) {
                'approved' => 'consultation-status-approved',
                'pending' => 'consultation-status-pending',
                'cancelled', 'rejected', 'no_show' => 'consultation-status-cancelled',
                'completed' => 'consultation-status-completed',
                default => 'consultation-status-approved',
            };
            $statusLabel = match ($c['status']) {
                'approved' => 'Disetujui',
                'pending' => 'Pending',
                'cancelled', 'rejected', 'no_show' => 'Dibatalkan',
                'completed' => 'Selesai',
                default => ucfirst($c['status']),
            };
          ?>
          <td><span class="badge consultation-status <?= $statusClass ?>"><?= e($statusLabel) ?></span></td>
          <td><a href="<?= base_url('/consultation/' . $c['id']) ?>" class="btn btn-sm btn-outline-primary">Detail</a></td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($list)): ?><tr><td colspan="5" class="text-center text-muted py-3">Tidak ada data.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
