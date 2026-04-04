<?php

declare(strict_types=1);

namespace Modules\Warehouse\Application\Contracts;

interface DeleteLocationServiceInterface
{
    public function execute(int $id): void;
}
