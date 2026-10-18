<?php $isEdit = $user !== null; ?>
<h4 class="fw-bold mb-3"><?= $isEdit ? 'Edit User' : 'Tambah User' ?></h4>
<div class="card p-3" style="max-width:560px">
  <form method="POST" action="<?= $isEdit ? base_url('/admin/users/' . $user['id'] . '/update') : base_url('/admin/users') ?>">
    <?= \Core\Csrf::field() ?>
    <div class="mb-2">
      <label class="form-label small">Nama</label>
      <input type="text" name="name" class="form-control" required value="<?= e($user['name'] ?? '') ?>">
    </div>
    <div class="mb-2">
      <label class="form-label small">Email</label>
      <input type="email" name="email" class="form-control" required value="<?= e($user['email'] ?? '') ?>">
    </div>
    <div class="mb-2">
      <label class="form-label small">Password <?= $isEdit ? '(kosongkan jika tidak diubah)' : '' ?></label>
      <input type="password" name="password" class="form-control" <?= $isEdit ? '' : 'required' ?>>
    </div>
    <div class="mb-2">
      <label class="form-label small">Role</label>
      <select name="role_id" class="form-select" required>
        <?php foreach ($roles as $r): ?>
          <option value="<?= $r['id'] ?>" <?= ($user['role_id'] ?? null) == $r['id'] ? 'selected' : '' ?>><?= e($r['label']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="mb-3">
      <label class="form-label small">Status</label>
      <select name="status" class="form-select">
        <option value="active" <?= ($user['status'] ?? '') === 'active' ? 'selected' : '' ?>>Aktif</option>
        <option value="inactive" <?= ($user['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Nonaktif</option>
        <option value="banned" <?= ($user['status'] ?? '') === 'banned' ? 'selected' : '' ?>>Banned</option>
      </select>
    </div>
    <button class="btn btn-primary" type="submit">Simpan</button>
    <a href="<?= base_url('/admin/users') ?>" class="btn btn-outline-secondary">Batal</a>
  </form>
</div>
