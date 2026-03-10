<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'tenant_id'   => $this->tenant_id,
            'category_id' => $this->category_id,
            'category'    => $this->whenLoaded('category', fn() => new CategoryResource($this->category)),
            'name'        => $this->name,
            'code'        => $this->code,
            'sku'         => $this->sku,
            'description' => $this->description,
            'price'       => $this->price,
            'cost'        => $this->cost,
            'unit'        => $this->unit,
            'status'      => $this->status,
            'attributes'  => $this->attributes,
            'created_at'  => $this->created_at->toIso8601String(),
            'updated_at'  => $this->updated_at->toIso8601String(),
        ];
    }
}
