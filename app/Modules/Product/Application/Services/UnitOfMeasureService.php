<?php

declare(strict_types=1);

namespace Modules\Product\Application\Services;

use Illuminate\Support\Collection;
use Modules\Core\Domain\Exceptions\NotFoundException;
use Modules\Product\Application\Contracts\UnitOfMeasureServiceInterface;
use Modules\Product\Domain\Entities\UnitOfMeasure;
use Modules\Product\Domain\RepositoryInterfaces\UnitOfMeasureRepositoryInterface;

final class UnitOfMeasureService implements UnitOfMeasureServiceInterface
{
    public function __construct(
        private readonly UnitOfMeasureRepositoryInterface $uomRepository,
    ) {}

    public function getById(int $id): UnitOfMeasure
    {
        $uom = $this->uomRepository->findById($id);

        if ($uom === null) {
            throw new NotFoundException('UnitOfMeasure', $id);
        }

        return $uom;
    }

    public function getByTenant(int $tenantId): Collection
    {
        return $this->uomRepository->findByTenant($tenantId);
    }

    public function getByType(int $tenantId, string $type): Collection
    {
        return $this->uomRepository->findByType($tenantId, $type);
    }

    public function create(array $data): UnitOfMeasure
    {
        return $this->uomRepository->create($data);
    }

    public function update(int $id, array $data): UnitOfMeasure
    {
        $uom = $this->uomRepository->update($id, $data);

        if ($uom === null) {
            throw new NotFoundException('UnitOfMeasure', $id);
        }

        return $uom;
    }

    public function delete(int $id): bool
    {
        return $this->uomRepository->delete($id);
    }

    public function convert(int $fromUomId, int $toUomId, float $quantity): float
    {
        $fromUom = $this->uomRepository->findById($fromUomId);

        if ($fromUom === null) {
            throw new NotFoundException('UnitOfMeasure', $fromUomId);
        }

        $toUom = $this->uomRepository->findById($toUomId);

        if ($toUom === null) {
            throw new NotFoundException('UnitOfMeasure', $toUomId);
        }

        if ($fromUom->type !== $toUom->type) {
            throw new \InvalidArgumentException(
                "Cannot convert between incompatible UoM types: '{$fromUom->type}' and '{$toUom->type}'."
            );
        }

        $baseQuantity = $quantity * $fromUom->baseUnitFactor;

        return $baseQuantity / $toUom->baseUnitFactor;
    }
}
