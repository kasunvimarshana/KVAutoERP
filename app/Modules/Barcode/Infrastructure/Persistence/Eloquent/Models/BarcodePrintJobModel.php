<?php

declare(strict_types=1);

namespace Modules\Barcode\Infrastructure\Persistence\Eloquent\Models;

use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class BarcodePrintJobModel extends BaseModel
{
    use HasTenant;

    protected $table = 'barcode_print_jobs';

    protected $fillable = [
        'tenant_id',
        'label_template_id',
        'barcode_id',
        'status',
        'printer_id',
        'copies',
        'printed_at',
        'error_message',
    ];

    protected $casts = [
        'id'                 => 'int',
        'tenant_id'          => 'int',
        'label_template_id'  => 'int',
        'barcode_id'         => 'int',
        'copies'             => 'int',
        'printed_at'         => 'datetime',
        'created_at'         => 'datetime',
        'updated_at'         => 'datetime',
        'deleted_at'         => 'datetime',
    ];
}
