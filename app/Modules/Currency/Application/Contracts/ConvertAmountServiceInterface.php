<?php declare(strict_types=1);
namespace Modules\Currency\Application\Contracts;
interface ConvertAmountServiceInterface {
    public function convert(int $tenantId, float $amount, string $fromCurrency, string $toCurrency): float;
}
