<?php

namespace Shared\Core\Hierarchies;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait HierarchicalIsolation
{
    /**
     * Scope a query to include only records within the organization's hierarchy.
     */
    public function scopeWithinOrgHierarchy(Builder $query, int $orgId, bool $includeChildren = true): Builder
    {
        if ($includeChildren) {
            $descendants = $this->getOrgDescendants($orgId);
            return $query->whereIn('organisation_id', array_merge([$orgId], $descendants));
        }

        return $query->where('organisation_id', $orgId);
    }

    /**
     * Mock function for organization descendant lookup.
     * In a real system, this would query a closure table for 10k+ scalability.
     */
    protected function getOrgDescendants(int $orgId): array
    {
        return []; // Placeholder for closure table lookup
    }
}
