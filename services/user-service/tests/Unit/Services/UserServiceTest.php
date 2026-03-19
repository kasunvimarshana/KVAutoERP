<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    private UserService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new UserService();
    }

    public function test_find_by_email_returns_null_when_not_found(): void
    {
        $result = $this->service->findByEmail('nonexistent@example.com');

        $this->assertNull($result);
    }

    public function test_validate_credentials_returns_false_for_wrong_password(): void
    {
        $user = User::create([
            'id'       => (string) Str::uuid(),
            'name'     => 'Test User',
            'email'    => 'test@example.com',
            'password' => Hash::make('correct-password'),
        ]);

        $result = $this->service->validateCredentials($user->id, 'wrong-password');

        $this->assertFalse($result);
    }

    public function test_get_user_claims_returns_correct_structure(): void
    {
        $user = User::create([
            'id'            => (string) Str::uuid(),
            'name'          => 'Claims User',
            'email'         => 'claims@example.com',
            'password'      => Hash::make('password'),
            'tenant_id'     => (string) Str::uuid(),
            'status'        => 'active',
            'token_version' => 1,
        ]);

        $claims = $this->service->getUserClaims($user->id);

        $this->assertArrayHasKey('id', $claims);
        $this->assertArrayHasKey('email', $claims);
        $this->assertArrayHasKey('tenant_id', $claims);
        $this->assertArrayHasKey('roles', $claims);
        $this->assertArrayHasKey('permissions', $claims);
        $this->assertArrayHasKey('token_version', $claims);
        $this->assertArrayHasKey('status', $claims);
        $this->assertEquals($user->id, $claims['id']);
        $this->assertEquals('claims@example.com', $claims['email']);
        $this->assertIsArray($claims['roles']);
        $this->assertIsArray($claims['permissions']);
    }

    public function test_increment_token_version_increases_version(): void
    {
        $user = User::create([
            'id'            => (string) Str::uuid(),
            'name'          => 'Version User',
            'email'         => 'version@example.com',
            'password'      => Hash::make('password'),
            'token_version' => 1,
        ]);

        $newVersion = $this->service->incrementTokenVersion($user->id);

        $this->assertEquals(2, $newVersion);
        $this->assertEquals(2, $user->fresh()->token_version);
    }
}
