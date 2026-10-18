<div class="dashboard-shell">
  <div class="hero-panel rounded-4 p-4 mb-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start gap-3">
      <div>
        <div class="badge rounded-pill px-3 py-2 mb-3" style="background:rgba(255,255,255,.18);color:#fff">Operasional Penyedia</div>
        <h4 class="fw-bold mb-1 text-white">Dasbor Psikolog</h4>
        <p class="mb-0 text-white-50">Ringkasan aktivitas layanan Anda hari ini dalam tampilan yang lebih terarah.</p>
      </div>
      <div class="hero-pill">
        <i class="fas fa-user-md me-2"></i>Kelola client dan sesi lebih mudah
      </div>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-4">
      <div class="dashboard-small-box" style="background: linear-gradient(135deg,#3366cc,#4887eb);">
        <div class="box-top">
          <div class="box-content">
            <div class="box-title">Client Ditangani</div>
            <div class="box-value"><?= (int) $stats['total_patients'] ?></div>
            <div class="box-note">Jumlah client yang Anda kelola.</div>
          </div>
          <div class="box-icon"><i class="fas fa-users"></i></div>
        </div>
        <div class="box-footer"><a href="<?= base_url('/patients') ?>">Detail <i class="fas fa-arrow-right fa-xs"></i></a></div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-4">
      <div class="dashboard-small-box" style="background: linear-gradient(135deg,#13b5b8,#44d7c1);">
        <div class="box-top">
          <div class="box-content">
            <div class="box-title">Booking Hari Ini</div>
            <div class="box-value"><?= (int) $stats['today_bookings'] ?></div>
            <div class="box-note">Sesi yang dijadwalkan untuk hari ini.</div>
          </div>
          <div class="box-icon"><i class="fas fa-calendar-day"></i></div>
        </div>
        <div class="box-footer"><a href="<?= base_url('/consultation') ?>">Detail <i class="fas fa-arrow-right fa-xs"></i></a></div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-4">
      <div class="dashboard-small-box" style="background: linear-gradient(135deg,#f29d35,#f5c56f);color:#111;">
        <div class="box-top">
          <div class="box-content">
            <div class="box-title">Menunggu Approval</div>
            <div class="box-value"><?= (int) $stats['pending_approval'] ?></div>
            <div class="box-note">Request konsultasi yang perlu ditinjau.</div>
          </div>
          <div class="box-icon" style="background: rgba(17,17,17,.12);color:#111;"><i class="fas fa-hourglass-half"></i></div>
        </div>
        <div class="box-footer"><a href="<?= base_url('/consultation') ?>">Detail <i class="fas fa-arrow-right fa-xs"></i></a></div>
      </div>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-4">
      <div class="dashboard-small-box" style="background: linear-gradient(135deg,#ff8c42,#ffb54d);">
        <div class="box-top">
          <div class="box-content">
            <div class="box-title">Pendapatan Hari Ini</div>
            <div class="box-value">Rp<?= number_format($stats['today_revenue'],0,',','.') ?></div>
            <div class="box-note">Pembayaran yang diterima hari ini.</div>
          </div>
          <div class="box-icon"><i class="fas fa-sun"></i></div>
        </div>
        <div class="box-footer"><a href="<?= base_url('/consultation') ?>">Detail <i class="fas fa-arrow-right fa-xs"></i></a></div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-4">
      <div class="dashboard-small-box" style="background: linear-gradient(135deg,#2f5a8f,#5f81c8);">
        <div class="box-top">
          <div class="box-content">
            <div class="box-title">Total Pendapatan</div>
            <div class="box-value">Rp<?= number_format($stats['total_revenue'],0,',','.') ?></div>
            <div class="box-note">Total pendapatan sesi Anda.</div>
          </div>
          <div class="box-icon"><i class="fas fa-wallet"></i></div>
        </div>
        <div class="box-footer"><a href="<?= base_url('/consultation') ?>">Detail <i class="fas fa-arrow-right fa-xs"></i></a></div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-4">
      <div class="dashboard-small-box" style="background: linear-gradient(135deg,#0e7c86,#3bb8c0);">
        <div class="box-top">
          <div class="box-content">
            <div class="box-title">Rencana Tindak Lanjut</div>
            <div class="box-value"><?= (int) $stats['total_followups'] ?></div>
            <div class="box-note">Rencana tindak lanjut yang telah Anda buat.</div>
          </div>
          <div class="box-icon"><i class="fas fa-clipboard-list"></i></div>
        </div>
        <div class="box-footer"><a href="<?= base_url('/consultation') ?>">Detail <i class="fas fa-arrow-right fa-xs"></i></a></div>
      </div>
    </div>
  </div>

  <?php if (!empty($crisisEvents)): ?>
  <div class="alert alert-danger rounded-4 border-0 shadow-sm mb-4">
    <h6 class="fw-bold"><i class="fas fa-triangle-exclamation me-1"></i>Perhatian: Indikasi Risiko Tinggi</h6>
    <ul class="mb-0 small">
      <?php foreach ($crisisEvents as $c): ?>
        <li><?= e($c['patient_name']) ?> &mdash; <?= e($c['context_snippet']) ?> (<?= format_date($c['created_at'], 'd M Y H:i') ?>)</li>
      <?php endforeach; ?>
    </ul>
  </div>
  <?php endif; ?>

  <div class="row g-3 mb-4">
    <div class="col-lg-6">
      <div class="card border-0 rounded-4 dashboard-card h-100">
        <div class="card-body dashboard-panel">
          <div class="panel-header">
            <div>
              <h5 class="panel-title">Pendapatan Bulanan</h5>
              <p class="panel-subtitle">Pendapatan per bulan untuk tahun berjalan.</p>
            </div>
            <div class="panel-actions">
              <span class="badge bg-success-subtle text-success-emphasis">Tahun Ini</span>
            </div>
          </div>
          <canvas id="providerRevenueChart" height="180"></canvas>
        </div>
      </div>
    </div>
    <div class="col-lg-6 d-flex flex-column gap-3">
      <div class="card border-0 rounded-4 dashboard-card h-100">
        <div class="card-body dashboard-panel">
          <div class="panel-header">
            <div>
              <h5 class="panel-title">Menunggu Persetujuan</h5>
              <p class="panel-subtitle">Booking yang masih perlu Anda tinjau.</p>
            </div>
            <div class="panel-actions">
              <span class="badge bg-info-subtle text-info-emphasis">Tinjau</span>
            </div>
          </div>
          <?php if (empty($pendingList)): ?>
            <div class="empty-box">
              <i class="fas fa-calendar-check"></i>
              <p class="mb-0 small">Tidak ada booking yang menunggu persetujuan.</p>
            </div>
          <?php else: foreach ($pendingList as $p): ?>
            <div class="list-item">
              <div>
                <div class="small fw-semibold"><?= e($p['patient_name']) ?></div>
                <div class="small text-muted"><?= format_date($p['schedule_at'], 'd M Y H:i') ?></div>
              </div>
              <a href="<?= base_url('/consultation/' . $p['id']) ?>" class="btn btn-sm btn-outline-primary">Tinjau</a>
            </div>
          <?php endforeach; endif; ?>
        </div>
      </div>

      <div class="card border-0 rounded-4 dashboard-card h-100">
        <div class="card-body dashboard-panel">
          <div class="panel-header">
            <div>
              <h5 class="panel-title">Sesi Mendatang (Disetujui)</h5>
              <p class="panel-subtitle">Jadwal yang sudah disetujui untuk hari-hari mendatang.</p>
            </div>
            <div class="panel-actions">
              <span class="badge bg-success-subtle text-success-emphasis">Mendatang</span>
            </div>
          </div>
          <?php if (empty($upcoming)): ?>
            <div class="empty-box">
              <i class="fas fa-clock"></i>
              <p class="mb-0 small">Belum ada sesi mendatang.</p>
            </div>
          <?php else: foreach ($upcoming as $u): ?>
            <div class="list-item">
              <div>
                <div class="small fw-semibold"><?= e($u['patient_name']) ?></div>
                <div class="small text-muted"><?= format_date($u['schedule_at'], 'd M Y H:i') ?> &middot; <?= e($u['method']) ?></div>
              </div>
              <a href="<?= base_url('/consultation/' . $u['id']) ?>" class="btn btn-sm btn-outline-primary">Detail</a>
            </div>
          <?php endforeach; endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  if (typeof Chart === 'undefined') return;
  const revenueCtx = document.getElementById('providerRevenueChart');
  if (revenueCtx) {
    new Chart(revenueCtx, {
      type: 'bar',
      data: {
        labels: <?= json_encode($revenueLabels) ?>,
        datasets: [{
          label: 'Pendapatan (Rp)',
          data: <?= json_encode($revenueValues) ?>,
          borderColor: '#1d4e89',
          backgroundColor: 'rgba(29,78,137,.16)',
          borderWidth: 1,
          borderRadius: 12,
          barPercentage: 0.72,
          categoryPercentage: 0.82
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: function(context) {
                return 'Rp ' + context.formattedValue.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
              }
            }
          }
        },
        scales: {
          y: {
            ticks: {
              callback: function(value) {
                return 'Rp ' + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
              }
            }
          }
        }
      }
    });
  }
});
</script>

