<?php $pageTitle = 'Rencana Tindak Lanjut'; ?>
<h4 class="fw-bold mb-3">Rencana Tindak Lanjut</h4>
<p class="text-muted small">Daftar rencana tindak lanjut yang dibuat penyedia layanan setelah sesi konseling.</p>
<div class="card p-3">
  <div class="table-responsive">
    <table class="table table-sm align-middle">
      <thead><tr><th>Waktu</th><th>Client</th><th>Penyedia</th><th>Sesi</th><th>Rencana</th><th>Target</th><th>Status</th></tr></thead>
      <tbody>
      <?php foreach ($plans as $fp): ?>
        <tr>
          <td class="small"><?= format_date($fp['created_at'], 'd M Y H:i') ?></td>
          <td><?= e($fp['patient_name']) ?></td>
          <td class="small"><?= e($fp['provider_name']) ?></td>
          <td class="small"><?= format_date($fp['schedule_at'], 'd M Y') ?></td>
          <td class="small text-muted" style="max-width:320px"><?= e(mb_strimwidth($fp['plan_text'], 0, 120, '…')) ?></td>
          <td class="small"><?= e($fp['target_date'] ?: '-') ?></td>
          <td>
            <?php $fpBadge = [
              'open' => 'bg-warning-subtle text-warning-emphasis',
              'in_progress' => 'bg-primary-subtle text-primary-emphasis',
              'done' => 'bg-success-subtle text-success-emphasis',
            ][$fp['status']] ?? 'bg-secondary-subtle text-secondary-emphasis'; ?>
            <span class="badge <?= $fpBadge ?> text-uppercase"><?= e(str_replace('_', ' ', $fp['status'])) ?></span>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($plans)): ?><tr><td colspan="7" class="text-center text-muted py-3">Belum ada rencana tindak lanjut.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>