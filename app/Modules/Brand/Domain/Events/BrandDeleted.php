<?php
declare(strict_types=1);
namespace Modules\Brand\Domain\Events;
use Modules\Core\Domain\Events\BaseEvent;

class BrandDeleted extends BaseEvent
{
    public int $brandId;
    public function __construct(int $brandId, int $tenantId)
    {
        parent::__construct($tenantId);
        $this->brandId = $brandId;
    }
    public function broadcastWith(): array
    {
        return ['brandId' => $this->brandId, 'tenantId' => $this->tenantId];
    }
}
