<?php
namespace App\Controllers\Admin;

use Core\Controller;
use Core\Session;
use App\Models\Role;
use App\Models\Permission;
use App\Models\RolePermission;

class RoleController extends Controller
{
    public function index(): void
    {
        $roles = Role::all('label ASC');
        $this->view('admin.roles.index', ['roles' => $roles]);
    }

    public function edit(array $params): void
    {
        $role = Role::find((int) $params['id']);
        $permissions = Permission::all('module ASC, label ASC');
        $assigned = array_column(RolePermission::forRole($role['id']), 'permission_id');
        $this->view('admin.roles.form', ['role' => $role, 'permissions' => $permissions, 'assigned' => $assigned]);
    }

    public function update(array $params): void
    {
        $this->requireCsrf();
        $permissionIds = $_POST['permissions'] ?? [];
        RolePermission::sync((int) $params['id'], $permissionIds);
        audit('update', 'role', 'Mengubah permission role ID ' . $params['id']);
        Session::flash('success', 'Permission role berhasil diperbarui.');
        $this->redirect('/admin/roles');
    }
}
