<?php declare(strict_types=1);
namespace Modules\Maintenance\Application\Services;

use Modules\Maintenance\Domain\Entities\MaintenanceSchedule;
use Modules\Maintenance\Domain\Entities\ServiceOrder;

class ProcessDueMaintenanceService
{
    /** @param MaintenanceSchedule[] $schedules */
    public function processDue(array $schedules): array
    {
        $orders = [];
        foreach ($schedules as $schedule) {
            if ($schedule->isDue()) {
                $orders[] = new ServiceOrder(
                    null,
                    $schedule->getTenantId(),
                    'SO-AUTO-' . $schedule->getId(),
                    'preventive',
                    $schedule->getAssetId(),
                    null,
                    'normal',
                    'open',
                    'Auto-generated: ' . $schedule->getName(),
                    $schedule->getNextDueDate(),
                    null,
                    null,
                );
            }
        }
        return $orders;
    }
}
