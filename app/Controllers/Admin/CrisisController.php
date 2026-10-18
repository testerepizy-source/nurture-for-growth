<?php
namespace App\Controllers\Admin;

use Core\Controller;
use App\Models\CrisisEvent;

class CrisisController extends Controller
{
    public function index(): void
    {
        $events = CrisisEvent::raw("SELECT ce.*, u.name AS patient_name FROM crisis_events ce
                                     JOIN patients p ON p.id = ce.patient_id JOIN users u ON u.id = p.user_id
                                     ORDER BY ce.created_at DESC");
        $this->view('admin.crisis.index', ['events' => $events]);
    }
}
