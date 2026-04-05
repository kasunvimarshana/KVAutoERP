<?php declare(strict_types=1);
namespace Modules\Notification\Infrastructure\Persistence\Eloquent\Repositories;
use Modules\Notification\Domain\Entities\Notification;
use Modules\Notification\Domain\RepositoryInterfaces\NotificationRepositoryInterface;
use Modules\Notification\Infrastructure\Persistence\Eloquent\Models\NotificationModel;
class EloquentNotificationRepository implements NotificationRepositoryInterface {
    public function __construct(private readonly NotificationModel $model) {}
    public function findById(int $id): ?Notification { $m=$this->model->newQuery()->find($id); return $m?$this->toEntity($m):null; }
    public function findByUser(int $userId, bool $unreadOnly=false): array {
        $q=$this->model->newQuery()->where('user_id',$userId);
        if($unreadOnly) $q->whereNull('read_at');
        return $q->get()->map(fn($m)=>$this->toEntity($m))->all();
    }
    public function save(Notification $n): Notification {
        $m=$n->getId()?$this->model->newQuery()->findOrFail($n->getId()):new NotificationModel();
        $m->tenant_id=$n->getTenantId();$m->user_id=$n->getUserId();$m->channel=$n->getChannel();$m->subject=$n->getSubject();$m->body=$n->getBody();$m->status=$n->getStatus();$m->error_message=$n->getErrorMessage();$m->sent_at=$n->getSentAt()?->format('Y-m-d H:i:s');$m->read_at=$n->getReadAt()?->format('Y-m-d H:i:s');
        $m->save(); return $this->toEntity($m);
    }
    public function markAsRead(int $id): void {
        $m=$this->model->newQuery()->findOrFail($id);
        $m->read_at=now();
        $m->save();
    }
    private function toEntity(NotificationModel $m): Notification { return new Notification($m->id,$m->tenant_id,$m->user_id,$m->channel,$m->subject,$m->body,$m->status,$m->error_message,$m->sent_at?new \DateTimeImmutable($m->sent_at->toDateTimeString()):null,$m->read_at?new \DateTimeImmutable($m->read_at->toDateTimeString()):null); }
}
