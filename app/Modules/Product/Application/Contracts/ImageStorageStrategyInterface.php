<?php declare(strict_types=1);
namespace Modules\Product\Application\Contracts;
use Illuminate\Http\UploadedFile;
interface ImageStorageStrategyInterface {
    public function store(UploadedFile $file, string $directory): string;
    public function storeFromPath(string $sourcePath, string $directory, string $filename): string;
    public function delete(string $path): bool;
    public function stream(string $path): mixed;
}
