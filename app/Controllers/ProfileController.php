<?php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Session;
use App\Models\User;
use App\Models\Patient;
use App\Models\Psychologist;
use App\Models\Psychiatrist;
use App\Models\ConcernTag;
use App\Models\LoginHistory;
use App\Models\FamilyProfileAccess;
use App\Models\InformedConsent;

class ProfileController extends Controller
{
    private ?string $uploadError = null;

    public function index(): void
    {
        $user = Auth::user();
        $role = Auth::roleName();
        $familyProfiles = [];
        $activePatientId = null;
        if ($role === 'pasien') {
            FamilyProfileAccess::ensurePrimaryProfile($user['id']);
            $familyProfiles = FamilyProfileAccess::forUser($user['id']);
            $activePatientId = current_patient_id();
        }
        $patient = $role === 'pasien' && $activePatientId ? Patient::find($activePatientId) : null;
        $informedConsent = $patient ? InformedConsent::forPatient((int) $patient['id']) : null;
        $profileUserId = (int) ($patient['user_id'] ?? $user['id']);
        if ($profileUserId !== (int) $user['id']) {
            $profileUser = User::find($profileUserId);
            if ($profileUser) $user = array_merge($user, $profileUser);
        }

        $providerRecord = null;
        $providerTags = [];
        $allTags = [];
        if (in_array($role, ['psikolog', 'psikiater'], true)) {
            $providerRecord = $role === 'psikolog' ? Psychologist::byUserId($user['id']) : Psychiatrist::byUserId($user['id']);
            $providerTags = array_column(ConcernTag::tagsForProvider($user['id']), 'id');
            $allTags = ConcernTag::active();
        }

        $logins = LoginHistory::forUser($user['id']);
        $this->view('profile.index', compact('user', 'patient', 'informedConsent', 'logins', 'providerRecord', 'providerTags', 'allTags', 'role', 'familyProfiles', 'activePatientId'));
    }

    public function switchFamilyProfile(): void
    {
        $this->requireCsrf();
        if (Auth::roleName() !== 'pasien') {
            http_response_code(403);
            return;
        }

        $patientId = (int) $this->input('patient_id');
        if (!$patientId || !FamilyProfileAccess::canAccess(Auth::id(), $patientId)) {
            Session::flash('error', 'Profil keluarga tidak memiliki akses.');
            $this->redirect('/profile');
            return;
        }

        Session::set('active_patient_id', $patientId);
        Session::flash('success', 'Profil aktif berhasil diganti.');
        $this->redirect('/dashboard');
    }

    public function addFamilyProfile(): void
    {
        $this->requireCsrf();
        if (Auth::roleName() !== 'pasien') {
            http_response_code(403);
            return;
        }

        $name = $this->input('family_name');
        $relationship = $this->input('relationship', 'family');
        $canViewMedicalData = $this->input('can_view_medical_data', '1') === '1' ? 1 : 0;
        if (!$name) {
            Session::flash('error', 'Nama anggota keluarga wajib diisi.');
            $this->redirect('/profile');
            return;
        }

        $role = \App\Models\Role::findBy('name', 'pasien');
        if (!$role) {
            Session::flash('error', 'Role client belum tersedia.');
            $this->redirect('/profile');
            return;
        }

        $temporaryEmail = 'family-' . Auth::id() . '-' . bin2hex(random_bytes(5)) . '@nfg.local';
        $memberUserId = User::create([
            'role_id' => $role['id'],
            'name' => $name,
            'email' => $temporaryEmail,
            'password' => password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT),
            'status' => 'active',
        ]);
        $patientId = Patient::create([
            'user_id' => $memberUserId,
            'medical_record_number' => 'MRN-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(3))),
            'privacy_pref' => 'private',
        ]);
        FamilyProfileAccess::create([
            'user_id' => Auth::id(),
            'patient_id' => $patientId,
            'relationship' => $relationship,
            'is_primary' => 0,
            'can_manage' => 1,
            'can_view_medical_data' => $canViewMedicalData,
        ]);

