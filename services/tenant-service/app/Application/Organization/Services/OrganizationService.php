<?php

declare(strict_types=1);

namespace App\Application\Organization\Services;

use App\Application\Organization\Commands\CreateOrganizationCommand;
use App\Application\Organization\DTOs\OrganizationDTO;
use App\Application\Shared\DTOs\PaginationDTO;
use App\Domain\Organization\Repositories\OrganizationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

final class OrganizationService
{
    public function __construct(
        private readonly OrganizationRepositoryInterface $organizationRepository,
    ) {}

    public function createOrganization(CreateOrganizationCommand $command): OrganizationDTO
    {
        $slug = $command->slug ?: Str::slug($command->name);

        if ($this->organizationRepository->exists(['tenant_id' => $command->tenantId, 'slug' => $slug])) {
            throw new InvalidArgumentException("Organization with slug '{$slug}' already exists in this tenant.");
        }

        // Validate parent belongs to same tenant
        if ($command->parentId !== null) {
            $parent = $this->organizationRepository->findById($command->parentId);

            if ($parent === null) {
                throw new RuntimeException("Parent organization '{$command->parentId}' not found.", 404);
            }

            if ($parent->tenant_id !== $command->tenantId) {
                throw new InvalidArgumentException('Parent organization does not belong to the same tenant.');
            }
        }

        $org = $this->organizationRepository->create([
            'tenant_id'   => $command->tenantId,
            'parent_id'   => $command->parentId,
            'name'        => $command->name,
            'slug'        => $slug,
            'description' => $command->description,
            'status'      => $command->status,
            'settings'    => $command->settings ?: null,
            'metadata'    => $command->metadata ?: null,
        ]);

        Log::info('Organization created', ['org_id' => $org->id, 'tenant_id' => $command->tenantId]);

        return OrganizationDTO::fromEntity($org);
    }

    public function updateOrganization(string $id, array $data): OrganizationDTO
    {
        $org = $this->organizationRepository->findById($id);

        if ($org === null) {
            throw new RuntimeException("Organization '{$id}' not found.", 404);
        }

        // Prevent circular parent assignment
        if (isset($data['parent_id']) && $data['parent_id'] !== null) {
            if ($data['parent_id'] === $id) {
                throw new InvalidArgumentException('An organization cannot be its own parent.');
            }

            $descendants = $org->getDescendants();

            foreach ($descendants as $desc) {
                if ($desc->id === $data['parent_id']) {
                    throw new InvalidArgumentException('Cannot set a descendant as the parent (circular hierarchy).');
                }
            }
        }

        $updated = $this->organizationRepository->update($id, $data);

        return OrganizationDTO::fromEntity($updated);
    }

    public function deleteOrganization(string $id): void
    {
        $org = $this->organizationRepository->findById($id);

        if ($org === null) {
            throw new RuntimeException("Organization '{$id}' not found.", 404);
        }

        // Re-parent children to the deleted org's parent
        foreach ($org->children as $child) {
            $this->organizationRepository->update($child->id, ['parent_id' => $org->parent_id]);
        }

        $this->organizationRepository->delete($id);

        Log::info('Organization deleted', ['org_id' => $id]);
    }

    public function getOrganization(string $id): OrganizationDTO
    {
        $org = $this->organizationRepository->findById($id);

        if ($org === null) {
            throw new RuntimeException("Organization '{$id}' not found.", 404);
        }

        return OrganizationDTO::fromEntity($org);
    }

    public function getOrganizations(string $tenantId, PaginationDTO $pagination, array $filters = []): Collection|LengthAwarePaginator
    {
        $options = array_merge($filters, [
            'where'         => array_merge($filters['where'] ?? [], ['tenant_id' => $tenantId]),
            'perPage'       => $pagination->perPage,
            'page'          => $pagination->page,
            'sortBy'        => $pagination->sortBy,
            'sortDirection' => $pagination->sortDir,
        ]);

        return $this->organizationRepository->findAll($options);
    }

    /**
     * Return the full nested hierarchy tree for a tenant.
     *
     * @return list<OrganizationDTO>
     */
    public function getHierarchy(string $tenantId): array
    {
        $roots = $this->organizationRepository->findRoots($tenantId);

        $tree = [];

        foreach ($roots as $root) {
            $root->load('children.children.children'); // 3 levels deep
            $tree[] = OrganizationDTO::fromEntity($root, true);
        }

        return $tree;
    }
}
