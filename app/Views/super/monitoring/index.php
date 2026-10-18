<h4 class="fw-bold mb-3">System Monitoring</h4>
<div class="row g-3 mb-4">
  <div class="col-md-3"><div class="stat-card"><div class="stat-label">PHP Version</div><div class="stat-value" style="font-size:1.1rem"><?= e($snapshot['php_version']) ?></div></div></div>
  <div class="col-md-3"><div class="stat-card"><div class="stat-label">Memory Usage</div><div class="stat-value" style="font-size:1.1rem"><?= e($snapshot['memory_usage_mb']) ?> MB</div></div></div>
  <div class="col-md-3"><div class="stat-card"><div class="stat-label">Disk Free</div><div class="stat-value" style="font-size:1.1rem"><?= $snapshot['disk_free_gb'] !== null ? e($snapshot['disk_free_gb']) . ' GB' : 'N/A' ?></div></div></div>
  <div class="col-md-3"><div class="stat-card"><div class="stat-label">Database</div><div class="stat-value" style="font-size:1.1rem"><span class="badge <?= $snapshot['db_status'] === 'connected' ? 'bg-success' : 'bg-danger' ?>"><?= e($snapshot['db_status']) ?></span></div></div></div>
</div>
<div class="card p-3">
  <h6 class="fw-bold mb-2">Log Monitoring Terakhir</h6>
  <table class="table table-sm">
    <thead><tr><th>Waktu</th><th>Metric</th><th>Value</th></tr></thead>
    <tbody>
    <?php foreach ($recent as $r): ?>
      <tr><td class="small"><?= format_date($r['recorded_at'], 'd M Y H:i:s') ?></td><td class="small"><?= e($r['metric']) ?></td><td class="small"><?= e($r['value']) ?></td></tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <p class="text-muted small mb-0"><i class="fas fa-circle-info me-1"></i>Snapshot diambil setiap kali halaman ini dibuka. Monitoring otomatis/terjadwal belum diimplementasikan pada starter ini.</p>
</div>
