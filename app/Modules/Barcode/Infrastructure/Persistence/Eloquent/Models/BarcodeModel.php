<?php

declare(strict_types=1);

namespace Modules\Barcode\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class BarcodeModel extends BaseModel
{
    use HasTenant;

    protected $table = 'barcodes';

    protected $fillable = [
        'tenant_id',
        'symbology',
        'data',
        'check_digit',
        'encoded_data',
        'generated_at',
        'metadata',
    ];

    protected $casts = [
        'id'           => 'int',
        'tenant_id'    => 'int',
        'generated_at' => 'datetime',
        'metadata'     => 'array',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
        'deleted_at'   => 'datetime',
    ];
}
