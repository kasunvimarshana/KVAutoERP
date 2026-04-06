<?php
declare(strict_types=1);
namespace Modules\Tenant\Infrastructure\Http\Resources;

use Illuminate\Http\Request;
use Modules\Core\Infrastructure\Http\Resources\BaseResource;

class TenantResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'slug'       => $this->slug,
            'domain'     => $this->domain,
            'email'      => $this->email,
            'phone'      => $this->phone,
            'address'    => $this->address,
            'is_active'  => $this->is_active,
            'plan_id'    => $this->plan_id,
            'settings'   => $this->settings,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
