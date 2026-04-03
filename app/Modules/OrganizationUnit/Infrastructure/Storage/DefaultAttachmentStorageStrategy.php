<?php
declare(strict_types=1);
namespace Modules\OrganizationUnit\Infrastructure\Storage;
use Illuminate\Http\UploadedFile;
use Modules\Core\Application\Contracts\FileStorageServiceInterface;
use Modules\OrganizationUnit\Application\Contracts\AttachmentStorageStrategyInterface;

class DefaultAttachmentStorageStrategy implements AttachmentStorageStrategyInterface {
    public function __construct(private FileStorageServiceInterface $fileStorage) {}

    public function store(UploadedFile $file, int $orgUnitId): string {
        return $this->fileStorage->storeFile($file, "org-units/{$orgUnitId}");
    }

    public function storeFromPath(string $path, int $orgUnitId): string {
        return $this->fileStorage->store($path, "org-units/{$orgUnitId}", basename($path));
    }

    public function delete(string $path): bool {
        return $this->fileStorage->delete($path);
    }

    public function stream(string $path): mixed {
        return $this->fileStorage->stream($path);
    }

    public function url(string $path): string {
        return $this->fileStorage->url($path);
    }
}
