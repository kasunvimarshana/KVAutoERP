<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use KvEnterprise\SharedKernel\Models\TenantAwareModel;

/**
 * UOM conversion matrix entry.
 *
 * Stores the multiplicative factor to convert a quantity from one UOM
 * to another, stored to 6 decimal places (BCMath precision).
 *
 * To convert: result = amount × factor
 *
 * @property string      $id
 * @property string      $tenant_id
 * @property string      $from_uom_id
 * @property string      $to_uom_id
 * @property string      $factor        Decimal stored as string for BCMath precision
 * @property string|null $created_by
 * @property string|null $updated_by
 */
final class UomConversion extends TenantAwareModel
{
    /** @var string */
    protected $table = 'uom_conversions';

    /** @var array<string, string> */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Source UOM.
     *
     * @return BelongsTo<UnitOfMeasure, UomConversion>
     */
    public function fromUom(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasure::class, 'from_uom_id');
    }

    /**
     * Target UOM.
     *
     * @return BelongsTo<UnitOfMeasure, UomConversion>
     */
    public function toUom(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasure::class, 'to_uom_id');
    }
}
