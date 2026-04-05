<?php declare(strict_types=1);
namespace Modules\Order\Application\Services;
use Modules\Order\Domain\Entities\Order;
use Modules\Order\Domain\Entities\OrderLine;
use Modules\Order\Domain\RepositoryInterfaces\OrderRepositoryInterface;
class CreateOrderService {
    public function __construct(private readonly OrderRepositoryInterface $repo) {}
    public function create(array $data): Order {
        $lines = [];
        $subtotal = 0.0;
        $taxTotal = 0.0;
        $discTotal = 0.0;
        foreach ($data['lines'] ?? [] as $ld) {
            $qty = (float)$ld['quantity'];
            $price = (float)$ld['unit_price'];
            $tax = (float)($ld['tax_amount']??0.0);
            $disc = (float)($ld['discount_amount']??0.0);
            $lineTotal = ($qty * $price) + $tax - $disc;
            $subtotal += $qty * $price;
            $taxTotal += $tax;
            $discTotal += $disc;
            $lines[] = new OrderLine(null,0,$ld['product_id'],$ld['variant_id']??null,$qty,$price,$tax,$disc,$lineTotal,$ld['batch_number']??null,$ld['lot_number']??null,$ld['serial_number']??null);
        }
        $total = $subtotal + $taxTotal - $discTotal;
        $order = new Order(null,$data['tenant_id'],$data['order_number'],$data['type']??'sales','draft',$data['party_id'],$data['warehouse_id']??null,new \DateTimeImmutable($data['order_date']??date('Y-m-d')),$data['currency']??'USD',$subtotal,$taxTotal,$discTotal,$total,$data['notes']??null,$lines);
        $saved = $this->repo->save($order);
        foreach ($lines as $i => $line) {
            $newLine = new OrderLine(null,$saved->getId(),$line->getProductId(),$line->getVariantId(),$line->getQuantity(),$line->getUnitPrice(),$line->getTaxAmount(),$line->getDiscountAmount(),$line->getLineTotal(),$line->getBatchNumber(),$line->getLotNumber(),$line->getSerialNumber());
            $lines[$i] = $this->repo->saveLine($newLine);
        }
        $saved->setLines($lines);
        return $saved;
    }
}
