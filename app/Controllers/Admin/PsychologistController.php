<?php
namespace App\Controllers\Admin;

use Core\Controller;
use Core\Session;
use App\Repositories\UserRepository;
use App\Models\Psychologist;

class PsychologistController extends Controller
{
    private UserRepository $users;
    public function __construct() { $this->users = new UserRepository(); }

    public function index(): void
    {
        $psychologists = Psychologist::allWithUser();
        $this->view('admin.psychologists.index', ['psychologists' => $psychologists]);
    }

    public function create(): void
    {
        $this->view('admin.psychologists.form', ['psychologist' => null]);
    }

    public function edit(array $params): void
    {
        $psychologist = Psychologist::find((int) $params['id']);
        if (!$psychologist) {
            $this->redirect('/admin/psychologists');
        }

        $user = $this->users->find($psychologist['user_id']);
        if ($user) {
            $psychologist['name'] = $user['name'];
            $psychologist['email'] = $user['email'];
            $psychologist['status'] = $user['status'];
        }

        $this->view('admin.psychologists.form', ['psychologist' => $psychologist]);
    }

    public function store(): void
    {
        $this->requireCsrf();
        if ($this->users->findByEmail($this->input('email'))) {
            Session::flash('error', 'Email sudah terdaftar. Gunakan email lain.');
            $this->redirect('/admin/psychologists/create');
        }
        $userId = $this->users->create([
            'role_id' => 4, // psikolog
            'name'    => $this->input('name'),
            'email'   => $this->input('email'),
            'password'=> $this->input('password'),
            'status'  => 'active',
        ]);
        Psychologist::create([
            'user_id' => $userId,
            'str_number' => $this->input('str_number'),
            'sipp_number' => $this->input('sipp_number'),
            'specialization' => $this->input('specialization'),
            'bio' => $this->input('bio'),
            'years_experience' => (int) $this->input('years_experience', 0),
        ]);
        audit('create', 'psychologist', 'Menambahkan psikolog: ' . $this->input('email'));
        Session::flash('success', 'Psikolog berhasil ditambahkan.');
        $this->redirect('/admin/psychologists');
    }

    public function update(array $params): void
    {
        $this->requireCsrf();
        $psychologist = Psychologist::find((int) $params['id']);
        if (!$psychologist) {
            $this->redirect('/admin/psychologists');
        }

        $user = $this->users->find($psychologist['user_id']);
        $this->users->update($psychologist['user_id'], [
            'name' => $this->input('name'),
            'email' => $this->input('email'),
            'status' => $this->input('status', $user['status'] ?? 'active'),
        ]);

        Psychologist::update((int) $params['id'], [
            'str_number' => $this->input('str_number'),
            'sipp_number' => $this->input('sipp_number'),
            'specialization' => $this->input('specialization'),
            'bio' => $this->input('bio'),
            'years_experience' => (int) $this->input('years_experience', 0),
        ]);

        audit('update', 'psychologist', 'Memperbarui psikolog ID: ' . $params['id']);
        Session::flash('success', 'Psikolog berhasil diperbarui.');
        $this->redirect('/admin/psychologists');
    }

    public function delete(array $params): void
    {
        $psychologist = Psychologist::find((int) $params['id']);
        if ($psychologist) {
            $this->users->delete($psychologist['user_id']);
        }
        $this->redirect('/admin/psychologists');
    }
}
