<?php

declare(strict_types=1);

namespace App\Application\Tenant\Commands;

/**
 * Command: Create Tenant.
 *
 * Carries all data required to register a new tenant and provision its resources.
 */
final readonly class CreateTenantCommand
{
    /**
     * @param  string              $name          Human-readable tenant name.
     * @param  string              $slug          URL-safe unique identifier.
     * @param  string|null         $domain        Optional custom domain.
     * @param  string              $plan          Subscription plan (starter|pro|enterprise).
     * @param  string              $billingEmail  Billing contact email.
     * @param  string              $adminEmail    Email for the initial admin user.
     * @param  array<string,mixed> $settings      Initial settings bag.
     */
    public function __construct(
        public string $name,
        public string $slug,
        public ?string $domain,
        public string $plan,
        public string $billingEmail,
        public string $adminEmail,
        public array $settings = [],
    ) {}
}
