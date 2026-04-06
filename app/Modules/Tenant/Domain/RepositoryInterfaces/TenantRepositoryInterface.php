<?php
declare(strict_types=1);
namespace Modules\Tenant\Domain\RepositoryInterfaces;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Core\Domain\Contracts\Repositories\RepositoryInterface;

interface TenantRepositoryInterface extends RepositoryInterface
{
    public function findById(int $id): mixed;
    public function findBySlug(string $slug): mixed;
    public function findByDomain(string $domain): mixed;
    public function findAll(int $perPage = 15, int $page = 1): LengthAwarePaginator;
}
