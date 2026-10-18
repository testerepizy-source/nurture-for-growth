<?php
namespace App\Controllers\Admin;

use Core\Controller;
use Core\Auth;
use Core\Session;
use App\Repositories\UserRepository;
use App\Models\Role;
use PDOException;

class UserController extends Controller
{
    private UserRepository $repo;
    public function __construct() { $this->repo = new UserRepository(); }

    public function index(): void
    {
        $roleId = (int) $this->input('role_id', 0);
        $users = $this->repo->all();

        if (Auth::roleName() !== 'super_admin') {
            $users = array_values(array_filter($users, fn($user) => ($user['role_name'] ?? '') !== 'super_admin'));
        }

        $roles = Role::all('label ASC');

        if ($roleId > 0) {
            $users = array_values(array_filter($users, fn($user) => (int) $user['role_id'] === $roleId));
        }

        $this->view('admin.users.index', [
            'users' => $users,
            'roles' => $roles,
            'selectedRole' => $roleId,
        ]);
    }

    public function create(): void
    {
        $roles = Role::all('label ASC');
        $this->view('admin.users.form', ['user' => null, 'roles' => $roles]);
    }

    public function store(): void
    {
        $this->requireCsrf();
        $email = strtolower(trim((string) $this->input('email')));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Alamat email tidak valid.');
            $this->redirect('/admin/users/create');
        }
        if ($this->repo->findByEmail($email)) {
            Session::flash('error', 'Email tersebut sudah digunakan oleh akun lain.');
            $this->redirect('/admin/users/create');
        }

        try {
            $this->repo->create([
            'role_id' => (int) $this->input('role_id'),
            'name'    => $this->input('name'),
            'email'   => $email,
            'password'=> $this->input('password'),
            'status'  => $this->input('status', 'active'),
            ]);
        } catch (PDOException $e) {
            Session::flash('error', 'Email tersebut sudah digunakan oleh akun lain.');
            $this->redirect('/admin/users/create');
        }
        audit('create', 'user', 'Admin menambahkan user: ' . $email);
        Session::flash('success', 'User berhasil ditambahkan.');
        $this->redirect('/admin/users');
    }

    public function edit(array $params): void
    {
        $user = $this->repo->find((int) $params['id']);
        $roles = Role::all('label ASC');
        $this->view('admin.users.form', ['user' => $user, 'roles' => $roles]);
    }

    public function update(array $params): void
    {
        $this->requireCsrf();
        $userId = (int) $params['id'];
        $email = strtolower(trim((string) $this->input('email')));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Alamat email tidak valid.');
            $this->redirect('/admin/users/' . $userId . '/edit');
        }
        $existingUser = $this->repo->findByEmail($email);
        if ($existingUser && (int) $existingUser['id'] !== $userId) {
            Session::flash('error', 'Email tersebut sudah digunakan oleh akun lain.');
            $this->redirect('/admin/users/' . $userId . '/edit');
        }

        $data = [
            'role_id' => (int) $this->input('role_id'),
            'name'    => $this->input('name'),
            'email'   => $email,
            'status'  => $this->input('status', 'active'),
        ];
        if ($this->input('password')) $data['password'] = $this->input('password');
        try {
            $this->repo->update($userId, $data);
        } catch (PDOException $e) {
            Session::flash('error', 'Email tersebut sudah digunakan oleh akun lain.');
            $this->redirect('/admin/users/' . $userId . '/edit');
        }
        audit('update', 'user', 'Admin mengubah user ID ' . $params['id']);
        Session::flash('success', 'User berhasil diperbarui.');
        $this->redirect('/admin/users');
    }

    public function delete(array $params): void
    {
        $this->repo->delete((int) $params['id']);
        audit('delete', 'user', 'Admin menghapus user ID ' . $params['id']);
        $this->redirect('/admin/users');
    }
}
