<?php declare(strict_types=1);
namespace Modules\Notification\Application\Services;
use Modules\Notification\Domain\Entities\Notification;
use Modules\Notification\Domain\Entities\NotificationTemplate;
use Modules\Notification\Domain\RepositoryInterfaces\NotificationRepositoryInterface;
use Modules\Notification\Infrastructure\Channels\NotificationChannelDispatcher;
class SendNotificationService {
    public function __construct(
        private readonly NotificationRepositoryInterface $repo,
        private readonly NotificationChannelDispatcher $dispatcher,
    ) {}
    public function send(int $tenantId, int $userId, NotificationTemplate $template, array $variables = []): Notification {
        $body = $template->render($variables);
        $notification = new Notification(null,$tenantId,$userId,$template->getChannel(),$template->getSubject(),$body,'pending',null,null,null);
        $saved = $this->repo->save($notification);
        $success = $this->dispatcher->dispatch($saved);
        $final = new Notification($saved->getId(),$saved->getTenantId(),$saved->getUserId(),$saved->getChannel(),$saved->getSubject(),$saved->getBody(),$success?'sent':'failed',null,$success?new \DateTimeImmutable():null,null);
        return $this->repo->save($final);
    }
}
