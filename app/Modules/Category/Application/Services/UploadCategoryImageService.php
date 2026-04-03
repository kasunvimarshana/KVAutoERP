<?php
declare(strict_types=1);
namespace Modules\Category\Application\Services;

use Modules\Category\Application\Contracts\UploadCategoryImageServiceInterface;
use Modules\Category\Domain\Entities\CategoryImage;
use Modules\Category\Domain\RepositoryInterfaces\CategoryImageRepositoryInterface;
use Modules\Core\Application\Services\BaseService;

class UploadCategoryImageService extends BaseService implements UploadCategoryImageServiceInterface
{
    public function __construct(CategoryImageRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    protected function handle(array $data): CategoryImage
    {
        $image = new CategoryImage(
            tenantId:   $data['tenant_id'],
            categoryId: $data['category_id'],
            uuid:       $data['uuid'],
            name:       $data['name'],
            filePath:   $data['file_path'],
            mimeType:   $data['mime_type'],
            size:       $data['size'],
            metadata:   $data['metadata'] ?? null,
        );
        return $this->repository->save($image);
    }
}
