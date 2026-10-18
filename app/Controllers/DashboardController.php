<?php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use App\Models\User;
use App\Models\Patient;
use App\Models\AssessmentResult;
use App\Models\Mood;
use App\Models\Consultation;
use App\Models\Article;
use App\Models\Notification;
use App\Models\CrisisEvent;
use App\Models\PaymentTransaction;
use App\Models\FollowUpPlan;
use App\Services\MoodService;

class DashboardController extends Controller
{
    public function index(): void
    {
        $role = Auth::roleName();
        $user = Auth::user();

        if (!$role || !$user) {
            $this->redirect('/login');
            return;
        }

        if ($role === 'pasien') {
            $this->patientDashboard($user);
        } elseif (in_array($role, ['psikolog', 'psikiater'], true)) {
            $this->providerDashboard($user, $role);
        } else {
            $this->adminDashboard($role);
        }
    }

    private function patientDashboard(array $user): void
    {
        $pid = current_patient_id();
        $moodService = new MoodService();
        $weekly = $moodService->chartData($pid, 'weekly');
        $monthly = $moodService->chartData($pid, 'monthly');

        $stats = [
            'total_assessments' => AssessmentResult::count('patient_id = ?', [$pid]),
            'total_consultations' => Consultation::count('patient_id = ?', [$pid]),
            'upcoming' => Consultation::raw("SELECT c.*, u.name AS provider_name FROM consultations c JOIN users u ON u.id = c.provider_id WHERE c.patient_id = ? AND c.status = 'approved' AND c.schedule_at >= NOW() ORDER BY c.schedule_at ASC LIMIT 3", [$pid]),
        ];

        $recentArticles = array_slice(Article::published(), 0, 5);
        $notifications = Notification::forUser($user['id'], 5);
        $myPrescriptions = array_slice(\App\Models\Prescription::forPatient($pid), 0, 3);
        $myFollowUps = array_slice(FollowUpPlan::forPatient($pid), 0, 3);

        $this->view('dashboard.patient', compact('stats', 'weekly', 'monthly', 'recentArticles', 'notifications', 'myPrescriptions', 'myFollowUps'));
    }

