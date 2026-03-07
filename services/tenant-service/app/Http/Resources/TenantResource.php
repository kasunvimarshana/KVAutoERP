<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * Sensitive connection fields (db_config, mail_config, broker_config) are
     * always excluded from the public API response.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'name'                 => $this->name,
            'slug'                 => $this->slug,
            'domain'               => $this->domain,
            'email'                => $this->email,
            'phone'                => $this->phone,
            'status'               => $this->status,
            'plan'                 => $this->plan,
            'settings'             => $this->settings,
            'cache_config'         => $this->cache_config,
            'trial_ends_at'        => $this->trial_ends_at?->toIso8601String(),
            'subscription_ends_at' => $this->subscription_ends_at?->toIso8601String(),
            'metadata'             => $this->metadata,
            'is_active'            => $this->isActive(),
            'is_on_trial'          => $this->isOnTrial(),
            'created_at'           => $this->created_at?->toIso8601String(),
            'updated_at'           => $this->updated_at?->toIso8601String(),
        ];
    }
}
