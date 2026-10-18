<?php
if (!function_exists('promo_month_label')) {
    function promo_month_label(string $date): string
    {
        $bulan = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'];
        $ts = strtotime($date);
        return $bulan[(int) date('n', $ts)] . ' ' . date('Y', $ts);
    }
}
$editing = isset($promo);
$formAction = $editing
    ? ($isAdmin ? base_url('/admin/promos/' . $promo['id'] . '/update') : base_url('/promo/' . $promo['id'] . '/update'))
    : ($isAdmin ? base_url('/admin/promos') : base_url('/promo'));
?>
<h4 class="fw-bold mb-1">Promo Layanan Konseling</h4>
<p class="text-muted small">Atur diskon potongan untuk semua layanan konseling selama periode bulan tertentu. Diskon otomatis diterapkan pada booking dengan jadwal sesi di dalam periode promo.</p>
<div class="row g-3">
  <div class="col-md-5">
    <div class="card p-3">
      <h6 class="fw-bold mb-2"><?= $editing ? 'Edit Promo' : 'Tambah Promo' ?></h6>
      <form method="POST" action="<?= $formAction ?>">
        <?= \Core\Csrf::field() ?>
        <div class="mb-2">
          <label class="form-label small">Nama Promo</label>
          <input type="text" name="name" class="form-control form-control-sm" value="<?= e($promo['name'] ?? '') ?>" placeholder="cth: Promo Awal Tahun" required>
        </div>
        <div class="mb-2">
          <label class="form-label small">Deskripsi (opsional)</label>
          <input type="text" name="description" class="form-control form-control-sm" value="<?= e($promo['description'] ?? '') ?>" maxlength="255">
        </div>
        <?php if ($isAdmin): ?>
        <div class="mb-2">
          <label class="form-label small">Cakupan</label>
          <select name="scope" id="promoScope" class="form-select form-select-sm" onchange="document.getElementById('promoProviderGroup').style.display = this.value === 'provider' ? 'block' : 'none'">
            <option value="all" <?= ($editing && $promo['scope'] === 'all') ? 'selected' : '' ?>>Semua tenaga profesional</option>
            <option value="provider" <?= ($editing && $promo['scope'] === 'provider') ? 'selected' : '' ?>>Tenaga profesional tertentu</option>
          </select>
        </div>
        <div class="mb-2" id="promoProviderGroup" style="display: <?= ($editing && $promo['scope'] === 'provider') ? 'block' : 'none' ?>">
          <label class="form-label small">Tenaga Profesional</label>
          <select name="provider_id" class="form-select form-select-sm">
            <option value="">-- Pilih --</option>
            <?php foreach ($providers as $pr): ?>
              <option value="<?= (int) $pr['id'] ?>" <?= ($editing && (int) $promo['provider_id'] === (int) $pr['id']) ? 'selected' : '' ?>>
                <?= e($pr['name']) ?> (Psikolog)
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <?php endif; ?>
        <div class="row g-2 mb-2">
          <div class="col-7">
            <label class="form-label small">Jenis Diskon</label>
            <select name="discount_type" id="promoDiscountType" class="form-select form-select-sm">
              <option value="percent" <?= (($promo['discount_type'] ?? '') === 'fixed') ? '' : 'selected' ?>>Persen (%)</option>
              <option value="fixed" <?= (($promo['discount_type'] ?? '') === 'fixed') ? 'selected' : '' ?>>Nominal (Rp)</option>
            </select>
          </div>
          <div class="col-5">
            <label class="form-label small">Nilai</label>
            <input type="number" name="discount_value" min="1" max="100000000" class="form-control form-control-sm" value="<?= e($promo['discount_value'] ?? '') ?>" required>
          </div>
        </div>
        <div class="row g-2 mb-3">
          <div class="col-6">
            <label class="form-label small">Bulan Mulai</label>
            <input type="month" name="start_month" class="form-control form-control-sm" value="<?= isset($promo['start_date']) ? substr($promo['start_date'], 0, 7) : date('Y-m') ?>" required>
          </div>
          <div class="col-6">
            <label class="form-label small">Bulan Selesai</label>
            <input type="month" name="end_month" class="form-control form-control-sm" value="<?= isset($promo['end_date']) ? substr($promo['end_date'], 0, 7) : date('Y-m') ?>" required>
          </div>
        </div>
        <button class="btn btn-primary btn-sm" type="submit"><?= $editing ? 'Perbarui' : 'Simpan' ?></button>
        <?php if ($editing): ?>
          <a href="<?= $isAdmin ? base_url('/admin/promos') : base_url('/promo') ?>" class="btn btn-outline-secondary btn-sm ms-2">Batal</a>
        <?php endif; ?>
      </form>
      <p class="text-muted small mt-3 mb-0"><i class="fas fa-circle-info me-1"></i>Promo berlaku untuk semua jenis layanan konseling (individu, pasangan, keluarga; online &amp; tatap muka) yang jadwalnya jatuh dalam periode promo.</p>
    </div>
  </div>

  <div class="col-md-7">
    <div class="card p-3">
      <table class="table table-sm align-middle">
        <thead><tr><th>Promo</th><th>Cakupan</th><th>Diskon</th><th>Periode</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php if (empty($promos)): ?>
          <tr><td colspan="6" class="text-center text-muted small py-4">Belum ada promo.</td></tr>
        <?php endif; ?>
        <?php foreach ($promos as $sp):
          $isOwner = $isAdmin || ((int) $sp['provider_id'] === (int) \Core\Auth::id());
          $isActiveNow = (int) $sp['is_active'] === 1 && date('Y-m-d') >= $sp['start_date'] && date('Y-m-d') <= $sp['end_date'];
        ?>
          <tr>
            <td class="small">
              <span class="fw-semibold"><?= e($sp['name']) ?></span><br>
              <?php if (!empty($sp['description'])): ?><span class="text-muted"><?= e($sp['description']) ?></span><br><?php endif; ?>
              <?php if ($isActiveNow): ?><span class="badge bg-success-subtle text-success-emphasis mt-1">Sedang berjalan</span><?php endif; ?>
            </td>
            <td class="small">
              <?= $sp['scope'] === 'all'
                  ? '<span class="badge bg-primary-soft text-primary">Semua Psikolog</span>'
                  : '<span class="badge bg-secondary-subtle text-secondary-emphasis">' . e($sp['provider_name'] ?: 'Psikolog #' . $sp['provider_id']) . '</span>' ?>
            </td>
            <td class="small fw-semibold"><?= $sp['discount_type'] === 'percent' ? (int) $sp['discount_value'] . '%' : 'Rp' . number_format((int) $sp['discount_value'], 0, ',', '.') ?></td>
            <td class="small"><?= promo_month_label($sp['start_date']) ?> &ndash; <?= promo_month_label($sp['end_date']) ?></td>
            <td class="small">
              <?= (int) $sp['is_active'] === 1 ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-danger">Nonaktif</span>' ?>
            </td>
            <td class="text-end text-nowrap">
              <?php if ($isOwner): ?>
                <a href="<?= ($isAdmin ? base_url('/admin/promos') : base_url('/promo')) . '?edit=' . (int) $sp['id'] ?>" class="btn btn-sm btn-outline-secondary me-1" title="Edit"><i class="fas fa-edit"></i></a>
                <a href="<?= base_url(($isAdmin ? '/admin/promos/' : '/promo/') . $sp['id'] . '/toggle') ?>" class="btn btn-sm btn-outline-warning me-1" title="<?= (int) $sp['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?>"><i class="fas fa-power-off"></i></a>
              <?php endif; ?>
              <?php if ($isOwner): ?>
                <a href="<?= base_url(($isAdmin ? '/admin/promos/' : '/promo/') . $sp['id'] . '/delete') ?>" class="btn btn-sm btn-outline-danger" title="Hapus" onclick="return confirm('Hapus promo ini?')"><i class="fas fa-trash"></i></a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
