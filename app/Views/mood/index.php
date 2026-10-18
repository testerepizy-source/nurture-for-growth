<h4 class="fw-bold mb-3">Mood Tracker</h4>

<div class="row g-3 mb-4">
  <div class="col-lg-5">
    <div class="card p-3">
      <h6 class="fw-bold mb-3">Bagaimana perasaan Anda hari ini?</h6>
      <form method="POST" action="<?= base_url('/mood') ?>">
        <?= \Core\Csrf::field() ?>
        <input type="hidden" name="mood_scale" id="mood_scale_input" value="5">
        <div class="d-flex flex-wrap gap-2 mb-3">
          <?php
          $emojis = ['1'=>'😭','2'=>'😢','3'=>'😔','4'=>'😕','5'=>'😐','6'=>'🙂','7'=>'😊','8'=>'😄','9'=>'😁','10'=>'🤩'];
          foreach ($emojis as $val => $emo): ?>
            <button type="button" class="mood-emoji-btn <?= $val == 5 ? 'selected' : '' ?>" onclick="selectMoodEmoji(this, '<?= $val ?>')" data-value="<?= $val ?>"><?= $emo ?></button>
          <?php endforeach; ?>
        </div>
        <input type="hidden" name="emoji" id="emoji_input" value="😐">

        <div class="mb-2">
          <label class="form-label small">Tanggal</label>
          <input type="date" name="mood_date" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>">
        </div>
        <div class="mb-2">
          <label class="form-label small">Penyebab Mood</label>
          <input type="text" name="cause" class="form-control form-control-sm" placeholder="Misal: pekerjaan, keluarga...">
        </div>
        <div class="mb-2">
          <label class="form-label small">Aktivitas</label>
          <input type="text" name="activity" class="form-control form-control-sm" placeholder="Misal: olahraga, membaca...">
        </div>
        <div class="row g-2 mb-2">
          <div class="col-6">
            <label class="form-label small">Lokasi (opsional)</label>
            <input type="text" name="location" class="form-control form-control-sm">
          </div>
          <div class="col-6">
            <label class="form-label small">Cuaca (opsional)</label>
            <input type="text" name="weather" class="form-control form-control-sm">
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label small">Catatan</label>
          <textarea name="note" class="form-control form-control-sm" rows="2"></textarea>
        </div>
        <button class="btn btn-primary btn-sm w-100" type="submit">Simpan Mood</button>
      </form>
    </div>
  </div>

  <div class="col-lg-7">
    <div class="card p-3 mb-3">
      <h6 class="fw-bold mb-2">Grafik Mingguan</h6>
      <?php if (!empty($weekly['labels'])): ?>
        <canvas id="weeklyChart" height="90"></canvas>
      <?php else: ?>
        <div class="alert alert-secondary mb-0">Tidak ada data mood minggu ini.</div>
      <?php endif; ?>
    </div>
    <div class="card p-3">
      <h6 class="fw-bold mb-2">Grafik Bulanan</h6>
      <?php if (!empty($monthly['labels'])): ?>
        <canvas id="monthlyChart" height="90"></canvas>
      <?php else: ?>
        <div class="alert alert-secondary mb-0">Tidak ada data mood bulan ini.</div>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="card p-3">
  <h6 class="fw-bold mb-3">Riwayat Mood</h6>
  <div class="table-responsive">
    <table class="table table-sm align-middle">
      <thead><tr><th>Tanggal</th><th>Mood</th><th>Penyebab</th><th>Aktivitas</th><th>Catatan</th></tr></thead>
      <tbody>
      <?php if (empty($history)): ?>
        <tr><td colspan="5" class="text-center text-muted py-3">Belum ada data mood.</td></tr>
      <?php endif; ?>
      <?php foreach ($history as $h): ?>
        <tr>
          <td><?= format_date($h['mood_date']) ?></td>
          <td><span style="font-size:1.3rem"><?= e($h['emoji']) ?></span> <?= (int) $h['mood_scale'] ?>/10</td>
          <td><?= e($h['cause']) ?></td>
          <td><?= e($h['activity']) ?></td>
          <td class="small text-muted"><?= e($h['note']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <p class="text-muted small mb-0"><i class="fas fa-circle-info me-1"></i>Analisis tren mood bersifat informatif berdasarkan histori Anda dan bukan merupakan diagnosis.</p>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.mood-emoji-btn').forEach(btn => {
    btn.addEventListener('click', function(){
      document.getElementById('emoji_input').value = this.textContent.trim();
    });
  });

  const weekly = <?= json_encode($weekly ?? ['labels' => [], 'values' => []]) ?>;
  const monthly = <?= json_encode($monthly ?? ['labels' => [], 'values' => []]) ?>;
  const chartTextColor = getComputedStyle(document.documentElement).getPropertyValue('--nfg-text').trim() || '#212529';
  const chartGridColor = getComputedStyle(document.documentElement).getPropertyValue('--nfg-border').trim() || 'rgba(0,0,0,0.12)';

  const weeklyCanvas = document.getElementById('weeklyChart');
  const monthlyCanvas = document.getElementById('monthlyChart');

  if (typeof Chart === 'undefined') {
    return;
  }

  if (weeklyCanvas && weekly.labels.length > 0) {
    new Chart(weeklyCanvas, {
      type: 'line',
      data: {
        labels: weekly.labels,
        datasets: [{
          label: 'Mood',
          data: weekly.values,
          borderColor: '#6f8f74',
          backgroundColor: 'rgba(111,143,116,.15)',
          fill: true,
          tension: .35
        }]
      },
      options: {
        scales: {
          x: { ticks: { color: chartTextColor }, grid: { color: chartGridColor } },
          y: { min: 0, max: 10, ticks: { color: chartTextColor }, grid: { color: chartGridColor } }
        },
        plugins: { legend: { display: false } }
      }
    });
  }

  if (monthlyCanvas && monthly.labels.length > 0) {
    new Chart(monthlyCanvas, {
      type: 'bar',
      data: {
        labels: monthly.labels,
        datasets: [{
          label: 'Mood',
          data: monthly.values,
          backgroundColor: '#e08a5b'
        }]
      },
      options: {
        scales: {
          x: { ticks: { color: chartTextColor }, grid: { color: chartGridColor } },
          y: { min: 0, max: 10, ticks: { color: chartTextColor }, grid: { color: chartGridColor } }
        },
        plugins: { legend: { display: false } }
      }
    });
  }
});
</script>
