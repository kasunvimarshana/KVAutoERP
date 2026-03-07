<?php

namespace App\Application\Queries;

final class GetInventoryQuery
{
    public function __construct(
        public readonly string  $tenantId,
        public readonly ?string $id             = null,
        public readonly ?string $search         = null,
        public readonly array   $filters        = [],
        public readonly ?string $sortBy         = 'created_at',
        public readonly string  $sortDirection  = 'desc',
        public readonly ?int    $perPage        = null,
        public readonly int     $page           = 1
    ) {}

    public function toArray(): array
    {
        $params = [
            'filters'        => array_merge(['tenant_id' => $this->tenantId], $this->filters),
            'sort_by'        => $this->sortBy,
            'sort_direction' => $this->sortDirection,
            'page'           => $this->page,
        ];

        if (! empty($this->search)) {
            $params['search'] = $this->search;
        }

        if ($this->perPage !== null) {
            $params['per_page'] = $this->perPage;
        }

        return $params;
    }
}
