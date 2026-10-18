<?php
namespace App\Controllers\Admin;

use Core\Controller;
use Core\Session;
use App\Models\ProviderApplication;
use App\Repositories\UserRepository;
use App\Models\Psychologist;
use App\Models\Psychiatrist;

class ProviderApplicationController extends Controller
{
    public function index(): void
    {
        $applications = ProviderApplication::allList();
        $this->view('admin.provider_applications.index', ['applications' => $applications]);
    }

    public function show(array $params): void
    {
        $application = ProviderApplication::find((int) $params['id']);
        if (!$application) { http_response_code(404); require __DIR__ . '/../../Views/errors/404.php'; return; }
        $this->view('admin.provider_applications.show', ['application' => $application]);
    }

    public function approve(array $params): void
    {
        $this->requireCsrf();
        $application = ProviderApplication::find((int) $params['id']);
        if (!$application || $application['status'] !== 'pending') {
            Session::flash('error', 'Aplikasi tidak ditemukan atau sudah diproses.');
            $this->redirect('/admin/provider-applications');
        }

        $tempPassword = bin2hex(random_bytes(4));
        $users = new UserRepository();
        $userId = $users->create([
            'role_id' => $application['role_type'] === 'psikiater' ? 3 : 4,
            'name' => $application['name'],
            'email' => $application['email'],
            'password' => $tempPassword,
            'phone' => $application['phone'],
            'status' => 'active',
        ]);

        if ($application['role_type'] === 'psikiater') {
            Psychiatrist::create([
                'user_id' => $userId,
                'str_number' => $application['str_number'],
                'sip_number' => $application['license_number'],
                'specialization' => $application['specialization'],
                'bio' => $application['bio'],
                'years_experience' => $application['years_experience'],
                'tier' => 'associate',
            ]);
        } else {
            Psychologist::create([
                'user_id' => $userId,
                'str_number' => $application['str_number'],
                'sipp_number' => $application['license_number'],
                'specialization' => $application['specialization'],
                'bio' => $application['bio'],
                'years_experience' => $application['years_experience'],
                'tier' => 'associate',
            ]);
        }

        ProviderApplication::update($application['id'], [
            'status' => 'approved',
            'reviewed_by' => \Core\Auth::id(),
            'reviewed_at' => date('Y-m-d H:i:s'),
            'created_user_id' => $userId,
            'admin_note' => 'Akun dibuat otomatis. Password sementara: ' . $tempPassword . ' (sampaikan ke pelamar secara aman, wajib diganti saat login pertama).',
        ]);

        audit('approve', 'provider_application', 'Menyetujui aplikasi & membuat akun untuk ' . $application['email']);
        Session::flash('success', 'Aplikasi disetujui. Akun dibuat dengan password sementara: ' . $tempPassword . ' — segera sampaikan ke pelamar secara aman (WA/email terenkripsi), lalu minta mereka ganti password setelah login pertama.');
        $this->redirect('/admin/provider-applications');
    }

    public function reject(array $params): void
    {
        $this->requireCsrf();
        $application = ProviderApplication::find((int) $params['id']);
        if ($application) {
            ProviderApplication::update($application['id'], [
                'status' => 'rejected',
                'reviewed_by' => \Core\Auth::id(),
                'reviewed_at' => date('Y-m-d H:i:s'),
                'admin_note' => $this->input('admin_note'),
            ]);
            audit('reject', 'provider_application', 'Menolak aplikasi ' . $application['email']);
        }
        Session::flash('success', 'Aplikasi ditolak.');
        $this->redirect('/admin/provider-applications');
    }
}
