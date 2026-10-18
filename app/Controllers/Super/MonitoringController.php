<?php
namespace App\Controllers\Super;

use Core\Controller;
use App\Models\MonitoringLog;
use Core\Database;

class MonitoringController extends Controller
{
    public function index(): void
    {
        $dbStatus = 'unknown';
        try { Database::connection(); $dbStatus = 'connected'; } catch (\Throwable $e) { $dbStatus = 'error'; }

        $snapshot = [
            'php_version' => PHP_VERSION,
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'disk_free_gb' => @disk_free_space('.') ? round(disk_free_space('.') / 1024 / 1024 / 1024, 2) : null,
            'db_status' => $dbStatus,
            'server_time' => date('Y-m-d H:i:s'),
        ];

        foreach ($snapshot as $metric => $value) {
            MonitoringLog::create(['metric' => $metric, 'value' => (string) $value]);
        }

        $recent = MonitoringLog::raw('SELECT * FROM monitoring_logs ORDER BY recorded_at DESC LIMIT 30');
        $this->view('super.monitoring.index', compact('snapshot', 'recent'));
    }
}
