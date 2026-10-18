<div class="profile-heading">
  <div>
    <span class="profile-eyebrow"><i class="fas fa-id-card"></i> Akun Saya</span>
    <h4 class="mb-1">Profil Saya</h4>
    <p class="mb-0">Kelola informasi pribadi dan preferensi akun Anda.</p>
  </div>
  <span class="profile-role-badge"><i class="fas fa-shield-heart me-1"></i><?= e(ucfirst($role)) ?></span>
</div>

<?php if ($role === 'pasien'): ?>
<div class="family-profile-panel mb-4">
  <div class="family-profile-panel-heading">
    <div>
      <span class="profile-eyebrow"><i class="fas fa-people-roof"></i> Keluarga</span>
      <h6 class="mb-1">Profil Keluarga</h6>
      <p class="mb-0">Setiap anggota memiliki data kesehatan yang terpisah.</p>
    </div>
    <span class="family-profile-count"><?= count($familyProfiles) ?> profil</span>
  </div>
  <div class="family-profile-list">
    <?php foreach ($familyProfiles as $familyProfile): ?>
      <form method="POST" action="<?= base_url('/profile/family/switch') ?>">
        <?= \Core\Csrf::field() ?>
        <input type="hidden" name="patient_id" value="<?= (int) $familyProfile['patient_id'] ?>">
        <button type="submit" class="family-profile-card <?= (int) $activePatientId === (int) $familyProfile['patient_id'] ? 'is-active' : '' ?>">
          <span class="family-profile-avatar"><?= e(mb_substr($familyProfile['patient_name'], 0, 1)) ?></span>
          <span class="family-profile-info"><strong><?= e($familyProfile['patient_name']) ?></strong><small><?= e(ucfirst($familyProfile['relationship'])) ?> · <?= (int) $familyProfile['can_view_medical_data'] ? 'Data kesehatan aktif' : 'Data kesehatan terbatas' ?></small></span>
          <?php if ((int) $activePatientId === (int) $familyProfile['patient_id']): ?><i class="fas fa-circle-check family-profile-check"></i><?php endif; ?>
        </button>
      </form>
    <?php endforeach; ?>
  </div>
  <form method="POST" action="<?= base_url('/profile/family/add') ?>" class="family-profile-add">
    <?= \Core\Csrf::field() ?>
    <div class="row g-2 align-items-end">
      <div class="col-md-5"><label class="form-label small">Nama anggota</label><input type="text" name="family_name" class="form-control form-control-sm" placeholder="Contoh: Siti Aminah" required></div>
      <div class="col-md-3"><label class="form-label small">Hubungan</label><select name="relationship" class="form-select form-select-sm"><option value="ayah">Ayah</option><option value="ibu">Ibu</option><option value="suami">Suami</option><option value="istri">Istri</option><option value="anak">Anak</option></select></div>
      <div class="col-md-4"><div class="form-check mt-4"><input type="hidden" name="can_view_medical_data" value="0"><input class="form-check-input" type="checkbox" name="can_view_medical_data" value="1" id="canViewMedicalData" checked><label class="form-check-label small" for="canViewMedicalData">Izinkan akses data kesehatan <span class="text-muted">(aktif otomatis)</span></label></div></div>
      <div class="col-md-3"><button class="btn btn-outline-primary btn-sm w-100" type="submit"><i class="fas fa-user-plus me-1"></i>Tambah Profil</button></div>
    </div>
  </form>
</div>
<?php endif; ?>

