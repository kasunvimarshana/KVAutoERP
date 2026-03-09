<?php

declare(strict_types=1);

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Tenant Collection Resource.
 *
 * Wraps a paginated or full list of tenants with metadata.
 */
class TenantCollection extends ResourceCollection
{
    public $collects = TenantResource::class;

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'success' => true,
            'data'    => $this->collection,
            'meta'    => $this->when(
                method_exists($this->resource, 'total'),
                fn () => [
                    'current_page' => $this->resource->currentPage(),
                    'per_page'     => $this->resource->perPage(),
                    'total'        => $this->resource->total(),
                    'last_page'    => $this->resource->lastPage(),
                    'from'         => $this->resource->firstItem(),
                    'to'           => $this->resource->lastItem(),
                ],
            ),
        ];
    }
}
