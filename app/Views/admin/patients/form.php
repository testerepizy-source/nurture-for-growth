<?php $isEdit = $patient !== null; ?>
<h4 class="fw-bold mb-3"><?= $isEdit ? 'Edit Client' : 'Tambah Client' ?></h4>
<div class="card p-3" style="max-width:720px">
  <form method="POST" action="<?= $isEdit ? base_url('/admin/patients/' . $patient['id'] . '/update') : base_url('/admin/patients') ?>">
    <?= \Core\Csrf::field() ?>

    <h6 class="text-teal fw-semibold mb-2">Akun</h6>
    <div class="row g-2">
      <div class="col-12">
        <label class="form-label small">Nama Lengkap</label>
        <input type="text" name="name" class="form-control" required value="<?= e($patient['name'] ?? '') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label small">Email</label>
        <input type="email" name="email" class="form-control" required value="<?= e($patient['email'] ?? '') ?>">
      </div>
      <?php if (!$isEdit): ?>
        <div class="col-md-6">
          <label class="form-label small">Password</label>
          <input type="password" name="password" class="form-control" required minlength="8">
        </div>
      <?php endif; ?>
      <div class="col-md-4">
        <label class="form-label small">Status</label>
        <select name="status" class="form-select">
          <option value="active" <?= ($patient['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Aktif</option>
          <option value="inactive" <?= ($patient['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Nonaktif</option>
          <option value="banned" <?= ($patient['status'] ?? '') === 'banned' ? 'selected' : '' ?>>Banned</option>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label small">Jenis Kelamin</label>
        <select name="gender" class="form-select">
          <option value="">-</option>
          <option value="L" <?= ($patient['gender'] ?? '') === 'L' ? 'selected' : '' ?>>Laki-laki</option>
          <option value="P" <?= ($patient['gender'] ?? '') === 'P' ? 'selected' : '' ?>>Perempuan</option>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label small">Tanggal Lahir</label>
        <input type="date" name="birth_date" class="form-control" value="<?= e($patient['birth_date'] ?? '') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label small">No. HP</label>
        <input type="text" name="phone" class="form-control" value="<?= e($patient['phone'] ?? '') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label small">Alamat</label>
        <input type="text" name="address" class="form-control" value="<?= e($patient['address'] ?? '') ?>">
      </div>
    </div>

    <hr class="my-3">
    <h6 class="text-teal fw-semibold mb-2">Data Tambahan</h6>
    <div class="row g-2">
      <div class="col-md-6">
        <label class="form-label small">Pekerjaan</label>
        <input type="text" name="occupation" class="form-control" value="<?= e($patient['occupation'] ?? '') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label small">Pendidikan</label>
        <input type="text" name="education" class="form-control" value="<?= e($patient['education'] ?? '') ?>">
      </div>
      <div class="col-12">
        <label class="form-label small">Status Pernikahan</label>
        <select name="marital_status" class="form-select">
          <option value="">-</option>
          <option value="single" <?= ($patient['marital_status'] ?? '') === 'single' ? 'selected' : '' ?>>Lajang</option>
          <option value="married" <?= ($patient['marital_status'] ?? '') === 'married' ? 'selected' : '' ?>>Menikah</option>
          <option value="divorced" <?= ($patient['marital_status'] ?? '') === 'divorced' ? 'selected' : '' ?>>Bercerai</option>
          <option value="widowed" <?= ($patient['marital_status'] ?? '') === 'widowed' ? 'selected' : '' ?>>Duda/Janda</option>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label small">Kontak Darurat - Nama</label>
        <input type="text" name="emergency_contact_name" class="form-control" value="<?= e($patient['emergency_contact_name'] ?? '') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label small">Kontak Darurat - Telepon</label>
        <input type="text" name="emergency_contact_phone" class="form-control" value="<?= e($patient['emergency_contact_phone'] ?? '') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label small">Kontak Darurat - Hubungan</label>
        <input type="text" name="emergency_contact_relation" class="form-control" value="<?= e($patient['emergency_contact_relation'] ?? '') ?>">
      </div>
      <div class="col-12">
        <label class="form-label small">Riwayat Medis</label>
        <textarea name="medical_history" class="form-control" rows="2"><?= e($patient['medical_history'] ?? '') ?></textarea>
      </div>
      <div class="col-12">
        <label class="form-label small">Riwayat Alergi</label>
        <textarea name="allergy_history" class="form-control" rows="2"><?= e($patient['allergy_history'] ?? '') ?></textarea>
      </div>
      <div class="col-12">
        <label class="form-label small">Dirujuk Oleh</label>
        <input type="text" name="referred_by" class="form-control" value="<?= e($patient['referred_by'] ?? '') ?>">
      </div>
    </div>

    <hr class="my-3">
    <h6 class="text-teal fw-semibold mb-2">Penugasan Psikolog</h6>
    <div class="row g-2">
      <div class="col-md-12">
        <label class="form-label small">Psikolog</label>
        <select name="assigned_psychologist_id" class="form-select">
          <option value="">-</option>
          <?php foreach ($psychologists as $ps): ?>
            <option value="<?= $ps['user_id'] ?>" <?= ($patient['assigned_psychologist_id'] ?? null) == $ps['user_id'] ? 'selected' : '' ?>><?= e($ps['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="mt-3">
      <button class="btn btn-primary" type="submit"><?= $isEdit ? 'Simpan' : 'Simpan Client' ?></button>
      <a href="<?= base_url('/admin/patients') ?>" class="btn btn-outline-secondary">Batal</a>
    </div>
  </form>
</div>