<h4 class="fw-bold mb-3">Pengaturan &amp; Integrasi API</h4>

<div class="row g-3 mb-4">
  <div class="col-md-6">
    <div class="card p-3">
      <h6 class="fw-bold mb-2">Pengaturan Umum</h6>
      <form method="POST" action="<?= base_url('/super/settings/general') ?>">
        <?= \Core\Csrf::field() ?>
        <div class="mb-2"><label class="form-label small">Nama Aplikasi</label><input type="text" name="app_name" class="form-control form-control-sm" value="<?= e($settings['app_name']) ?>"></div>
        <div class="mb-2"><label class="form-label small">Timezone</label><input type="text" name="app_timezone" class="form-control form-control-sm" value="<?= e($settings['app_timezone']) ?>"></div>
        <div class="mb-2"><label class="form-label small">Bahasa</label><input type="text" name="app_language" class="form-control form-control-sm" value="<?= e($settings['app_language']) ?>"></div>
        <div class="form-check mb-3">
          <input class="form-check-input" type="checkbox" name="maintenance_mode" value="1" id="maint" <?= $settings['maintenance_mode'] === '1' ? 'checked' : '' ?>>
          <label class="form-check-label small" for="maint">Aktifkan Maintenance Mode</label>
        </div>
        <button class="btn btn-primary btn-sm">Simpan Pengaturan Umum</button>
      </form>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card p-3">
      <h6 class="fw-bold mb-2">SMTP (Email)</h6>
      <form method="POST" action="<?= base_url('/super/settings/integration/smtp') ?>">
        <?= \Core\Csrf::field() ?>
        <div class="row g-2 mb-2">
          <div class="col-8"><input type="text" name="host" class="form-control form-control-sm" placeholder="SMTP Host" value="<?= e($smtp['host'] ?? '') ?>"></div>
          <div class="col-4"><input type="text" name="port" class="form-control form-control-sm" placeholder="Port" value="<?= e($smtp['port'] ?? '') ?>"></div>
        </div>
        <input type="text" name="user" class="form-control form-control-sm mb-2" placeholder="Username" value="<?= e($smtp['user'] ?? '') ?>">
        <input type="password" name="pass" class="form-control form-control-sm mb-2" placeholder="Password (kosongkan jika tidak diubah)">
        <input type="email" name="from" class="form-control form-control-sm mb-2" placeholder="Email pengirim" value="<?= e($smtp['from'] ?? '') ?>">
        <div class="form-check mb-2">
          <input class="form-check-input" type="checkbox" name="is_active" value="1" <?= $smtpActive ? 'checked' : '' ?>>
          <label class="form-check-label small">Aktifkan</label>
        </div>
        <button class="btn btn-sm btn-outline-primary">Simpan SMTP</button>
      </form>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card p-3">
      <h6 class="fw-bold mb-2">WhatsApp API</h6>
      <form method="POST" action="<?= base_url('/super/settings/integration/whatsapp') ?>">
        <?= \Core\Csrf::field() ?>
        <input type="text" name="api_url" class="form-control form-control-sm mb-2" placeholder="API URL" value="<?= e($whatsapp['api_url'] ?? '') ?>">
        <input type="password" name="token" class="form-control form-control-sm mb-2" placeholder="Token (kosongkan jika tidak diubah)">
        <input type="text" name="session_id" class="form-control form-control-sm mb-2" placeholder="OpenWA session id (mis. default)" value="<?= e($whatsapp['session_id'] ?? '') ?>">
        <div class="small text-muted mb-2">Kosongkan pengaturan ini jika Anda tidak ingin menggunakan WhatsApp API. Sistem akan menyediakan tautan WhatsApp manual di email dan halaman booking.</div>
        <div class="form-check mb-2">
          <input class="form-check-input" type="checkbox" name="is_active" value="1" <?= $waActive ? 'checked' : '' ?>>
          <label class="form-check-label small">Aktifkan</label>
        </div>
        <button class="btn btn-sm btn-outline-primary">Simpan WhatsApp</button>
      </form>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card p-3">
      <h6 class="fw-bold mb-2">Google API</h6>
      <form method="POST" action="<?= base_url('/super/settings/integration/google') ?>">
        <?= \Core\Csrf::field() ?>
        <input type="text" name="client_id" class="form-control form-control-sm mb-2" placeholder="Client ID" value="<?= e($google['client_id'] ?? '') ?>">
        <input type="password" name="client_secret" class="form-control form-control-sm mb-2" placeholder="Client Secret (kosongkan jika tidak diubah)">
        <input type="password" name="refresh_token" class="form-control form-control-sm mb-2" placeholder="Refresh Token (kosongkan jika tidak diubah)">
        <input type="text" name="calendar_id" class="form-control form-control-sm mb-2" placeholder="Calendar ID (default: primary)" value="<?= e($google['calendar_id'] ?? 'primary') ?>">
        <p class="small text-muted mb-2">Scope wajib: Google Calendar API <code>calendar.events</code>. Event akan mengundang client dan psikolog serta membuat link Google Meet.</p>
        <div class="form-check mb-2">
          <input class="form-check-input" type="checkbox" name="is_active" value="1" <?= $googleActive ? 'checked' : '' ?>>
          <label class="form-check-label small">Aktifkan</label>
        </div>
        <button class="btn btn-sm btn-outline-primary">Simpan Google API</button>
      </form>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card p-3">
      <h6 class="fw-bold mb-2">Penyedia Asisten Pintar</h6>
      <form method="POST" action="<?= base_url('/super/settings/integration/ai') ?>">
        <?= \Core\Csrf::field() ?>
        <input type="text" name="provider" class="form-control form-control-sm mb-2" placeholder="Penyedia (mis. openai/claude)" value="<?= e($ai['provider'] ?? '') ?>">
        <input type="password" name="api_key" class="form-control form-control-sm mb-2" placeholder="Kunci API (kosongkan jika tidak diubah)">
        <input type="text" name="model" class="form-control form-control-sm mb-2" placeholder="Model" value="<?= e($ai['model'] ?? '') ?>">
        <div class="form-check mb-2">
          <input class="form-check-input" type="checkbox" name="is_active" value="1" <?= $aiActive ? 'checked' : '' ?>>
          <label class="form-check-label small">Aktifkan</label>
        </div>
        <button class="btn btn-sm btn-outline-primary">Simpan Konfigurasi Asisten</button>
      </form>
      <p class="text-muted small mt-2 mb-0"><i class="fas fa-circle-info me-1"></i>Asisten otomatis di sini berjalan dengan ringkasan berbasis aturan (lihat README). Kredensial hanya digunakan sebagai titik integrasi lanjutan.</p>
    </div>
  </div>
</div>
