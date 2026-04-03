<?php
declare(strict_types=1);
namespace Modules\Tenant\Application\Contracts;
use Illuminate\Http\UploadedFile;

interface AttachmentStorageStrategyInterface {
    public function store(UploadedFile $file, int $tenantId): string;
    public function storeFromPath(string $path, int $tenantId): string;
    public function delete(string $path): bool;
    public function stream(string $path): mixed;
}
