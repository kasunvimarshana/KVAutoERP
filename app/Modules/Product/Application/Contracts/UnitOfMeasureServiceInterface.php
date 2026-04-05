<?php

declare(strict_types=1);

namespace Modules\Product\Application\Contracts;

use Illuminate\Support\Collection;
use Modules\Product\Domain\Entities\UnitOfMeasure;

interface UnitOfMeasureServiceInterface
{
    public function getById(int $id): UnitOfMeasure;

    public function getByTenant(int $tenantId): Collection;

    public function getByType(int $tenantId, string $type): Collection;

    public function create(array $data): UnitOfMeasure;

    public function update(int $id, array $data): UnitOfMeasure;

    public function delete(int $id): bool;

    public function convert(int $fromUomId, int $toUomId, float $quantity): float;
}
