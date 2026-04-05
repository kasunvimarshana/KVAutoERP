<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class ProductComponentModel extends BaseModel
{
    use HasTenant;

    protected $table = 'product_components';

    protected $fillable = [
        'tenant_id',
        'product_id',
        'component_product_id',
        'quantity',
        'unit',
        'notes',
    ];

    protected $casts = [
        'quantity'             => 'float',
        'product_id'           => 'int',
        'component_product_id' => 'int',
    ];

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProductModel::class, 'product_id');
    }

    public function componentProduct(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProductModel::class, 'component_product_id');
    }
}
