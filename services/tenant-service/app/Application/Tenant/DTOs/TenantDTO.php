<?php

declare(strict_types=1);

namespace App\Application\Tenant\DTOs;

use App\Domain\Tenant\Entities\Tenant;

final class TenantDTO
{
    public function __construct(
        public readonly string  $id,
        public readonly string  $name,
        public readonly string  $slug,
        public readonly ?string $domain,
        public readonly string  $status,
        public readonly string  $plan,
        public readonly array   $settings,
        public readonly array   $config,
        public readonly int     $maxUsers,
        public readonly int     $maxOrganizations,
        public readonly ?string $trialEndsAt,
        public readonly ?string $subscriptionEndsAt,
        public readonly array   $metadata,
        public readonly string  $createdAt,
        public readonly string  $updatedAt,
        public readonly bool    $isActive,
        public readonly bool    $isOnTrial,
        public readonly bool    $isPlanActive,
    ) {}

    public static function fromEntity(Tenant $tenant): self
    {
        return new self(
            id:                 $tenant->id,
            name:               $tenant->name,
            slug:               $tenant->slug,
            domain:             $tenant->domain,
            status:             $tenant->status,
            plan:               $tenant->plan,
            settings:           $tenant->settings ?? [],
            config:             $tenant->config ?? [],
            maxUsers:           $tenant->max_users,
            maxOrganizations:   $tenant->max_organizations,
            trialEndsAt:        $tenant->trial_ends_at?->toIso8601String(),
            subscriptionEndsAt: $tenant->subscription_ends_at?->toIso8601String(),
            metadata:           $tenant->metadata ?? [],
            createdAt:          $tenant->created_at->toIso8601String(),
            updatedAt:          $tenant->updated_at->toIso8601String(),
            isActive:           $tenant->isActive(),
            isOnTrial:          $tenant->isOnTrial(),
            isPlanActive:       $tenant->isPlanActive(),
        );
    }
}
