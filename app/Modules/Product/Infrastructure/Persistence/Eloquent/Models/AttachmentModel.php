<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Infrastructure\Persistence\Eloquent\Traits\HasTenant;

class AttachmentModel extends Model
{
    use HasTenant;

    protected $table = 'attachments';

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'attachable_type',
        'attachable_id',
        'filename',
        'original_name',
        'mime_type',
        'size',
        'disk',
        'path',
        'metadata',
    ];

    protected $casts = [
        'id'             => 'int',
        'tenant_id'      => 'int',
        'attachable_id'  => 'int',
        'size'           => 'int',
        'metadata'       => 'array',
        'created_at'     => 'datetime',
    ];
}
