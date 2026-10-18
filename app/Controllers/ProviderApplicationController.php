<?php
namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Models\ProviderApplication;

class ProviderApplicationController extends Controller
{
    public function show(): void
    {
        $this->view('provider_application.create', [], 'public');
    }

    public function store(): void
    {
        $this->requireCsrf();

        $cvPath = $this->handleFileUpload('cv_file', 'cv');
        $certPath = $this->handleFileUpload('certificate_file', 'certificate');

        ProviderApplication::create([
            'name' => $this->input('name'),
            'email' => $this->input('email'),
            'phone' => $this->input('phone'),
            'role_type' => $this->input('role_type', 'psikolog'),
            'str_number' => $this->input('str_number'),
            'license_number' => $this->input('license_number'),
            'specialization' => $this->input('specialization'),
            'years_experience' => (int) $this->input('years_experience', 0),
            'bio' => $this->input('bio'),
            'cv_file_path' => $cvPath,
            'certificate_file_path' => $certPath,
            'status' => 'pending',
        ]);

        Session::flash('success', 'Aplikasi Anda berhasil dikirim. Tim kami akan meninjau dan menghubungi Anda melalui email.');
        $this->redirect('/join-us');
    }

    private ?string $uploadError = null;

    private function handleFileUpload(string $field, string $prefix): ?string
    {
        if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        $file = $_FILES[$field];
        if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 5 * 1024 * 1024) {
            return null;
        }

        $allowedMime = ['application/pdf' => 'pdf', 'image/jpeg' => 'jpg', 'image/png' => 'png'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!isset($allowedMime[$mime])) return null;

        $dir = __DIR__ . '/../../public/assets/uploads/applications';
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        $filename = $prefix . '-' . bin2hex(random_bytes(6)) . '.' . $allowedMime[$mime];
        $destination = $dir . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $destination)) return null;

        return 'assets/uploads/applications/' . $filename;
    }
}
