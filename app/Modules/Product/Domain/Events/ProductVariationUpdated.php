<?php declare(strict_types=1);
namespace Modules\Product\Domain\Events;
use Modules\Core\Domain\Events\BaseEvent;
class ProductVariationUpdated extends BaseEvent {
    public function __construct(int $tenantId) { parent::__construct($tenantId); }
}
