<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'slug'       => $this->slug,
            'domain'     => $this->domain,
            'status'     => $this->status,
            'plan'       => $this->plan,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
