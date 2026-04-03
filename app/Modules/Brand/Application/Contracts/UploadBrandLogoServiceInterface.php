<?php
declare(strict_types=1);
namespace Modules\Brand\Application\Contracts;
interface UploadBrandLogoServiceInterface
{
    public function execute(array $data = []): mixed;
}
