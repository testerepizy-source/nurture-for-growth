<?php
namespace App\Controllers\Admin;

use Core\Controller;
use App\Models\Consultation;
use App\Models\Patient;

class AnalyticsController extends Controller
{
    public function index(): void
    {
        // Revenue per bulan (12 bulan terakhir, dari sesi yang sudah dibayar)
        $revenueRows = Consultation::raw("
            SELECT DATE_FORMAT(schedule_at, '%Y-%m') AS ym, SUM(price_amount) AS total
            FROM consultations
            WHERE payment_status = 'paid' AND schedule_at >= (CURDATE() - INTERVAL 12 MONTH)
            GROUP BY ym ORDER BY ym ASC");
        $revenueLabels = array_column($revenueRows, 'ym');
        $revenueValues = array_map('intval', array_column($revenueRows, 'total'));

        // Funnel konversi booking
        $funnel = [
            'total'     => Consultation::count('1=1'),
            'approved'  => Consultation::count("status IN ('approved','completed')"),
            'completed' => Consultation::count("status = 'completed'"),
            'cancelled' => Consultation::count("status IN ('cancelled','rejected','no_show')"),
        ];

        // Retensi client: client dengan >1 sesi vs hanya 1 sesi
        $retentionRows = Consultation::raw("
            SELECT patient_id, COUNT(*) AS total FROM consultations GROUP BY patient_id");
        $repeatPatients = count(array_filter($retentionRows, fn($r) => $r['total'] > 1));
        $oneTimePatients = count($retentionRows) - $repeatPatients;

        // Performa per provider
        $providerPerformance = Consultation::raw("
            SELECT u.name, COUNT(c.id) AS total_sessions,
                   SUM(CASE WHEN c.status = 'completed' THEN 1 ELSE 0 END) AS completed_sessions,
                   COALESCE(SUM(CASE WHEN c.payment_status = 'paid' THEN c.price_amount ELSE 0 END),0) AS revenue
            FROM consultations c
            JOIN users u ON u.id = c.provider_id
            GROUP BY c.provider_id ORDER BY revenue DESC");

        $stats = [
            'total_patients' => Patient::count(),
            'total_revenue_all_time' => (int) (Consultation::rawOne("SELECT COALESCE(SUM(price_amount),0) AS t FROM consultations WHERE payment_status = 'paid'")['t'] ?? 0),
        ];

        $this->view('admin.analytics.index', compact(
            'revenueLabels', 'revenueValues', 'funnel', 'repeatPatients', 'oneTimePatients',
            'providerPerformance', 'stats'
        ));
    }
}
