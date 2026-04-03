<?php
declare(strict_types=1);
namespace Modules\Brand\Application\Services;
use Modules\Brand\Application\Contracts\UploadBrandLogoServiceInterface;
use Modules\Brand\Domain\Entities\BrandLogo;
use Modules\Brand\Domain\RepositoryInterfaces\BrandLogoRepositoryInterface;
use Modules\Core\Application\Services\BaseService;
class UploadBrandLogoService extends BaseService implements UploadBrandLogoServiceInterface
{
    public function __construct(BrandLogoRepositoryInterface $repository) { parent::__construct($repository); }
    protected function handle(array $data): BrandLogo
    {
        $logo = new BrandLogo($data['tenant_id'], $data['brand_id'], $data['uuid'], $data['name'], $data['file_path'], $data['mime_type'], $data['size'], $data['metadata'] ?? null);
        return $this->repository->save($logo);
    }
}
