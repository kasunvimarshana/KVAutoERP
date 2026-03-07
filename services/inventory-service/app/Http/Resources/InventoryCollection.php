<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

class InventoryCollection extends ResourceCollection
{
    public $collects = InventoryResource::class;

    public function toArray($request): array
    {
        $data = [
            'data' => $this->collection,
        ];

        // Attach pagination meta only when the underlying resource is a paginator
        if ($this->resource instanceof LengthAwarePaginator) {
            $data['meta'] = [
                'total'        => $this->resource->total(),
                'per_page'     => $this->resource->perPage(),
                'current_page' => $this->resource->currentPage(),
                'last_page'    => $this->resource->lastPage(),
                'from'         => $this->resource->firstItem(),
                'to'           => $this->resource->lastItem(),
            ];
            $data['links'] = [
                'first' => $this->resource->url(1),
                'last'  => $this->resource->url($this->resource->lastPage()),
                'prev'  => $this->resource->previousPageUrl(),
                'next'  => $this->resource->nextPageUrl(),
            ];
        } else {
            $data['meta'] = [
                'total' => $this->collection->count(),
            ];
        }

        return $data;
    }
}
