<h4 class="fw-bold mb-3">Pesan &amp; Permintaan Booking dari Website</h4>
<p class="text-muted small mb-4">Lead dari form kontak publik. Setelah dihubungi, buatkan akun client untuk mereka lewat Data Pengguna agar dapat masuk ke alur booking &amp; sistem pencocokan cerdas.</p>
<div class="card p-3">
  <div class="table-responsive">
    <table class="table table-sm align-middle">
      <thead><tr><th>Waktu</th><th>Nama</th><th>Kontak</th><th>Layanan</th><th>Preferensi Dokter/Jadwal</th><th>Pesan</th><th>Status</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($messages as $m): ?>
        <tr class="<?= $m['is_read'] ? '' : 'fw-semibold' ?>">
          <td class="small"><?= format_date($m['created_at'], 'd M Y H:i') ?></td>
          <td><?= e($m['name']) ?></td>
          <td class="small"><?= e($m['email']) ?><?php if ($m['phone']): ?><br><?= e($m['phone']) ?><?php endif; ?></td>
          <td><span class="badge bg-primary-soft text-primary"><?= e($m['service_interest'] ?: '-') ?></span></td>
          <td class="small">
            <?= e($m['preferred_provider_name'] ?? '-') ?><br>
            <?= $m['preferred_date'] ? format_date($m['preferred_date']) : '-' ?> <?= e($m['preferred_time']) ?>
          </td>
          <td class="small text-muted" style="max-width:220px"><?= e(mb_substr($m['message'], 0, 100)) ?></td>
          <td><span class="badge <?= $m['is_read'] ? 'bg-secondary' : 'bg-warning text-dark' ?>"><?= $m['is_read'] ? 'Sudah Dibaca' : 'Baru' ?></span></td>
          <td><?php if (!$m['is_read']): ?><a href="<?= base_url('/admin/contact-messages/' . $m['id'] . '/read') ?>" class="btn btn-sm btn-outline-primary">Tandai Dibaca</a><?php endif; ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($messages)): ?><tr><td colspan="8" class="text-center text-muted py-3">Belum ada pesan masuk.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
