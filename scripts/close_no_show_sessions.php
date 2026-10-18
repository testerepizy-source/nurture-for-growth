<?php
/**
 * Close video consultation rooms when the patient has not joined within 30 minutes.
 * Schedule every 5 minutes, e.g. `php scripts/close_no_show_sessions.php`.
 */
require __DIR__ . '/../core/autoload.php';

use App\Models\Consultation;
use App\Models\VideoSession;
use Core\Logger;

$sessions = VideoSession::raw(
    "SELECT vs.id AS video_session_id, c.id AS consultation_id
     FROM video_sessions vs
     JOIN consultations c ON c.id = vs.consultation_id
     JOIN patients p ON p.id = c.patient_id
     WHERE c.method = 'video'
       AND c.status IN ('approved', 'rescheduled')
       AND vs.status IN ('waiting', 'ongoing')
       AND c.meeting_link LIKE '%/room%'
       AND c.schedule_at <= DATE_SUB(NOW(), INTERVAL 30 MINUTE)
       AND NOT EXISTS (
           SELECT 1 FROM session_attendance sa
           WHERE sa.video_session_id = vs.id AND sa.user_id = p.user_id
       )"
);

foreach ($sessions as $session) {
    VideoSession::update((int) $session['video_session_id'], [
        'status' => 'ended',
        'ended_at' => date('Y-m-d H:i:s'),
        'session_summary' => 'Sesi ditutup otomatis: pasien tidak hadir dalam 30 menit sejak jadwal dimulai.',
    ]);
    Consultation::update((int) $session['consultation_id'], ['status' => 'no_show']);
}

Logger::info('No-show session check completed. Closed rooms: ' . count($sessions));
echo 'Closed rooms: ' . count($sessions) . PHP_EOL;
