<?php
declare(strict_types=1);
namespace Modules\Auth\Domain\RepositoryInterfaces;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Core\Domain\Contracts\Repositories\RepositoryInterface;

interface UserRepositoryInterface extends RepositoryInterface
{
    public function findByEmail(string $email, int $tenantId): mixed;
    public function findById(int|string $id, int $tenantId): mixed;
    public function findAllByTenant(int $tenantId, int $perPage = 15, int $page = 1): LengthAwarePaginator;
    public function findWithRoles(int|string $id, int $tenantId): mixed;
}
