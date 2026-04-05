<?php declare(strict_types=1);
namespace Modules\Notification\Infrastructure\Channels;
use Modules\Notification\Application\Contracts\NotificationChannelInterface;
use Modules\Notification\Domain\Entities\Notification;
class NotificationChannelDispatcher {
    /** @var NotificationChannelInterface[] */
    private array $channels = [];
    public function register(NotificationChannelInterface $channel): void { $this->channels[] = $channel; }
    public function dispatch(Notification $notification): bool {
        foreach ($this->channels as $channel) {
            if ($channel->supports($notification->getChannel())) {
                return $channel->send($notification);
            }
        }
        return false;
    }
}
