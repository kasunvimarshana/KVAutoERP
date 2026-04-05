<?php

declare(strict_types=1);

namespace Modules\Order\Application\Contracts;

use Modules\Order\Domain\Entities\OrderReturn;

interface ReturnServiceInterface
{
    public function createReturn(int $tenantId, array $data): OrderReturn;
    public function approveReturn(int $returnId): OrderReturn;
    public function processReturn(int $returnId): OrderReturn;
    public function completeReturn(int $returnId, float $creditMemoAmount): OrderReturn;
    public function cancelReturn(int $returnId): OrderReturn;
}
