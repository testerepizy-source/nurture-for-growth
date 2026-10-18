<h4 class="fw-bold mb-3">Audit Log</h4>
<div class="card p-3">
  <div class="table-responsive" style="max-height:70vh">
    <table class="table table-sm align-middle">
      <thead><tr><th>Waktu</th><th>User</th><th>Aksi</th><th>Modul</th><th>Deskripsi</th><th>IP</th></tr></thead>
      <tbody>
      <?php foreach ($logs as $l): ?>
        <tr>
          <td class="small"><?= format_date($l['created_at'], 'd M Y H:i:s') ?></td>
          <td class="small"><?= e($l['user_name'] ?? 'System') ?></td>
          <td><span class="badge bg-primary-soft text-primary text-uppercase"><?= e($l['action']) ?></span></td>
          <td class="small"><?= e($l['module']) ?></td>
          <td class="small text-muted"><?= e($l['description']) ?></td>
          <td class="small text-muted"><?= e($l['ip_address']) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($logs)): ?><tr><td colspan="6" class="text-center text-muted py-3">Belum ada log.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
