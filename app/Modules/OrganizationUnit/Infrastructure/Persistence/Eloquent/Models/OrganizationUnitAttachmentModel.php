<?php
declare(strict_types=1);
namespace Modules\OrganizationUnit\Infrastructure\Persistence\Eloquent\Models;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;

class OrganizationUnitAttachmentModel extends BaseModel {
    protected $table = 'organization_unit_attachments';
    protected $fillable = ['tenant_id', 'organization_unit_id', 'uuid', 'name', 'file_path', 'mime_type', 'size', 'type', 'metadata'];
}
