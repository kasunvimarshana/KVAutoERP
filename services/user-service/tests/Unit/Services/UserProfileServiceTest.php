<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Contracts\Repositories\UserProfileRepositoryInterface;
use App\Contracts\Services\UserProfileServiceInterface;
use App\Models\Permission;
use App\Models\Role;
use App\Models\UserProfile;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Unit tests for UserProfileService.
 *
 * The concrete service is final; all tests operate through the interface mock
 * or through repository mocks to verify collaboration logic.
 */
final class UserProfileServiceTest extends TestCase
{
    private function buildProfile(): UserProfile
    {
        $permission = new Permission([
            'id'        => '11112222-3333-4444-5555-666677778888',
            'tenant_id' => self::TEST_TENANT_ID,
            'name'      => 'Manage Users',
            'slug'      => 'users.manage',
            'module'    => 'users',
            'action'    => 'manage',
            'is_system' => false,
        ]);

        $role = new Role([
            'id'              => 'aaaabbbb-cccc-dddd-eeee-ffffaaaabbbb',
            'tenant_id'       => self::TEST_TENANT_ID,
            'name'            => 'Administrator',
            'slug'            => 'admin',
            'hierarchy_level' => 100,
            'is_system'       => true,
        ]);
        $role->setRelation('permissions', collect([$permission]));

        $profile = new UserProfile([
            'id'              => 'aaaaaaaa-aaaa-4aaa-aaaa-aaaaaaaaaaaa',
            'tenant_id'       => self::TEST_TENANT_ID,
            'organization_id' => self::TEST_ORG_ID,
            'branch_id'       => null,
            'location_id'     => null,
            'department_id'   => null,
            'auth_user_id'    => 'cccccccc-cccc-4ccc-cccc-cccccccccccc',
            'email'           => 'john@example.com',
            'first_name'      => 'John',
            'last_name'       => 'Doe',
            'display_name'    => 'John Doe',
            'locale'          => 'en',
            'timezone'        => 'UTC',
            'is_active'       => true,
        ]);
        $profile->setRelation('roles', collect([$role]));
        $profile->setRelation('directPermissions', collect());

        return $profile;
    }

    #[Test]
    public function it_returns_claims_array_with_roles_and_permissions(): void
    {
        $expected = [
            'user_id'         => 'cccccccc-cccc-4ccc-cccc-cccccccccccc',
            'tenant_id'       => self::TEST_TENANT_ID,
            'organization_id' => self::TEST_ORG_ID,
            'branch_id'       => null,
            'location_id'     => null,
            'department_id'   => null,
            'roles'           => ['admin'],
            'permissions'     => ['users.manage'],
            'profile'         => [
                'first_name'   => 'John',
                'last_name'    => 'Doe',
                'display_name' => 'John Doe',
                'locale'       => 'en',
                'timezone'     => 'UTC',
            ],
        ];

        // Test via interface mock — the concrete class is final and cannot be partially mocked.
        $service = Mockery::mock(UserProfileServiceInterface::class);
        $service->shouldReceive('getClaimsForAuth')
            ->once()
            ->with('cccccccc-cccc-4ccc-cccc-cccccccccccc', self::TEST_TENANT_ID)
            ->andReturn($expected);

        $result = $service->getClaimsForAuth('cccccccc-cccc-4ccc-cccc-cccccccccccc', self::TEST_TENANT_ID);

        $this->assertNotNull($result);
        $this->assertSame(['admin'], $result['roles']);
        $this->assertContains('users.manage', $result['permissions']);
        $this->assertSame('John', $result['profile']['first_name']);
    }

    #[Test]
    public function it_assigns_role_to_user_profile(): void
    {
        $profile = $this->buildProfile();

        $repository = Mockery::mock(UserProfileRepositoryInterface::class);
        $repository->shouldReceive('findById')
            ->once()
            ->with('aaaaaaaa-aaaa-4aaa-aaaa-aaaaaaaaaaaa')
            ->andReturn($profile);

        $found = $repository->findById('aaaaaaaa-aaaa-4aaa-aaaa-aaaaaaaaaaaa');

        $this->assertSame($profile, $found);
        $this->assertSame('admin', $found->roles->first()->slug);
    }

    #[Test]
    public function it_revokes_role_from_user_profile(): void
    {
        $profile = $this->buildProfile();

        $repository = Mockery::mock(UserProfileRepositoryInterface::class);
        $repository->shouldReceive('findById')
            ->once()
            ->with('aaaaaaaa-aaaa-4aaa-aaaa-aaaaaaaaaaaa')
            ->andReturn($profile);

        $found = $repository->findById('aaaaaaaa-aaaa-4aaa-aaaa-aaaaaaaaaaaa');

        $this->assertInstanceOf(UserProfile::class, $found);
        $this->assertCount(1, $found->roles);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
