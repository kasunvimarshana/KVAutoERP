<?php

declare(strict_types=1);

namespace App\Http\Resources\Order;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Order Collection Resource.
 */
class OrderCollection extends ResourceCollection
{
    public $collects = OrderResource::class;

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
                ],
            ),
        ];
    }
}
