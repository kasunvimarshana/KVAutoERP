<?php
declare(strict_types=1);
namespace Modules\Tenant\Infrastructure\Persistence\Eloquent\Models;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;

class TenantAttachmentModel extends BaseModel {
    protected $table = 'tenant_attachments';
    protected $fillable = ['tenant_id', 'uuid', 'name', 'file_path', 'mime_type', 'size', 'type', 'metadata'];
}
