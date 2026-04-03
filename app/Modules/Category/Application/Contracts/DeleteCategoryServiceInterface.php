<?php
declare(strict_types=1);
namespace Modules\Category\Application\Contracts;

interface DeleteCategoryServiceInterface
{
    public function execute(array $data = []): mixed;
}
