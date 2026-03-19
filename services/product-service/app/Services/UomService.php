<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\UomRepositoryInterface;
use App\Contracts\Services\UomServiceInterface;
use App\Models\UnitOfMeasure;
use App\Models\UomConversion;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use KvEnterprise\SharedKernel\DTOs\FilterDTO;
use KvEnterprise\SharedKernel\Exceptions\NotFoundException;
use KvEnterprise\SharedKernel\Exceptions\ValidationException;

/**
 * UOM application service.
 *
 * All conversion factors and amounts are handled via BCMath to ensure
 * 6 decimal places of precision.
 */
final class UomService implements UomServiceInterface
{
    /** Decimal precision for UOM conversion factors and results. */
    private const CONVERSION_SCALE = 6;

    public function __construct(
        private readonly UomRepositoryInterface $uomRepository,
    ) {}

    /**
     * Return a paginated list of UOMs.
     *
     * @param  array<string, mixed>  $filters
     * @param  int                   $page
     * @param  int                   $perPage
     * @return LengthAwarePaginator<UnitOfMeasure>
     */
    public function list(array $filters = [], int $page = 1, int $perPage = 15): LengthAwarePaginator
    {
        $filterDTO = new FilterDTO(
            filters: array_filter([
                'category'     => $filters['category'] ?? null,
                'is_base_unit' => isset($filters['is_base_unit']) ? (bool) $filters['is_base_unit'] : null,
            ], static fn ($v) => $v !== null),
            search: $filters['search'] ?? null,
        );

        return $this->uomRepository->paginate($page, $perPage, $filterDTO);
    }

    /**
     * Return all UOMs.
     *
     * @return Collection<int, UnitOfMeasure>
     */
    public function all(): Collection
    {
        return $this->uomRepository->all();
    }

    /**
     * Find a UOM by UUID or throw NotFoundException.
     *
     * @param  string  $id
     * @return UnitOfMeasure
     *
     * @throws NotFoundException
     */
    public function findOrFail(string $id): UnitOfMeasure
    {
        $uom = $this->uomRepository->findById($id);

        if ($uom === null) {
            throw NotFoundException::for('UnitOfMeasure', $id);
        }

        return $uom;
    }

    /**
     * Create a new UOM.
     *
     * @param  array<string, mixed>  $data
     * @return UnitOfMeasure
     */
    public function create(array $data): UnitOfMeasure
    {
        $data['is_base_unit'] = $data['is_base_unit'] ?? false;

        return $this->uomRepository->create($data);
    }

    /**
     * Update an existing UOM.
     *
     * @param  string                $id
     * @param  array<string, mixed>  $data
     * @return UnitOfMeasure
     *
     * @throws NotFoundException
     */
    public function update(string $id, array $data): UnitOfMeasure
    {
        $uom = $this->findOrFail($id);

        return $this->uomRepository->update($uom, $data);
    }

    /**
     * Delete a UOM.
     *
     * @param  string  $id
     * @return void
     *
     * @throws NotFoundException
     */
    public function delete(string $id): void
    {
        $uom = $this->findOrFail($id);
        $this->uomRepository->delete($uom);
    }

    /**
     * Create or update a UOM conversion rule.
     *
     * The inverse conversion is automatically stored to avoid round-trip
     * lookups: if 1 kg = 1000 g, the reverse 1 g = 0.001 kg is stored.
     *
     * @param  string  $fromUomId
     * @param  string  $toUomId
     * @param  string  $factor     BCMath decimal string (6 places)
     * @return UomConversion
     *
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function upsertConversion(string $fromUomId, string $toUomId, string $factor): UomConversion
    {
        if ($fromUomId === $toUomId) {
            throw ValidationException::forField('to_uom_id', 'Source and target UOM must be different.');
        }

        // Validate that both UOMs exist.
        $this->findOrFail($fromUomId);
        $this->findOrFail($toUomId);

        // Validate factor > 0.
        if (bccomp($factor, '0', self::CONVERSION_SCALE) !== 1) {
            throw ValidationException::forField('factor', 'Conversion factor must be greater than zero.');
        }

        $normalisedFactor = bcadd($factor, '0', self::CONVERSION_SCALE);

        // Store forward conversion.
        $existing = $this->uomRepository->findConversion($fromUomId, $toUomId);

        if ($existing !== null) {
            $existing->update(['factor' => $normalisedFactor]);
            $conversion = $existing;
        } else {
            $conversion = $this->uomRepository->createConversion([
                'from_uom_id' => $fromUomId,
                'to_uom_id'   => $toUomId,
                'factor'      => $normalisedFactor,
            ]);
        }

        // Store inverse conversion (1 / factor), only if not already present.
        $inverseFactor   = bcdiv('1', $normalisedFactor, self::CONVERSION_SCALE);
        $existingInverse = $this->uomRepository->findConversion($toUomId, $fromUomId);

        if ($existingInverse !== null) {
            $existingInverse->update(['factor' => $inverseFactor]);
        } else {
            $this->uomRepository->createConversion([
                'from_uom_id' => $toUomId,
                'to_uom_id'   => $fromUomId,
                'factor'      => $inverseFactor,
            ]);
        }

        return $conversion;
    }

    /**
     * Convert an amount between two UOMs using BCMath.
     *
     * @param  string  $fromUomId
     * @param  string  $toUomId
     * @param  string  $amount
     * @return string   Converted amount with 6 decimal places
     *
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function convert(string $fromUomId, string $toUomId, string $amount): string
    {
        if ($fromUomId === $toUomId) {
            return bcadd($amount, '0', self::CONVERSION_SCALE);
        }

        $conversion = $this->uomRepository->findConversion($fromUomId, $toUomId);

        if ($conversion === null) {
            throw NotFoundException::for(
                'UomConversion',
                "{$fromUomId} → {$toUomId}",
            );
        }

        return bcmul($amount, $conversion->factor, self::CONVERSION_SCALE);
    }

    /**
     * Return all conversions for a UOM.
     *
     * @param  string  $uomId
     * @return Collection<int, UomConversion>
     */
    public function getConversions(string $uomId): Collection
    {
        return $this->uomRepository->getConversionsForUom($uomId);
    }
}
