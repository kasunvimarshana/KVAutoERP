<?php
declare(strict_types=1);
namespace Modules\Auth\Domain\RepositoryInterfaces;

use Modules\Core\Domain\Contracts\Repositories\RepositoryInterface;

interface RoleRepositoryInterface extends RepositoryInterface
{
    public function findByTenant(int $tenantId): \Illuminate\Support\Collection;
    public function findById(int|string $id, int $tenantId): mixed;
    public function findBySlug(string $slug, int $tenantId): mixed;
}
