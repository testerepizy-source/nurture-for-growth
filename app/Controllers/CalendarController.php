<?php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use App\Models\Consultation;
use App\Models\CalendarSyncLog;
use App\Models\ConsultationReschedule;
use App\Models\Patient;
use App\Models\Psychologist;
use App\Models\Psychiatrist;
use App\Models\User;
use App\Models\VideoSession;
use App\Models\Notification;
use App\Services\GoogleMeetService;

/**
 * Sinkronisasi kalender berbasis LINK (bukan OAuth two-way sync).
 *
 * CATATAN JUJUR: sinkronisasi dua arah sungguhan ke Google Calendar API
 * memerlukan kredensial OAuth resmi (Client ID/Secret dari Google Cloud
 * Console) yang belum dikonfigurasi di sistem ini (lihat Super Admin >
 * Settings > Integrasi Google, saat ini masih placeholder kosong). Yang
 * diimplementasikan di sini adalah event kalender internal berbasis data
 * konsultasi yang otomatis dibuat untuk client dan provider. Ini bukan sync
 * otomatis dua arah ke Google Calendar, tapi event jadwal akan muncul di
 * kalender aplikasi dan dapat dibuka langsung sebagai meeting.
 */
class CalendarController extends Controller
{
    public function index(): void
    {
        $role = Auth::roleName();
        $user = Auth::user();

        if ($role === 'pasien') {
           $patientId = current_patient_id();
           $consultations = Consultation::forPatient($patientId);
           $patients = [];
           $providers = [];
        } elseif (in_array($role, ['psikolog', 'psikiater'], true)) {
           $consultations = Consultation::forProvider($user['id']);
           $patients = Patient::assignedTo($user['id'], $role);
           $providers = [];
        } else {
           $consultations = Consultation::raw("SELECT c.*, u.name AS provider_name, pu.name AS patient_name FROM consultations c
                                              JOIN users u ON u.id = c.provider_id
                                              JOIN patients p ON p.id = c.patient_id
                                              JOIN users pu ON pu.id = p.user_id
                                              ORDER BY c.schedule_at ASC");
           $patients = Patient::allWithUser();
           $providers = array_merge(
               array_map(fn($p) => array_merge($p, ['provider_type' => 'Psikolog']), Psychologist::allWithUser()),
               array_map(fn($p) => array_merge($p, ['provider_type' => 'Psikiater']), Psychiatrist::allWithUser())
           );
        }
 
        $consultations = $this->deduplicateConsultations($consultations);
 
        $events = [];
        $now = time();
        foreach ($consultations as $consultation) {
            $duplicateOriginalId = ConsultationReschedule::findOriginalConsultationIdForDuplicateTarget($consultation);
            if (!empty($duplicateOriginalId) && $duplicateOriginalId !== (int) $consultation['id']) {
                continue;
            }

            $scheduleAt = ConsultationReschedule::getEffectiveScheduleAt($consultation['id'], $consultation['schedule_at']);
            $scheduleTime = strtotime($scheduleAt);
            if (!$scheduleTime) {
                continue;
            }
            $endTime = $scheduleTime + ((int) $consultation['duration_minutes'] * 60);

            // Hide sessions that have completely passed; keep today's ongoing sessions visible.
            if ($endTime < $now) {
                continue;
            }

            if ($role === 'pasien') {
                $title = 'Konseling dengan ' . $consultation['provider_name'];
            } else {
                $title = trim((string) ($consultation['reason'] ?? '')) ?: 'Konseling: ' . ($consultation['patient_name'] ?? $consultation['provider_name'] ?? 'Sesi');
            }

            $start = date('c', $scheduleTime);
            $end = date('c', $endTime);
            $status = $consultation['status'] ?? 'scheduled';
            $statusKey = match ($status) {
                'pending' => 'pending',
                'completed' => 'completed',
                'cancelled', 'rejected', 'no_show' => 'cancelled',
                default => 'approved',
            };
            $statusLabel = match ($statusKey) {
                'pending' => 'Pending',
                'completed' => 'Selesai',
                'cancelled' => 'Dibatalkan',
                default => 'Disetujui',
            };

            $events[] = [
                'id' => $consultation['id'],
                'title' => $title,
                'start' => $start,
                'end' => $end,
                'status_label' => $statusLabel,
                'status' => $status,
                'status_key' => $statusKey,
                'description' => trim((string) ($consultation['reason'] ?? '')),
                'provider_name' => $consultation['provider_name'] ?? '',
                'patient_name' => $consultation['patient_name'] ?? '',
                'meeting_link' => $consultation['meeting_link'] ?? null,
                'start_readable' => format_date($scheduleAt, 'd M Y H:i'),
                'end_readable' => format_date(date('Y-m-d H:i:s', $endTime), 'H:i'),
            ];
        }

        $statusColors = [
            'approved' => ['#0e7c86', '#0e7c86'],
            'pending' => ['#d97706', '#d97706'],
            'cancelled' => ['#dc3f67', '#dc3f67'],
            'completed' => ['#667085', '#667085'],
        ];
        $calendarEvents = array_map(function ($event) use ($statusColors) {
            [$backgroundColor, $borderColor] = $statusColors[$event['status_key']] ?? $statusColors['approved'];
            return array_merge($event, [
                'backgroundColor' => $backgroundColor,
                'borderColor' => $borderColor,
                'textColor' => '#fff',
                'classNames' => ['calendar-status-' . $event['status_key']],
            ]);
        }, $events);

        $calendarEvents = $this->deduplicateCalendarEvents($calendarEvents);

        $this->view('calendar.index', compact('events', 'calendarEvents', 'role', 'patients', 'providers'));
    }
 
    private function deduplicateConsultations(array $consultations): array
    {
        $unique = [];
        foreach ($consultations as $consultation) {
            $key = implode('|', [
                $consultation['schedule_at'] ?? '',
                $consultation['provider_id'] ?? '',
                $consultation['patient_id'] ?? '',
                $consultation['duration_minutes'] ?? '',
            ]);
 
            if (!isset($unique[$key])) {
                $unique[$key] = $consultation;
                continue;
            }
 
            $unique[$key] = $this->pickPreferredConsultation($unique[$key], $consultation);
        }
 
        return array_values($unique);
    }
 
    private function pickPreferredConsultation(array $first, array $second): array
    {
        $priority = [
            'approved' => 6,
            'completed' => 5,
            'pending' => 4,
            'scheduled' => 3,
            'rejected' => 2,
            'cancelled' => 1,
            'no_show' => 0,
        ];
 
        $firstScore = $priority[$first['status'] ?? 'pending'] ?? 0;
        $secondScore = $priority[$second['status'] ?? 'pending'] ?? 0;
 
        if ($firstScore !== $secondScore) {
            return $firstScore > $secondScore ? $first : $second;
        }
 
        $firstCreated = strtotime($first['created_at'] ?? '') ?: 0;
        $secondCreated = strtotime($second['created_at'] ?? '') ?: 0;
        if ($secondCreated !== $firstCreated) {
            return $secondCreated > $firstCreated ? $second : $first;
        }
 
        return $second;
    }
 
    public function storeEvent(): void
    {
        $this->requireCsrf();

        $role = Auth::roleName();
        if (!in_array($role, ['admin', 'super_admin', 'psikolog', 'psikiater'], true)) {
            http_response_code(403);
            require __DIR__ . '/../Views/errors/403.php';
            return;
        }

        $providerId = $role === 'admin' || $role === 'super_admin'
            ? (int) $this->input('provider_id')
            : Auth::id();
        $patientId = (int) $this->input('patient_id');
        $scheduleAt = $this->input('schedule_at');
        $durationMinutes = max(30, (int) $this->input('duration_minutes', 60));
        $title = $this->input('title', 'Jadwal Konseling');
        $description = $this->input('description', '');

        if (!$providerId || !$patientId || !$scheduleAt) {
            Session::flash('error', 'Psikolog, client, dan waktu jadwal wajib diisi.');
            $this->redirect('/calendar');
            return;
        }

        $patient = Patient::find($patientId);
        if (!$patient) {
            Session::flash('error', 'Client tidak ditemukan.');
            $this->redirect('/calendar');
            return;
        }

        $consultationId = Consultation::create([
            'patient_id' => $patientId,
            'provider_id' => $providerId,
            'schedule_at' => $scheduleAt,
            'duration_minutes' => $durationMinutes,
            'method' => 'video',
            'session_type' => 'individual',
            'reason' => $title,
            'status' => 'approved',
            'price_amount' => 0,
            'payment_method' => 'free',
            'payment_status' => 'paid',
        ]);

        $providerUser = User::find($providerId);
        $meetingLink = null;
        if ($consultationId && $patient && $providerUser && !empty($patient['email']) && !empty($providerUser['email'])) {
            $meetingLink = GoogleMeetService::createMeeting([
                'id' => $consultationId,
                'schedule_at' => $scheduleAt,
                'duration_minutes' => $durationMinutes,
            ], $patient, $providerUser);
        }
        $meetingLink = $meetingLink ?: base_url('/consultation/' . $consultationId . '/room');
        Consultation::update($consultationId, ['meeting_link' => $meetingLink]);
        if (!VideoSession::byConsultation($consultationId)) {
            VideoSession::create(['consultation_id' => $consultationId, 'room_code' => bin2hex(random_bytes(6)), 'status' => 'waiting']);
        }

        Notification::create([
            'user_id' => $providerId,
            'title' => 'Jadwal Baru: ' . $title,
            'message' => 'Anda memiliki jadwal baru pada ' . format_date($scheduleAt, 'd M Y H:i'),
            'type' => 'calendar',
            'url' => base_url('/calendar'),
        ]);

        Notification::create([
            'user_id' => (int) Patient::find($patientId)['user_id'],
            'title' => 'Jadwal Konseling Baru',
            'message' => 'Anda dijadwalkan pada ' . format_date($scheduleAt, 'd M Y H:i') . ' oleh NFG.',
            'type' => 'calendar',
            'url' => base_url('/calendar'),
        ]);

        audit('create', 'consultation', 'Jadwal baru dibuat oleh ' . Auth::user()['name'] . ' untuk client ID ' . $patientId);
        Session::flash('success', 'Jadwal berhasil ditambahkan ke kalender.');
        $this->redirect('/calendar');
    }

    private function deduplicateCalendarEvents(array $events): array
    {
        $unique = [];
        foreach ($events as $event) {
            $key = implode('|', [
                $event['id'] ?? '',
                $event['start'] ?? '',
                $event['end'] ?? '',
            ]);
            if (!isset($unique[$key])) {
                $unique[$key] = $event;
            }
        }

        return array_values($unique);
    }


    private function canAccess(array $consultation): bool
    {
        if (Auth::roleName() === 'pasien') return (int) $consultation['patient_id'] === current_patient_id();
        return (int) $consultation['provider_id'] === Auth::id() || in_array(Auth::roleName(), ['admin', 'super_admin'], true);
    }
}
