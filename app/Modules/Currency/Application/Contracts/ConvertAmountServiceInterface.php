<?php

declare(strict_types=1);

namespace Modules\Currency\Application\Contracts;

interface ConvertAmountServiceInterface
{
    public function convert(int $tenantId, string $fromCurrency, string $toCurrency, float $amount): float;
}
