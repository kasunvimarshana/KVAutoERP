<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Token response DTO — shapes the access/refresh token pair returned
 * by login and refresh endpoints.
 *
 * @property string $access_token
 * @property string $refresh_token
 * @property int    $expires_in
 * @property string $token_type
 */
final class AuthTokenResource extends JsonResource
{
    /** @var array<string, mixed> */
    public $resource;

    /**
     * @param  array<string, mixed>  $resource
     */
    public function __construct(array $resource)
    {
        parent::__construct($resource);
    }

    /**
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'access_token'  => $this->resource['access_token'],
            'refresh_token' => $this->resource['refresh_token'],
            'token_type'    => $this->resource['token_type'] ?? 'Bearer',
            'expires_in'    => (int) ($this->resource['expires_in'] ?? 900),
        ];
    }
}
