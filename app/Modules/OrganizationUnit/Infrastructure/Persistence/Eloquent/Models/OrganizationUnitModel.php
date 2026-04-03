<?php
declare(strict_types=1);
namespace Modules\OrganizationUnit\Infrastructure\Persistence\Eloquent\Models;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;

class OrganizationUnitModel extends BaseModel {
    protected $table = 'organization_units';
    protected $fillable = ['tenant_id', 'name', 'code', 'type', 'parent_id', 'description', 'metadata'];
}
