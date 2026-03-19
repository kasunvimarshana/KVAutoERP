<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\DTOs\AuthResultDto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthTokenResource extends JsonResource
{
    public function __construct(private readonly AuthResultDto $dto)
    {
        parent::__construct($dto);
    }

    public function toArray(Request $request): array
    {
        return [
            'user'       => new UserResource($this->dto->user),
            'tokens'     => $this->dto->tokenPair->toArray(),
            'session_id' => $this->dto->sessionId,
        ];
    }
}
