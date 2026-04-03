<?php declare(strict_types=1);
namespace Modules\Product\Infrastructure\Storage;
use Illuminate\Http\UploadedFile;
use Modules\Core\Application\Contracts\FileStorageServiceInterface;
use Modules\Product\Application\Contracts\ImageStorageStrategyInterface;
class DefaultImageStorageStrategy implements ImageStorageStrategyInterface {
    public function __construct(private FileStorageServiceInterface $fileStorage) {}
    public function store(UploadedFile $file, string $directory): string { return $this->fileStorage->store($file->getRealPath(), $directory, $file->getClientOriginalName()); }
    public function storeFromPath(string $sourcePath, string $directory, string $filename): string { return $this->fileStorage->store($sourcePath, $directory, $filename); }
    public function delete(string $path): bool { return $this->fileStorage->delete($path); }
    public function stream(string $path): mixed { return $this->fileStorage->stream($path); }
}
