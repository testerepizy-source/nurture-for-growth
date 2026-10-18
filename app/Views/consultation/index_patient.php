<?php
use App\Models\ConsultationReschedule;

$ckMonths = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
$ckDays = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
$ckMethodMeta = [
    'chat'       => ['label' => 'Chat', 'icon' => 'fa-comment-dots'],
    'voice'      => ['label' => 'Voice Call', 'icon' => 'fa-phone'],
    'video'      => ['label' => 'Video Call', 'icon' => 'fa-video'],
    'tatap_muka' => ['label' => 'Tatap Muka', 'icon' => 'fa-location-dot'],
];
$ckSessionType = ['individual' => 'Individu', 'couple' => 'Pasangan', 'family' => 'Keluarga'];
$ckPaymentMeta = [
    'unpaid'                => ['label' => 'Belum Bayar', 'class' => 'warn'],
    'pending_verification'  => ['label' => 'Verifikasi Pembayaran', 'class' => 'info'],
    'paid'                  => ['label' => 'Lunas', 'class' => 'ok'],
];
$ckInitials = function (string $name): string {
    $parts = preg_split('/\s+/', trim($name)) ?: [];
    $ini = strtoupper(substr($parts[0] ?? 'K', 0, 1) . substr($parts[1] ?? '', 0, 1));
    return $ini !== '' ? $ini : 'K';
};
$ckStats = [
    ['key' => 'all',       'label' => 'Total Sesi',   'icon' => 'fa-layer-group',        'count' => $statusCounts['all'] ?? 0],
    ['key' => 'pending',   'label' => 'Menunggu',     'icon' => 'fa-hourglass-half',     'count' => ($statusCounts['pending'] ?? 0)],
    ['key' => 'approved',  'label' => 'Terjadwal',    'icon' => 'fa-calendar-check',     'count' => $statusCounts['approved'] ?? 0],
    ['key' => 'completed', 'label' => 'Selesai',      'icon' => 'fa-circle-check',       'count' => $statusCounts['completed'] ?? 0],
];
?>

<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
  <div>
    <h4 class="fw-bold mb-1">Riwayat Konseling Saya</h4>
    <p class="text-muted mb-0">Pantau jadwal, status, dan perkembangan booking konseling Anda dengan lebih rapi.</p>
  </div>
  <a href="<?= base_url('/consultation/create') ?>" class="btn btn-primary btn-sm rounded-pill px-3"><i class="fas fa-plus me-1"></i>Booking Baru</a>
</div>

<!-- Ringkasan -->
<div class="row g-3 mb-4">
  <?php foreach ($ckStats as $s): ?>
    <div class="col-6 col-lg-3">
      <a href="<?= base_url($s['key'] === 'all' ? '/consultation' : '/consultation?status=' . $s['key']) ?>"
         class="ck-stat text-decoration-none <?= ($s['key'] === 'all' && $status === '') || $status === $s['key'] ? 'is-active' : '' ?>">
        <span class="ck-stat-icon"><i class="fas <?= $s['icon'] ?>"></i></span>
        <span class="ck-stat-num"><?= (int) $s['count'] ?></span>
        <span class="ck-stat-label"><?= e($s['label']) ?></span>
      </a>
    </div>
  <?php endforeach; ?>
</div>

