<?php
namespace App\Controllers\Super;

use Core\Controller;
use Core\Session;
use Core\Auth;
use App\Models\BackupLog;

class BackupController extends Controller
{
    public function index(): void
    {
        $logs = BackupLog::recent();
        $this->view('super.backup.index', ['logs' => $logs]);
    }

    /**
     * Mencoba menjalankan mysqldump jika binary tersedia di server.
     * Jika tidak tersedia (umum pada shared hosting / lingkungan terbatas),
     * dicatat sebagai gagal dengan pesan yang jelas - bukan pura-pura berhasil.
     */
    public function runDatabaseBackup(): void
    {
        $this->requireCsrf();
        $config = require __DIR__ . '/../../../config/config.php';
        $db = $config['db'];
        $dir = __DIR__ . '/../../../storage/backups';
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        $file = $dir . '/backup-' . date('Ymd-His') . '.sql';

        $status = 'failed';
        $path = null;

        if (function_exists('shell_exec')) {
            $cmd = sprintf(
                'mysqldump --host=%s --port=%s --user=%s --password=%s %s > %s 2>&1',
                escapeshellarg($db['host']), escapeshellarg($db['port']), escapeshellarg($db['user']),
                escapeshellarg($db['pass']), escapeshellarg($db['name']), escapeshellarg($file)
            );
            @shell_exec($cmd);
            if (file_exists($file) && filesize($file) > 0) {
                $status = 'success';
                $path = $file;
            }
        }

        BackupLog::create([
            'type' => 'database',
            'file_path' => $path,
            'status' => $status,
            'triggered_by' => Auth::id(),
            'is_auto' => 0,
        ]);

        audit($status === 'success' ? 'export' : 'error', 'backup', 'Backup database: ' . $status);

        if ($status === 'success') {
            Session::flash('success', 'Backup database berhasil dibuat: ' . basename($file));
        } else {
            Session::flash('error', 'Backup database gagal. Pastikan binary "mysqldump" tersedia dan dapat diakses oleh PHP (shell_exec) di server Anda.');
        }
        $this->redirect('/super/backup');
    }
}
