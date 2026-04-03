<?php
declare(strict_types=1);
namespace Modules\Tenant\Domain\RepositoryInterfaces;
use Illuminate\Support\Collection;
use Modules\Tenant\Domain\Entities\Tenant;

interface TenantRepositoryInterface {
    public function find(int $id): ?Tenant;
    public function save(Tenant $tenant): Tenant;
    public function delete(int $id): bool;
    public function all(): Collection;
}
