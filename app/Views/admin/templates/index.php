<h4 class="fw-bold mb-3">Template Notifikasi &amp; Email</h4>

<h6 class="fw-bold">Template Notifikasi (In-App)</h6>
<div class="row g-3 mb-4">
  <?php foreach ($notificationTemplates as $t): ?>
    <div class="col-md-6">
      <div class="card p-3">
        <form method="POST" action="<?= base_url('/admin/templates/notification/' . $t['id']) ?>">
          <?= \Core\Csrf::field() ?>
          <p class="small text-muted mb-1">Kode: <code><?= e($t['code']) ?></code> &middot; Channel: <?= e($t['channel']) ?></p>
          <textarea name="body" class="form-control form-control-sm mb-2" rows="2"><?= e($t['body']) ?></textarea>
          <button class="btn btn-sm btn-outline-primary">Simpan</button>
        </form>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<h6 class="fw-bold">Template Email</h6>
<div class="row g-3">
  <?php foreach ($emailTemplates as $t): ?>
    <div class="col-md-6">
      <div class="card p-3">
        <form method="POST" action="<?= base_url('/admin/templates/email/' . $t['id']) ?>">
          <?= \Core\Csrf::field() ?>
          <p class="small text-muted mb-1">Kode: <code><?= e($t['code']) ?></code></p>
          <input type="text" name="subject" class="form-control form-control-sm mb-2" value="<?= e($t['subject']) ?>">
          <textarea name="body_html" class="form-control form-control-sm mb-2" rows="3"><?= e($t['body_html']) ?></textarea>
          <button class="btn btn-sm btn-outline-primary">Simpan</button>
        </form>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<p class="text-muted small mt-3"><i class="fas fa-circle-info me-1"></i>Template ini digunakan sebagai isi pesan. Pengiriman email/WhatsApp sungguhan memerlukan konfigurasi SMTP/WhatsApp API aktif di menu Super Admin &gt; Settings &amp; API.</p>
