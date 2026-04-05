<?php declare(strict_types=1);
namespace Tests\Unit;

use Modules\Asset\Domain\Entities\FixedAsset;
use Modules\Asset\Domain\Entities\AssetDepreciation;
use Modules\Asset\Application\Services\RecordDepreciationService;
use PHPUnit\Framework\TestCase;

class AssetModuleTest extends TestCase
{
    private function makeAsset(float $cost = 12000.0, float $residual = 0.0, int $lifeMonths = 120): FixedAsset
    {
        return new FixedAsset(
            1, 1, 'ASSET-001', 'Office Computer', 'equipment',
            $cost, new \DateTimeImmutable('2024-01-01'),
            $residual, $lifeMonths, 'straight_line', 'active', null, null
        );
    }

    public function test_fixed_asset_entity(): void
    {
        $asset = $this->makeAsset();
        $this->assertSame('ASSET-001', $asset->getCode());
        $this->assertSame('equipment', $asset->getCategory());
        $this->assertTrue($asset->isActive());
    }

    public function test_depreciable_amount(): void
    {
        $asset = $this->makeAsset(10000.0, 1000.0);
        $this->assertEqualsWithDelta(9000.0, $asset->getDepreciableAmount(), 0.001);
    }

    public function test_monthly_depreciation_straight_line(): void
    {
        $asset = $this->makeAsset(12000.0, 0.0, 120);
        $this->assertEqualsWithDelta(100.0, $asset->getMonthlyDepreciation(), 0.001);
    }

    public function test_monthly_depreciation_with_residual(): void
    {
        $asset = $this->makeAsset(12000.0, 2000.0, 100);
        $this->assertEqualsWithDelta(100.0, $asset->getMonthlyDepreciation(), 0.001);
    }

    public function test_asset_not_active_when_disposed(): void
    {
        $asset = new FixedAsset(
            2, 1, 'ASSET-002', 'Old Printer', 'equipment',
            5000.0, new \DateTimeImmutable('2020-01-01'),
            0.0, 60, 'straight_line', 'disposed', null, null
        );
        $this->assertFalse($asset->isActive());
    }

    public function test_record_depreciation_service(): void
    {
        $asset = $this->makeAsset(12000.0, 0.0, 120);
        $svc = new RecordDepreciationService();
        $dep = $svc->record($asset, 12000.0, new \DateTimeImmutable('2024-02-01'));

        $this->assertInstanceOf(AssetDepreciation::class, $dep);
        $this->assertEqualsWithDelta(100.0, $dep->getAmount(), 0.001);
        $this->assertEqualsWithDelta(12000.0, $dep->getBookValueBefore(), 0.001);
        $this->assertEqualsWithDelta(11900.0, $dep->getBookValueAfter(), 0.001);
    }

    public function test_record_depreciation_does_not_go_below_residual(): void
    {
        $asset = $this->makeAsset(12000.0, 11950.0, 120);
        $svc = new RecordDepreciationService();
        $dep = $svc->record($asset, 11960.0, new \DateTimeImmutable('2024-02-01'));

        // remaining depreciable = 11960 - 11950 = 10, monthly would be (12000-11950)/120 = 0.41
        // amount = min(0.41, 10) = 0.41...
        $this->assertGreaterThanOrEqual(0.0, $dep->getAmount());
        $this->assertGreaterThanOrEqual($dep->getBookValueAfter(), $asset->getResidualValue() - 0.001);
    }

    public function test_asset_depreciation_entity(): void
    {
        $dep = new AssetDepreciation(1, 1, 1, new \DateTimeImmutable('2024-02-01'), 100.0, 12000.0, 11900.0, null);
        $this->assertSame(1, $dep->getAssetId());
        $this->assertEqualsWithDelta(100.0, $dep->getAmount(), 0.001);
        $this->assertNull($dep->getJournalEntryId());
    }
}
