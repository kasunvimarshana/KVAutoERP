<?php

declare(strict_types=1);

namespace Modules\Tenant\Infrastructure\Http\Resources;

use Modules\Core\Infrastructure\Http\Resources\BaseResource;
use Modules\Tenant\Domain\Entities\Tenant;

class TenantResource extends BaseResource
{
    public function toArray($request): array
    {
        /** @var Tenant $tenant */
        $tenant = $this->resource;

        return [
            'id' => $tenant->id,
            'name' => $tenant->name,
            'slug' => $tenant->slug,
            'domain' => $tenant->domain,
            'database' => $tenant->database,
            'status' => $tenant->status,
            'plan' => $tenant->plan,
            'locale' => $tenant->locale,
            'timezone' => $tenant->timezone,
            'currency' => $tenant->currency,
            'settings' => $tenant->settings,
            'trial_ends_at' => $tenant->trialEndsAt?->format('Y-m-d\TH:i:s\Z'),
            'suspended_at' => $tenant->suspendedAt?->format('Y-m-d\TH:i:s\Z'),
            'created_at' => $tenant->createdAt?->format('Y-m-d\TH:i:s\Z'),
            'updated_at' => $tenant->updatedAt?->format('Y-m-d\TH:i:s\Z'),
        ];
    }
}
