<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string      $id
 * @property string      $name
 * @property string      $slug
 * @property string|null $domain
 * @property string      $status
 * @property string      $plan
 * @property array|null  $settings
 * @property array|null  $config
 * @property int         $max_users
 * @property int         $max_organizations
 * @property string|null $trial_ends_at
 * @property string|null $subscription_ends_at
 * @property array|null  $metadata
 * @property string      $created_at
 * @property string      $updated_at
 */
class TenantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'name'                 => $this->name,
            'slug'                 => $this->slug,
            'domain'               => $this->domain,
            'status'               => $this->status,
            'plan'                 => $this->plan,
            'settings'             => $this->settings,
            'config'               => $this->config,
            'max_users'            => $this->max_users,
            'max_organizations'    => $this->max_organizations,
            'trial_ends_at'        => $this->trial_ends_at,
            'subscription_ends_at' => $this->subscription_ends_at,
            'metadata'             => $this->metadata,
            'is_active'            => $this->isActive(),
            'is_on_trial'          => $this->isOnTrial(),
            'is_plan_active'       => $this->isPlanActive(),
            'created_at'           => $this->created_at,
            'updated_at'           => $this->updated_at,

            // Sensitive configs are intentionally excluded:
            // database_config, mail_config, cache_config, broker_config
        ];
    }
}
