<?php declare(strict_types=1);
namespace Tests\Unit;
use Modules\Auth\Domain\Entities\User;
use PHPUnit\Framework\TestCase;
class AuthModuleTest extends TestCase {
    public function test_user_entity_can_be_constructed(): void {
        $user = new User(1, 1, 'John Doe', 'john@example.com', 'hashed_pw', 'admin', true, null, new \DateTimeImmutable());
        $this->assertSame(1, $user->getId());
        $this->assertSame('John Doe', $user->getName());
        $this->assertSame('john@example.com', $user->getEmail());
        $this->assertSame('admin', $user->getRole());
        $this->assertTrue($user->isActive());
    }
    public function test_user_entity_with_null_id(): void {
        $user = new User(null, 1, 'Jane', 'jane@example.com', 'pw', 'staff', false, null, null);
        $this->assertNull($user->getId());
        $this->assertFalse($user->isActive());
    }
}
