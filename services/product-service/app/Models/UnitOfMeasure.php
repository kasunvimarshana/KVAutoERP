<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use KvEnterprise\SharedKernel\Models\TenantAwareModel;

/**
 * Unit of Measure (UOM) entity.
 *
 * Supports categories such as weight, volume, length, area, and count.
 * Each tenant defines its own UOM catalogue.
 *
 * @property string      $id
 * @property string      $tenant_id
 * @property string      $name
 * @property string      $symbol
 * @property string      $category
 * @property bool        $is_base_unit
 * @property string|null $created_by
 * @property string|null $updated_by
 */
final class UnitOfMeasure extends TenantAwareModel
{
    /** @var string */
    protected $table = 'units_of_measure';

    /** @var array<string, string> */
    protected $casts = [
        'is_base_unit' => 'boolean',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];

    /** Valid UOM categories. */
    public const CATEGORIES = ['weight', 'volume', 'length', 'area', 'count', 'time', 'other'];

    /**
     * Conversion rules from this UOM to others.
     *
     * @return HasMany<UomConversion>
     */
    public function conversionsFrom(): HasMany
    {
        return $this->hasMany(UomConversion::class, 'from_uom_id');
    }

    /**
     * Conversion rules into this UOM from others.
     *
     * @return HasMany<UomConversion>
     */
    public function conversionsTo(): HasMany
    {
        return $this->hasMany(UomConversion::class, 'to_uom_id');
    }
}
