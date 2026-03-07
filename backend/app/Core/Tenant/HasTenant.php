<?php

namespace App\Core\Tenant;

trait HasTenant
{
    public static function bootHasTenant(): void
    {
        static::addGlobalScope(new TenantScope());

        static::creating(function ($model) {
            $tenantManager = app(TenantManager::class);
            if ($tenantManager->hasTenant() && empty($model->tenant_id)) {
                $model->tenant_id = $tenantManager->getTenantId();
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }
}
