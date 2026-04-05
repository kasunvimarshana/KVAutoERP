<?php declare(strict_types=1);
namespace Modules\Asset\Application\Services;

use Modules\Asset\Domain\Entities\AssetDepreciation;
use Modules\Asset\Domain\Entities\FixedAsset;

class RecordDepreciationService
{
    public function record(FixedAsset $asset, float $currentBookValue, \DateTimeInterface $periodDate): AssetDepreciation
    {
        $monthly = $asset->getMonthlyDepreciation();
        $remaining = $currentBookValue - $asset->getResidualValue();
        $amount = min($monthly, max(0.0, $remaining));
        $bookValueAfter = $currentBookValue - $amount;

        return new AssetDepreciation(
            null,
            (int) $asset->getId(),
            $asset->getTenantId(),
            $periodDate,
            $amount,
            $currentBookValue,
            $bookValueAfter,
            null,
        );
    }
}
