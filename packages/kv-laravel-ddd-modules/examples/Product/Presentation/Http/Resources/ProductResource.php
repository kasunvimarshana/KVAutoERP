<?php

declare(strict_types=1);

namespace LaravelDDD\Examples\Product\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use LaravelDDD\Examples\Product\Application\DTOs\ProductDTO;

/**
 * API Resource transformer for ProductDTO.
 *
 * @property ProductDTO $resource
 */
class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var ProductDTO $dto */
        $dto = $this->resource;

        return [
            'id'       => $dto->id,
            'name'     => $dto->name,
            'price'    => [
                'amount'   => $dto->price,
                'currency' => $dto->currency,
                'formatted' => number_format($dto->price / 100, 2).' '.$dto->currency,
            ],
            'status'   => $dto->status,
        ];
    }
}
