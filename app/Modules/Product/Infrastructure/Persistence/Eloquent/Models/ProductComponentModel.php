<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;

final class ProductComponentModel extends BaseModel
{
    protected $table = 'product_components';

    protected $fillable = [
        'product_id',
        'component_id',
        'component_variant_id',
        'quantity',
        'unit_of_measure',
        'notes',
    ];

    protected $casts = [
        'id'                   => 'int',
        'product_id'           => 'int',
        'component_id'         => 'int',
        'component_variant_id' => 'int',
        'quantity'             => 'float',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(ProductModel::class, 'product_id');
    }

    public function component(): BelongsTo
    {
        return $this->belongsTo(ProductModel::class, 'component_id');
    }

    public function componentVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariantModel::class, 'component_variant_id');
    }
}
