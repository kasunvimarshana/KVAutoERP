<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'category' => $this->whenLoaded('category', function() {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                ];
            }),
            'base_uom' => $this->whenLoaded('baseUom', function() {
                return [
                    'id' => $this->baseUom->id,
                    'name' => $this->baseUom->name,
                    'short_name' => $this->baseUom->short_name,
                ];
            }),
            'prices' => $this->whenLoaded('prices'),
            'images' => $this->whenLoaded('images', function() {
                return $this->images->pluck('url');
            }),
            'is_traceable' => $this->is_traceable,
            'traceability_type' => $this->traceability_type,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
