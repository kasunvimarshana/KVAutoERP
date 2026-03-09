<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * ProductCollection – wraps paginated product lists.
 */
class ProductCollection extends ResourceCollection
{
    /** @var class-string */
    public $collects = ProductResource::class;

    /** @return array<string,mixed> */
    public function toArray(Request $request): array
    {
        return [
            'items'      => $this->collection,
            'pagination' => $this->when(
                $this->resource instanceof \Illuminate\Pagination\AbstractPaginator,
                fn () => [
                    'total'        => $this->resource->total(),
                    'per_page'     => $this->resource->perPage(),
                    'current_page' => $this->resource->currentPage(),
                    'last_page'    => $this->resource->lastPage(),
                    'from'         => $this->resource->firstItem(),
                    'to'           => $this->resource->lastItem(),
                ]
            ),
        ];
    }
}
