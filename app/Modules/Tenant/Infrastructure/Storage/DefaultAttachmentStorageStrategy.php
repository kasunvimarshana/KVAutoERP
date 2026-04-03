<?php
declare(strict_types=1);
namespace Modules\Tenant\Infrastructure\Storage;
use Illuminate\Http\UploadedFile;
use Modules\Core\Application\Contracts\FileStorageServiceInterface;
use Modules\Tenant\Application\Contracts\AttachmentStorageStrategyInterface;

class DefaultAttachmentStorageStrategy implements AttachmentStorageStrategyInterface {
    public function __construct(private FileStorageServiceInterface $fileStorage) {}

    public function store(UploadedFile $file, int $tenantId): string {
        return $this->fileStorage->storeFile($file, "tenants/{$tenantId}");
    }

    public function storeFromPath(string $path, int $tenantId): string {
        return $this->fileStorage->store($path, "tenants/{$tenantId}", basename($path));
    }

    public function delete(string $path): bool {
        return $this->fileStorage->delete($path);
    }

    public function stream(string $path): mixed {
        return $this->fileStorage->stream($path);
    }
}
