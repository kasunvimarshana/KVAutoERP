<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->resource['id'] ?? $this->id,
            'name'                => $this->resource['name'] ?? $this->name,
            'slug'                => $this->resource['slug'] ?? $this->slug,
            'status'              => $this->resource['status'] ?? $this->status,
            'iam_provider'        => $this->resource['iam_provider'] ?? $this->iam_provider,
            'organizations_count' => $this->resource['organizations_count'] ?? null,
            'created_at'          => $this->resource['created_at'] ?? null,
        ];
    }
}
