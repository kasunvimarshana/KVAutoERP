<?php

namespace Tests\Unit;

use App\Modules\User\DTOs\UserDTO;
use App\Modules\User\Events\UserCreated;
use App\Modules\User\Models\User;
use App\Modules\User\Repositories\UserRepositoryInterface;
use App\Modules\User\Services\UserService;
use Illuminate\Support\Facades\Event;
use Mockery;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    private UserService $service;
    private $repositoryMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryMock = Mockery::mock(UserRepositoryInterface::class);
        $this->service        = new UserService($this->repositoryMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_create_user_dispatches_event(): void
    {
        Event::fake();

        $user = new User();
        $user->forceFill([
            'id'        => '550e8400-e29b-41d4-a716-446655440000',
            'tenant_id' => 'tenant-uuid',
            'username'  => 'testuser',
            'email'     => 'test@example.com',
            'role'      => 'viewer',
        ]);

        $this->repositoryMock
            ->shouldReceive('create')
            ->once()
            ->andReturn($user);

        $dto = new UserDTO(
            tenantId: 'tenant-uuid',
            username: 'testuser',
            email:    'test@example.com',
            role:     'viewer',
        );

        $result = $this->service->create($dto);

        Event::assertDispatched(UserCreated::class);
        $this->assertInstanceOf(User::class, $result);
    }

    public function test_find_by_id_throws_when_not_found(): void
    {
        $this->repositoryMock
            ->shouldReceive('findById')
            ->with('unknown-id', 'tenant-uuid')
            ->once()
            ->andReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('User not found: unknown-id');

        $this->service->findById('unknown-id', 'tenant-uuid');
    }

    public function test_delete_user_dispatches_event(): void
    {
        Event::fake();

        $user = new User();
        $user->forceFill([
            'id'        => 'user-uuid',
            'tenant_id' => 'tenant-uuid',
        ]);

        $this->repositoryMock
            ->shouldReceive('findById')
            ->with('user-uuid', 'tenant-uuid')
            ->once()
            ->andReturn($user);

        $this->repositoryMock
            ->shouldReceive('delete')
            ->with($user)
            ->once()
            ->andReturn(true);

        $result = $this->service->delete('user-uuid', 'tenant-uuid');

        $this->assertTrue($result);
    }

    public function test_list_users_returns_paginated_results(): void
    {
        $paginator = Mockery::mock(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class);

        $this->repositoryMock
            ->shouldReceive('paginate')
            ->with('tenant-uuid', 15, [])
            ->once()
            ->andReturn($paginator);

        $result = $this->service->list('tenant-uuid', 15, []);

        $this->assertSame($paginator, $result);
    }
}
