<?php

declare(strict_types=1);

namespace App\Application\Tenant\Commands;

/**
 * Command: Update Tenant.
 *
 * Carries all mutable fields that may be updated on an existing tenant.
 * All fields except tenantId are nullable — only non-null values are applied.
 */
final readonly class UpdateTenantCommand
{
    /**
     * @param  string              $tenantId      Target tenant UUID.
     * @param  string|null         $name          New display name.
     * @param  string|null         $domain        New custom domain (empty string = remove).
     * @param  string|null         $plan          New subscription plan.
     * @param  string|null         $billingEmail  New billing email address.
     * @param  bool|null           $isActive      Activation toggle.
     * @param  array<string,mixed>|null $settings Partial settings overrides.
     */
    public function __construct(
        public string $tenantId,
        public ?string $name = null,
        public ?string $domain = null,
        public ?string $plan = null,
        public ?string $billingEmail = null,
        public ?bool $isActive = null,
        public ?array $settings = null,
    ) {}
}
