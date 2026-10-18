<?php
namespace App\Controllers\Admin;

use Core\Controller;
use App\Models\Consultation;
use App\Models\FollowUpPlan;

class ConsultationController extends Controller
{
    public function index(): void
    {
        $date = trim((string) $this->input('date', ''));
        $status = trim((string) $this->input('status', ''));

        $sql = "SELECT c.*, u.name AS provider_name, pu.name AS patient_name
                FROM consultations c
                JOIN users u ON u.id = c.provider_id
                JOIN patients p ON p.id = c.patient_id
                JOIN users pu ON pu.id = p.user_id
                WHERE 1 = 1";
        $params = [];
        if ($date !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $sql .= ' AND DATE(c.schedule_at) = ?';
            $params[] = $date;
        }
        if ($status !== '') {
            $sql .= ' AND c.status = ?';
            $params[] = $status;
        }
        $sql .= ' ORDER BY c.schedule_at DESC';

        $list = Consultation::raw($sql, $params);

        $this->view('admin.consultations.index', ['list' => $list, 'date' => $date, 'status' => $status]);
    }

    public function followUps(): void
    {
        $plans = FollowUpPlan::recent(500);
        $this->view('admin.followups.index', ['plans' => $plans]);
    }
}