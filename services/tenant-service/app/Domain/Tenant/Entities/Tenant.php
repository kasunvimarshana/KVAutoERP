<?php

declare(strict_types=1);

namespace App\Domain\Tenant\Entities;

use DateTimeImmutable;

/**
 * Tenant Domain Entity.
 *
 * Pure domain object — no Eloquent, no framework dependencies.
 * Represents a tenant in the multi-tenant SaaS inventory system.
 */
final class Tenant
{
    /**
     * @param  string             $id            UUID primary key.
     * @param  string             $name          Human-readable display name.
     * @param  string             $slug          URL-safe unique identifier.
     * @param  string|null        $domain        Custom domain for tenant (e.g. acme.example.com).
     * @param  string             $databaseName  Isolated database or schema name.
     * @param  array<string,mixed> $settings     Tenant-level settings bag.
     * @param  bool               $isActive      Whether the tenant account is active.
     * @param  string             $plan          Subscription plan (starter|pro|enterprise).
     * @param  string             $billingEmail  Billing contact email address.
     * @param  DateTimeImmutable  $createdAt     Record creation timestamp.
     * @param  DateTimeImmutable  $updatedAt     Last update timestamp.
     */
    public function __construct(
        private readonly string $id,
        private readonly string $name,
        private readonly string $slug,
        private readonly ?string $domain,
        private readonly string $databaseName,
        private readonly array $settings,
        private readonly bool $isActive,
        private readonly string $plan,
        private readonly string $billingEmail,
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

    public function getName(): string
    {
        return $this->name;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getDomain(): ?string
    {
        return $this->domain;
    }

    public function getDatabaseName(): string
    {
        return $this->databaseName;
    }

    /** @return array<string,mixed> */
    public function getSettings(): array
    {
        return $this->settings;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getPlan(): string
    {
        return $this->plan;
    }

    public function getBillingEmail(): string
    {
        return $this->billingEmail;
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
    // Infrastructure helpers
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Build the Laravel DB connection config array for this tenant's database.
     *
     * @return array<string, mixed>
     */
    public function getDbConnectionConfig(): array
    {
        return [
            'driver'    => 'mysql',
            'host'      => env('DB_HOST', '127.0.0.1'),
            'port'      => env('DB_PORT', '3306'),
            'database'  => $this->databaseName,
            'username'  => env('DB_USERNAME', 'root'),
            'password'  => env('DB_PASSWORD', ''),
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
            'strict'    => true,
            'engine'    => null,
        ];
    }

    /**
     * Return the Redis / cache key prefix for this tenant.
     */
    public function getCachePrefix(): string
    {
        return 'tenant:' . $this->slug . ':';
    }

    /**
     * Return the queue connection name to use for this tenant.
     */
    public function getQueueConnection(): string
    {
        return $this->settings['queue_connection'] ?? env('QUEUE_CONNECTION', 'sync');
    }

    // ──────────────────────────────────────────────────────────────────────
    // Factory
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Hydrate a Tenant entity from a plain associative array.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (string) ($data['id'] ?? ''),
            name: (string) ($data['name'] ?? ''),
            slug: (string) ($data['slug'] ?? ''),
            domain: isset($data['domain']) && $data['domain'] !== '' ? (string) $data['domain'] : null,
            databaseName: (string) ($data['database_name'] ?? ''),
            settings: is_array($data['settings'] ?? null) ? $data['settings'] : [],
            isActive: (bool) ($data['is_active'] ?? true),
            plan: (string) ($data['plan'] ?? 'starter'),
            billingEmail: (string) ($data['billing_email'] ?? ''),
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
            'id'            => $this->id,
            'name'          => $this->name,
            'slug'          => $this->slug,
            'domain'        => $this->domain,
            'database_name' => $this->databaseName,
            'settings'      => $this->settings,
            'is_active'     => $this->isActive,
            'plan'          => $this->plan,
            'billing_email' => $this->billingEmail,
            'created_at'    => $this->createdAt->format(DateTimeImmutable::ATOM),
            'updated_at'    => $this->updatedAt->format(DateTimeImmutable::ATOM),
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
