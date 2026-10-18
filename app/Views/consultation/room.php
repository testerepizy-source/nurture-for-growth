<?php use Core\Auth; $isProvider = in_array(Auth::roleName(), ['psikolog','psikiater'], true); ?>

<div class="card p-4 text-center" style="max-width:640px;margin:0 auto">
  <h5 class="fw-bold mb-1">Ruang Tunggu Telekonseling</h5>
  <p class="text-muted small">Sesi antara <?= e($consultation['patient_name']) ?> &amp; <?= e($consultation['provider_name']) ?></p>

  <div class="my-3">
    <span class="badge fs-6 <?= $videoSession['status'] === 'ongoing' ? 'bg-success' : ($videoSession['status'] === 'ended' ? 'bg-secondary' : 'bg-warning text-dark') ?>">
      <?= strtoupper($videoSession['status']) ?>
    </span>
  </div>

  <div class="mb-3 text-start">
    <div class="fw-semibold mb-1">Jadwal Sesi</div>
    <div class="text-muted small">
      <?= e(format_date($consultation['schedule_at'], 'd M Y H:i')) ?> · <?= (int)$consultation['duration_minutes'] ?> menit
    </div>
  </div>

  <div class="mb-3 text-start">
    <div id="sessionTimer" class="fs-4 fw-bold">--:--</div>
    <div id="sessionTimerLabel" class="text-muted small">Menghitung waktu sesi...</div>
  </div>

  <?php $roomMeetingLink = !empty($consultation['meeting_link']) && !str_contains((string) $consultation['meeting_link'], '/room') ? $consultation['meeting_link'] : null; ?>
  <?php if ($roomMeetingLink): ?>
  <div class="mb-3 text-start">
    <div class="fw-semibold mb-1">Link Meeting</div>
    <a href="<?= e($roomMeetingLink) ?>" class="btn btn-outline-primary btn-sm w-100 mb-2" target="_blank">
      <i class="fas fa-video me-1"></i> Buka Meeting
    </a>
    <div class="small text-muted">Gunakan tautan meeting yang disiapkan psikolog untuk bergabung pada waktu sesi.</div>
  </div>
  <?php else: ?>
  <div class="mb-3 text-start">
    <div class="fw-semibold mb-1">Link Meeting</div>
    <button class="btn btn-outline-secondary btn-sm w-100 mb-2" type="button" disabled title="Link meeting belum tersedia">
      <i class="fas fa-video-slash me-1"></i> Buka Meeting
    </button>
    <div class="small text-muted">Link meeting belum dimasukkan oleh psikolog. Tunggu sampai psikolog menyiapkan link meeting, lalu halaman ini akan menampilkan tombol <strong>Buka Meeting</strong>.</div>
  </div>
  <?php endif; ?>

  <?php if ($videoSession['status'] !== 'ended'): ?>
    <form method="GET" action="<?= base_url('/consultation/room/' . $videoSession['id'] . '/join') ?>">
      <button class="btn btn-primary btn-lg mb-2" type="submit"><i class="fas fa-video me-2"></i>Absen Sesi Consultation</button>
    </form>
    <!-- <p class="text-muted small">Room code: <code><?= e($videoSession['room_code']) ?></code></p>
    <div class="disclaimer-box text-start mt-3">
      <i class="fas fa-circle-info me-1"></i>
      <strong>Catatan teknis:</strong> Starter ini mencatat status sesi (menunggu/berlangsung/selesai) dan kehadiran peserta,
      namun belum mengimplementasikan streaming audio/video sungguhan (WebRTC/SFU). Untuk penggunaan produksi,
      integrasikan dengan penyedia seperti Zoom SDK, Jitsi, atau Google Meet dan tempelkan link pertemuan di sini.
    </div> -->
  <?php else: ?>
    <p class="text-muted">Sesi telah berakhir.</p>
    <?php if ($videoSession['session_summary']): ?>
      <div class="alert alert-light border text-start small"><strong>Ringkasan Sesi:</strong> <?= e($videoSession['session_summary']) ?></div>
    <?php endif; ?>
  <?php endif; ?>

  <?php if ($isProvider && $videoSession['status'] === 'ongoing'): ?>
    <form method="POST" action="<?= base_url('/consultation/room/' . $videoSession['id'] . '/end') ?>" class="mt-3 text-start">
      <?= \Core\Csrf::field() ?>
      <label class="form-label small">Ringkasan Sesi (Session Summary)</label>
      <textarea name="session_summary" class="form-control form-control-sm mb-2" rows="2" placeholder="Ringkasan singkat sesi..."></textarea>
      <button class="btn btn-outline-danger btn-sm w-100" type="submit">Akhiri Sesi</button>
    </form>
  <?php endif; ?>

  <script>
  (function () {
    const timerEl = document.getElementById('sessionTimer');
    const labelEl = document.getElementById('sessionTimerLabel');
    if (!timerEl || !labelEl) return;

    const scheduleAt = new Date(<?= json_encode(date('c', strtotime($consultation['schedule_at']))) ?>);
    const durationMinutes = <?= (int) $consultation['duration_minutes'] ?>;
    const status = <?= json_encode($videoSession['status']) ?>;
    const endAt = new Date(scheduleAt.getTime() + durationMinutes * 60000);

    function formatDuration(diffMs) {
      const totalSeconds = Math.max(0, Math.floor(diffMs / 1000));
      const minutes = Math.floor(totalSeconds / 60);
      const seconds = totalSeconds % 60;
      return String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
    }

    function updateTimer() {
      const now = new Date();
      if (status === 'ended') {
        timerEl.textContent = '00:00';
        labelEl.textContent = 'Sesi sudah berakhir.';
        return;
      }

      if (status === 'waiting') {
        const diff = scheduleAt - now;
        if (diff > 0) {
          timerEl.textContent = formatDuration(diff);
          labelEl.textContent = 'Waktu sampai sesi dimulai.';
        } else {
          timerEl.textContent = '00:00';
          labelEl.textContent = 'Sesi seharusnya dimulai sekarang. Klik Gabung Sesi jika sudah waktunya.';
        }
        return;
      }

      if (status === 'ongoing') {
        const diff = endAt - now;
        if (diff > 0) {
          timerEl.textContent = formatDuration(diff);
          labelEl.textContent = 'Sisa waktu sesi.';
        } else {
          timerEl.textContent = '00:00';
          labelEl.textContent = 'Durasi sesi telah berakhir.';
        }
        return;
      }

      timerEl.textContent = '00:00';
      labelEl.textContent = 'Status sesi: ' + status;
    }

    updateTimer();
    setInterval(updateTimer, 1000);
  })();
  </script>

  <h6 class="fw-bold mt-4 mb-2 text-start">Kehadiran (Attendance)</h6>
  <table class="table table-sm text-start">
    <thead><tr><th>Nama</th><th>Bergabung</th></tr></thead>
    <tbody>
    <?php foreach ($attendance as $a): ?>
      <tr><td><?= e($a['name']) ?></td><td class="small text-muted"><?= format_date($a['joined_at'], 'd M Y H:i') ?></td></tr>
    <?php endforeach; ?>
    <?php if (empty($attendance)): ?><tr><td colspan="2" class="text-muted small">Belum ada yang bergabung.</td></tr><?php endif; ?>
    </tbody>
  </table>

  <a href="<?= base_url('/consultation/' . $consultation['id']) ?>" class="btn btn-sm btn-outline-secondary">Kembali ke Detail Sesi</a>
</div>
