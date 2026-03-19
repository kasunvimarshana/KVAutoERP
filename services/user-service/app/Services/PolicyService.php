<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\PolicyServiceContract;
use App\Models\Policy;
use Illuminate\Support\Str;

/**
 * Attribute-Based Access Control (ABAC) policy evaluation engine.
 *
 * Policies are stored in the `policies` table and evaluated in priority
 * order (higher priority first).  The first matching policy whose effect
 * is "deny" returns false; the first matching "allow" returns true.
 * If no policy matches, the default is DENY (fail-secure).
 */
class PolicyService implements PolicyServiceContract
{
    // ──────────────────────────────────────────────────────────
    // Evaluation
    // ──────────────────────────────────────────────────────────

    public function evaluate(
        array  $subject,
        string $action,
        array  $resource    = [],
        array  $environment = [],
    ): bool {
        $tenantId = $subject['tenant_id'] ?? null;

        // Load active policies for this tenant + global policies, ordered by priority desc
        $policies = Policy::where('is_active', true)
            ->where(function ($q) use ($tenantId): void {
                $q->whereNull('tenant_id');
                if ($tenantId) {
                    $q->orWhere('tenant_id', $tenantId);
                }
            })
            ->orderByDesc('priority')
            ->get();

        foreach ($policies as $policy) {
            if (! $this->matchesAction($policy->action, $action)) {
                continue;
            }

            if (! $this->matchesConditions((array) ($policy->subject_conditions ?? []), $subject)) {
                continue;
            }

            if (! $this->matchesConditions((array) ($policy->resource_conditions ?? []), $resource)) {
                continue;
            }

            if (! $this->matchesConditions((array) ($policy->environment_conditions ?? []), $environment)) {
                continue;
            }

            // First matching policy wins
            return $policy->isAllow();
        }

        // Default: deny (fail-secure)
        return false;
    }

    // ──────────────────────────────────────────────────────────
    // CRUD
    // ──────────────────────────────────────────────────────────

    public function create(array $data): array
    {
        $policy = Policy::create([
            'id'                    => (string) Str::uuid(),
            'tenant_id'             => $data['tenant_id'] ?? null,
            'name'                  => $data['name'],
            'slug'                  => $data['slug'] ?? Str::slug($data['name']),
            'description'           => $data['description'] ?? null,
            'effect'                => $data['effect'] ?? 'allow',
            'action'                => $data['action'],
            'subject_conditions'    => $data['subject_conditions'] ?? null,
            'resource_conditions'   => $data['resource_conditions'] ?? null,
            'environment_conditions' => $data['environment_conditions'] ?? null,
            'is_active'             => $data['is_active'] ?? true,
            'priority'              => $data['priority'] ?? 0,
            'created_by'            => $data['created_by'] ?? null,
        ]);

        return $this->toArray($policy);
    }

    public function update(string $policyId, array $data): array
    {
        $policy = Policy::findOrFail($policyId);

        $allowedFields = [
            'name', 'slug', 'description', 'effect', 'action',
            'subject_conditions', 'resource_conditions',
            'environment_conditions', 'is_active', 'priority',
        ];

        $policy->update(array_intersect_key($data, array_flip($allowedFields)));

        return $this->toArray($policy->fresh());
    }

    public function delete(string $policyId): void
    {
        Policy::findOrFail($policyId)->delete();
    }

    public function findById(string $policyId): ?array
    {
        $policy = Policy::find($policyId);

        return $policy ? $this->toArray($policy) : null;
    }

    public function list(string $tenantId, array $filters = [], int $perPage = 20): array
    {
        $query = Policy::where(function ($q) use ($tenantId): void {
            $q->whereNull('tenant_id');
            if ($tenantId) {
                $q->orWhere('tenant_id', $tenantId);
            }
        });

        if (isset($filters['is_active'])) {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        if (! empty($filters['effect'])) {
            $query->where('effect', $filters['effect']);
        }

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters): void {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('slug', 'like', '%' . $filters['search'] . '%');
            });
        }

        $paginated = $query->orderByDesc('priority')->paginate($perPage);

        return [
            'data'       => $paginated->map(fn (Policy $p) => $this->toArray($p))->all(),
            'pagination' => [
                'total'        => $paginated->total(),
                'per_page'     => $paginated->perPage(),
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
            ],
        ];
    }

    // ──────────────────────────────────────────────────────────
    // Matching helpers
    // ──────────────────────────────────────────────────────────

    /**
     * Match a policy action pattern against the requested action.
     * Supports "*" wildcard (e.g. "users:*" matches "users:read").
     */
    private function matchesAction(string $policyAction, string $requestedAction): bool
    {
        if ($policyAction === '*') {
            return true;
        }

        // Convert glob-style pattern to regex: "users:*" → "/^users:.+$/"
        // Use '.+' to require at least one character after the wildcard position
        $pattern = '/^' . str_replace('\*', '.+', preg_quote($policyAction, '/')) . '$/';

        return (bool) preg_match($pattern, $requestedAction);
    }

    /**
     * Check that all conditions defined in the policy match the provided attributes.
     *
     * Each condition key maps to a value that can be:
     *  - "*"        : any value passes
     *  - a string   : exact match
     *  - an array   : value must be one of the listed items
     */
    private function matchesConditions(array $conditions, array $attributes): bool
    {
        foreach ($conditions as $key => $expected) {
            $actual = $attributes[$key] ?? null;

            if ($expected === '*') {
                continue;
            }

            if (is_array($expected)) {
                // Actual must intersect with expected list
                $actual = is_array($actual) ? $actual : [$actual];
                if (empty(array_intersect($actual, $expected))) {
                    return false;
                }
                continue;
            }

            // String comparison: allow {{tenant_id}} placeholder
            $expected = $this->resolvePlaceholders($expected, $attributes);

            if (is_array($actual)) {
                if (! in_array($expected, $actual, true)) {
                    return false;
                }
            } elseif ((string) $actual !== (string) $expected) {
                return false;
            }
        }

        return true;
    }

    /**
     * Replace {{key}} placeholders in condition values with attribute values.
     */
    private function resolvePlaceholders(string $value, array $attributes): string
    {
        return preg_replace_callback('/\{\{(\w+)\}\}/', function (array $m) use ($attributes): string {
            return (string) ($attributes[$m[1]] ?? $m[0]);
        }, $value) ?? $value;
    }

    // ──────────────────────────────────────────────────────────
    // Serialisation
    // ──────────────────────────────────────────────────────────

    private function toArray(Policy $policy): array
    {
        return [
            'id'                     => $policy->id,
            'tenant_id'              => $policy->tenant_id,
            'name'                   => $policy->name,
            'slug'                   => $policy->slug,
            'description'            => $policy->description,
            'effect'                 => $policy->effect,
            'action'                 => $policy->action,
            'subject_conditions'     => $policy->subject_conditions,
            'resource_conditions'    => $policy->resource_conditions,
            'environment_conditions' => $policy->environment_conditions,
            'is_active'              => $policy->is_active,
            'priority'               => $policy->priority,
            'created_by'             => $policy->created_by,
            'created_at'             => $policy->created_at?->toIso8601String(),
            'updated_at'             => $policy->updated_at?->toIso8601String(),
        ];
    }
}
