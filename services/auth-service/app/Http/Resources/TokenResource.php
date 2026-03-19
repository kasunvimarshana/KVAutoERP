<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TokenResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'access_token'  => $this->resource->accessToken,
            'refresh_token' => $this->resource->refreshToken,
            'expires_in'    => $this->resource->expiresIn,
            'token_type'    => $this->resource->tokenType,
        ];
    }
}
