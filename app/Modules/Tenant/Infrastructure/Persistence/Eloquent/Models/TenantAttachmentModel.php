<?php

namespace Modules\Tenant\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TenantAttachmentModel extends Model
{
    use SoftDeletes;

    protected $table = 'tenant_attachments';
    protected $guarded = ['id'];
    protected $casts = [
        'metadata' => 'array',
        'size'     => 'integer',
    ];

    public function tenant()
    {
        return $this->belongsTo(TenantModel::class);
    }
}
