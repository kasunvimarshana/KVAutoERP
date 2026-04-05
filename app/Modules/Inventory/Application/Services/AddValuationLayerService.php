<?php declare(strict_types=1);
namespace Modules\Inventory\Application\Services;
use Modules\Inventory\Domain\Entities\ValuationLayer;
use Modules\Inventory\Domain\RepositoryInterfaces\ValuationLayerRepositoryInterface;
class AddValuationLayerService {
    public function __construct(private readonly ValuationLayerRepositoryInterface $repo) {}
    public function add(array $data): ValuationLayer {
        $layer = new ValuationLayer(null,$data['tenant_id'],$data['product_id'],$data['variant_id']??null,$data['warehouse_id'],(float)$data['quantity'],(float)$data['quantity'],(float)$data['unit_cost'],new \DateTimeImmutable(),$data['batch_number']??null,$data['lot_number']??null,isset($data['expiry_date'])?new \DateTimeImmutable($data['expiry_date']):null);
        return $this->repo->save($layer);
    }
}
