<div class="dashboard-shell">
  <div class="hero-panel rounded-4 p-4 mb-4" style="background: linear-gradient(135deg, #4169e1, #2dc8c8 60%, #4ad7a8); box-shadow:0 18px 45px rgba(34,105,168,.22);">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start gap-3">
      <div>
        <div class="badge rounded-pill px-3 py-2 mb-3" style="background:rgba(255,255,255,.18);color:#fff">Calendar</div>
        <h4 class="fw-bold mb-1 text-white">Kalender Jadwal Penuh</h4>
        <p class="mb-0 text-white-50">Lihat seluruh jadwal konsultasi Anda dalam tampilan kalender penuh.</p>
      </div>
      <div class="d-flex gap-2 align-items-center">
        <?php if (in_array($role, ['admin', 'super_admin', 'psikolog', 'psikiater'], true)): ?>
          <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#calendarEventModal">
            <i class="fas fa-plus me-1"></i> Tambah Jadwal
          </button>
        <?php endif; ?>
        <a href="<?= base_url('/calendar') ?>" class="btn btn-outline-light btn-sm">Muat Ulang</a>
      </div>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-12">
      <div class="card border-0 rounded-4 dashboard-card h-100">
        <div class="card-body dashboard-panel p-3">
          <div class="calendar-legend" aria-label="Legenda status jadwal">
            <span><i class="calendar-status-dot calendar-status-dot-approved"></i>Disetujui</span>
            <span><i class="calendar-status-dot calendar-status-dot-pending"></i>Pending</span>
            <span><i class="calendar-status-dot calendar-status-dot-cancelled"></i>Dibatalkan</span>
            <span><i class="calendar-status-dot calendar-status-dot-completed"></i>Selesai</span>
          </div>
          <div id="calendar"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php if (in_array($role, ['admin', 'super_admin', 'psikolog', 'psikiater'], true)): ?>
<div class="modal fade" id="calendarEventModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content rounded-4">
      <div class="modal-header border-0">
        <h5 class="modal-title">Tambah Jadwal Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="<?= base_url('/calendar/event') ?>">
        <?= \Core\Csrf::field() ?>
        <div class="modal-body">
          <?php if (in_array($role, ['admin', 'super_admin'], true)): ?>
            <div class="mb-3">
              <label class="form-label small fw-semibold">Psikolog</label>
              <select name="provider_id" class="form-select form-select-sm" required>
                <option value="">Pilih psikolog</option>
                <?php foreach ($providers as $provider): ?>
                  <option value="<?= e($provider['user_id']) ?>"><?= e($provider['name']) ?> (<?= e($provider['provider_type']) ?>)</option>
                <?php endforeach; ?>
              </select>
            </div>
          <?php else: ?>
            <input type="hidden" name="provider_id" value="<?= \Core\Auth::id() ?>">
          <?php endif; ?>

          <div class="mb-3">
            <label class="form-label small fw-semibold">Client</label>
            <select name="patient_id" class="form-select form-select-sm" required>
              <option value="">Pilih client</option>
              <?php foreach ($patients as $patient): ?>
                <option value="<?= e($patient['id']) ?>"><?= e($patient['name']) ?><?= !empty($patient['medical_record_number']) ? ' · ' . e($patient['medical_record_number']) : '' ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Judul</label>
              <input type="text" name="title" class="form-control form-control-sm" placeholder="Contoh: Sesi Konseling Mingguan" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Tanggal & Waktu</label>
              <input type="datetime-local" name="schedule_at" class="form-control form-control-sm" required>
            </div>
          </div>

          <div class="row g-3 mt-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Durasi (menit)</label>
              <input type="number" name="duration_minutes" class="form-control form-control-sm" value="60" min="15" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Jenis</label>
              <select name="method" class="form-select form-select-sm" disabled>
                <option value="online" selected>Online</option>
              </select>
            </div>
          </div>

          <div class="mb-3 mt-3">
            <label class="form-label small fw-semibold">Catatan / Deskripsi</label>
            <textarea name="description" class="form-control form-control-sm" rows="3" placeholder="Catatan tambahan untuk jadwal"></textarea>
          </div>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary btn-sm">Simpan Jadwal</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<div class="modal fade" id="calendarEventDetailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4">
      <div class="modal-header border-0">
        <div>
          <h4 class="modal-title" id="calendarEventTitle">Detail Jadwal</h4>
          <small class="text-muted" id="calendarEventTime"></small>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <span class="badge bg-success-subtle text-success" id="calendarEventStatus"></span>
        </div>
        <div class="mb-3 text-muted small" id="calendarEventParticipants"></div>
        <div class="mb-3" id="calendarEventDescriptionWrapper" style="display:none;">
          <p class="mb-0 text-muted" id="calendarEventDescription"></p>
        </div>
        <div class="d-flex gap-2">
          <a id="calendarJoinButton" href="#" class="btn btn-primary btn-sm" target="_blank" style="display:none;"><i class="fas fa-video me-1"></i> Meeting</a>
        </div>
      </div>
    </div>
  </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<style>
