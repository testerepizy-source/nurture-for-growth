<?php
namespace App\Controllers\Admin;

use Core\Controller;
use Core\Session;
use App\Repositories\UserRepository;
use App\Models\Patient;
use App\Models\Psychologist;
use App\Models\Psychiatrist;
use PDOException;

class PatientController extends Controller
{
    private UserRepository $users;

    public function __construct() { $this->users = new UserRepository(); }

    public function index(): void
    {
        $patients = Patient::allWithUser();
        $this->view('admin.patients.index', ['patients' => $patients]);
    }

    public function create(): void
    {
        $this->view('admin.patients.form', [
            'patient' => null,
            'psychologists' => Psychologist::allWithUser(),
            'psychiatrists' => Psychiatrist::allWithUser(),
        ]);
    }

    public function store(): void
    {
        $this->requireCsrf();
        $email = strtolower(trim((string) $this->input('email')));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flashCreate('error', 'Alamat email tidak valid.');
        }
        $this->validatePatientInput();
        if ($this->users->findByEmail($email)) {
            $this->flashCreate('error', 'Email tersebut sudah digunakan oleh akun lain.');
        }

        try {
            $userId = $this->users->create([
                'role_id'    => 5, // client
                'name'       => $this->input('name'),
                'email'      => $email,
                'password'   => $this->input('password'),
                'phone'      => $this->input('phone'),
                'gender'     => $this->genderValue(),
                'birth_date' => $this->input('birth_date') ?: null,
                'address'    => $this->input('address'),
                'status'     => $this->input('status', 'active'),
            ]);

            Patient::create(array_merge([
                'user_id'              => $userId,
                'medical_record_number' => 'MRN-' . date('Y') . '-' . str_pad((string) $userId, 4, '0', STR_PAD_LEFT),
            ], $this->patientFields()));

            audit('create', 'patient', 'Admin menambahkan client: ' . $email);
            Session::flash('success', 'Client berhasil ditambahkan.');
            $this->redirect('/admin/patients');
        } catch (PDOException $e) {
            $this->flashCreate('error', 'Email sudah digunakan oleh akun lain.');
        }
    }

    public function edit(array $params): void
    {
        $patient = Patient::detailWithUser((int) $params['id']);
        if (!$patient) {
            $this->redirect('/admin/patients');
        }
        $this->view('admin.patients.form', [
            'patient' => $patient,
            'psychologists' => Psychologist::allWithUser(),
            'psychiatrists' => Psychiatrist::allWithUser(),
        ]);
    }

    public function update(array $params): void
    {
        $this->requireCsrf();
        $patient = Patient::find((int) $params['id']);
        if (!$patient) {
            $this->redirect('/admin/patients');
        }

        $email = strtolower(trim((string) $this->input('email')));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flashUpdate((int) $params['id'], 'error', 'Alamat email tidak valid.');
        }
        $this->validatePatientInput(false, (int) $params['id']);
        $existing = $this->users->findByEmail($email);
        if ($existing && (int) $existing['id'] !== (int) $patient['user_id']) {
            $this->flashUpdate((int) $params['id'], 'error', 'Email tersebut sudah digunakan oleh akun lain.');
        }

        try {
            $this->users->update((int) $patient['user_id'], [
                'name'       => $this->input('name'),
                'email'      => $email,
                'phone'      => $this->input('phone'),
                'gender'     => $this->genderValue(),
                'birth_date' => $this->input('birth_date') ?: null,
                'address'    => $this->input('address'),
                'status'     => $this->input('status', 'active'),
            ]);

            Patient::update((int) $params['id'], $this->patientFields());

            audit('update', 'patient', 'Admin memperbarui client ID ' . $params['id']);
            Session::flash('success', 'Data client berhasil diperbarui.');
            $this->redirect('/admin/patients');
        } catch (PDOException $e) {
            $this->flashUpdate((int) $params['id'], 'error', 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.');
        }
    }

    public function delete(array $params): void
    {
        $patient = Patient::find((int) $params['id']);
        if ($patient) {
            try {
                Patient::delete((int) $params['id']);
                $this->users->delete((int) $patient['user_id']);
                audit('delete', 'patient', 'Admin menghapus client ID ' . $params['id']);
                Session::flash('success', 'Client berhasil dihapus.');
            } catch (PDOException $e) {
                Session::flash('error', 'Client tidak dapat dihapus karena masih memiliki data terkait.');
            }
        }
        $this->redirect('/admin/patients');
    }

    private function validatePatientInput(bool $requirePassword = true, ?int $id = null): void
    {
        if (trim((string) $this->input('name')) === '') {
            $this->formRedirect($id, 'error', 'Nama wajib diisi.');
        }
        if ($requirePassword && strlen((string) $this->input('password')) < 8) {
            $this->formRedirect($id, 'error', 'Password minimal 8 karakter.');
        }
        $marital = $this->input('marital_status');
        if ($marital && !in_array($marital, ['single', 'married', 'divorced', 'widowed'], true)) {
            $this->formRedirect($id, 'error', 'Status pernikahan tidak valid.');
        }
    }

    private function formRedirect(?int $id, string $key, string $message): void
    {
        Session::flash($key, $message);
        $this->redirect($id ? '/admin/patients/' . $id . '/edit' : '/admin/patients/create');
    }

    private function genderValue(): ?string
    {
        $g = $this->input('gender');
        return in_array($g, ['L', 'P'], true) ? $g : null;
    }

    private function patientFields(): array
    {
        return [
            'occupation'               => $this->input('occupation'),
            'education'                => $this->input('education'),
            'marital_status'           => $this->input('marital_status') ?: null,
            'emergency_contact_name'   => $this->input('emergency_contact_name'),
            'emergency_contact_phone'  => $this->input('emergency_contact_phone'),
            'emergency_contact_relation' => $this->input('emergency_contact_relation'),
            'medical_history'          => $this->input('medical_history'),
            'allergy_history'          => $this->input('allergy_history'),
            'referred_by'              => $this->input('referred_by'),
            'assigned_psychologist_id' => $this->input('assigned_psychologist_id') ?: null,
            'assigned_psychiatrist_id' => $this->input('assigned_psychiatrist_id') ?: null,
        ];
    }

    private function createOrRedirect(string $key, string $message): void
    {
        Session::flash($key, $message);
        $this->redirect('/admin/patients/create');
    }

    private function flashCreate(string $key, string $message): void
    {
        $this->createOrRedirect($key, $message);
    }

    private function flashUpdate(int $id, string $key, string $message): void
    {
        Session::flash($key, $message);
        $this->redirect('/admin/patients/' . $id . '/edit');
    }
}