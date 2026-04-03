<?php
declare(strict_types=1);
namespace Modules\Tenant\Infrastructure\Persistence\Eloquent\Models;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;

class TenantModel extends BaseModel {
    protected $table = 'tenants';
    protected $fillable = ['name', 'domain', 'active', 'database_config', 'feature_flags', 'metadata'];
}
