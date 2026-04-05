<?php

declare(strict_types=1);

namespace Modules\Product\Domain\RepositoryInterfaces;

use Illuminate\Support\Collection;
use Modules\Product\Domain\Entities\UnitOfMeasure;

interface UnitOfMeasureRepositoryInterface
{
    public function findById(int $id): ?UnitOfMeasure;

    public function findByAbbreviation(int $tenantId, string $abbreviation): ?UnitOfMeasure;

    public function findByType(int $tenantId, string $type): Collection;

    public function findByTenant(int $tenantId): Collection;

    public function create(array $data): UnitOfMeasure;

    public function update(int $id, array $data): ?UnitOfMeasure;

    public function delete(int $id): bool;
}
