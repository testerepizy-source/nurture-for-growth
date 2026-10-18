<h4 class="fw-bold mb-3">Analitik Bisnis</h4>

<div class="row g-3 mb-4">
  <div class="col-md-3"><div class="stat-card"><p class="small text-muted mb-1">Total Client</p><h4 class="fw-bold mb-0"><?= (int) $stats['total_patients'] ?></h4></div></div>
  <div class="col-md-3"><div class="stat-card"><p class="small text-muted mb-1">Revenue Sepanjang Waktu</p><h4 class="fw-bold mb-0">Rp<?= number_format($stats['total_revenue_all_time'],0,',','.') ?></h4></div></div>
  <div class="col-md-3"><div class="stat-card"><p class="small text-muted mb-1">Client Berulang</p><h4 class="fw-bold mb-0"><?= $repeatPatients ?></h4></div></div>
  <div class="col-md-3"><div class="stat-card"><p class="small text-muted mb-1">Client Sekali Sesi</p><h4 class="fw-bold mb-0"><?= $oneTimePatients ?></h4></div></div>
</div>

<div class="row g-3 mb-4">
  <div class="col-lg-7">
    <div class="card p-3">
      <h6 class="fw-bold mb-2">Revenue per Bulan (12 Bulan Terakhir)</h6>
      <canvas id="revenueChart" height="180"></canvas>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="card p-3">
      <h6 class="fw-bold mb-2">Funnel Booking</h6>
      <canvas id="funnelChart" height="180"></canvas>
    </div>
  </div>
</div>

<div class="card p-3">
  <h6 class="fw-bold mb-2">Performa per Psikolog</h6>
  <div class="table-responsive">
  <table class="table table-sm">
    <thead><tr><th>Nama</th><th>Total Sesi</th><th>Sesi Selesai</th><th>Revenue</th></tr></thead>
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('revenueChart'), {
  type: 'line',
  data: {
    labels: <?= json_encode($revenueLabels) ?>,
    datasets: [{ label: 'Revenue (Rp)', data: <?= json_encode($revenueValues) ?>, borderColor: '#00c79c', backgroundColor: 'rgba(0,199,156,.12)', tension: .3, fill: true }]
  },
  options: { plugins: { legend: { display: false } } }
});
new Chart(document.getElementById('funnelChart'), {
  type: 'bar',
  data: {
    labels: ['Total Booking', 'Disetujui', 'Selesai', 'Dibatalkan/Ditolak'],
    datasets: [{ data: [<?= $funnel['total'] ?>, <?= $funnel['approved'] ?>, <?= $funnel['completed'] ?>, <?= $funnel['cancelled'] ?>], backgroundColor: ['#00c79c','#f68c12','#ee156e','#6c757d'] }]
  },
  options: { plugins: { legend: { display: false } } }
});
</script>
