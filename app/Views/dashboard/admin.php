<div class="dashboard-shell">
  <div class="hero-panel rounded-4 p-4 mb-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start gap-3">
      <div>
        <div class="badge rounded-pill px-3 py-2 mb-3" style="background:rgba(255,255,255,.18);color:#fff">Dasbor Operasional</div>
        <h4 class="fw-bold mb-1 text-white">Dasbor <?= $role === 'super_admin' ? 'Super Admin' : 'Admin' ?></h4>
        <p class="mb-0 text-white-50">Ringkasan operasional klinik dengan data yang lebih cepat dipantau.</p>
      </div>
      <div class="hero-pill">
        <i class="fas fa-chart-line me-2"></i>Monitor performa klinik secara real-time
      </div>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
      <div class="dashboard-small-box" style="background: linear-gradient(135deg,#3b7bff,#2962ff);">
        <div class="box-top">
          <div class="box-content">
            <div class="box-title">Total Pengguna</div>
            <div class="box-value"><?= (int) $stats['total_users'] ?></div>
            <div class="box-note">Pengguna terdaftar di platform.</div>
          </div>
          <div class="box-icon"><i class="fas fa-users"></i></div>
        </div>
        <div class="box-footer"><a href="<?= base_url('/admin/users') ?>">Detail <i class="fas fa-arrow-right fa-xs"></i></a></div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="dashboard-small-box" style="background: linear-gradient(135deg,#28a745,#20c997);">
        <div class="box-top">
          <div class="box-content">
            <div class="box-title">Total Client</div>
            <div class="box-value"><?= (int) $stats['total_patients'] ?></div>
            <div class="box-note">Client yang terdaftar di klinik Anda.</div>
          </div>
          <div class="box-icon"><i class="fas fa-hospital-user"></i></div>
        </div>
        <div class="box-footer"><a href="<?= base_url('/admin/patients') ?>">Detail <i class="fas fa-arrow-right fa-xs"></i></a></div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="dashboard-small-box" style="background: linear-gradient(135deg,#0e7c86,#3bb8c0);">
        <div class="box-top">
          <div class="box-content">
            <div class="box-title">Konseling Hari Ini</div>
            <div class="box-value"><?= (int) $stats['today_consultations'] ?></div>
            <div class="box-note">Jumlah sesi yang dijadwalkan hari ini.</div>
          </div>
          <div class="box-icon"><i class="fas fa-calendar-day"></i></div>
        </div>
        <div class="box-footer"><a href="<?= base_url('/admin/consultations?date=' . date('Y-m-d')) ?>">Detail <i class="fas fa-arrow-right fa-xs"></i></a></div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="dashboard-small-box" style="background: linear-gradient(135deg,#ff8c42,#ffb54d);">
        <div class="box-top">
          <div class="box-content">
            <div class="box-title">Pendapatan Hari Ini</div>
            <div class="box-value">Rp<?= number_format($bizStats['today_revenue'],0,',','.') ?></div>
            <div class="box-note">Total pembayaran yang diterima hari ini.</div>
          </div>
          <div class="box-icon"><i class="fas fa-sun"></i></div>
        </div>
        <div class="box-footer"><a href="<?= base_url('/report') ?>">Detail <i class="fas fa-arrow-right fa-xs"></i></a></div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="dashboard-small-box" style="background: linear-gradient(135deg,#f1535c,#f96d60);">
        <div class="box-top">
          <div class="box-content">
            <div class="box-title">Indikasi Krisis</div>
            <div class="box-value"><?= (int) $stats['total_crisis_flags'] ?></div>
            <div class="box-note">Kejadian risiko tinggi yang perlu perhatian.</div>
          </div>
          <div class="box-icon"><i class="fas fa-triangle-exclamation"></i></div>
        </div>
        <div class="box-footer"><a href="<?= base_url('/admin/crisis') ?>">Detail <i class="fas fa-arrow-right fa-xs"></i></a></div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="dashboard-small-box" style="background: linear-gradient(135deg,#0e7c86,#3bb8c0);">
        <div class="box-top">
          <div class="box-content">
            <div class="box-title">Rencana Tindak Lanjut</div>
            <div class="box-value"><?= (int) $stats['total_followups'] ?></div>
            <div class="box-note">Rencana tindak lanjut yang telah dibuat.</div>
          </div>
          <div class="box-icon"><i class="fas fa-clipboard-list"></i></div>
        </div>
        <div class="box-footer"><a href="<?= base_url('/admin/followups') ?>">Detail <i class="fas fa-arrow-right fa-xs"></i></a></div>
      </div>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="dashboard-small-box">
        <div class="box-icon" style="background: linear-gradient(135deg,#2f5a8f,#5f81c8);"><i class="fas fa-wallet"></i></div>
        <div class="box-content">
          <div class="box-title">Pendapatan Total</div>
          <div class="box-value">Rp<?= number_format($bizStats['total_revenue_all_time'],0,',','.') ?></div>
          <div class="box-note">Total pendapatan klinik sejak awal.</div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="dashboard-small-box">
        <div class="box-icon" style="background: linear-gradient(135deg,#7a4ca0,#9f76c9);"><i class="fas fa-repeat"></i></div>
        <div class="box-content">
          <div class="box-title">Client Berulang</div>
          <div class="box-value"><?= $repeatPatients ?></div>
          <div class="box-note">Client dengan lebih dari satu sesi.</div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="dashboard-small-box">
        <div class="box-icon" style="background: linear-gradient(135deg,#c2527f,#e786b5);"><i class="fas fa-user-clock"></i></div>
        <div class="box-content">
          <div class="box-title">Client Sekali Sesi</div>
          <div class="box-value"><?= $oneTimePatients ?></div>
          <div class="box-note">Client hanya sekali sesi.</div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-lg-7">
      <div class="card border-0 rounded-4 dashboard-card h-100">
        <div class="card-body dashboard-panel">
          <div class="panel-header">
            <div>
              <h5 class="panel-title">Pendapatan per Bulan (12 Bulan Terakhir)</h5>
              <p class="panel-subtitle">Grafik pertumbuhan pendapatan klinik Anda.</p>
            </div>
            <div class="panel-actions">
              <span class="badge bg-success-subtle text-success-emphasis">Naik</span>
            </div>
          </div>
          <canvas id="revenueChart" height="180"></canvas>
        </div>
      </div>
    </div>
    <div class="col-lg-5">
      <div class="card border-0 rounded-4 dashboard-card h-100">
        <div class="card-body dashboard-panel">
          <div class="panel-header">
            <div>
              <h5 class="panel-title">Funnel Booking</h5>
              <p class="panel-subtitle">Perbandingan status booking klinik.</p>
            </div>
            <div class="panel-actions">
              <span class="badge bg-primary-subtle text-primary-emphasis">Ringkasan</span>
            </div>
          </div>
          <canvas id="funnelChart" height="180"></canvas>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-lg-6">
      <div class="card border-0 rounded-4 dashboard-card h-100">
        <div class="card-body dashboard-panel">
          <div class="panel-header">
            <div>
              <h5 class="panel-title">Performa per Psikolog</h5>
              <p class="panel-subtitle">Pantau kontribusi psikolog terhadap klinik.</p>
            </div>
            <div class="panel-actions">
              <span class="badge bg-info-subtle text-info-emphasis">Penyedia</span>
            </div>
          </div>
          <div class="table-responsive">
            <table class="table table-sm align-middle">
              <thead><tr><th>Nama</th><th>Total Sesi</th><th>Selesai</th><th>Pendapatan</th></tr></thead>
              <tbody>
                <?php foreach ($providerPerformance as $p): ?>
                <tr>
                  <td><?= e($p['name']) ?></td>
                  <td><?= (int) $p['total_sessions'] ?></td>
                  <td><?= (int) $p['completed_sessions'] ?></td>
                  <td>Rp<?= number_format($p['revenue'],0,',','.') ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="card border-0 rounded-4 dashboard-card h-100">
        <div class="card-body dashboard-panel">
          <div class="panel-header">
            <div>
              <h5 class="panel-title">Aktivitas Terbaru (Audit Log)</h5>
              <p class="panel-subtitle">Jejak aktivitas pengguna dan sistem.</p>
            </div>
            <div class="panel-actions">
              <!-- <a href="<?= base_url('/admin/audit') ?>" class="btn btn-sm btn-outline-primary">Lihat Semua Log</a> -->
            </div>
          </div>
          <div class="table-responsive">
            <table class="table table-sm align-middle">
              <thead><tr><th>Waktu</th><th>User</th><th>Aksi</th><th>Modul</th></tr></thead>
              <tbody>
              <?php foreach ($recentAudit as $a): ?>
                <tr>
                  <td class="small"><?= format_date($a['created_at'], 'd M Y H:i') ?></td>
                  <td class="small"><?= e($a['user_name'] ?? 'System') ?></td>
                  <td><span class="badge bg-primary-soft text-primary text-uppercase"><?= e($a['action']) ?></span></td>
                  <td class="small"><?= e($a['module']) ?></td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($recentAudit)): ?>
              <tr><td colspan="4" class="text-center text-muted py-3">Belum ada aktivitas tercatat.</td></tr>
              <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
