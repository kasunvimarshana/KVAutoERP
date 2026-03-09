<?php

declare(strict_types=1);

namespace App\Modules\Order\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class OrderCollection extends ResourceCollection
{
    /** @var class-string */
    public $collects = OrderResource::class;

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
                ]
            ),
        ];
    }
}
