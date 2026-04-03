<?php
declare(strict_types=1);
namespace Modules\Brand\Application\Contracts;
interface DeleteBrandLogoServiceInterface
{
    public function execute(array $data = []): mixed;
}
