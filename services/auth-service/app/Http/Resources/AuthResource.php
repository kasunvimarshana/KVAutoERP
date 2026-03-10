<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'success'      => true,
            'access_token' => $this->resource['access_token'],
            'token_type'   => $this->resource['token_type'] ?? 'Bearer',
            'user'         => new UserResource($this->resource['user']),
        ];
    }
}
