<?php use Core\Auth; $canProcess = !in_array(Auth::roleName(), ['psikolog', 'psikiater'], true); ?>
<h4 class="fw-bold mb-3">Permintaan Pembatalan &amp; Refund</h4>
<?php if (empty($requests)): ?>
  <p class="text-muted">Tidak ada permintaan pembatalan yang menunggu.</p>
<?php else: ?>
<?php if (!$canProcess): ?>
  <p class="text-muted small mb-3">Daftar permintaan pembatalan yang terkait dengan sesi Anda. Keputusan refund diproses oleh pihak klinik.</p>
<?php endif; ?>
<div class="table-responsive">
<table class="table">
  <thead><tr><th>Client</th><th>Psikolog</th><th>Jadwal</th><th>Jam Sebelum</th><th>Refund</th><th>Alasan</th><?php if ($canProcess): ?><th>Aksi</th><?php endif; ?></tr></thead>
  <tbody>
  <?php foreach ($requests as $r): ?>
    <tr>
      <td><?= e($r['patient_name']) ?></td>
      <td><?= e($r['provider_name']) ?></td>
      <td><?= format_date($r['schedule_at']) ?></td>
      <td><?= (int) $r['hours_before_session'] ?> jam</td>
      <td><?= (int) $r['refund_eligible_percentage'] ?>% (Rp<?= number_format($r['refund_amount'], 0, ',', '.') ?>)</td>
      <td class="small"><?= e($r['reason']) ?></td>
      <?php if ($canProcess): ?>
      <td>
        <form method="POST" action="<?= base_url('/admin/cancellations/' . $r['id'] . '/approve') ?>" class="d-inline">
          <?= \Core\Csrf::field() ?><button class="btn btn-sm btn-success">Setujui</button>
        </form>
        <form method="POST" action="<?= base_url('/admin/cancellations/' . $r['id'] . '/reject') ?>" class="d-inline">
          <?= \Core\Csrf::field() ?><button class="btn btn-sm btn-outline-danger">Tolak</button>
        </form>
      </td>
      <?php endif; ?>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php endif; ?>
