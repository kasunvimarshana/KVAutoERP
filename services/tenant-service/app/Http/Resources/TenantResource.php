<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Shared\Base\BaseResource;
use Illuminate\Http\Request;

/**
 * Tenant API Resource.
 *
 * Serialises a Tenant for API responses.
 * Settings are excluded from normal responses — only admin requests include them.
 */
final class TenantResource extends BaseResource
{
    /**
     * {@inheritDoc}
     *
     * @return array<string, mixed>
     */
    protected function resourceData(Request $request): array
    {
        $data = [
            'name'          => $this->attr('name'),
            'slug'          => $this->attr('slug'),
            'domain'        => $this->attr('domain'),
            'plan'          => $this->attr('plan'),
            'is_active'     => (bool) $this->attr('is_active', true),
            'database_name' => $this->attr('database_name'),
            'billing_email' => $this->attr('billing_email'),
        ];

        // Include settings only for super-admin roles or explicit opt-in.
        if ($request->boolean('include_settings') || $request->user()?->hasRole('super-admin')) {
            $data['settings'] = $this->attr('settings', []);
        }

        return $data;
    }
}
