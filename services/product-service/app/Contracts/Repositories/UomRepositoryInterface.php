<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\UnitOfMeasure;
use App\Models\UomConversion;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use KvEnterprise\SharedKernel\DTOs\FilterDTO;

/**
 * Contract for the UOM repository.
 */
interface UomRepositoryInterface
{
    /**
     * Return a paginated list of UOMs.
     *
     * @param  int             $page
     * @param  int             $perPage
     * @param  FilterDTO|null  $filter
     * @return LengthAwarePaginator<UnitOfMeasure>
     */
    public function paginate(int $page = 1, int $perPage = 15, ?FilterDTO $filter = null): LengthAwarePaginator;

    /**
     * Return all UOMs as a flat collection.
     *
     * @return Collection<int, UnitOfMeasure>
     */
    public function all(): Collection;

    /**
     * Find a UOM by its UUID.
     *
     * @param  string  $id
     * @return UnitOfMeasure|null
     */
    public function findById(string $id): ?UnitOfMeasure;

    /**
     * Create a new UOM.
     *
     * @param  array<string, mixed>  $data
     * @return UnitOfMeasure
     */
    public function create(array $data): UnitOfMeasure;

    /**
     * Update an existing UOM.
     *
     * @param  UnitOfMeasure         $uom
     * @param  array<string, mixed>  $data
     * @return UnitOfMeasure
     */
    public function update(UnitOfMeasure $uom, array $data): UnitOfMeasure;

    /**
     * Delete a UOM.
     *
     * @param  UnitOfMeasure  $uom
     * @return void
     */
    public function delete(UnitOfMeasure $uom): void;

    /**
     * Find a conversion between two UOMs.
     *
     * @param  string  $fromUomId
     * @param  string  $toUomId
     * @return UomConversion|null
     */
    public function findConversion(string $fromUomId, string $toUomId): ?UomConversion;

    /**
     * Create a UOM conversion entry.
     *
     * @param  array<string, mixed>  $data
     * @return UomConversion
     */
    public function createConversion(array $data): UomConversion;

    /**
     * Return all conversions for a given UOM (both directions).
     *
     * @param  string  $uomId
     * @return Collection<int, UomConversion>
     */
    public function getConversionsForUom(string $uomId): Collection;
}
