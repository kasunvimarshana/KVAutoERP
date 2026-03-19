<?php

namespace Shared\Core\Services;

use Shared\Core\Events\AuditTrail;
use Shared\Core\Outbox\OutboxPublisher;
use Shared\Core\MultiTenancy\TenantManager;
use Illuminate\Support\Facades\DB;

abstract class BaseService
{
    protected $auditTrail;
    protected $outbox;
    protected $tenantManager;

    public function __construct(AuditTrail $auditTrail, OutboxPublisher $outbox, TenantManager $tenantManager)
    {
        $this->auditTrail = $auditTrail;
        $this->outbox = $outbox;
        $this->tenantManager = $tenantManager;
    }

    /**
     * Executes a transactional business operation with audit and outbox delivery.
     */
    protected function transactionalOperation(callable $operation, string $action, array $payload): mixed
    {
        return DB::transaction(function () use ($operation, $action, $payload) {
            $result = $operation();

            // Record Audit Trail
            if ($result instanceof \Illuminate\Database\Eloquent\Model) {
                $this->auditTrail->log($result, $action, $payload, $this->tenantManager->getTenantId(), auth()->id() ?? 0);
            }

            // Publish to Outbox (idempotency, guaranteed delivery)
            $this->outbox->publish($action, $payload, $this->tenantManager->getTenantId());

            return $result;
        });
    }
}