<div class="card border-0 shadow-sm rounded-4 p-3 p-lg-4 mb-4">
  <!-- Filter status -->
  <div class="ck-filters" role="tablist" aria-label="Filter status konseling">
    <?php
      $ckFilters = [
          ''                   => 'Semua',
          'pending'            => 'Menunggu',
          'approved'           => 'Disetujui',
          'completed'          => 'Selesai',
          'reschedule_pending' => 'Reschedule',
      ];
      foreach ($ckFilters as $key => $label):
        $active = $status === $key;
        $count = (int) ($statusCounts[$key === '' ? 'all' : $key] ?? 0);
    ?>
      <a href="<?= base_url($key === '' ? '/consultation' : '/consultation?status=' . $key) ?>"
         class="ck-chip <?= $active ? 'is-active' : '' ?>" aria-current="<?= $active ? 'true' : 'false' ?>">
        <?= e($label) ?><span class="ck-chip-count"><?= $count ?></span>
      </a>
    <?php endforeach; ?>
  </div>

  <?php if (empty($list)): ?>
    <div class="ck-empty text-center py-5 px-3">
      <div class="ck-empty-art mb-3">
        <i class="fas fa-calendar-check"></i>
        <span class="ck-empty-dot d1"></span><span class="ck-empty-dot d2"></span><span class="ck-empty-dot d3"></span>
      </div>
      <h6 class="fw-bold mb-2">
        <?= $status !== '' ? 'Tidak ada sesi pada filter ini' : 'Belum ada riwayat konseling' ?>
      </h6>
      <p class="text-muted mb-4 mx-auto" style="max-width:420px">
        <?= $status !== ''
            ? 'Coba pilih filter lain untuk melihat jadwal konseling Anda.'
            : 'Mulai perjalanan kesehatan mental Anda — booking konseling pertama bersama psikolog berlisensi.' ?>
      </p>
      <a href="<?= base_url('/consultation/create') ?>" class="btn btn-primary rounded-pill px-4"><i class="fas fa-plus me-2"></i>Booking Sekarang</a>
    </div>
  <?php else: ?>
    <div class="ck-list">
      <?php foreach ($list as $c): ?>
        <?php
          $pendingReschedule = ConsultationReschedule::getPending($c['id']);
          $displayScheduleAt = ConsultationReschedule::getEffectiveScheduleAt($c['id'], $c['schedule_at']);
          $ts = strtotime((string) $displayScheduleAt);
          $statusValue = strtolower((string) ($c['status'] ?? ''));

          $statusLabel = 'Lainnya';
          $statusClass = 'neutral';
          $statusIcon = 'fa-circle-info';
          if ($pendingReschedule) {
              $statusLabel = 'Menunggu Reschedule';
              $statusClass = 'warn';
              $statusIcon = 'fa-clock';
          } elseif ($statusValue === 'pending') {
              $statusLabel = 'Menunggu Persetujuan';
              $statusClass = 'info';
              $statusIcon = 'fa-hourglass-half';
          } elseif ($statusValue === 'approved') {
              $statusLabel = 'Disetujui';
              $statusClass = 'ok';
              $statusIcon = 'fa-circle-check';
          } elseif ($statusValue === 'completed') {
              $statusLabel = 'Selesai';
              $statusClass = 'done';
              $statusIcon = 'fa-check-double';
          } elseif ($statusValue === 'cancelled') {
              $statusLabel = 'Dibatalkan';
              $statusClass = 'danger';
              $statusIcon = 'fa-ban';
          } elseif ($statusValue === 'rejected') {
              $statusLabel = 'Ditolak';
              $statusClass = 'danger';
              $statusIcon = 'fa-xmark-circle';
          }

          $method = $ckMethodMeta[$c['method']] ?? ['label' => ucfirst((string) $c['method']), 'icon' => 'fa-comment-medical'];
          $payment = $ckPaymentMeta[$c['payment_status'] ?? 'unpaid'] ?? $ckPaymentMeta['unpaid'];
          $price = (int) ($c['price_amount'] ?? 0);
          $discount = (int) ($c['discount_amount'] ?? 0);
          $originalPrice = (int) ($c['original_price_amount'] ?? 0);
          $isUpcoming = $ts >= time() && in_array($statusValue, ['pending', 'approved'], true);
        ?>
        <article class="ck-card <?= $isUpcoming ? 'is-upcoming' : '' ?> status-<?= e($statusClass) ?>">
          <div class="ck-card-accent"></div>
          <div class="ck-datebox">
            <span class="ck-date-day"><?= $ckDays[(int) date('w', $ts)] ?></span>
            <span class="ck-date-num"><?= date('j', $ts) ?></span>
            <span class="ck-date-mon"><?= $ckMonths[(int) date('n', $ts)] ?></span>
          </div>

          <div class="ck-body">
            <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
              <div class="min-w-0">
                <h5 class="ck-provider mb-1">
                  <span class="ck-avatar"><?= e($ckInitials((string) $c['provider_name'])) ?></span>
                  <span class="text-truncate"><?= e($c['provider_name']) ?></span>
                </h5>
                <div class="ck-meta">
                  <span class="ck-tag"><i class="fas <?= $method['icon'] ?>"></i><?= e($method['label']) ?></span>
                  <span class="ck-tag"><i class="fas fa-clock"></i><?= (int) ($c['duration_minutes'] ?? 30) ?> menit</span>
                  <span class="ck-tag"><i class="fas fa-user-group"></i><?= e($ckSessionType[$c['session_type'] ?? 'individual'] ?? 'Individu') ?></span>
                  <span class="ck-tag ck-time"><i class="fas fa-clock-rotate-left"></i><?= date('H:i', $ts) ?> WIB</span>
                </div>
              </div>
              <span class="ck-badge ck-<?= e($statusClass) ?>"><i class="fas <?= $statusIcon ?>"></i><?= e($statusLabel) ?></span>
            </div>

            <div class="ck-foot">
              <div class="ck-price">
                <span class="ck-price-value">Rp<?= number_format($price, 0, ',', '.') ?></span>
                <?php if ($discount > 0 && $originalPrice > 0): ?>
                  <span class="ck-price-old">Rp<?= number_format($originalPrice, 0, ',', '.') ?></span>
                  <span class="ck-promo-chip"><i class="fas fa-tag"></i>Hemat Rp<?= number_format($discount, 0, ',', '.') ?></span>
                <?php endif; ?>
              </div>
              <div class="ck-actions">
                <span class="ck-pay ck-pay-<?= e($payment['class']) ?>"><span class="dot"></span><?= e($payment['label']) ?></span>
                <?php if (in_array($statusValue, ['pending', 'approved'], true)): ?>
                  <form method="POST" action="<?= base_url('/consultation/' . $c['id'] . '/cancel') ?>" class="d-inline"
                        onsubmit="return confirm('Batalkan booking ini?')">
                    <?= \Core\Csrf::field() ?>
                    <button class="btn btn-sm btn-outline-danger rounded-pill"><i class="fas fa-xmark me-1"></i>Batalkan</button>
                  </form>
                <?php endif; ?>
                <a href="<?= base_url('/consultation/' . $c['id']) ?>" class="btn btn-sm btn-primary rounded-pill px-3">
                  Detail<i class="fas fa-arrow-right ms-1"></i>
                </a>
              </div>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<style>
  .ck-stat{
    display:flex; flex-direction:column; gap:2px; height:100%;
    padding:14px 16px; border-radius:18px;
    background:#fff; border:1px solid var(--nfg-border);
    transition:transform .2s ease, box-shadow .2s ease, border-color .2s ease;
  }
  .ck-stat:hover{ transform:translateY(-3px); box-shadow:0 12px 28px rgba(12,74,110,.10); }
  .ck-stat.is-active{ border-color:var(--nfg-teal-500); box-shadow:0 8px 22px rgba(0,199,156,.16); background:linear-gradient(160deg,#fff,var(--nfg-teal-100)); }
  .ck-stat-icon{ width:34px; height:34px; border-radius:12px; display:grid; place-items:center;
    background:var(--nfg-teal-100); color:var(--nfg-teal-700); font-size:.85rem; margin-bottom:8px; }
  .ck-stat-num{ font-size:1.45rem; font-weight:800; color:var(--ink); line-height:1.1; }
  .ck-stat-label{ font-size:.76rem; font-weight:600; color:#6b7f78; }

  .ck-filters{ display:flex; gap:8px; overflow-x:auto; padding-bottom:6px; margin-bottom:18px; scrollbar-width:none; }
  .ck-filters::-webkit-scrollbar{ display:none; }
  .ck-chip{ display:inline-flex; align-items:center; gap:8px; white-space:nowrap;
    padding:7px 14px; border-radius:999px; font-size:.82rem; font-weight:700;
    color:#4b6058; background:#fff; border:1.5px solid var(--nfg-border); text-decoration:none;
    transition:all .18s ease; }
  .ck-chip:hover{ border-color:var(--nfg-teal-500); color:var(--nfg-teal-700); }
  .ck-chip-count{ min-width:22px; height:20px; padding:0 6px; border-radius:999px; display:inline-grid; place-items:center;
    font-size:.68rem; font-weight:800; background:var(--nfg-teal-100); color:var(--nfg-teal-700); }
  .ck-chip.is-active{ background:var(--ink); border-color:var(--ink); color:#fff; }
  .ck-chip.is-active .ck-chip-count{ background:rgba(255,255,255,.18); color:#fff; }

  .ck-list{ display:grid; gap:14px; }
  .ck-card{ position:relative; display:flex; gap:16px; padding:18px 18px 16px 20px;
    background:linear-gradient(150deg,#fff 55%,#f6fbfa); border:1px solid var(--nfg-border);
    border-radius:20px; box-shadow:0 6px 18px rgba(12,74,110,.05); overflow:hidden;
    transition:transform .22s ease, box-shadow .22s ease; }
  .ck-card:hover{ transform:translateY(-3px); box-shadow:0 16px 36px rgba(12,74,110,.11); }
  .ck-card-accent{ position:absolute; left:0; top:0; bottom:0; width:5px; background:var(--nfg-teal-500); opacity:.85; }
  .ck-card.status-info .ck-card-accent{ background:#3ab0ff; }
  .ck-card.status-warn .ck-card-accent{ background:#f6a723; }
  .ck-card.status-danger .ck-card-accent{ background:#ee5a5a; }
  .ck-card.status-done .ck-card-accent{ background:#8aa8a0; }
  .ck-card.is-upcoming{ border-color:rgba(0,199,156,.45); box-shadow:0 10px 26px rgba(0,199,156,.13); }

  .ck-datebox{ flex:0 0 66px; align-self:flex-start; display:flex; flex-direction:column; align-items:center; gap:1px;
    padding:10px 6px 9px; border-radius:16px; background:var(--nfg-teal-100); color:var(--nfg-teal-700); }
  .ck-date-day{ font-size:.62rem; font-weight:800; letter-spacing:.12em; text-transform:uppercase; opacity:.75; }
  .ck-date-num{ font-size:1.5rem; font-weight:800; line-height:1; }
  .ck-date-mon{ font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.08em; opacity:.85; }

  .ck-body{ flex:1 1 auto; min-width:0; display:flex; flex-direction:column; gap:12px; }
  .ck-provider{ display:flex; align-items:center; gap:10px; font-size:1.02rem; font-weight:800; color:var(--ink); margin:0; }
  .ck-avatar{ flex:0 0 38px; width:38px; height:38px; border-radius:50%; display:grid; place-items:center;
    font-size:.78rem; font-weight:800; color:var(--nfg-teal-700); background:linear-gradient(135deg,var(--nfg-teal-100),#d9f2ec); }
  .ck-meta{ display:flex; flex-wrap:wrap; gap:6px; margin-top:2px; }
  .ck-tag{ display:inline-flex; align-items:center; gap:5px; font-size:.72rem; font-weight:600; color:#54685f;
    background:#f3f8f6; border:1px solid #e5efeb; border-radius:999px; padding:4px 10px; }
  .ck-tag i{ font-size:.68rem; color:var(--nfg-teal-500); }
  .ck-time{ background:#fff8ef; border-color:#f5e5c8; }
  .ck-time i{ color:#e19a2f; }

  .ck-badge{ display:inline-flex; align-items:center; gap:6px; white-space:nowrap;
    font-size:.72rem; font-weight:800; padding:6px 12px; border-radius:999px; }
  .ck-badge i{ font-size:.72rem; }
  .ck-ok{ background:#e3f7ef; color:#0d7d54; }
  .ck-done{ background:#eef1f0; color:#5c6f68; }
  .ck-info{ background:#e7f3ff; color:#1272c4; }
  .ck-warn{ background:#fdf3df; color:#a06b00; }
  .ck-danger{ background:#fdeaea; color:#c0392b; }
  .ck-neutral{ background:#eef1f0; color:#5c6f68; }

  .ck-foot{ display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap;
    border-top:1px dashed var(--nfg-border); padding-top:12px; margin-top:auto; }
  .ck-price{ display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
  .ck-price-value{ font-weight:800; color:var(--ink); }
  .ck-price-old{ font-size:.78rem; color:#9aaba3; text-decoration:line-through; }
  .ck-promo-chip{ display:inline-flex; align-items:center; gap:4px; font-size:.68rem; font-weight:800;
    color:#b23a67; background:#fdeaf1; border-radius:999px; padding:3px 9px; }
  .ck-actions{ display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
  .ck-pay{ display:inline-flex; align-items:center; gap:6px; font-size:.72rem; font-weight:700; }
  .ck-pay .dot{ width:8px; height:8px; border-radius:50%; }
  .ck-pay-ok{ color:#0d7d54; } .ck-pay-ok .dot{ background:#17b26a; }
  .ck-pay-info{ color:#1272c4; } .ck-pay-info .dot{ background:#3ab0ff; }
  .ck-pay-warn{ color:#a06b00; } .ck-pay-warn .dot{ background:#f6a723; }

  .ck-empty{ border:1.5px dashed #d5e5df; border-radius:20px; background:
    radial-gradient(400px 180px at 50% 0%, rgba(0,199,156,.07), transparent 70%), #fcfefd; }
  .ck-empty-art{ position:relative; width:84px; height:84px; margin:0 auto; border-radius:50%;
    display:grid; place-items:center; font-size:2rem; color:var(--nfg-teal-500);
    background:var(--nfg-teal-100); }
  .ck-empty-dot{ position:absolute; border-radius:50%; background:rgba(0,199,156,.25); }
  .ck-empty-dot.d1{ width:10px; height:10px; top:-4px; right:14px; }
  .ck-empty-dot.d2{ width:6px; height:6px; bottom:6px; left:-2px; }
  .ck-empty-dot.d3{ width:8px; height:8px; top:12px; left:-10px; }

  @media (max-width:575.98px){
    .ck-card{ flex-direction:column; gap:12px; padding:16px 14px 14px 18px; }
    .ck-datebox{ flex-direction:row; gap:8px; padding:8px 12px; align-self:stretch; justify-content:center; }
    .ck-date-num{ font-size:1.15rem; }
    .ck-foot{ flex-direction:column; align-items:stretch; }
    .ck-price{ justify-content:space-between; }
    .ck-actions{ justify-content:stretch; }
    .ck-actions .btn{ flex:1 1 auto; justify-content:center; }
    .ck-actions form{ flex:1 1 auto; display:flex; }
    .ck-actions form button{ width:100%; }
    .ck-stat{ flex-direction:row; align-items:center; gap:10px; padding:12px; }
    .ck-stat-icon{ margin-bottom:0; flex:0 0 34px; }
    .ck-stat-num{ font-size:1.15rem; }
    .ck-stat-label{ font-size:.7rem; }
  }
</style>
