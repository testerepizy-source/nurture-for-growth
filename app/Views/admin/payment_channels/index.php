<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h4 class="fw-bold mb-1">Kanal Pembayaran Manual</h4>
    <p class="text-muted small mb-0">Rekening bank &amp; QRIS yang ditampilkan ke client saat pembayaran manual. Bukti pembayaran wajib diunggah client.</p>
  </div>
  <a href="<?= base_url('/admin/payment-channels/create') ?>" class="btn btn-primary btn-sm">+ Tambah Kanal</a>
</div>

<?php
  $groups = ['bank' => 'Transfer Bank', 'qris' => 'QRIS'];
  $grouped = [];
  foreach ($channels as $c) { $grouped[$c['type']][] = $c; }
?>
<?php if (empty($channels)): ?>
  <div class="card p-4 text-center">
    <i class="fas fa-money-bill-transfer fa-2x text-muted mb-2"></i>
    <p class="text-muted mb-0">Belum ada kanal pembayaran. Tambahkan minimal satu rekening bank atau QRIS agar client dapat membayar secara manual.</p>
  </div>
<?php else: ?>
  <?php foreach ($groups as $type => $typeLabel): ?>
    <?php if (empty($grouped[$type])) continue; ?>
    <h6 class="fw-bold text-uppercase text-muted small mt-4 mb-2">
      <i class="fas <?= $type === 'bank' ? 'fa-building-columns' : 'fa-qrcode' ?> me-1"></i><?= $typeLabel ?>
    </h6>
    <div class="row g-3">
      <?php foreach ($grouped[$type] as $i => $ch): ?>
        <div class="col-md-6 col-xl-4">
          <div class="card p-3 h-100 d-flex flex-column">
            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
              <h6 class="fw-bold mb-0"><?= strip_tags($ch['label']) ?></h6>
              <span class="badge <?= $ch['is_active'] ? 'bg-success' : 'bg-secondary' ?> flex-shrink-0"><?= $ch['is_active'] ? 'Aktif' : 'Nonaktif' ?></span>
            </div>
            <?php if ($ch['type'] === 'bank'): ?>
              <p class="small mb-1"><strong><?= e($ch['bank_name']) ?></strong></p>
              <p class="small font-monospace mb-1"><?= e($ch['account_number']) ?></p>
              <p class="small text-muted mb-2">a.n. <?= e($ch['account_name']) ?></p>
            <?php else: ?>
              <p class="small text-muted mb-2">Kode QR statis untuk semua aplikasi pembayaran.</p>
            <?php endif; ?>
            <div style="width:110px;height:110px;border-radius:.6rem;overflow:hidden;background:#f1f5f3;border:1px solid var(--nfg-border);display:grid;place-items:center;margin-bottom:10px;">
              <?php if (!empty($ch['image_path'])): ?>
                <img src="<?= base_url($ch['image_path']) ?>" alt="" style="width:100%;height:100%;object-fit:contain;" loading="lazy">
              <?php else: ?>
                <i class="fas <?= $ch['type'] === 'bank' ? 'fa-building-columns' : 'fa-qrcode' ?> text-muted"></i>
              <?php endif; ?>
            </div>
            <div class="mt-auto d-flex gap-1 flex-wrap">
              <a href="<?= base_url('/admin/payment-channels/' . $ch['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary">Ubah</a>
              <form method="POST" action="<?= base_url('/admin/payment-channels/' . $ch['id'] . '/toggle') ?>" class="d-inline"><?= \Core\Csrf::field() ?><button class="btn btn-sm btn-outline-<?= $ch['is_active'] ? 'warning' : 'success' ?>"><?= $ch['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?></button></form>
              <form method="POST" action="<?= base_url('/admin/payment-channels/' . $ch['id'] . '/move-up') ?>" class="d-inline"><?= \Core\Csrf::field() ?><button class="btn btn-sm btn-outline-secondary" title="Naikkan urutan"><i class="fas fa-arrow-up"></i></button></form>
              <form method="POST" action="<?= base_url('/admin/payment-channels/' . $ch['id'] . '/move-down') ?>" class="d-inline"><?= \Core\Csrf::field() ?><button class="btn btn-sm btn-outline-secondary" title="Turunkan urutan"><i class="fas fa-arrow-down"></i></button></form>
              <form method="POST" action="<?= base_url('/admin/payment-channels/' . $ch['id'] . '/delete') ?>" class="d-inline" onsubmit="return confirm('Hapus kanal ini?')"><?= \Core\Csrf::field() ?><button class="btn btn-sm btn-outline-danger">Hapus</button></form>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endforeach; ?>
<?php endif; ?>
