<?php

declare(strict_types=1);

namespace App\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Authentication Resource
 * 
 * Formats token + user data for API responses.
 * All responses go through Resources - no raw arrays in controllers.
 */
class AuthResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'access_token' => $this->resource['access_token'],
            'token_type' => $this->resource['token_type'],
            'expires_in' => $this->resource['expires_in'],
            'user' => [
                'id' => $this->resource['user']['id'],
                'name' => $this->resource['user']['name'],
                'email' => $this->resource['user']['email'],
                'role' => $this->resource['user']['role'],
                'tenant_id' => $this->resource['user']['tenant_id'],
            ],
        ];
    }
}