#calendar { min-height: 720px; }
.fc .fc-toolbar-title { font-weight:700; color: var(--nfg-text); }
.fc .fc-button-primary { background: var(--nfg-teal-600); border:none; }
.fc .fc-button-primary:hover { background: var(--nfg-teal-700); }
.fc-theme-standard td.fc-daygrid-day.fc-day-today { background: rgba(14,124,134,.08); }
.fc .fc-daygrid-event { border-radius: 14px; padding: .55rem .75rem; font-weight: 600; }
.fc .fc-scrollgrid-section td { border-color: var(--nfg-border); }
.modal-content { border-radius: 1rem; }
.calendar-legend { display:flex; flex-wrap:wrap; gap:10px 18px; margin-bottom:16px; padding:10px 12px; background:var(--mint-tint); border:1px solid var(--sand-line); border-radius:12px; }
.calendar-legend span { display:inline-flex; align-items:center; gap:7px; color:var(--ink-soft); font-size:.78rem; font-weight:700; }
.calendar-status-dot { width:10px; height:10px; border-radius:50%; display:inline-block; }
.calendar-status-dot-approved { background:#0e7c86; }
.calendar-status-dot-pending { background:#d97706; }
.calendar-status-dot-cancelled { background:#dc3f67; }
.calendar-status-dot-completed { background:#667085; }
.fc .calendar-status-pending { box-shadow: inset 4px 0 #fbbf24; }
.fc .calendar-status-cancelled { box-shadow: inset 4px 0 #fda4af; }
.fc .calendar-status-completed { opacity:.82; }
@media (max-width: 991px) {
  #calendar { min-height: 620px; }
  .calendar-legend { flex-direction: column; align-items: stretch; gap: 10px; }
  .calendar-legend span { width: 100%; justify-content: flex-start; }
  .modal-dialog.modal-lg { max-width: 100%; margin: 1rem; }
}
@media (max-width: 767px) {
  #calendar { min-height: 520px; }
  .calendar-legend { padding: 12px 10px; gap: 10px; }
  .calendar-legend span { font-size: .74rem; }
  .fc .fc-toolbar { display: grid; grid-template-columns: 1fr; gap: 0.85rem; align-items: center; }
  .fc .fc-toolbar-chunk { display: flex; flex-wrap: wrap; gap: 0.5rem; justify-content: space-between; align-items: center; width: 100%; }
  .fc .fc-toolbar-chunk:first-child { justify-content: flex-start; }
  .fc .fc-toolbar-chunk:last-child { justify-content: flex-end; }
  .fc .fc-toolbar-title { width: 100%; text-align: center; font-size: 1rem; margin: 0.2rem 0 0; order: 2; }
  .fc .fc-button-group { display: flex; flex-wrap: wrap; gap: 0.45rem; justify-content: flex-start; width: 100%; }
  .fc .fc-button { min-width: 0; flex: 1 1 auto; padding: .55rem .75rem; font-size: .88rem; white-space: normal; border-radius: .9rem; }
  .fc .fc-button.fc-prev-button,
  .fc .fc-button.fc-next-button {
    flex: 0 0 2.9rem;
    min-width: 2.9rem;
    width: 2.9rem;
    padding: .55rem .5rem;
  }
  .fc .fc-button.fc-today-button { flex: 1 1 40%; min-width: 5.2rem; }
  .fc .fc-button.fc-button-active { white-space: normal; }
  .fc .fc-daygrid-event { padding: .55rem .65rem; font-size: .88rem; border-radius: 12px; }
  .fc .fc-daygrid-day-number { font-size: .95rem; }
  .fc .fc-scrollgrid { overflow-x: auto; }
  .fc .fc-scrollgrid > div { min-width: 100%; }
  .fc .fc-daygrid-day-frame { padding: 0.45rem; }
  .fc .fc-daygrid-day-top { padding-bottom: 0.35rem; }
  .dashboard-panel { padding: 14px; }
  .hero-panel { padding: 18px; }
  .badge { font-size: .75rem; padding: .48rem .65rem; }
  button.fc-button { font-size: .85rem; padding: .55rem .8rem; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const calendarEl = document.getElementById('calendar');
  if (!calendarEl || typeof FullCalendar === 'undefined') return;

  const events = <?= json_encode($calendarEvents, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>;
  const baseUrl = (window.NFG_BASE_URL || '').replace(/\/$/, '');
  const eventDetailModalEl = document.getElementById('calendarEventDetailModal');
  const eventDetailModal = eventDetailModalEl ? new bootstrap.Modal(eventDetailModalEl) : null;

  const nfgCalendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'dayGridMonth,timeGridWeek,listWeek'
    },
    buttonText: {
      today: 'Hari Ini',
      month: 'Bulan',
      week: 'Minggu',
      day: 'Hari',
      list: 'Daftar'
    },
    locale: 'id',
    events: events,
    themeSystem: 'bootstrap',
    height: 'auto',
    eventDisplay: 'block',
    navLinks: true,
    dayMaxEvents: true,
    eventClick: function(info) {
      info.jsEvent.preventDefault();
      const ev = info.event;
      const start = ev.start ? ev.start.toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }) : '-';
      const end = ev.end ? ev.end.toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }) : '-';
      const eventTitle = document.getElementById('calendarEventTitle');
      const eventTime = document.getElementById('calendarEventTime');
      const eventStatus = document.getElementById('calendarEventStatus');
      const eventParticipants = document.getElementById('calendarEventParticipants');
      const joinButton = document.getElementById('calendarJoinButton');
      const eventDescription = document.getElementById('calendarEventDescription');
      const eventDescriptionWrapper = document.getElementById('calendarEventDescriptionWrapper');
 
      if (eventTitle) eventTitle.textContent = ev.title;
      if (eventTime) eventTime.textContent = start + (end ? ' — ' + end : '');
      if (eventStatus) eventStatus.textContent = ev.extendedProps.status_label || ev.extendedProps.status || 'Jadwal';
      if (eventParticipants) {
        const providerLabel = ev.extendedProps.provider_name ? 'Provider: ' + ev.extendedProps.provider_name : '';
        const patientLabel = ev.extendedProps.patient_name ? 'Client: ' + ev.extendedProps.patient_name : '';
        eventParticipants.textContent = [providerLabel, patientLabel].filter(Boolean).join(' · ');
      }
      if (eventDescription) {
        const description = ev.extendedProps.description || '';
        eventDescription.textContent = description || 'Tidak ada catatan tambahan.';
      }
      if (eventDescriptionWrapper) {
        eventDescriptionWrapper.style.display = ev.extendedProps.description ? 'block' : 'none';
      }
      if (joinButton) {
        const link = ev.extendedProps.meeting_link || ev.extendedProps.meetingLink || ev.extendedProps.meetinglink || null;
        if (link) {
          joinButton.href = link;
          joinButton.style.display = 'inline-block';
        } else {
          joinButton.style.display = 'none';
        }
      }
      if (eventDetailModal) eventDetailModal.show();
    }
  });
  nfgCalendar.render();
  window.NFG_CALENDAR = nfgCalendar;
});
</script>
