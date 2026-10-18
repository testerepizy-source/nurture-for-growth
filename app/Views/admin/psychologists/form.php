<h4 class="fw-bold mb-3"><?= isset($psychologist) ? 'Edit Psikolog' : 'Tambah Psikolog' ?></h4>
<div class="card p-3" style="max-width:560px">
  <form method="POST" action="<?= isset($psychologist) ? base_url('/admin/psychologists/' . $psychologist['id'] . '/update') : base_url('/admin/psychologists') ?>">
    <?= \Core\Csrf::field() ?>
    <div class="mb-2"><label class="form-label small">Nama</label><input type="text" name="name" class="form-control" value="<?= isset($psychologist) ? e($psychologist['name']) : '' ?>" required></div>
    <div class="mb-2"><label class="form-label small">Email</label><input type="email" name="email" class="form-control" value="<?= isset($psychologist) ? e($psychologist['email']) : '' ?>" required></div>
    <?php if (!isset($psychologist)): ?>
      <div class="mb-2"><label class="form-label small">Password</label><input type="password" name="password" class="form-control" required></div>
    <?php endif; ?>
    <div class="row g-2 mb-2">
      <div class="col-6"><label class="form-label small">No. STR</label><input type="text" name="str_number" class="form-control" value="<?= isset($psychologist) ? e($psychologist['str_number']) : '' ?>"></div>
      <div class="col-6"><label class="form-label small">No. SIPP</label><input type="text" name="sipp_number" class="form-control" value="<?= isset($psychologist) ? e($psychologist['sipp_number']) : '' ?>"></div>
    </div>
    <div class="mb-2"><label class="form-label small">Spesialisasi</label><input type="text" name="specialization" class="form-control" value="<?= isset($psychologist) ? e($psychologist['specialization']) : '' ?>"></div>
    <div class="mb-2"><label class="form-label small">Pengalaman (tahun)</label><input type="number" name="years_experience" class="form-control" value="<?= isset($psychologist) ? e($psychologist['years_experience']) : '' ?>"></div>
    <div class="mb-3"><label class="form-label small">Bio</label><textarea name="bio" class="form-control" rows="2"><?= isset($psychologist) ? e($psychologist['bio']) : '' ?></textarea></div>
    <button class="btn btn-primary" type="submit"><?= isset($psychologist) ? 'Perbarui' : 'Simpan' ?></button>
    <a href="<?= base_url('/admin/psychologists') ?>" class="btn btn-outline-secondary">Batal</a>
  </form>
</div>
