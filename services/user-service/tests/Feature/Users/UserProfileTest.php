<?php

declare(strict_types=1);

namespace Tests\Feature\Users;

use App\Contracts\Services\UserProfileServiceInterface;
use App\Http\Middleware\VerifyJwtMiddleware;
use App\Models\UserProfile;
use KvEnterprise\SharedKernel\Exceptions\NotFoundException;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Feature tests for the UserProfile CRUD endpoints.
 *
 * JWT middleware is bypassed; claims are injected via request attributes.
 * All service calls are mocked — no database connection is needed.
 */
final class UserProfileTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setTenantContext();

        // Bypass JWT verification — we inject claims directly.
        $this->withoutMiddleware([VerifyJwtMiddleware::class]);
    }

    /**
     * Build a partial UserProfile mock with the given attributes.
     *
     * @param  array<string, mixed>  $attributes
     * @return UserProfile
     */
    private function makeProfileMock(array $attributes = []): UserProfile
    {
        $profile = new UserProfile(array_merge([
            'id'              => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
            'tenant_id'       => self::TEST_TENANT_ID,
            'organization_id' => self::TEST_ORG_ID,
            'auth_user_id'    => 'cccccccc-cccc-cccc-cccc-cccccccccccc',
            'email'           => 'john.doe@example.com',
            'first_name'      => 'John',
            'last_name'       => 'Doe',
            'display_name'    => 'John Doe',
            'is_active'       => true,
            'locale'          => 'en',
            'timezone'        => 'UTC',
        ], $attributes));

        // Prevent lazy-loading relationships from firing.
        $profile->setRelation('roles', collect());
        $profile->setRelation('directPermissions', collect());

        return $profile;
    }

    #[Test]
    public function it_creates_a_user_profile_successfully(): void
    {
        $profile = $this->makeProfileMock();

        $service = Mockery::mock(UserProfileServiceInterface::class);
        $service->shouldReceive('createProfile')
            ->once()
            ->andReturn($profile);

        $this->app->instance(UserProfileServiceInterface::class, $service);

        $response = $this->postJson('/api/v1/users', [
            'email'        => 'john.doe@example.com',
            'first_name'   => 'John',
            'last_name'    => 'Doe',
            'auth_user_id' => 'cccccccc-cccc-cccc-cccc-cccccccccccc',
        ], ['jwt_claims' => $this->makeClaims()]);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.email', 'john.doe@example.com');
    }

    #[Test]
    public function it_returns_user_profile_by_id(): void
    {
        $profile = $this->makeProfileMock();

        $service = Mockery::mock(UserProfileServiceInterface::class);
        $service->shouldReceive('getProfile')
            ->once()
            ->with('aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa')
            ->andReturn($profile);

        $this->app->instance(UserProfileServiceInterface::class, $service);

        $response = $this->getJson('/api/v1/users/aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.id', 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa');
    }

    #[Test]
    public function it_returns_404_for_unknown_user(): void
    {
        $service = Mockery::mock(UserProfileServiceInterface::class);
        $service->shouldReceive('getProfile')
            ->once()
            ->andReturn(null);

        $this->app->instance(UserProfileServiceInterface::class, $service);

        $response = $this->getJson('/api/v1/users/unknown-id');

        $response->assertStatus(404)
            ->assertJsonPath('status', 'error');
    }

    #[Test]
    public function it_updates_a_user_profile(): void
    {
        $profile = $this->makeProfileMock(['first_name' => 'Jane']);

        $service = Mockery::mock(UserProfileServiceInterface::class);
        $service->shouldReceive('updateProfile')
            ->once()
            ->andReturn($profile);

        $this->app->instance(UserProfileServiceInterface::class, $service);

        $response = $this->putJson(
            '/api/v1/users/aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
            ['first_name' => 'Jane'],
        );

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.first_name', 'Jane');
    }

    #[Test]
    public function it_returns_404_on_update_for_unknown_user(): void
    {
        $service = Mockery::mock(UserProfileServiceInterface::class);
        $service->shouldReceive('updateProfile')
            ->once()
            ->andThrow(NotFoundException::for('UserProfile', 'bad-id'));

        $this->app->instance(UserProfileServiceInterface::class, $service);

        $response = $this->putJson('/api/v1/users/bad-id', ['first_name' => 'Jane']);

        $response->assertStatus(404);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
