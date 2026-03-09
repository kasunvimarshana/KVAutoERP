<?php

declare(strict_types=1);

namespace App\Domain\Tenant\Entities;

use DateTimeImmutable;

/**
 * TenantConfiguration Domain Entity.
 *
 * Represents a single runtime configuration key/value pair belonging to a tenant.
 * Values are persisted as strings and type-inferred on read.
 */
final class TenantConfiguration
{
    /**
     * @param  string             $id          UUID primary key.
     * @param  string             $tenantId    Owning tenant UUID.
     * @param  string             $configKey   Dot-notation configuration key.
     * @param  string             $configValue Serialised string value.
     * @param  string             $environment Target environment (testing|staging|production).
     * @param  bool               $isSecret    Whether to mask the value in API responses.
     * @param  DateTimeImmutable  $createdAt   Record creation timestamp.
     * @param  DateTimeImmutable  $updatedAt   Last update timestamp.
     */
    public function __construct(
        private readonly string $id,
        private readonly string $tenantId,
        private readonly string $configKey,
        private readonly string $configValue,
        private readonly string $environment,
        private readonly bool $isSecret,
        private readonly DateTimeImmutable $createdAt = new DateTimeImmutable(),
        private readonly DateTimeImmutable $updatedAt = new DateTimeImmutable(),
    ) {}

    // ──────────────────────────────────────────────────────────────────────
    // Getters
    // ──────────────────────────────────────────────────────────────────────

    public function getId(): string
    {
        return $this->id;
    }

    public function getTenantId(): string
    {
        return $this->tenantId;
    }

    public function getConfigKey(): string
    {
        return $this->configKey;
    }

    public function getConfigValue(): string
    {
        return $this->configValue;
    }

    public function getEnvironment(): string
    {
        return $this->environment;
    }

    public function isSecret(): bool
    {
        return $this->isSecret;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    // ──────────────────────────────────────────────────────────────────────
    // Type-inferred value access
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Return the config value cast to its inferred PHP type.
     *
     * Inference order:
     *  1. JSON object/array  → decoded array
     *  2. "true" / "false"   → bool
     *  3. Numeric string     → int or float
     *  4. Everything else    → raw string
     *
     * @return mixed
     */
    public function getValue(): mixed
    {
        $raw = $this->configValue;

        // Attempt JSON decode for objects and arrays.
        if (str_starts_with($raw, '{') || str_starts_with($raw, '[')) {
            $decoded = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        // Boolean strings.
        if ($raw === 'true') {
            return true;
        }

        if ($raw === 'false') {
            return false;
        }

        // Numeric coercion.
        if (is_numeric($raw)) {
            return str_contains($raw, '.') ? (float) $raw : (int) $raw;
        }

        return $raw;
    }

    // ──────────────────────────────────────────────────────────────────────
    // Factory
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Hydrate a TenantConfiguration entity from a plain associative array.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (string) ($data['id'] ?? ''),
            tenantId: (string) ($data['tenant_id'] ?? ''),
            configKey: (string) ($data['config_key'] ?? ''),
            configValue: (string) ($data['config_value'] ?? ''),
            environment: (string) ($data['environment'] ?? 'production'),
            isSecret: (bool) ($data['is_secret'] ?? false),
            createdAt: self::parseDate($data['created_at'] ?? null),
            updatedAt: self::parseDate($data['updated_at'] ?? null),
        );
    }

    /**
     * Serialise the entity to a plain associative array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id'           => $this->id,
            'tenant_id'    => $this->tenantId,
            'config_key'   => $this->configKey,
            'config_value' => $this->configValue,
            'environment'  => $this->environment,
            'is_secret'    => $this->isSecret,
            'created_at'   => $this->createdAt->format(DateTimeImmutable::ATOM),
            'updated_at'   => $this->updatedAt->format(DateTimeImmutable::ATOM),
        ];
    }

    // ──────────────────────────────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────────────────────────────

    private static function parseDate(mixed $value): DateTimeImmutable
    {
        if ($value instanceof DateTimeImmutable) {
            return $value;
        }

        if ($value instanceof \DateTime) {
            return DateTimeImmutable::createFromMutable($value);
        }

        if (is_string($value) && $value !== '') {
            return new DateTimeImmutable($value);
        }

        return new DateTimeImmutable();
    }
}
