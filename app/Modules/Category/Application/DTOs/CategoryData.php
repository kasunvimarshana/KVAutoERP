<?php
declare(strict_types=1);
namespace Modules\Category\Application\DTOs;

class CategoryData
{
    public int $tenant_id = 0;
    public string $name = '';
    public string $slug = '';
    public ?string $description = null;
    public ?int $parent_id = null;
    public string $status = 'active';
    public int $depth = 0;
    public string $path = '';
    public ?array $attributes = null;
    public ?array $metadata = null;

    public static function fromArray(array $data): self
    {
        $dto             = new self();
        $dto->tenant_id  = $data['tenant_id'] ?? 0;
        $dto->name       = $data['name'] ?? '';
        $dto->slug       = $data['slug'] ?? '';
        $dto->description = $data['description'] ?? null;
        $dto->parent_id  = isset($data['parent_id']) ? (int)$data['parent_id'] : null;
        $dto->status     = $data['status'] ?? 'active';
        $dto->depth      = $data['depth'] ?? 0;
        $dto->path       = $data['path'] ?? '';
        $dto->attributes = $data['attributes'] ?? null;
        $dto->metadata   = $data['metadata'] ?? null;
        return $dto;
    }

    public function toArray(): array
    {
        return [
            'tenant_id'   => $this->tenant_id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'description' => $this->description,
            'parent_id'   => $this->parent_id,
            'status'      => $this->status,
            'depth'       => $this->depth,
            'path'        => $this->path,
            'attributes'  => $this->attributes,
            'metadata'    => $this->metadata,
        ];
    }
}
