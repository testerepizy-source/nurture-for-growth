<?php
namespace App\Controllers\Admin;

use Core\Controller;
use App\Models\AuditLog;
use App\Models\CrisisEvent;

class AuditLogController extends Controller
{
    public function index(): void
    {
        $logs = AuditLog::recent(300);
        $this->view('admin.audit.index', ['logs' => $logs]);
    }
}
