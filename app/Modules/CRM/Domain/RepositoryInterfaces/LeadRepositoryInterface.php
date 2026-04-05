<?php

declare(strict_types=1);

namespace Modules\CRM\Domain\RepositoryInterfaces;

use Illuminate\Support\Collection;
use Modules\CRM\Domain\Entities\Lead;

interface LeadRepositoryInterface
{
    public function findById(int $id): ?Lead;
    public function findByTenant(int $tenantId): Collection;
    public function findByStatus(int $tenantId, string $status): Collection;
    public function save(array $data): Lead;
    public function update(int $id, array $data): Lead;
    public function delete(int $id): void;
}
