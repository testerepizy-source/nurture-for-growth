<h4 class="fw-bold mb-3">Backup &amp; Restore</h4>
<div class="row g-3">
  <div class="col-md-4">
    <div class="card p-3">
      <h6 class="fw-bold mb-2">Backup Database</h6>
      <p class="small text-muted">Menjalankan <code>mysqldump</code> di server dan menyimpan hasilnya ke <code>storage/backups</code>.</p>
      <form method="POST" action="<?= base_url('/super/backup/run') ?>">
        <?= \Core\Csrf::field() ?>
        <button class="btn btn-primary btn-sm w-100" type="submit"><i class="fas fa-database me-1"></i>Jalankan Backup Sekarang</button>
      </form>
      <p class="text-muted small mt-2 mb-0"><i class="fas fa-circle-info me-1"></i>Memerlukan binary <code>mysqldump</code> dapat diakses oleh PHP (<code>shell_exec</code>) di server Anda. Restore &amp; auto-backup terjadwal belum diimplementasikan pada starter ini.</p>
    </div>
  </div>
  <div class="col-md-8">
    <div class="card p-3">
      <h6 class="fw-bold mb-2">Riwayat Backup</h6>
      <table class="table table-sm">
        <thead><tr><th>Waktu</th><th>Tipe</th><th>File</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($logs as $l): ?>
          <tr>
            <td class="small"><?= format_date($l['created_at'], 'd M Y H:i') ?></td>
            <td class="text-uppercase small"><?= e($l['type']) ?></td>
            <td class="small"><?= e($l['file_path'] ? basename($l['file_path']) : '-') ?></td>
            <td><span class="badge <?= $l['status'] === 'success' ? 'bg-success' : 'bg-danger' ?>"><?= e($l['status']) ?></span></td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($logs)): ?><tr><td colspan="4" class="text-center text-muted py-3">Belum ada riwayat backup.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
