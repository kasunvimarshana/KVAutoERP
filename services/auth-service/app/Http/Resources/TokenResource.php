<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Application\Auth\DTOs\TokenDTO;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TokenDTO
 */
class TokenResource extends JsonResource
{
    /** @var TokenDTO */
    public $resource;

    public function __construct(TokenDTO $resource)
    {
        parent::__construct($resource);
    }

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'access_token'  => $this->resource->accessToken,
            'token_type'    => $this->resource->tokenType,
            'expires_in'    => $this->resource->expiresIn,
            'refresh_token' => $this->resource->refreshToken,
            'user'          => new UserResource($this->resource->user),
        ];
    }
}