    private function providerDashboard(array $user, string $role): void
    {
        $patients = Patient::assignedTo($user['id'], $role);
        $today = date('Y-m-d');

        $stats = [
            'total_patients' => count($patients),
            'today_bookings' => Consultation::count('provider_id = ? AND DATE(schedule_at) = ?', [$user['id'], $today]),
            'pending_approval' => Consultation::count('provider_id = ? AND status = ?', [$user['id'], 'pending']),
            'today_revenue' => (int) (Consultation::rawOne("SELECT COALESCE(SUM(price_amount),0) AS t FROM consultations WHERE payment_status = 'paid' AND provider_id = ? AND DATE(schedule_at) = CURDATE()", [$user['id']])['t'] ?? 0),
            'total_revenue' => (int) (Consultation::rawOne("SELECT COALESCE(SUM(price_amount),0) AS t FROM consultations WHERE payment_status = 'paid' AND provider_id = ?", [$user['id']])['t'] ?? 0),
            'total_followups' => FollowUpPlan::count('created_by = ?', [$user['id']]),
        ];

        $monthlyRevenueRows = Consultation::raw(
            "SELECT MONTH(schedule_at) AS month, SUM(price_amount) AS total
             FROM consultations
             WHERE payment_status = 'paid' AND provider_id = ? AND YEAR(schedule_at) = YEAR(CURDATE())
             GROUP BY month ORDER BY month ASC",
            [$user['id']]
        );
        $revenueLabels = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        $revenueValues = array_fill(0, 12, 0);
        foreach ($monthlyRevenueRows as $row) {
            $monthIndex = (int) $row['month'] - 1;
            if ($monthIndex >= 0 && $monthIndex < 12) {
                $revenueValues[$monthIndex] = (int) $row['total'];
            }
        }

        $upcoming = Consultation::forProvider($user['id'], 'approved');
        $upcoming = array_slice($upcoming, 0, 5);
        $pendingList = Consultation::forProvider($user['id'], 'pending');

        $crisisEvents = CrisisEvent::raw("SELECT ce.*, u.name AS patient_name FROM crisis_events ce
                                           JOIN patients p ON p.id = ce.patient_id JOIN users u ON u.id = p.user_id
                                           WHERE (p.assigned_psychologist_id = ? OR p.assigned_psychiatrist_id = ?) AND ce.is_flagged = 1
                                           ORDER BY ce.created_at DESC LIMIT 5", [$user['id'], $user['id']]);

        $this->view('dashboard.provider', compact('stats', 'patients', 'upcoming', 'pendingList', 'crisisEvents', 'role', 'revenueLabels', 'revenueValues'));
    }

    private function adminDashboard(string $role): void
    {
        $stats = [
            'total_users' => User::count(),
            'total_patients' => Patient::count(),
            'today_consultations' => Consultation::count('DATE(schedule_at) = ?', [date('Y-m-d')]),
            'total_crisis_flags' => CrisisEvent::count('is_flagged = 1'),
            'total_followups' => FollowUpPlan::count(),
        ];
        $recentAudit = \App\Models\AuditLog::recent(10);

        // Analitik Bisnis (digabung ke Dashboard agar tidak ada 2 halaman terpisah untuk info serupa)
        $revenueRows = Consultation::raw("
            SELECT MONTH(schedule_at) AS month, SUM(price_amount) AS total
            FROM consultations
            WHERE payment_status = 'paid' AND YEAR(schedule_at) = YEAR(CURDATE())
            GROUP BY month ORDER BY month ASC");

        $revenueLabels = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        $revenueValues = array_fill(0, 12, 0);
        foreach ($revenueRows as $row) {
            $monthIndex = (int) $row['month'] - 1;
            if ($monthIndex >= 0 && $monthIndex < 12) {
                $revenueValues[$monthIndex] = (int) $row['total'];
            }
        }

        $funnel = [
            'total'     => Consultation::count('1=1'),
            'approved'  => Consultation::count("status IN ('approved','completed')"),
            'completed' => Consultation::count("status = 'completed'"),
            'cancelled' => Consultation::count("status IN ('cancelled','rejected','no_show')"),
        ];

        $retentionRows = Consultation::raw("SELECT patient_id, COUNT(*) AS total FROM consultations GROUP BY patient_id");
        $repeatPatients = count(array_filter($retentionRows, fn($r) => $r['total'] > 1));
        $oneTimePatients = count($retentionRows) - $repeatPatients;

        $providerPerformance = Consultation::raw("
            SELECT u.name, COUNT(c.id) AS total_sessions,
                   SUM(CASE WHEN c.status = 'completed' THEN 1 ELSE 0 END) AS completed_sessions,
                   COALESCE(SUM(CASE WHEN c.payment_status = 'paid' THEN c.price_amount ELSE 0 END),0) AS revenue
            FROM consultations c
            JOIN users u ON u.id = c.provider_id
            GROUP BY c.provider_id ORDER BY revenue DESC");

        $bizStats = [
            'today_revenue' => (int) (Consultation::rawOne("SELECT COALESCE(SUM(price_amount),0) AS t FROM consultations WHERE payment_status = 'paid' AND DATE(schedule_at) = CURDATE()")['t'] ?? 0),
            'total_revenue_all_time' => (int) (Consultation::rawOne("SELECT COALESCE(SUM(price_amount),0) AS t FROM consultations WHERE payment_status = 'paid'")['t'] ?? 0),
        ];

        $this->view('dashboard.admin', compact(
            'stats', 'recentAudit', 'role',
            'revenueLabels', 'revenueValues', 'funnel', 'repeatPatients', 'oneTimePatients',
            'providerPerformance', 'bizStats'
        ));
    }
}
