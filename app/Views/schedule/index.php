<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <h4 class="fw-bold mb-0">Jadwal Praktik</h4>
  <span class="hours-card-count"><?= count($schedules) ?> slot tersimpan</span>
</div>
<?php
$days = [0 => 'Minggu', 1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'];
$modes = [
  'online'  => ['label' => 'Online', 'icon' => 'fa-video', 'class' => 'hours-mode-online'],
  'offline' => ['label' => 'Tatap Muka', 'icon' => 'fa-location-dot', 'class' => 'hours-mode-offline'],
  'both'    => ['label' => 'Online & Tatap Muka', 'icon' => 'fa-globe', 'class' => 'hours-mode-both'],
];
$grouped = [];
foreach ((array) $schedules as $sch) {
  $grouped[(int) $sch['day_of_week']][] = $sch;
}
ksort($grouped);
?>
<div class="row g-3">
  <div class="col-md-5">
    <div class="card p-3">
      <h6 class="fw-bold mb-2">Tambah Slot Jadwal</h6>
      <p class="text-muted small mb-3">Slot dipakai otomatis saat client memilih jadwal pada halaman booking.</p>
      <form method="POST" action="<?= base_url('/schedule') ?>">
        <?= \Core\Csrf::field() ?>
        <div class="mb-2">
          <label class="form-label small">Hari</label>
          <select name="day_of_week" class="form-select form-select-sm" required>
            <?php foreach ($days as $val => $label): ?>
              <option value="<?= $val ?>"><?= $label ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="row g-2 mb-2">
          <div class="col-6">
            <label class="form-label small">Jam Mulai</label>
            <input type="time" name="start_time" class="form-control form-control-sm" required>
          </div>
          <div class="col-6">
            <label class="form-label small">Jam Selesai</label>
            <input type="time" name="end_time" class="form-control form-control-sm" required>
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label small">Durasi per Slot (menit)</label>
          <input type="number" name="slot_duration_minutes" class="form-control form-control-sm" value="60" min="15" step="5">
        </div>
        <div class="mb-3">
          <label class="form-label small">Mode Layanan</label>
          <select name="mode" class="form-select form-select-sm" required>
            <option value="online">Online (Video/Voice Call)</option>
            <option value="offline">Tatap Muka</option>
            <option value="both">Online &amp; Tatap Muka</option>
          </select>
        </div>
        <button class="btn btn-primary btn-sm" type="submit">Simpan Jadwal</button>
      </form>
    </div>
  </div>
  <div class="col-md-7">
    <div class="card p-3">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
        <h6 class="fw-bold mb-0">Jadwal Aktif</h6>
        <span class="hours-legend">
          <span class="hours-mode hours-mode-online"><i class="fas fa-video"></i> Online</span>
          <span class="hours-mode hours-mode-offline"><i class="fas fa-location-dot"></i> Tatap Muka</span>
        </span>
      </div>
      <?php if (empty($grouped)): ?>
        <div class="hours-card">
          <p class="hours-empty"><i class="fas fa-calendar-xmark"></i> Belum ada jadwal. Tambahkan slot lewat formulir di samping.</p>
        </div>
      <?php else: ?>
        <div class="hours-card">
          <ul class="hours-list">
            <?php foreach ($grouped as $dayIndex => $dayRows): ?>
            <li class="hours-row">
              <span class="hours-day"><?= $days[$dayIndex] ?? 'Hari ' . $dayIndex ?></span>
              <span class="hours-slots">
                <?php foreach ($dayRows as $row):
                  $modeKey = $row['mode'] ?? 'both';
                  $mode = $modes[$modeKey] ?? ['label' => ucfirst((string) $modeKey), 'icon' => 'fa-clock', 'class' => 'hours-mode-both'];
                  $isActive = !empty($row['is_active']);
                ?>
                <span class="hours-slot<?= $isActive ? '' : ' hours-slot--off' ?>">
                  <span class="hours-time"><?= substr((string) $row['start_time'], 0, 5) ?><span class="hours-dash">&ndash;</span><?= substr((string) $row['end_time'], 0, 5) ?></span>
                  <span class="hours-mode <?= $mode['class'] ?>"><i class="fas <?= $mode['icon'] ?>"></i> <?= $mode['label'] ?></span>
                  <span class="hours-slot-meta">slot &plusmn;<?= (int) ($row['slot_duration_minutes'] ?? 60) ?> mnt</span>
                  <span class="hours-slot-state">
                    <span class="badge <?= $isActive ? 'bg-success' : 'bg-secondary' ?>"><?= $isActive ? 'Aktif' : 'Nonaktif' ?></span>
                  </span>
                  <span class="hours-slot-actions">
                    <a href="<?= base_url('/schedule/' . $row['id'] . '/toggle') ?>"
                      class="btn btn-sm btn-outline-secondary"><?= $isActive ? 'Nonaktifkan' : 'Aktifkan' ?></a>
                    <a href="<?= base_url('/schedule/' . $row['id'] . '/delete') ?>" class="btn btn-sm btn-outline-danger"
                      onclick="return confirm('Hapus jadwal ini?')">Hapus</a>
                  </span>
                </span>
                <?php endforeach; ?>
              </span>
            </li>
            <?php endforeach; ?>
          </ul>
          <p class="hours-note">
            <i class="fas fa-circle-info"></i>
            Slot berstatus Aktif otomatis bisa dipilih client saat booking. Slot Nonaktif disembunyikan dari pilihan.
          </p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>