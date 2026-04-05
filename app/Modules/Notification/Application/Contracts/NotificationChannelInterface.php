<?php declare(strict_types=1);
namespace Modules\Notification\Application\Contracts;
use Modules\Notification\Domain\Entities\Notification;
interface NotificationChannelInterface {
    public function send(Notification $notification): bool;
    public function supports(string $channel): bool;
}
