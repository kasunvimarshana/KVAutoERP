<?php declare(strict_types=1);
namespace Tests\Unit;
use Modules\Notification\Domain\Entities\Notification;
use Modules\Notification\Domain\Entities\NotificationTemplate;
use Modules\Notification\Infrastructure\Channels\NotificationChannelDispatcher;
use Modules\Notification\Infrastructure\Channels\DatabaseChannel;
use PHPUnit\Framework\TestCase;
class NotificationModuleTest extends TestCase {
    public function test_template_render(): void {
        $t = new NotificationTemplate(1, 1, 'Welcome', 'user.registered', 'email', 'Welcome, {{ name }}!', 'Hello {{ name }}, your account is ready.', true);
        $rendered = $t->render(['name' => 'Alice']);
        $this->assertStringContainsString('Alice', $rendered);
        $this->assertStringNotContainsString('{{ name }}', $rendered);
    }
    public function test_notification_is_read(): void {
        $n = new Notification(1, 1, 5, 'email', 'Subject', 'Body', 'sent', null, new \DateTimeImmutable(), new \DateTimeImmutable());
        $this->assertTrue($n->isRead());
    }
    public function test_notification_not_read(): void {
        $n = new Notification(1, 1, 5, 'email', 'Subject', 'Body', 'sent', null, new \DateTimeImmutable(), null);
        $this->assertFalse($n->isRead());
    }
    public function test_dispatcher_sends_to_database_channel(): void {
        $dispatcher = new NotificationChannelDispatcher();
        $dispatcher->register(new DatabaseChannel());
        $n = new Notification(1, 1, 5, 'database', 'Test', 'Body', 'pending', null, null, null);
        $result = $dispatcher->dispatch($n);
        $this->assertTrue($result);
    }
    public function test_dispatcher_no_channel_returns_false(): void {
        $dispatcher = new NotificationChannelDispatcher();
        $n = new Notification(1, 1, 5, 'sms', 'Test', 'Body', 'pending', null, null, null);
        $result = $dispatcher->dispatch($n);
        $this->assertFalse($result);
    }
}
