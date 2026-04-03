<?php
declare(strict_types=1);
namespace Modules\Brand\Application\Contracts;
interface UpdateBrandServiceInterface
{
    public function execute(array $data = []): mixed;
}
