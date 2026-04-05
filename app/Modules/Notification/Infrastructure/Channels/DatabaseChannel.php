<?php declare(strict_types=1);
namespace Modules\Notification\Infrastructure\Channels;
use Modules\Notification\Application\Contracts\NotificationChannelInterface;
use Modules\Notification\Domain\Entities\Notification;
class DatabaseChannel implements NotificationChannelInterface {
    public function send(Notification $notification): bool { return true; }
    public function supports(string $channel): bool { return $channel === 'database'; }
}
