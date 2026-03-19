<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Workflow state machine definitions.
 * Describes states, transitions, guards, and actions for entity lifecycle management.
 * Used by the Workflow Service to drive dynamic approval chains and process automation.
 */
class WorkflowDefinition extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'id',
        'tenant_id',
        'name',
        'entity_type',
        'states',
        'transitions',
        'guards',
        'actions',
        'is_active',
        'version',
        'metadata',
    ];

    protected $casts = [
        'states'      => 'array',
        'transitions' => 'array',
        'guards'      => 'array',
        'actions'     => 'array',
        'is_active'   => 'boolean',
        'version'     => 'integer',
        'metadata'    => 'array',
    ];

    // -----------------------------------------------------------------
    // Scopes
    // -----------------------------------------------------------------

    public function scopeForTenant(\Illuminate\Database\Eloquent\Builder $query, string $tenantId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForEntity(\Illuminate\Database\Eloquent\Builder $query, string $entityType): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('entity_type', $entityType);
    }

    // -----------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------

    /** Return available transition events from the given state. */
    public function getTransitionsFrom(string $fromState): array
    {
        return array_values(array_filter(
            $this->transitions ?? [],
            fn (array $t) => ($t['from'] ?? '') === $fromState,
        ));
    }

    /** Resolve which state a transition leads to given an event. */
    public function resolveNextState(string $fromState, string $event): ?string
    {
        foreach ($this->transitions ?? [] as $transition) {
            if (($transition['from'] ?? '') === $fromState && ($transition['event'] ?? '') === $event) {
                return $transition['to'] ?? null;
            }
        }

        return null;
    }
}
