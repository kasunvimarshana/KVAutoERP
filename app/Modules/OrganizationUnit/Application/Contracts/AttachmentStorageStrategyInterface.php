<?php
declare(strict_types=1);
namespace Modules\OrganizationUnit\Application\Contracts;
use Illuminate\Http\UploadedFile;

interface AttachmentStorageStrategyInterface {
    public function store(UploadedFile $file, int $orgUnitId): string;
    public function storeFromPath(string $path, int $orgUnitId): string;
    public function delete(string $path): bool;
    public function stream(string $path): mixed;
    public function url(string $path): string;
}