</div>
  </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
  if (typeof Chart === 'undefined') return;
  const revenueCtx = document.getElementById('revenueChart');
  const funnelCtx = document.getElementById('funnelChart');
  if (revenueCtx) {
    new Chart(revenueCtx, {
      type: 'bar',
      data: {
        labels: <?= json_encode($revenueLabels) ?>,
        datasets: [{
          label: 'Pendapatan (Rp)',
          data: <?= json_encode($revenueValues) ?>,
          borderColor: '#2f5a8f',
          backgroundColor: '#2f5a8f',
          borderWidth: 1,
          hoverBackgroundColor: '#1f4e7b',
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
          y: { ticks: { callback: function(value) { return 'Rp ' + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.'); } } }
        }
      }
    });
  }
  if (funnelCtx) {
    new Chart(funnelCtx, {
      type: 'bar',
      data: {
        labels: ['Total Booking', 'Disetujui', 'Selesai', 'Dibatalkan/Ditolak'],
        datasets: [{
          data: [<?= $funnel['total'] ?>, <?= $funnel['approved'] ?>, <?= $funnel['completed'] ?>, <?= $funnel['cancelled'] ?>],
          backgroundColor: ['#4e8f5a', '#f0b36c', '#4bb2a5', '#c2527f']
        }]
      },
      options: {
        responsive: true,
        plugins: { legend: { display: false } }
      }
    });
  }
});
</script>
