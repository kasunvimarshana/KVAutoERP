<?php

declare(strict_types=1);

namespace Modules\Configuration\Application\Contracts;

interface OrgUnitTreeServiceInterface
{
    public function getTree(int $tenantId): array;

    public function getDescendants(int $id): array;
}
