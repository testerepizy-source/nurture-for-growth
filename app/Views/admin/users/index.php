<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h4 class="fw-bold mb-0">Data Pengguna</h4>
    <div class="text-muted small">Filter berdasarkan role pengguna</div>
  </div>
  <!-- <a href="<?= base_url('/admin/users/create') ?>" class="btn btn-primary btn-sm d-none d-md-inline"><i class="fas fa-plus me-1"></i>Tambah User</a> -->
</div>

<div class="card p-3 mb-4">
  <form method="GET" class="row g-2 align-items-end">
    <div class="col-auto">
      <label class="form-label small mb-1">Pilih Role</label>
      <select name="role_id" class="form-select form-select-sm">
        <option value="0" <?= $selectedRole === 0 ? 'selected' : '' ?>>Semua Role</option>
        <?php foreach ($roles as $role): ?>
          <option value="<?= e($role['id']) ?>" <?= $selectedRole === (int)$role['id'] ? 'selected' : '' ?>><?= e($role['label']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-auto">
      <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
    </div>
  </form>
</div>

<div class="card p-3">
  <div class="table-responsive">
    <table class="table table-sm align-middle" id="userTable">
      <thead><tr><th>Nama</th><th>Email</th><th>Role</th><th>Status</th><th>Login Terakhir</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php foreach ($users as $u): ?>
        <tr>
          <td><?= e($u['name']) ?></td>
          <td><?= e($u['email']) ?></td>
          <td><span class="badge bg-primary-soft text-primary"><?= e($u['role_label'] ?? '—') ?></span></td>
          <td><span class="badge <?= ($u['status'] ?? '') === 'active' ? 'bg-success' : 'bg-secondary' ?>"><?= e($u['status'] ?? '') ?></span></td>
          <td class="small text-muted"><?= !empty($u['last_login_at']) ? format_date($u['last_login_at'], 'd M Y H:i') : '-' ?></td>
          <td>
            <a href="<?= base_url('/admin/users/' . $u['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-pen"></i></a>
            <form method="POST" action="<?= base_url('/admin/users/' . $u['id'] . '/delete') ?>" class="d-inline" id="delUser<?= $u['id'] ?>">
              <?= \Core\Csrf::field() ?>
              <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete('delUser<?= $u['id'] ?>')"><i class="fas fa-trash"></i></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>

    <!-- Mobile card list (visible on small screens) -->
    <div class="user-cards d-none">
      <?php foreach ($users as $u): ?>
        <article class="user-card">
          <div class="user-card-main">
            <div class="user-card-info">
              <strong class="user-name"><?= e($u['name']) ?></strong>
              <div class="user-meta small text-muted"><?= e($u['role_label'] ?? '—') ?> &middot; <?= ($u['status'] ?? '') === 'active' ? 'active' : 'inactive' ?></div>
              <div class="user-email small text-muted"><?= e($u['email']) ?></div>
            </div>
            <div class="user-card-actions">
              <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="userAction<?= $u['id'] ?>" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="fas fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userAction<?= $u['id'] ?>">
                  <li><a class="dropdown-item" href="<?= base_url('/admin/users/' . $u['id'] . '/edit') ?>"><i class="fas fa-pen me-2"></i>Ubah</a></li>
                  <li>
                    <form method="POST" action="<?= base_url('/admin/users/' . $u['id'] . '/delete') ?>" id="delUserCard<?= $u['id'] ?>">
                      <?= \Core\Csrf::field() ?>
                      <button type="button" class="dropdown-item text-danger" onclick="confirmDelete('delUserCard<?= $u['id'] ?>')"><i class="fas fa-trash me-2"></i>Hapus</button>
                    </form>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- Floating Add button for mobile -->
<a href="<?= base_url('/admin/users/create') ?>" class="fab-btn d-lg-none" aria-label="Tambah User">
  <i class="fas fa-plus"></i>
</a>
