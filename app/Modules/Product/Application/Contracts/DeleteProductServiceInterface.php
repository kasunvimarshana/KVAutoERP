<?php

declare(strict_types=1);

namespace Modules\Product\Application\Contracts;

interface DeleteProductServiceInterface
{
    public function execute(int $id): void;
}
