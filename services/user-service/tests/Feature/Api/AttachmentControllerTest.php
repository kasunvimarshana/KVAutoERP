<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Attachment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Feature tests for AttachmentController.
 *
 * Covers multi-file upload, listing, deletion, and signed URL generation.
 */
class AttachmentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::fake('local');
    }

    // ──────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────

    private function withJwtHeaders(
        string $userId   = 'user-1',
        string $tenantId = 'tenant-1',
    ): static {
        $this->mock(\App\Http\Middleware\VerifyJwtToken::class, function ($mock) use ($userId, $tenantId): void {
            $mock->shouldReceive('handle')->andReturnUsing(function ($request, $next) use ($userId, $tenantId) {
                $request->attributes->set('jwt_claims', [
                    'sub'         => $userId,
                    'tenant_id'   => $tenantId,
                    'roles'       => ['admin'],
                    'permissions' => [],
                ]);
                $request->attributes->set('user_id', $userId);
                $request->attributes->set('tenant_id', $tenantId);
                $request->attributes->set('roles', ['admin']);
                $request->attributes->set('permissions', []);
                return $next($request);
            });
        });

        return $this;
    }

    private function makeAttachment(array $overrides = []): Attachment
    {
        return Attachment::create(array_merge([
            'id'                => (string) Str::uuid(),
            'entity_type'       => 'user',
            'entity_id'         => (string) Str::uuid(),
            'collection'        => 'documents',
            'disk'              => 'local',
            'path'              => 'user/test/documents/' . Str::uuid() . '.pdf',
            'original_filename' => 'test.pdf',
            'mime_type'         => 'application/pdf',
            'size'              => 1024,
            'visibility'        => 'private',
        ], $overrides));
    }

    // ──────────────────────────────────────────────────────────
    // POST /api/v1/attachments  (upload)
    // ──────────────────────────────────────────────────────────

    public function test_upload_stores_files_and_returns_attachment_records(): void
    {
        $entityId = (string) Str::uuid();

        $this->withJwtHeaders()
            ->postJson('/api/v1/attachments', [
                'files'       => [UploadedFile::fake()->create('document.pdf', 512, 'application/pdf')],
                'entity_type' => 'user',
                'entity_id'   => $entityId,
                'collection'  => 'documents',
                'visibility'  => 'private',
            ])
            ->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data');

        $this->assertDatabaseHas('attachments', [
            'entity_type' => 'user',
            'entity_id'   => $entityId,
            'collection'  => 'documents',
        ]);
    }

    public function test_upload_multiple_files(): void
    {
        $entityId = (string) Str::uuid();

        $this->withJwtHeaders()
            ->call('POST', '/api/v1/attachments', [
                'entity_type' => 'product',
                'entity_id'   => $entityId,
                'collection'  => 'images',
                'visibility'  => 'public',
            ], [], [
                'files' => [
                    UploadedFile::fake()->image('photo1.jpg'),
                    UploadedFile::fake()->image('photo2.jpg'),
                ],
            ])
            ->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data');
    }

    public function test_upload_returns_422_when_entity_type_missing(): void
    {
        $this->withJwtHeaders()
            ->postJson('/api/v1/attachments', [
                'files'      => [UploadedFile::fake()->create('file.pdf', 100)],
                'entity_id'  => (string) Str::uuid(),
            ])
            ->assertUnprocessable();
    }

    public function test_upload_returns_422_when_files_empty(): void
    {
        $this->withJwtHeaders()
            ->postJson('/api/v1/attachments', [
                'files'       => [],
                'entity_type' => 'user',
                'entity_id'   => (string) Str::uuid(),
            ])
            ->assertUnprocessable();
    }

    public function test_upload_returns_401_without_auth(): void
    {
        $this->postJson('/api/v1/attachments', [
            'files'       => [UploadedFile::fake()->create('file.pdf', 100)],
            'entity_type' => 'user',
            'entity_id'   => (string) Str::uuid(),
        ])
            ->assertStatus(401);
    }

    // ──────────────────────────────────────────────────────────
    // GET /api/v1/attachments
    // ──────────────────────────────────────────────────────────

    public function test_index_returns_attachments_for_entity(): void
    {
        $entityId = (string) Str::uuid();
        $this->makeAttachment(['entity_type' => 'user', 'entity_id' => $entityId, 'collection' => 'docs']);
        $this->makeAttachment(['entity_type' => 'user', 'entity_id' => $entityId, 'collection' => 'docs']);

        $this->withJwtHeaders()
            ->getJson("/api/v1/attachments?entity_type=user&entity_id={$entityId}&collection=docs")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data');
    }

    public function test_index_filters_by_collection(): void
    {
        $entityId = (string) Str::uuid();
        $this->makeAttachment(['entity_type' => 'user', 'entity_id' => $entityId, 'collection' => 'docs']);
        $this->makeAttachment(['entity_type' => 'user', 'entity_id' => $entityId, 'collection' => 'images']);

        $this->withJwtHeaders()
            ->getJson("/api/v1/attachments?entity_type=user&entity_id={$entityId}&collection=images")
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_index_returns_422_when_entity_type_missing(): void
    {
        $this->withJwtHeaders()
            ->getJson('/api/v1/attachments?entity_id=' . Str::uuid())
            ->assertUnprocessable();
    }

    // ──────────────────────────────────────────────────────────
    // DELETE /api/v1/attachments/{id}
    // ──────────────────────────────────────────────────────────

    public function test_destroy_deletes_attachment(): void
    {
        $attachment = $this->makeAttachment();

        $this->withJwtHeaders()
            ->deleteJson("/api/v1/attachments/{$attachment->id}")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Attachment deleted successfully');
    }

    // ──────────────────────────────────────────────────────────
    // GET /api/v1/attachments/{id}/signed-url
    // ──────────────────────────────────────────────────────────

    public function test_signed_url_returns_url_for_known_attachment(): void
    {
        $attachment = $this->makeAttachment(['disk' => 'public']);

        // Put a dummy file so Storage::url resolves
        Storage::disk('public')->put($attachment->path, 'dummy content');

        $this->withJwtHeaders()
            ->getJson("/api/v1/attachments/{$attachment->id}/signed-url")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['url', 'expires_in']]);
    }

    public function test_signed_url_returns_404_for_unknown_attachment(): void
    {
        $this->withJwtHeaders()
            ->getJson('/api/v1/attachments/' . Str::uuid() . '/signed-url')
            ->assertNotFound();
    }

    public function test_signed_url_validates_ttl_range(): void
    {
        $attachment = $this->makeAttachment(['disk' => 'public']);
        Storage::disk('public')->put($attachment->path, 'content');

        $this->withJwtHeaders()
            ->getJson("/api/v1/attachments/{$attachment->id}/signed-url?ttl=30")
            ->assertUnprocessable();
    }
}
