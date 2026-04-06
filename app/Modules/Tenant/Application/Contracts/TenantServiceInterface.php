<?php
declare(strict_types=1);
namespace Modules\Tenant\Application\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TenantServiceInterface
{
    public function create(array $data): mixed;
    public function update(int $id, array $data): mixed;
    public function delete(int $id): bool;
    public function find(int $id): mixed;
    public function findAll(int $perPage = 15, int $page = 1): LengthAwarePaginator;
    public function findBySlug(string $slug): mixed;
    public function findByDomain(string $domain): mixed;
    public function activate(int $id): mixed;
    public function deactivate(int $id): mixed;
}
