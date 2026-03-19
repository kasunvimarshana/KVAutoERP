<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Http\Middleware\VerifyJwtMiddleware;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use KvEnterprise\SharedKernel\Http\Middleware\TenantContextMiddleware;
use Tests\TestCase;

/**
 * Feature tests for RBAC authorization enforcement in the Product Service.
 *
 * These tests verify that:
 *   - Authenticated users WITHOUT the required permission receive a 403.
 *   - Authenticated users WITH the required permission are allowed through.
 *
 * JWT middleware is swapped with a lightweight stub that injects claims
 * directly — no real JWT signing/verification is required. This isolates
 * the RequirePermissionMiddleware behaviour under test.
 * The tenant context is seeded via setTenantContext().
 */
final class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed the tenant context for all tests in this class.
        $this->setTenantContext();

        // Bypass tenant-context resolution — we seed it directly above.
        $this->withoutMiddleware([TenantContextMiddleware::class]);
    }

    // -------------------------------------------------------------------------
    // Products — write operations require 'products.manage'
    // -------------------------------------------------------------------------

    #[Test]
    public function it_allows_product_creation_when_user_has_products_manage_permission(): void
    {
        $this->actingWithClaims(['permissions' => ['products.manage']]);

        // A 422 response means the request passed authorization and reached
        // the controller (which then fails product validation — expected).
        $response = $this->postJson('/api/v1/products', []);

        $response->assertStatus(422);
    }

    #[Test]
    public function it_denies_product_creation_when_user_lacks_products_manage_permission(): void
    {
        $this->actingWithClaims(['permissions' => ['products.view']]);

        $response = $this->postJson('/api/v1/products', [
            'name' => 'Test Product',
            'sku'  => 'SKU-001',
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('status', 'error');
    }

    #[Test]
    public function it_denies_product_update_when_user_lacks_products_manage_permission(): void
    {
        $this->actingWithClaims(['permissions' => []]);

        $response = $this->putJson('/api/v1/products/some-uuid', ['name' => 'Updated']);

        $response->assertStatus(403)
            ->assertJsonPath('status', 'error');
    }

    #[Test]
    public function it_denies_product_deletion_when_user_lacks_products_manage_permission(): void
    {
        $this->actingWithClaims(['permissions' => ['products.view']]);

        $response = $this->deleteJson('/api/v1/products/some-uuid');

        $response->assertStatus(403)
            ->assertJsonPath('status', 'error');
    }

    #[Test]
    public function it_allows_product_listing_without_products_manage_permission(): void
    {
        $this->actingWithClaims(['permissions' => []]);

        // Read endpoints are accessible to any authenticated user.
        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');
    }

    #[Test]
    public function it_returns_403_with_message_indicating_missing_permission(): void
    {
        $this->actingWithClaims(['permissions' => []]);

        $response = $this->postJson('/api/v1/products', ['name' => 'P']);

        $response->assertStatus(403)
            ->assertJsonPath('status', 'error')
            ->assertJsonFragment(['message' => 'You do not have the required permission: products.manage']);
    }

    // -------------------------------------------------------------------------
    // Categories — write access requires 'products.manage'
    // -------------------------------------------------------------------------

    #[Test]
    public function it_allows_category_creation_when_user_has_products_manage_permission(): void
    {
        $this->actingWithClaims(['permissions' => ['products.manage']]);

        // 422 = passed authorization, failed validation.
        $response = $this->postJson('/api/v1/categories', []);

        $response->assertStatus(422);
    }

    #[Test]
    public function it_denies_category_creation_without_products_manage_permission(): void
    {
        $this->actingWithClaims(['permissions' => ['categories.view']]);

        $response = $this->postJson('/api/v1/categories', ['name' => 'Electronics']);

        $response->assertStatus(403)
            ->assertJsonPath('status', 'error');
    }

    #[Test]
    public function it_allows_category_listing_without_products_manage_permission(): void
    {
        $this->actingWithClaims(['permissions' => []]);

        $response = $this->getJson('/api/v1/categories');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Swap VerifyJwtMiddleware with a stub that injects specified claims.
     *
     * The stub sets `jwt_claims` on the request attributes exactly as
     * the real VerifyJwtMiddleware does after successful token verification.
     * This lets RequirePermissionMiddleware (the actual SUT) run normally.
     *
     * @param  array<string, mixed>  $overrides  Claim values to override.
     * @return void
     */
    private function actingWithClaims(array $overrides = []): void
    {
        $claims = $this->makeClaims($overrides);

        // Swap the concrete VerifyJwtMiddleware binding so that Laravel's
        // middleware alias resolution picks up our stub.
        $this->app->singleton(VerifyJwtMiddleware::class, static function () use ($claims) {
            return new class ($claims) {
                /** @param array<string, mixed> $claims */
                public function __construct(private readonly array $claims) {}

                /** @param \Closure(\Illuminate\Http\Request): \Symfony\Component\HttpFoundation\Response $next */
                public function handle(\Illuminate\Http\Request $request, \Closure $next): mixed
                {
                    $request->attributes->set('jwt_claims', $this->claims);
                    $request->attributes->set('raw_token', 'stub-token');
                    return $next($request);
                }
            };
        });
    }
}
