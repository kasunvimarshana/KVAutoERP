<?php declare(strict_types=1);
namespace Tests\Unit;
use Modules\Pricing\Domain\Entities\PriceList;
use Modules\Pricing\Domain\Entities\PriceListItem;
use Modules\Pricing\Application\Services\ResolvePriceService;
use PHPUnit\Framework\TestCase;
class PricingModuleTest extends TestCase {
    public function test_price_list_entity(): void {
        $pl = new PriceList(1, 1, 'Retail', 'RETAIL', 'USD', true, true);
        $this->assertSame('Retail', $pl->getName());
        $this->assertTrue($pl->isDefault());
    }
    public function test_price_list_item_valid_on_date(): void {
        $item = new PriceListItem(1, 1, 5, 'fixed', 99.99, 1.0, new \DateTimeImmutable('2026-01-01'), new \DateTimeImmutable('2026-12-31'));
        $this->assertTrue($item->isValidOn(new \DateTimeImmutable('2026-06-15')));
        $this->assertFalse($item->isValidOn(new \DateTimeImmutable('2025-12-31')));
        $this->assertFalse($item->isValidOn(new \DateTimeImmutable('2027-01-01')));
    }
    public function test_resolve_price_picks_highest_tier(): void {
        $items = [
            new PriceListItem(1, 1, 5, 'fixed', 100.0, 1.0, null, null),
            new PriceListItem(2, 1, 5, 'fixed', 90.0, 10.0, null, null),
            new PriceListItem(3, 1, 5, 'fixed', 80.0, 50.0, null, null),
        ];
        $repo = new class($items) implements \Modules\Pricing\Domain\RepositoryInterfaces\PriceListRepositoryInterface {
            public function __construct(private array $items) {}
            public function findById(int $id): ?\Modules\Pricing\Domain\Entities\PriceList { return null; }
            public function findDefault(int $tenantId): ?\Modules\Pricing\Domain\Entities\PriceList { return null; }
            public function findItemsByProduct(int $priceListId, int $productId): array { return $this->items; }
            public function save(\Modules\Pricing\Domain\Entities\PriceList $list): \Modules\Pricing\Domain\Entities\PriceList { return $list; }
            public function saveItem(\Modules\Pricing\Domain\Entities\PriceListItem $item): \Modules\Pricing\Domain\Entities\PriceListItem { return $item; }
            public function deleteItem(int $id): void {}
        };
        $svc = new ResolvePriceService($repo);
        $this->assertSame(90.0, $svc->resolve(1, 5, 15.0));
        $this->assertSame(80.0, $svc->resolve(1, 5, 50.0));
        $this->assertSame(100.0, $svc->resolve(1, 5, 1.0));
    }
}
