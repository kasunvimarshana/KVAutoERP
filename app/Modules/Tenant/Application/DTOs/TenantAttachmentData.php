<?php
declare(strict_types=1);
namespace Modules\Tenant\Application\DTOs;
use Modules\Core\Application\DTOs\BaseDto;

class TenantAttachmentData extends BaseDto {
    public ?int $tenant_id = null;
    public ?string $name = null;
    public ?string $file_path = null;
    public ?string $mime_type = null;
    public ?int $size = null;
    public ?string $type = null;
    public ?array $metadata = null;
}
