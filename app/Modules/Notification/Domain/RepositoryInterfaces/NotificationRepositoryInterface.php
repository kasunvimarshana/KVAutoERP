<?php declare(strict_types=1);
namespace Modules\Notification\Domain\RepositoryInterfaces;
use Modules\Notification\Domain\Entities\Notification;
interface NotificationRepositoryInterface {
    public function findById(int $id): ?Notification;
    public function findByUser(int $userId, bool $unreadOnly = false): array;
    public function save(Notification $notification): Notification;
    public function markAsRead(int $id): void;
}
