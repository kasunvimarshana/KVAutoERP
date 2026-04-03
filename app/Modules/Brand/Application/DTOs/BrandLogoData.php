<?php
declare(strict_types=1);
namespace Modules\Brand\Application\DTOs;
class BrandLogoData
{
    public int $tenant_id = 0;
    public int $brand_id = 0;
    public string $uuid = '';
    public string $name = '';
    public string $file_path = '';
    public string $mime_type = '';
    public int $size = 0;
    public ?array $metadata = null;
    public static function fromArray(array $data): self
    {
        $dto = new self();
        foreach ($data as $k => $v) if (property_exists($dto, $k)) $dto->$k = $v;
        return $dto;
    }
    public function toArray(): array { return get_object_vars($this); }
}
