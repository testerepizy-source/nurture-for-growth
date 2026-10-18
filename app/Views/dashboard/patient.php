<div class="dashboard-shell">
  <section class="dashboard-hero">
    <div class="hero-panel">
      <div class="d-flex flex-column flex-lg-row justify-content-between gap-4 align-items-start">
        <div>
          <span class="badge badge-primary">Ikhtisar Kesehatan</span>
          <h1 class="mt-3 mb-2">Selamat datang kembali.</h1>
          <p class="section-sub">Pantau mood, sesi konseling, dan progres kesehatan mental Anda dalam satu tampilan cerdas.</p>
        </div>
        <div class="dashboard-pill">
          <i class="fas fa-heart-pulse"></i> Jaga ritme kesehatan diri
        </div>
      </div>
    </div>
  </section>

  <section class="dashboard-row">
    <article class="stat-card">
      <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
        <div>
          <p class="stat-label">Assessment Selesai</p>
          <h2 class="stat-value"><?= (int) $stats['total_assessments'] ?></h2>
        </div>
        <span class="stat-icon bg-info"><i class="fas fa-clipboard-list"></i></span>
      </div>
      <p class="box-note">Skor kesehatan mental yang telah Anda isi dan simpan sebagai referensi.</p>
      <a href="<?= base_url('/assessment/history') ?>" class="d-inline-flex align-items-center gap-2 mt-3 text-primary">Lihat detail <i class="fas fa-arrow-right"></i></a>
    </article>
    <article class="stat-card">
      <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
        <div>
          <p class="stat-label">Total Sesi Konseling</p>
          <h2 class="stat-value"><?= (int) $stats['total_consultations'] ?></h2>
        </div>
        <span class="stat-icon bg-success"><i class="fas fa-calendar-check"></i></span>
      </div>
      <p class="box-note">Sesi konseling yang sudah dijadwalkan dan terlaksana bersama tenaga profesional.</p>
      <a href="<?= base_url('/consultation') ?>" class="d-inline-flex align-items-center gap-2 mt-3 text-primary">Lihat detail <i class="fas fa-arrow-right"></i></a>
    </article>
    <article class="stat-card">
      <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
        <div>
          <p class="stat-label">Sesi Mendatang</p>
          <h2 class="stat-value"><?= count($stats['upcoming']) ?></h2>
        </div>
        <span class="stat-icon bg-warning"><i class="fas fa-hourglass-half"></i></span>
      </div>
      <p class="box-note">Jadwal berikutnya yang perlu Anda persiapkan bersama konselor.</p>
      <a href="<?= base_url('/consultation') ?>" class="d-inline-flex align-items-center gap-2 mt-3 text-primary">Kelola jadwal <i class="fas fa-arrow-right"></i></a>
    </article>
  </section>

  <section class="dashboard-row">
    <article class="dashboard-panel">
      <div class="panel-header">
        <div>
          <h3 class="panel-title">Mood 7 Hari Terakhir</h3>
          <p class="panel-subtitle">Perubahan mood Anda ditampilkan secara visual untuk membantu refleksi.</p>
        </div>
        <span class="badge badge-soft">Visualisasi</span>
      </div>
      <?php if (!empty($weekly['labels'])): ?>
        <div class="chart-container">
          <canvas id="moodWeeklyChart"></canvas>
        </div>
      <?php else: ?>
        <div class="empty-box mb-0">Belum ada data mood 7 hari terakhir untuk ditampilkan.</div>
      <?php endif; ?>
    </article>

    <article class="dashboard-panel">
      <div class="panel-header">
        <div>
          <h3 class="panel-title">Sesi Konseling Mendatang</h3>
          <p class="panel-subtitle">Jadwal yang telah dikonfirmasi untuk minggu ini.</p>
        </div>
      </div>
      <?php if (empty($stats['upcoming'])): ?>
        <div class="empty-box mb-0">
          <i class="fas fa-calendar-plus"></i>
          <p class="mb-0">Belum ada jadwal konseling mendatang.</p>
        </div>
      <?php else: ?>
        <?php foreach ($stats['upcoming'] as $u): ?>
          <div class="list-item">
            <div>
              <div class="fw-semibold"><?= e($u['provider_name']) ?></div>
              <div class="small text-muted"><?= format_date($u['schedule_at'], 'd M Y H:i') ?> · <?= e($u['method']) ?></div>
            </div>
            <span class="badge badge-primary badge-pill">Segera</span>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
      <a href="<?= base_url('/consultation') ?>" class="btn btn-outline-primary btn-sm mt-3">Lihat Semua</a>
    </article>
  </section>

  <?php if (!empty($myFollowUps)): ?>
    <section class="dashboard-row">
      <article class="dashboard-panel">
        <div class="panel-header">
          <div>
            <h3 class="panel-title">Rencana Tindak Lanjut</h3>
            <p class="panel-subtitle">Langkah lanjutan yang disusun oleh konselor Anda.</p>
          </div>
        </div>
        <?php foreach ($myFollowUps as $f): ?>
          <div class="list-item">
            <div>
              <div class="fw-semibold"><?= e($f['plan_text']) ?></div>
              <div class="small text-muted"><?= e($f['provider_name']) ?><?= $f['target_date'] ? ' &middot; target: ' . format_date($f['target_date']) : '' ?></div>
            </div>
            <span class="badge bg-secondary text-uppercase"><?= e($f['status']) ?></span>
          </div>
        <?php endforeach; ?>
        <a href="<?= base_url('/consultation') ?>" class="btn btn-outline-primary btn-sm mt-3">Lihat Semua</a>
      </article>
    </section>
  <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  if (typeof Chart === 'undefined') return;
  const weekly = <?= json_encode($weekly) ?>;
  const chartTextColor = getComputedStyle(document.documentElement).getPropertyValue('--color-text').trim() || '#102a43';
  const chartGridColor = getComputedStyle(document.documentElement).getPropertyValue('--color-border').trim() || 'rgba(16,42,67,.12)';
  const weeklyCanvas = document.getElementById('moodWeeklyChart');

  if (weeklyCanvas && weekly.labels.length > 0) {
    new Chart(weeklyCanvas, {
      type: 'line',
      data: {
        labels: weekly.labels,
        datasets: [{
          label: 'Mood',
          data: weekly.values,
          borderColor: 'rgba(23,100,124,0.95)',
          backgroundColor: 'rgba(23,100,124,0.2)',
          pointBackgroundColor: '#fff',
          pointBorderColor: 'rgba(23,100,124,0.95)',
          pointRadius: 4,
          pointHoverRadius: 5,
          tension: 0.35,
          fill: true
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          x: { ticks: { color: chartTextColor }, grid: { color: chartGridColor } },
          y: { min: 0, max: 10, ticks: { stepSize: 2, color: chartTextColor }, grid: { color: chartGridColor } }
        },
        plugins: { legend: { display: false } }
      }
    });
  }
});
</script>
