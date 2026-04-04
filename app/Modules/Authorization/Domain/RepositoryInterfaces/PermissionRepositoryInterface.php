<?php

declare(strict_types=1);

namespace Modules\Authorization\Domain\RepositoryInterfaces;

use Modules\Authorization\Domain\Entities\Permission;

interface PermissionRepositoryInterface
{
    public function findById(int $id): ?Permission;

    public function findBySlug(string $slug): ?Permission;

    /** @return Permission[] */
    public function findAll(): array;

    /** @return Permission[] */
    public function findByModule(string $module): array;

    public function save(Permission $permission): Permission;
}
