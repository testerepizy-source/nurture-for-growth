<h4 class="fw-bold mb-3">Laporan</h4>
<p class="text-muted small mb-4">Ekspor laporan dalam format PDF, Excel (.xlsx), atau CSV. Psikolog hanya melihat data client yang ditugaskan kepada mereka.</p>

<div class="d-flex align-items-center mb-2">
  <h5 class="fw-bold mb-0"><i class="fas fa-money-bill-trend-up text-success me-2"></i>Pendapatan</h5>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-6 col-xl-3">
    <div class="card p-3 h-100">
      <div class="small text-muted">Total Pendapatan</div>
      <div class="h4 fw-bold text-success mb-0">Rp <?= number_format($rev['revenue'], 0, ',', '.') ?></div>
      <div class="small text-muted"><?= $rev['sessions'] ?> sesi terbayar</div>
    </div>
  </div>
  <div class="col-md-6 col-xl-3">
    <div class="card p-3 h-100">
      <div class="small text-muted">Pendapatan Hari Ini</div>
      <div class="h4 fw-bold text-primary mb-0">Rp <?= number_format($rev['today'], 0, ',', '.') ?></div>
      <div class="small text-muted"><?= date('d/m/Y') ?></div>
    </div>
  </div>
  <div class="col-md-6 col-xl-3">
    <div class="card p-3 h-100">
      <div class="small text-muted">Rata-rata per Sesi</div>
      <div class="h4 fw-bold mb-0">Rp <?= number_format($rev['avg'], 0, ',', '.') ?></div>
      <div class="small text-muted">Setelah diskon promo</div>
    </div>
  </div>
  <div class="col-md-6 col-xl-3">
    <div class="card p-3 h-100">
      <div class="small text-muted">Total Diskon Promo</div>
      <div class="h4 fw-bold text-danger mb-0">Rp <?= number_format($rev['discount'], 0, ',', '.') ?></div>
      <div class="small text-muted">Seluruh periode</div>
    </div>
  </div>
</div>

<?php
$recaps = [
  'daily'   => ['judul' => 'Harian',  'icon' => 'fa-calendar-day', 'data' => $rev['daily'],  'label' => 'Tanggal'],
  'monthly' => ['judul' => 'Bulanan', 'icon' => 'fa-calendar',     'data' => $rev['monthly'], 'label' => 'Bulan'],
  'yearly'  => ['judul' => 'Tahunan', 'icon' => 'fa-calendar-days','data' => $rev['yearly'],  'label' => 'Tahun'],
];
$btns = function (string $type): void {
  echo '<div class="btn-group btn-group-sm" role="group">';
  echo '<a class="btn btn-outline-success" href="' . base_url("/report/export/{$type}/xlsx") . '" title="Excel"><i class="fas fa-file-excel"></i> Excel</a>';
  echo '<a class="btn btn-outline-danger" href="' . base_url("/report/export/{$type}/pdf") . '" title="PDF"><i class="fas fa-file-pdf"></i> PDF</a>';
  echo '<a class="btn btn-outline-secondary" href="' . base_url("/report/export/{$type}/csv") . '" title="CSV"><i class="fas fa-file-csv"></i> CSV</a>';
  echo '</div>';
};
?>
<div class="row g-3 mb-4">
  <?php foreach ($recaps as $key => $rc): ?>
  <div class="col-lg-4">
    <div class="card h-100">
      <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
        <span class="fw-semibold"><i class="fas <?= $rc['icon'] ?> text-success me-1"></i>Rekap <?= $rc['judul'] ?></span>
        <?php $btns("revenue_{$key}") ?>
      </div>
      <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
          <thead><tr><th><?= $rc['label'] ?></th><th class="text-center">Sesi</th><th class="text-end">Pendapatan</th></tr></thead>
          <tbody>
            <?php if (!$rc['data']): ?>
              <tr><td colspan="3" class="text-center text-muted small py-3">Belum ada transaksi terbayar.</td></tr>
            <?php else: foreach ($rc['data'] as $r): ?>
              <tr>
                <td class="small"><?= e((string) $r['periode']) ?></td>
                <td class="small text-center"><?= (int) $r['sessions'] ?></td>
                <td class="small text-end fw-semibold">Rp <?= number_format((float) $r['revenue'], 0, ',', '.') ?></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<hr class="my-4">

<h5 class="fw-bold mb-3"><i class="fas fa-file-lines text-primary me-2"></i>Laporan Data</h5>
<div class="row g-3">
  <?php
  $reports = [
    ['type' => 'assessment',   'icon' => 'fa-clipboard-list text-primary', 'judul' => 'Laporan Assessment', 'desc' => 'Riwayat hasil assessment seluruh client.'],
    ['type' => 'consultation', 'icon' => 'fa-calendar-check text-success', 'judul' => 'Laporan Konsultasi', 'desc' => 'Riwayat booking & sesi konseling.'],
    ['type' => 'mood',         'icon' => 'fa-face-smile text-warning',     'judul' => 'Laporan Mood',       'desc' => 'Riwayat input mood harian client.'],
    ['type' => 'revenue',      'icon' => 'fa-receipt text-info',           'judul' => 'Detail Pendapatan',  'desc' => 'Transaksi sesi terbayar lengkap dengan harga & diskon.'],
  ];
  foreach ($reports as $rp): ?>
  <div class="col-md-4 col-xl-3">
    <div class="card p-3 h-100 d-flex flex-column">
      <i class="fas <?= $rp['icon'] ?> fa-2x mb-2"></i>
      <h6 class="fw-bold mb-1"><?= $rp['judul'] ?></h6>
      <p class="small text-muted flex-grow-1"><?= $rp['desc'] ?></p>
      <?php $btns($rp['type']) ?>
    </div>
  </div>
  <?php endforeach; ?>
</div>