<div class="row g-4 profile-layout">
  <div class="col-12">
    <div class="card profile-form-card">
      <div class="profile-section-heading">
        <div class="profile-section-icon"><i class="fas fa-user"></i></div>
        <div>
          <h6 class="mb-1">Data Pribadi</h6>
          <p class="mb-0">Pastikan informasi kontak Anda tetap terbaru.</p>
        </div>
      </div>
      <form method="POST" action="<?= base_url('/profile') ?>" enctype="multipart/form-data">
        <?= \Core\Csrf::field() ?>
        <div class="profile-photo-row">
          <span class="avatar-circle profile-avatar">
            <?php if (!empty($user['photo'])): ?>
              <img src="<?= base_url($user['photo']) ?>" alt="" id="photoPreview">
            <?php else: ?>
              <span id="photoPreviewInitial"><?= e(mb_substr($user['name'], 0, 1)) ?></span>
            <?php endif; ?>
          </span>
          <div class="profile-photo-copy">
            <strong>Foto profil</strong>
            <label class="btn btn-sm btn-outline-primary" for="photoInput"><i class="fas fa-camera me-1"></i> Ganti Foto</label>
            <input type="file" name="photo" id="photoInput" accept="image/jpeg,image/png,image/webp" class="d-none" onchange="previewProfilePhoto(this)">
            <p class="text-muted small mb-0">JPG, PNG, atau WEBP, maksimal 2MB.</p>
          </div>
        </div>
        <div class="row g-2 mb-2">
          <div class="col-md-6">
            <label class="form-label small">Nama</label>
            <input type="text" name="name" class="form-control" value="<?= e($user['name']) ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label small">Email</label>
            <input type="email" class="form-control" value="<?= e($user['email']) ?>" disabled>
          </div>
        </div>
        <div class="row g-2 mb-2">
          <div class="col-md-6">
            <label class="form-label small">No. HP</label>
            <input type="text" name="phone" class="form-control" value="<?= e($user['phone']) ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label small">Jenis Kelamin</label>
            <select name="gender" class="form-select">
              <option value="">-</option>
              <option value="L" <?= $user['gender'] === 'L' ? 'selected' : '' ?>>Laki-laki</option>
              <option value="P" <?= $user['gender'] === 'P' ? 'selected' : '' ?>>Perempuan</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label small">Tanggal Lahir</label>
            <input type="date" name="birth_date" class="form-control" value="<?= e($user['birth_date']) ?>">
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label small">Alamat</label>
          <textarea name="address" class="form-control" rows="2"><?= e($user['address']) ?></textarea>
        </div>

        <?php if ($patient): ?>
        <hr>
        <h6 class="fw-bold mb-2">Data Klinis Tambahan</h6>
        <p class="text-muted small">No. Rekam Medis: <strong><?= e($patient['medical_record_number']) ?></strong></p>
        <div class="row g-2 mb-2">
          <div class="col-md-6">
            <label class="form-label small">Pekerjaan</label>
            <input type="text" name="occupation" class="form-control" value="<?= e($patient['occupation']) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label small">Pendidikan</label>
            <input type="text" name="education" class="form-control" value="<?= e($patient['education']) ?>">
          </div>
        </div>
        <div class="row g-2 mb-2">
          <div class="col-md-4">
            <label class="form-label small">Kontak Darurat - Nama</label>
            <input type="text" name="emergency_contact_name" class="form-control" value="<?= e($patient['emergency_contact_name']) ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label small">Kontak Darurat - No. HP</label>
            <input type="text" name="emergency_contact_phone" class="form-control" value="<?= e($patient['emergency_contact_phone']) ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label small">Hubungan</label>
            <input type="text" name="emergency_contact_relation" class="form-control" value="<?= e($patient['emergency_contact_relation']) ?>">
          </div>
        </div>
        <div class="mb-2">
          <label class="form-label small">Riwayat Penyakit (opsional)</label>
          <textarea name="medical_history" class="form-control" rows="2"><?= e($patient['medical_history']) ?></textarea>
        </div>
        <!-- <div class="mb-3">
          <label class="form-label small">Riwayat Alergi (opsional)</label>
          <textarea name="allergy_history" class="form-control" rows="2"><?= e($patient['allergy_history']) ?></textarea>
        </div> -->
        <div class="mb-3">
          <label class="form-label small">Preferensi Privasi</label>
          <select name="privacy_pref" class="form-select" style="max-width:220px">
            <option value="private" <?= $patient['privacy_pref'] === 'private' ? 'selected' : '' ?>>Privat</option>
            <option value="public" <?= $patient['privacy_pref'] === 'public' ? 'selected' : '' ?>>Publik (agregat/anonim)</option>
          </select>
        </div>
        <?php endif; ?>

        <?php if ($patient): ?>
        <div class="consent-record-inline">
        <div class="consent-record-header">
          <div class="consent-record-title"><span class="consent-record-icon"><i class="fas fa-file-signature"></i></span><div><h6>Informed Consent Layanan Psikologi</h6><p>Persetujuan layanan yang tersimpan di data Anda.</p></div></div>
          <?php if ($informedConsent): ?><span class="consent-status-badge"><i class="fas fa-check me-1"></i>Sudah disetujui</span><?php endif; ?>
        </div>
        <?php if ($informedConsent): ?>
          <p class="consent-record-meta">Disetujui pada <?= format_date($informedConsent['signed_at'], 'd M Y H:i') ?> oleh <strong><?= e($informedConsent['signature_name']) ?></strong>.</p>
          <details class="consent-record-details">
            <summary>Lihat ringkasan persetujuan</summary>
            <div class="consent-record-grid consent-record-grid-compact">
              <div><span class="record-label">Nama Klien</span><strong><?= e($informedConsent['full_name']) ?></strong></div>
              <div><span class="record-label">Orang Tua/Wali</span><strong><?= e($informedConsent['guardian_name'] ?: '-') ?></strong></div>
              <div class="consent-record-wide"><span class="record-label">Pernyataan</span><strong>Seluruh 8 poin informed consent disetujui secara elektronik.</strong></div>
              <div><span class="record-label">Tanda Tangan Elektronik</span><strong><?= e($informedConsent['signature_name']) ?></strong></div>
            </div>
          </details>
        <?php else: ?>
          <p class="consent-record-empty">Belum ada informed consent yang tersimpan.</p>
        <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($providerRecord): ?>
        <hr>
        <h6 class="fw-bold mb-2">Data Profesional <span class="text-muted small fw-normal">(tampil di halaman publik &amp; dapat dilihat calon client)</span></h6>
        <div class="mb-2">
          <label class="form-label small">Gelar / Spesialisasi Singkat</label>
          <input type="text" name="specialization" class="form-control" value="<?= e($providerRecord['specialization']) ?>" placeholder="Misal: Psikologi Klinis Anak dan Keluarga">
        </div>
        <div class="mb-3">
          <label class="form-label small">Ringkasan Profesional (Bio)</label>
          <textarea name="bio" class="form-control" rows="4" placeholder="Ceritakan pengalaman &amp; pendekatan Anda kepada calon client..."><?= e($providerRecord['bio']) ?></textarea>
        </div>
        <div class="mb-3" style="max-width:220px">
          <label class="form-label small">Tahun Pengalaman</label>
          <input type="number" name="years_experience" class="form-control" value="<?= (int) $providerRecord['years_experience'] ?>" min="0">
        </div>
        <div class="mb-3">
          <label class="form-label small d-block">Bidang Spesialisasi (untuk Sistem Pencocokan Cerdas)</label>
          <div class="d-flex flex-wrap gap-2">
            <?php foreach ($allTags as $t): ?>
              <label class="tag-chip">
                <input type="checkbox" name="tags[]" value="<?= $t['id'] ?>" class="d-none tag-chip-input" <?= in_array($t['id'], $providerTags) ? 'checked' : '' ?>>
                <span><?= e($t['name']) ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>
        <p class="text-muted small"><i class="fas fa-circle-info me-1"></i>No. STR/SIPP hanya dapat diubah oleh Admin untuk menjaga validitas lisensi.</p>

        <h6 class="fw-bold mb-2 mt-3">Tautan Media Sosial <span class="text-muted small fw-normal">(tampil di halaman profil publik, opsional)</span></h6>
        <div class="row g-2 mb-2">
          <div class="col-md-6">
            <label class="form-label small"><i class="fab fa-facebook-f me-1" style="color:#1877f2"></i>Facebook</label>
            <input type="url" name="facebook_url" class="form-control" value="<?= e($providerRecord['facebook_url'] ?? '') ?>" placeholder="https://facebook.com/namaanda">
          </div>
          <div class="col-md-6">
            <label class="form-label small"><i class="fab fa-x-twitter me-1"></i>X (Twitter)</label>
            <input type="url" name="twitter_url" class="form-control" value="<?= e($providerRecord['twitter_url'] ?? '') ?>" placeholder="https://x.com/namaanda">
          </div>
        </div>
        <div class="row g-2 mb-3">
          <div class="col-md-6">
            <label class="form-label small"><i class="fab fa-linkedin-in me-1" style="color:#0a66c2"></i>LinkedIn</label>
            <input type="url" name="linkedin_url" class="form-control" value="<?= e($providerRecord['linkedin_url'] ?? '') ?>" placeholder="https://linkedin.com/in/namaanda">
          </div>
          <div class="col-md-6">
            <label class="form-label small"><i class="fab fa-instagram me-1" style="color:#e1306c"></i>Instagram</label>
            <input type="url" name="instagram_url" class="form-control" value="<?= e($providerRecord['instagram_url'] ?? '') ?>" placeholder="https://instagram.com/namaanda">
          </div>
        </div>
        <div class="mb-3" style="max-width:320px">
          <label class="form-label small"><i class="fab fa-whatsapp me-1" style="color:#25d366"></i>Nomor WhatsApp</label>
          <input type="text" name="whatsapp_number" class="form-control" value="<?= e($providerRecord['whatsapp_number'] ?? '') ?>" placeholder="628123456789">
          <p class="text-muted small mt-1 mb-0">Format: kode negara tanpa tanda + atau spasi, contoh <code>628123456789</code>.</p>
        </div>
        <?php endif; ?>

        <div class="profile-form-actions">
          <button class="btn btn-primary" type="submit"><i class="fas fa-check me-2"></i>Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>

</div>
