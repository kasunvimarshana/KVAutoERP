<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Contracts\Repositories\UserProfileRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\DTOs\CreateUserDto;
use App\DTOs\UpdateUserDto;
use App\DTOs\UserProfileDto;
use App\Exceptions\UserException;
use App\Models\User;
use App\Models\UserProfile;
use App\Services\UserService;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    private MockInterface $userRepository;
    private MockInterface $userProfileRepository;
    private UserService $userService;

    private string $tenantId = 'tenant-uuid-0001';
    private string $userId   = 'user-uuid-0001';

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository        = Mockery::mock(UserRepositoryInterface::class);
        $this->userProfileRepository = Mockery::mock(UserProfileRepositoryInterface::class);

        $this->userService = new UserService(
            $this->userRepository,
            $this->userProfileRepository,
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // ─────────────────────────────────────────────────────────────────
    // getUser
    // ─────────────────────────────────────────────────────────────────

    public function test_get_user_throws_not_found_when_user_does_not_exist(): void
    {
        $this->userRepository
            ->shouldReceive('findById')
            ->with($this->userId)
            ->once()
            ->andReturn(null);

        $this->expectException(UserException::class);
        $this->expectExceptionCode(404);

        $this->userService->getUser($this->userId, $this->tenantId);
    }

    public function test_get_user_throws_tenant_mismatch_when_user_belongs_to_different_tenant(): void
    {
        $user = $this->makeUser(['tenant_id' => 'other-tenant-id']);

        $this->userRepository
            ->shouldReceive('findById')
            ->with($this->userId)
            ->once()
            ->andReturn($user);

        $this->expectException(UserException::class);
        $this->expectExceptionCode(403);

        $this->userService->getUser($this->userId, $this->tenantId);
    }

    public function test_get_user_returns_user_when_found_and_tenant_matches(): void
    {
        $user = $this->makeUser();

        $this->userRepository
            ->shouldReceive('findById')
            ->with($this->userId)
            ->once()
            ->andReturn($user);

        $result = $this->userService->getUser($this->userId, $this->tenantId);

        $this->assertSame($user, $result);
    }

    // ─────────────────────────────────────────────────────────────────
    // createUser
    // ─────────────────────────────────────────────────────────────────

    public function test_create_user_throws_exception_when_email_already_exists(): void
    {
        $dto = new CreateUserDto(
            tenantId: $this->tenantId,
            name: 'John Doe',
            email: 'john@example.com',
            password: 'secret1234',
        );

        $this->userRepository
            ->shouldReceive('existsByEmail')
            ->with($dto->email, $dto->tenantId)
            ->once()
            ->andReturn(true);

        $this->expectException(UserException::class);
        $this->expectExceptionCode(422);

        $this->userService->createUser($dto);
    }

    public function test_create_user_hashes_password_before_storing(): void
    {
        $dto = new CreateUserDto(
            tenantId: $this->tenantId,
            name: 'John Doe',
            email: 'john@example.com',
            password: 'plaintext_password',
        );

        $this->userRepository
            ->shouldReceive('existsByEmail')
            ->once()
            ->andReturn(false);

        $this->userRepository
            ->shouldReceive('create')
            ->once()
            ->withArgs(function (array $data): bool {
                return Hash::check('plaintext_password', $data['password'])
                    && $data['email'] === 'john@example.com'
                    && $data['tenant_id'] === $this->tenantId;
            })
            ->andReturn($this->makeUser());

        $this->userService->createUser($dto);
    }

    public function test_create_user_returns_created_user(): void
    {
        $dto  = new CreateUserDto(
            tenantId: $this->tenantId,
            name: 'Jane Doe',
            email: 'jane@example.com',
            password: 'secret1234',
        );
        $user = $this->makeUser(['name' => 'Jane Doe']);

        $this->userRepository->shouldReceive('existsByEmail')->once()->andReturn(false);
        $this->userRepository->shouldReceive('create')->once()->andReturn($user);

        $result = $this->userService->createUser($dto);

        $this->assertInstanceOf(User::class, $result);
        $this->assertSame('Jane Doe', $result->name);
    }

    // ─────────────────────────────────────────────────────────────────
    // updateUser
    // ─────────────────────────────────────────────────────────────────

    public function test_update_user_throws_exception_when_new_email_already_taken(): void
    {
        $user = $this->makeUser(['email' => 'old@example.com']);
        $dto  = UpdateUserDto::fromArray(['email' => 'taken@example.com']);

        $this->userRepository->shouldReceive('findById')->once()->andReturn($user);
        $this->userRepository
            ->shouldReceive('existsByEmail')
            ->with('taken@example.com', $this->tenantId, $this->userId)
            ->once()
            ->andReturn(true);

        $this->expectException(UserException::class);
        $this->expectExceptionCode(422);

        $this->userService->updateUser($this->userId, $this->tenantId, $dto);
    }

    // ─────────────────────────────────────────────────────────────────
    // changePassword
    // ─────────────────────────────────────────────────────────────────

    public function test_change_password_throws_exception_when_current_password_is_wrong(): void
    {
        $user = $this->makeUser(['password' => Hash::make('correct-password')]);

        $this->userRepository->shouldReceive('findById')->once()->andReturn($user);

        $this->expectException(UserException::class);
        $this->expectExceptionCode(422);

        $this->userService->changePassword(
            $this->userId,
            $this->tenantId,
            'wrong-password',
            'new-password123',
        );
    }

    public function test_change_password_updates_password_when_current_password_is_correct(): void
    {
        $user = $this->makeUser(['password' => Hash::make('correct-password')]);

        $this->userRepository->shouldReceive('findById')->once()->andReturn($user);
        $this->userRepository
            ->shouldReceive('updatePassword')
            ->once()
            ->withArgs(function (string $id, string $hashed): bool {
                return $id === $this->userId && Hash::check('new-password123', $hashed);
            });

        $this->userService->changePassword(
            $this->userId,
            $this->tenantId,
            'correct-password',
            'new-password123',
        );
    }

    // ─────────────────────────────────────────────────────────────────
    // toggleUserStatus
    // ─────────────────────────────────────────────────────────────────

    public function test_toggle_user_status_deactivates_active_user(): void
    {
        $user             = $this->makeUser(['is_active' => true]);
        $deactivatedUser  = $this->makeUser(['is_active' => false]);

        $this->userRepository->shouldReceive('findById')->twice()->andReturn($user, $deactivatedUser);
        $this->userRepository->shouldReceive('toggleStatus')->once()->with($this->userId, false);

        $result = $this->userService->toggleUserStatus($this->userId, $this->tenantId, false);

        $this->assertFalse($result->is_active);
    }

    // ─────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────

    private function makeUser(array $attributes = []): User
    {
        $user            = new User();
        $user->id        = $attributes['id'] ?? $this->userId;
        $user->tenant_id = $attributes['tenant_id'] ?? $this->tenantId;
        $user->name      = $attributes['name'] ?? 'Test User';
        $user->email     = $attributes['email'] ?? 'test@example.com';
        $user->password  = $attributes['password'] ?? Hash::make('password');
        $user->is_active = $attributes['is_active'] ?? true;

        return $user;
    }
}
