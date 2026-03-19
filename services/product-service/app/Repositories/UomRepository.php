<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\UomRepositoryInterface;
use App\Models\UnitOfMeasure;
use App\Models\UomConversion;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use KvEnterprise\SharedKernel\DTOs\FilterDTO;

/**
 * Eloquent-backed UOM repository.
 */
final class UomRepository implements UomRepositoryInterface
{
    /**
     * Return a paginated list of UOMs.
     *
     * @param  int             $page
     * @param  int             $perPage
     * @param  FilterDTO|null  $filter
     * @return LengthAwarePaginator<UnitOfMeasure>
     */
    public function paginate(int $page = 1, int $perPage = 15, ?FilterDTO $filter = null): LengthAwarePaginator
    {
        $query = UnitOfMeasure::query()->orderBy('category')->orderBy('name');

        if ($filter !== null) {
            if ($filter->search !== null && $filter->search !== '') {
                $search = $filter->search;
                $query->where(static function ($q) use ($search): void {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('symbol', 'like', "%{$search}%");
                });
            }

            if (isset($filter->filters['category'])) {
                $query->where('category', $filter->filters['category']);
            }

            if (isset($filter->filters['is_base_unit'])) {
                $query->where('is_base_unit', (bool) $filter->filters['is_base_unit']);
            }
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Return all UOMs as a flat collection.
     *
     * @return Collection<int, UnitOfMeasure>
     */
    public function all(): Collection
    {
        return UnitOfMeasure::orderBy('category')->orderBy('name')->get();
    }

    /**
     * Find a UOM by UUID.
     *
     * @param  string  $id
     * @return UnitOfMeasure|null
     */
    public function findById(string $id): ?UnitOfMeasure
    {
        return UnitOfMeasure::find($id);
    }

    /**
     * Create a new UOM.
     *
     * @param  array<string, mixed>  $data
     * @return UnitOfMeasure
     */
    public function create(array $data): UnitOfMeasure
    {
        return UnitOfMeasure::create($data);
    }

    /**
     * Update an existing UOM.
     *
     * @param  UnitOfMeasure         $uom
     * @param  array<string, mixed>  $data
     * @return UnitOfMeasure
     */
    public function update(UnitOfMeasure $uom, array $data): UnitOfMeasure
    {
        $uom->update($data);

        return $uom->fresh() ?? $uom;
    }

    /**
     * Delete a UOM.
     *
     * @param  UnitOfMeasure  $uom
     * @return void
     */
    public function delete(UnitOfMeasure $uom): void
    {
        $uom->delete();
    }

    /**
     * Find a conversion between two UOMs.
     *
     * @param  string  $fromUomId
     * @param  string  $toUomId
     * @return UomConversion|null
     */
    public function findConversion(string $fromUomId, string $toUomId): ?UomConversion
    {
        return UomConversion::where('from_uom_id', $fromUomId)
            ->where('to_uom_id', $toUomId)
            ->first();
    }

    /**
     * Create a new UOM conversion entry.
     *
     * @param  array<string, mixed>  $data
     * @return UomConversion
     */
    public function createConversion(array $data): UomConversion
    {
        return UomConversion::create($data);
    }

    /**
     * Return all conversions that involve a given UOM (from or to).
     *
     * @param  string  $uomId
     * @return Collection<int, UomConversion>
     */
    public function getConversionsForUom(string $uomId): Collection
    {
        return UomConversion::with(['fromUom', 'toUom'])
            ->where(static function ($q) use ($uomId): void {
                $q->where('from_uom_id', $uomId)
                  ->orWhere('to_uom_id', $uomId);
            })
            ->get();
    }
}
