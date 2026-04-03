<?php
declare(strict_types=1);
namespace Modules\Brand\Application\Contracts;
interface CreateBrandServiceInterface
{
    public function execute(array $data = []): mixed;
}
