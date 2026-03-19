<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\TenantServiceContract;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Location;
use App\Models\Organization;
use App\Models\Tenant;
use Illuminate\Support\Str;

class TenantService implements TenantServiceContract
{
    public function findById(string $tenantId): ?array
    {
        $tenant = Tenant::withCount('organizations')->find($tenantId);

        return $tenant ? $this->toArray($tenant) : null;
    }

    public function create(array $data): array
    {
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $tenant = Tenant::create([
            'id'            => (string) Str::uuid(),
            'name'          => $data['name'],
            'slug'          => $data['slug'],
            'status'        => $data['status'] ?? 'active',
            'iam_provider'  => $data['iam_provider'] ?? 'local',
            'configuration' => $data['configuration'] ?? null,
        ]);

        return $this->toArray($tenant);
    }

    public function update(string $tenantId, array $data): array
    {
        $tenant = Tenant::findOrFail($tenantId);
        $tenant->update($data);

        return $this->toArray($tenant->fresh());
    }

    public function delete(string $tenantId): void
    {
        Tenant::findOrFail($tenantId)->delete();
    }

    public function getHierarchy(string $tenantId): array
    {
        $tenant = Tenant::with([
            'organizations.branches.locations.departments',
        ])->findOrFail($tenantId);

        return [
            'id'            => $tenant->id,
            'name'          => $tenant->name,
            'slug'          => $tenant->slug,
            'status'        => $tenant->status,
            'iam_provider'  => $tenant->iam_provider,
            'organizations' => $tenant->organizations->map(
                fn (Organization $org) => [
                    'id'       => $org->id,
                    'name'     => $org->name,
                    'code'     => $org->code,
                    'status'   => $org->status,
                    'branches' => $org->branches->map(
                        fn (Branch $branch) => [
                            'id'        => $branch->id,
                            'name'      => $branch->name,
                            'code'      => $branch->code,
                            'status'    => $branch->status,
                            'locations' => $branch->locations->map(
                                fn (Location $loc) => [
                                    'id'          => $loc->id,
                                    'name'        => $loc->name,
                                    'code'        => $loc->code,
                                    'status'      => $loc->status,
                                    'departments' => $loc->departments->map(
                                        fn (Department $dept) => [
                                            'id'     => $dept->id,
                                            'name'   => $dept->name,
                                            'code'   => $dept->code,
                                            'status' => $dept->status,
                                        ]
                                    )->all(),
                                ]
                            )->all(),
                        ]
                    )->all(),
                ]
            )->all(),
        ];
    }

    public function list(array $filters = [], int $perPage = 20): array
    {
        $query = Tenant::withCount('organizations');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['search'])) {
            $query->where('name', 'like', '%'.$filters['search'].'%');
        }

        $paginated = $query->paginate($perPage);

        return [
            'data'       => $paginated->map(fn (Tenant $t) => $this->toArray($t))->all(),
            'pagination' => [
                'total'        => $paginated->total(),
                'per_page'     => $paginated->perPage(),
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
            ],
        ];
    }

    // ──────────────────────────────────────────────────────────
    // Internal helpers
    // ──────────────────────────────────────────────────────────

    private function toArray(Tenant $tenant): array
    {
        return [
            'id'                  => $tenant->id,
            'name'                => $tenant->name,
            'slug'                => $tenant->slug,
            'status'              => $tenant->status,
            'iam_provider'        => $tenant->iam_provider,
            'configuration'       => $tenant->configuration,
            'organizations_count' => $tenant->organizations_count ?? null,
            'created_at'          => $tenant->created_at?->toIso8601String(),
            'updated_at'          => $tenant->updated_at?->toIso8601String(),
        ];
    }
}
