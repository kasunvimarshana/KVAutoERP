<?php
declare(strict_types=1);
namespace Modules\Brand\Application\DTOs;
class BrandData
{
    public int $tenant_id = 0;
    public string $name = '';
    public string $slug = '';
    public ?string $description = null;
    public ?string $website = null;
    public string $status = 'active';
    public ?array $attributes = null;
    public ?array $metadata = null;

    public static function fromArray(array $data): self
    {
        $dto             = new self();
        $dto->tenant_id  = $data['tenant_id'] ?? 0;
        $dto->name       = $data['name'] ?? '';
        $dto->slug       = $data['slug'] ?? '';
        $dto->description = $data['description'] ?? null;
        $dto->website    = $data['website'] ?? null;
        $dto->status     = $data['status'] ?? 'active';
        $dto->attributes = $data['attributes'] ?? null;
        $dto->metadata   = $data['metadata'] ?? null;
        return $dto;
    }

    public function toArray(): array
    {
        return ['tenant_id' => $this->tenant_id, 'name' => $this->name, 'slug' => $this->slug, 'description' => $this->description, 'website' => $this->website, 'status' => $this->status, 'attributes' => $this->attributes, 'metadata' => $this->metadata];
    }
}
