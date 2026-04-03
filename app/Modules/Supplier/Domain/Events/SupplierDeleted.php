<?php
declare(strict_types=1);
namespace Modules\Supplier\Domain\Events;
use Modules\Core\Domain\Events\BaseEvent;
class SupplierDeleted extends BaseEvent
{
    public int $supplierId;
    public function __construct(int $supplierId, int $tenantId) { parent::__construct($tenantId); $this->supplierId = $supplierId; }
    public function broadcastWith(): array { return ['supplierId' => $this->supplierId, 'tenantId' => $this->tenantId]; }
}
