<?php

namespace App\Modules\User\DTOs;

class UserWebhookDTO
{
    public function __construct(
        public readonly string $event,
        public readonly string $userId,
        public readonly string $tenantId,
        public readonly string $email,
        public readonly ?string $keycloakId = null,
        public readonly array $attributes = [],
        public readonly ?string $timestamp = null,
    ) {}

    public static function fromPayload(array $payload): self
    {
        return new self(
            event:       $payload['event'] ?? '',
            userId:      $payload['user_id'] ?? '',
            tenantId:    $payload['tenant_id'] ?? '',
            email:       $payload['email'] ?? '',
            keycloakId:  $payload['keycloak_id'] ?? null,
            attributes:  $payload['attributes'] ?? [],
            timestamp:   $payload['timestamp'] ?? null,
        );
    }
}
