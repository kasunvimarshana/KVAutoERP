<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\Models\UnitOfMeasure;
use App\Models\UomConversion;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Contract for the UOM application service.
 */
interface UomServiceInterface
{
    /**
     * Return a paginated list of UOMs.
     *
     * @param  array<string, mixed>  $filters
     * @param  int                   $page
     * @param  int                   $perPage
     * @return LengthAwarePaginator<UnitOfMeasure>
     */
    public function list(array $filters = [], int $page = 1, int $perPage = 15): LengthAwarePaginator;

    /**
     * Return all UOMs (no pagination) — for select inputs.
     *
     * @return Collection<int, UnitOfMeasure>
     */
    public function all(): Collection;

    /**
     * Find a UOM by UUID or throw NotFoundException.
     *
     * @param  string  $id
     * @return UnitOfMeasure
     *
     * @throws \KvEnterprise\SharedKernel\Exceptions\NotFoundException
     */
    public function findOrFail(string $id): UnitOfMeasure;

    /**
     * Create a new UOM.
     *
     * @param  array<string, mixed>  $data
     * @return UnitOfMeasure
     */
    public function create(array $data): UnitOfMeasure;

    /**
     * Update a UOM.
     *
     * @param  string                $id
     * @param  array<string, mixed>  $data
     * @return UnitOfMeasure
     *
     * @throws \KvEnterprise\SharedKernel\Exceptions\NotFoundException
     */
    public function update(string $id, array $data): UnitOfMeasure;

    /**
     * Delete a UOM.
     *
     * @param  string  $id
     * @return void
     *
     * @throws \KvEnterprise\SharedKernel\Exceptions\NotFoundException
     */
    public function delete(string $id): void;

    /**
     * Create or update a conversion rule between two UOMs.
     *
     * The factor is stored with 6 decimal places precision.
     *
     * @param  string  $fromUomId
     * @param  string  $toUomId
     * @param  string  $factor     BCMath-compatible decimal string
     * @return UomConversion
     *
     * @throws \KvEnterprise\SharedKernel\Exceptions\ValidationException
     */
    public function upsertConversion(string $fromUomId, string $toUomId, string $factor): UomConversion;

    /**
     * Convert an amount from one UOM to another.
     *
     * Uses BCMath for 6 decimal place precision.
     *
     * @param  string  $fromUomId
     * @param  string  $toUomId
     * @param  string  $amount    BCMath-compatible decimal string
     * @return string              Converted amount with 6 decimal places
     *
     * @throws \KvEnterprise\SharedKernel\Exceptions\NotFoundException   When no conversion path exists.
     * @throws \KvEnterprise\SharedKernel\Exceptions\ValidationException When same-UOM conversion requested.
     */
    public function convert(string $fromUomId, string $toUomId, string $amount): string;

    /**
     * Return all conversions defined for a UOM.
     *
     * @param  string  $uomId
     * @return Collection<int, UomConversion>
     */
    public function getConversions(string $uomId): Collection;
}
