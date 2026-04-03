<?php
declare(strict_types=1);
namespace Modules\Category\Application\DTOs;

class CategoryImageData
{
    public int $tenant_id = 0;
    public int $category_id = 0;
    public string $uuid = '';
    public string $name = '';
    public string $file_path = '';
    public string $mime_type = '';
    public int $size = 0;
    public ?array $metadata = null;

    public static function fromArray(array $data): self
    {
        $dto             = new self();
        $dto->tenant_id  = $data['tenant_id'] ?? 0;
        $dto->category_id = $data['category_id'] ?? 0;
        $dto->uuid       = $data['uuid'] ?? '';
        $dto->name       = $data['name'] ?? '';
        $dto->file_path  = $data['file_path'] ?? '';
        $dto->mime_type  = $data['mime_type'] ?? '';
        $dto->size       = $data['size'] ?? 0;
        $dto->metadata   = $data['metadata'] ?? null;
        return $dto;
    }

    public function toArray(): array
    {
        return [
            'tenant_id'   => $this->tenant_id,
            'category_id' => $this->category_id,
            'uuid'        => $this->uuid,
            'name'        => $this->name,
            'file_path'   => $this->file_path,
            'mime_type'   => $this->mime_type,
            'size'        => $this->size,
            'metadata'    => $this->metadata,
        ];
    }
}
