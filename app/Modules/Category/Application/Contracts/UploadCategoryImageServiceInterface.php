<?php
declare(strict_types=1);
namespace Modules\Category\Application\Contracts;

interface UploadCategoryImageServiceInterface
{
    public function execute(array $data = []): mixed;
}
