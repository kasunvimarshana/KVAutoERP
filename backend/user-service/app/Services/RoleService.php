<?php

namespace App\Services;

class RoleService
{
    private array $roles = [
        'admin' => [
            'name' => 'admin',
            'description' => 'Full system access',
            'permissions' => ['users.*', 'roles.*', 'reports.*', 'settings.*'],
        ],
        'manager' => [
            'name' => 'manager',
            'description' => 'Department management access',
            'permissions' => ['users.read', 'users.create', 'users.update', 'reports.*'],
        ],
        'staff' => [
            'name' => 'staff',
            'description' => 'Basic access',
            'permissions' => ['users.read.own'],
        ],
    ];

    public function all(): array
    {
        return array_values($this->roles);
    }

    public function findByName(string $name): ?array
    {
        return $this->roles[$name] ?? null;
    }

    public function allPermissions(): array
    {
        return [
            ['name' => 'users.read', 'description' => 'Read any user'],
            ['name' => 'users.read.own', 'description' => 'Read own profile'],
            ['name' => 'users.create', 'description' => 'Create users'],
            ['name' => 'users.update', 'description' => 'Update users'],
            ['name' => 'users.delete', 'description' => 'Delete users'],
            ['name' => 'roles.*', 'description' => 'Full roles access'],
            ['name' => 'reports.*', 'description' => 'Full reports access'],
            ['name' => 'settings.*', 'description' => 'Full settings access'],
        ];
    }
}
