<h4 class="fw-bold mb-3">Permission untuk Role: <?= e($role['label']) ?></h4>
<div class="card p-3" style="max-width:720px">
  <form method="POST" action="<?= base_url('/admin/roles/' . $role['id'] . '/update') ?>">
    <?= \Core\Csrf::field() ?>
    <?php
    $grouped = [];
    foreach ($permissions as $p) { $grouped[$p['module']][] = $p; }
    ?>
    <?php foreach ($grouped as $module => $perms): ?>
      <h6 class="fw-bold text-uppercase small text-muted mt-3"><?= e($module) ?></h6>
      <?php foreach ($perms as $p): ?>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="permissions[]" value="<?= $p['id'] ?>" id="perm<?= $p['id'] ?>"
            <?= in_array($p['id'], $assigned) ? 'checked' : '' ?>>
          <label class="form-check-label small" for="perm<?= $p['id'] ?>"><?= e($p['label']) ?></label>
        </div>
      <?php endforeach; ?>
    <?php endforeach; ?>
    <button class="btn btn-primary mt-3" type="submit">Simpan Permission</button>
    <a href="<?= base_url('/admin/roles') ?>" class="btn btn-outline-secondary mt-3">Batal</a>
  </form>
</div>
