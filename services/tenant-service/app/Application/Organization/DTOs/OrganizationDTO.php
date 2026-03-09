<?php

declare(strict_types=1);

namespace App\Application\Organization\DTOs;

use App\Domain\Organization\Entities\Organization;

final class OrganizationDTO
{
    public function __construct(
        public readonly string  $id,
        public readonly string  $tenantId,
        public readonly ?string $parentId,
        public readonly string  $name,
        public readonly string  $slug,
        public readonly ?string $description,
        public readonly string  $status,
        public readonly array   $settings,
        public readonly array   $metadata,
        public readonly string  $createdAt,
        public readonly string  $updatedAt,
        public readonly bool    $isActive,
        public readonly bool    $isRoot,
        public readonly int     $depth,
        /** @var list<self> */
        public readonly array   $children = [],
    ) {}

    public static function fromEntity(Organization $organization, bool $withChildren = false): self
    {
        $children = [];

        if ($withChildren && $organization->relationLoaded('children')) {
            foreach ($organization->children as $child) {
                $children[] = self::fromEntity($child, true);
            }
        }

        return new self(
            id:          $organization->id,
            tenantId:    $organization->tenant_id,
            parentId:    $organization->parent_id,
            name:        $organization->name,
            slug:        $organization->slug,
            description: $organization->description,
            status:      $organization->status,
            settings:    $organization->settings ?? [],
            metadata:    $organization->metadata ?? [],
            createdAt:   $organization->created_at->toIso8601String(),
            updatedAt:   $organization->updated_at->toIso8601String(),
            isActive:    $organization->isActive(),
            isRoot:      $organization->isRoot(),
            depth:       $organization->getDepth(),
            children:    $children,
        );
    }
}