        audit('create', 'family_profile', 'Menambahkan profil keluarga ' . $name);
        Session::flash('success', 'Profil keluarga berhasil ditambahkan.');
        $this->redirect('/profile');
    }

    public function update(): void
    {
        $this->requireCsrf();
        $data = [
            'name' => $this->input('name'),
            'phone' => $this->input('phone'),
            'gender' => $this->input('gender') ?: null,
            'birth_date' => $this->input('birth_date') ?: null,
            'address' => $this->input('address'),
        ];

        $activePatient = Auth::roleName() === 'pasien' ? Patient::find(current_patient_id()) : null;
        $profileUserId = (int) ($activePatient['user_id'] ?? Auth::id());
        $photoPath = $this->handlePhotoUpload($profileUserId);
        if ($photoPath) {
            $data['photo'] = $photoPath;
        } elseif ($this->uploadError) {
            Session::flash('error', $this->uploadError);
            $this->redirect('/profile');
        }

        User::update($profileUserId, $data);

        if (Auth::roleName() === 'pasien') {
            $patient = Patient::find(current_patient_id());
            if ($patient) {
                Patient::update($patient['id'], [
                    'occupation' => $this->input('occupation'),
                    'education' => $this->input('education'),
                    'emergency_contact_name' => $this->input('emergency_contact_name'),
                    'emergency_contact_phone' => $this->input('emergency_contact_phone'),
                    'emergency_contact_relation' => $this->input('emergency_contact_relation'),
                    'medical_history' => $this->input('medical_history'),
                    'allergy_history' => $this->input('allergy_history'),
                    'privacy_pref' => $this->input('privacy_pref', 'private'),
                ]);
            }
        }

        if (in_array(Auth::roleName(), ['psikolog', 'psikiater'], true)) {
            $providerData = [
                'specialization'   => $this->input('specialization'),
                'bio'              => $this->input('bio'),
                'years_experience' => (int) $this->input('years_experience', 0),
                'facebook_url'     => $this->input('facebook_url') ?: null,
                'twitter_url'      => $this->input('twitter_url') ?: null,
                'linkedin_url'     => $this->input('linkedin_url') ?: null,
                'instagram_url'    => $this->input('instagram_url') ?: null,
                'whatsapp_number'  => $this->input('whatsapp_number') ?: null,
            ];
            if (Auth::roleName() === 'psikolog') {
                $record = Psychologist::byUserId(Auth::id());
                if ($record) Psychologist::update($record['id'], $providerData);
            } else {
                $record = Psychiatrist::byUserId(Auth::id());
                if ($record) Psychiatrist::update($record['id'], $providerData);
            }
            ConcernTag::syncForProvider(Auth::id(), $_POST['tags'] ?? []);
        }

        audit('update', 'profile', 'Memperbarui profil');
        Session::flash('success', 'Profil berhasil diperbarui.');
        $this->redirect('/profile');
    }

    /**
     * Tangani unggah foto profil. Mengembalikan path relatif (mis. "assets/uploads/avatars/xxx.jpg")
     * jika berhasil, atau null jika tidak ada file diunggah / gagal (pesan error disimpan di $uploadError).
     */
    private function handlePhotoUpload(int $profileUserId): ?string
    {
        if (empty($_FILES['photo']) || $_FILES['photo']['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        $file = $_FILES['photo'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->uploadError = 'Gagal mengunggah foto. Silakan coba lagi.';
            return null;
        }
        if ($file['size'] > 2 * 1024 * 1024) {
            $this->uploadError = 'Ukuran foto maksimal 2MB.';
            return null;
        }

        $allowedMime = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!isset($allowedMime[$mime])) {
            $this->uploadError = 'Format foto harus JPG, PNG, atau WEBP.';
            return null;
        }

        $dir = __DIR__ . '/../../public/assets/uploads/avatars';
        if (!is_dir($dir)) mkdir($dir, 0775, true);

        $filename = 'user-' . $profileUserId . '-' . bin2hex(random_bytes(6)) . '.' . $allowedMime[$mime];
        $destination = $dir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $this->uploadError = 'Gagal menyimpan foto ke server.';
            return null;
        }

        // Hapus foto lama HANYA jika sebelumnya juga hasil upload (jangan hapus avatar ilustrasi bawaan seeder).
        $oldPhoto = User::find($profileUserId)['photo'] ?? null;
        if ($oldPhoto && str_starts_with($oldPhoto, 'assets/uploads/avatars/')) {
            $oldFile = __DIR__ . '/../../public/' . $oldPhoto;
            if (is_file($oldFile)) @unlink($oldFile);
        }

        return 'assets/uploads/avatars/' . $filename;
    }

    public function downloadData(): void
    {
        $user = Auth::user();
        $patient = Auth::roleName() === 'pasien' ? Patient::byUserId($user['id']) : null;
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="my-data-' . $user['id'] . '.json"');
        unset($user['password'], $user['remember_token']);
        echo json_encode(['user' => $user, 'patient_record' => $patient], JSON_PRETTY_PRINT);
        exit;
    }

    public function deleteAccount(): void
    {
        $this->requireCsrf();
        $id = Auth::id();
        audit('delete', 'profile', 'User menghapus akunnya sendiri');
        User::delete($id);
        Auth::logout();
        $this->redirect('/login');
    }
}
