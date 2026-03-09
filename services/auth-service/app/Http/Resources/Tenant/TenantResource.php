<?php

declare(strict_types=1);

namespace App\Http\Resources\Tenant;

use App\Domain\Tenant\Entities\Tenant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Tenant Resource.
 *
 * @mixin Tenant
 */
class TenantResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'slug'       => $this->slug,
            'domain'     => $this->domain,
            'status'     => $this->status,
            'plan'       => $this->plan,
            'metadata'   => $this->metadata,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
