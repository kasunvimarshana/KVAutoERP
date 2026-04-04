<?php

declare(strict_types=1);

namespace Modules\Configuration\Domain\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Configuration\Domain\Entities\SystemConfig;

interface SystemConfigRepositoryInterface
{
    public function findById(int $id): ?SystemConfig;

    public function findByKey(string $key, ?int $tenantId = null): ?SystemConfig;

    public function findAll(?int $tenantId = null, int $perPage = 15, int $page = 1): LengthAwarePaginator;

    public function upsert(string $key, ?string $value, ?int $tenantId, string $group = 'general'): SystemConfig;

    public function delete(int $id): bool;
}
