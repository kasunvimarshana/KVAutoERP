<?php

namespace Tests\Unit\User;

use App\Modules\User\DTOs\UserDTO;
use App\Modules\User\Models\User;
use App\Modules\User\Repositories\Interfaces\UserRepositoryInterface;
use App\Modules\User\Services\UserService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    private UserService $service;
    private UserRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(UserRepositoryInterface::class);
        $this->service    = new UserService($this->repository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_checks_rbac_role_for_user(): void
    {
        $user        = new User();
        $user->id    = 1;
        $user->roles = ['admin', 'manager'];

        $this->repository->shouldReceive('findById')->with(1)->once()->andReturn($user);

        $this->assertTrue($this->service->userHasRole(1, 'admin'));
    }

    /** @test */
    public function it_returns_false_for_missing_role(): void
    {
        $user        = new User();
        $user->id    = 1;
        $user->roles = ['viewer'];

        $this->repository->shouldReceive('findById')->with(1)->once()->andReturn($user);

        $this->assertFalse($this->service->userHasRole(1, 'admin'));
    }

    /** @test */
    public function it_checks_abac_attribute(): void
    {
        $user             = new User();
        $user->id         = 1;
        $user->attributes = ['department' => 'warehouse', 'clearance' => 'level-2'];

        $this->repository->shouldReceive('findById')->with(1)->once()->andReturn($user);

        $this->assertTrue($this->service->userHasAttribute(1, 'department', 'warehouse'));
    }

    /** @test */
    public function it_throws_exception_if_email_already_exists(): void
    {
        $dto = new UserDTO(
            username:  'testuser',
            email:     'existing@example.com',
            firstName: 'Test',
            lastName:  'User',
            password:  'password123',
        );

        $existingUser        = new User();
        $existingUser->email = 'existing@example.com';

        DB::shouldReceive('transaction')->once()->andReturnUsing(fn ($cb) => $cb());

        $this->repository->shouldReceive('findByEmail')
            ->with('existing@example.com')
            ->once()
            ->andReturn($existingUser);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Email 'existing@example.com' is already registered.");

        $this->service->createUser($dto);
    }
}
