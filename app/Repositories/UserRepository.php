<?php
namespace App\Repositories;
use App\Models\User;

class UserRepository
{
    public function all(): array { return User::withRole(); }
    public function find(int $id): ?array { return User::find($id); }
    public function findByEmail(string $email): ?array { return User::findBy('email', $email); }

    public function create(array $data): int
    {
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        $data['created_at'] = date('Y-m-d H:i:s');
        return User::create($data);
    }

    public function update(int $id, array $data): bool
    {
        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        } else {
            unset($data['password']);
        }
        return User::update($id, $data);
    }

    public function delete(int $id): bool { return User::delete($id); }
}
