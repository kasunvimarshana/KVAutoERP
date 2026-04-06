<?php
declare(strict_types=1);
namespace Modules\Auth\Application\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Application\Contracts\PermissionServiceInterface;
use Modules\Auth\Domain\RepositoryInterfaces\PermissionRepositoryInterface;

class PermissionService implements PermissionServiceInterface
{
    public function __construct(
        private readonly PermissionRepositoryInterface $repository,
    ) {}

    public function findAll(): Collection
    {
        return $this->repository->findAll();
    }

    public function findByModule(string $module): Collection
    {
        return $this->repository->findByModule($module);
    }

    public function seedDefaultPermissions(array $permissions): void
    {
        DB::transaction(function () use ($permissions) {
            foreach ($permissions as $permission) {
                $this->repository->create($permission);
            }
        });
    }
}
