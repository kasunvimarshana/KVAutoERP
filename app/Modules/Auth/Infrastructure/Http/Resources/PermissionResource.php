<?php

declare(strict_types=1);

namespace Modules\Auth\Infrastructure\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PermissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'description' => $this->description,
            'module'      => $this->module,
            'action'      => $this->action,
            'created_at'  => isset($this->created_at) ? (string) $this->created_at : null,
            'updated_at'  => isset($this->updated_at) ? (string) $this->updated_at : null,
        ];
    }
}
