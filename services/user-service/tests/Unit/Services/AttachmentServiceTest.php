<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Attachment;
use App\Models\User;
use App\Services\AttachmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Unit tests for AttachmentService.
 *
 * Exercises multi-file upload, avatar upload, listing, deletion,
 * and signed URL generation in isolation (faked storage).
 */
class AttachmentServiceTest extends TestCase
{
    use RefreshDatabase;

    private AttachmentService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Storage::fake('local');

        $this->service = new AttachmentService();
    }

    // ──────────────────────────────────────────────────────────
    // uploadFiles()
    // ──────────────────────────────────────────────────────────

    public function test_upload_files_stores_files_and_returns_records(): void
    {
        $entityId = (string) Str::uuid();
        $file     = UploadedFile::fake()->create('report.pdf', 512, 'application/pdf');

        $results = $this->service->uploadFiles(
            entityType: 'user',
            entityId:   $entityId,
            files:      [$file],
            collection: 'documents',
            visibility: 'private',
        );

        $this->assertCount(1, $results);
        $this->assertSame('user', $results[0]['entity_type']);
        $this->assertSame($entityId, $results[0]['entity_id']);
        $this->assertSame('documents', $results[0]['collection']);
        $this->assertSame('application/pdf', $results[0]['mime_type']);

        $this->assertDatabaseHas('attachments', [
            'entity_type' => 'user',
            'entity_id'   => $entityId,
            'collection'  => 'documents',
        ]);
    }

    public function test_upload_files_stores_multiple_files(): void
    {
        $entityId = (string) Str::uuid();
        $files    = [
            UploadedFile::fake()->image('photo1.jpg'),
            UploadedFile::fake()->image('photo2.jpg'),
            UploadedFile::fake()->image('photo3.jpg'),
        ];

        $results = $this->service->uploadFiles(
            entityType: 'product',
            entityId:   $entityId,
            files:      $files,
            collection: 'images',
            visibility: 'public',
        );

        $this->assertCount(3, $results);

        foreach ($results as $result) {
            $this->assertSame('product', $result['entity_type']);
            $this->assertSame('images', $result['collection']);
            $this->assertSame('public', $result['visibility']);
        }
    }

    public function test_upload_files_sets_tenant_id_and_uploaded_by(): void
    {
        $entityId  = (string) Str::uuid();
        $tenantId  = (string) Str::uuid();
        $userId    = (string) Str::uuid();

        $results = $this->service->uploadFiles(
            entityType: 'user',
            entityId:   $entityId,
            files:      [UploadedFile::fake()->create('doc.txt', 10, 'text/plain')],
            collection: 'default',
            visibility: 'private',
            tenantId:   $tenantId,
            uploadedBy: $userId,
        );

        $this->assertSame($tenantId, $results[0]['tenant_id']);
        $this->assertSame($userId,   $results[0]['uploaded_by']);
    }

    // ──────────────────────────────────────────────────────────
    // uploadAvatar()
    // ──────────────────────────────────────────────────────────

    public function test_upload_avatar_updates_user_avatar_and_returns_url(): void
    {
        $user = User::create([
            'name'     => 'Avatar User',
            'email'    => 'avatar@example.com',
            'password' => bcrypt('secret'),
        ]);

        $file = UploadedFile::fake()->image('avatar.jpg');

        $url = $this->service->uploadAvatar($user->id, $file);

        $this->assertNotEmpty($url);
        $this->assertDatabaseHas('attachments', [
            'entity_type' => 'user',
            'entity_id'   => $user->id,
            'collection'  => 'avatar',
        ]);
    }

    public function test_upload_avatar_upserts_existing_record(): void
    {
        $user = User::create([
            'name'     => 'Avatar User 2',
            'email'    => 'avatar2@example.com',
            'password' => bcrypt('secret'),
        ]);

        $file = UploadedFile::fake()->image('avatar.jpg');

        // Upload twice — should not create duplicate Attachment records
        $this->service->uploadAvatar($user->id, $file);
        $this->service->uploadAvatar($user->id, UploadedFile::fake()->image('avatar2.jpg'));

        $count = Attachment::where('entity_type', 'user')
            ->where('entity_id', $user->id)
            ->where('collection', 'avatar')
            ->count();

        $this->assertSame(1, $count);
    }

    // ──────────────────────────────────────────────────────────
    // listAttachments()
    // ──────────────────────────────────────────────────────────

    public function test_list_attachments_returns_all_records_for_entity(): void
    {
        $entityId = (string) Str::uuid();

        Attachment::create([
            'id'                => (string) Str::uuid(),
            'entity_type'       => 'user',
            'entity_id'         => $entityId,
            'collection'        => 'docs',
            'disk'              => 'local',
            'path'              => 'user/docs/a.pdf',
            'original_filename' => 'a.pdf',
            'mime_type'         => 'application/pdf',
            'size'              => 1024,
            'visibility'        => 'private',
        ]);

        Attachment::create([
            'id'                => (string) Str::uuid(),
            'entity_type'       => 'user',
            'entity_id'         => $entityId,
            'collection'        => 'images',
            'disk'              => 'public',
            'path'              => 'user/images/b.jpg',
            'original_filename' => 'b.jpg',
            'mime_type'         => 'image/jpeg',
            'size'              => 2048,
            'visibility'        => 'public',
        ]);

        $all = $this->service->listAttachments('user', $entityId);

        $this->assertCount(2, $all);
    }

    public function test_list_attachments_filters_by_collection(): void
    {
        $entityId = (string) Str::uuid();

        Attachment::create([
            'id'                => (string) Str::uuid(),
            'entity_type'       => 'user',
            'entity_id'         => $entityId,
            'collection'        => 'docs',
            'disk'              => 'local',
            'path'              => 'user/docs/a.pdf',
            'original_filename' => 'a.pdf',
            'mime_type'         => 'application/pdf',
            'size'              => 1024,
            'visibility'        => 'private',
        ]);

        Attachment::create([
            'id'                => (string) Str::uuid(),
            'entity_type'       => 'user',
            'entity_id'         => $entityId,
            'collection'        => 'images',
            'disk'              => 'public',
            'path'              => 'user/images/b.jpg',
            'original_filename' => 'b.jpg',
            'mime_type'         => 'image/jpeg',
            'size'              => 2048,
            'visibility'        => 'public',
        ]);

        $docs = $this->service->listAttachments('user', $entityId, 'docs');

        $this->assertCount(1, $docs);
        $this->assertSame('docs', $docs[0]['collection']);
    }

    // ──────────────────────────────────────────────────────────
    // deleteAttachment()
    // ──────────────────────────────────────────────────────────

    public function test_delete_attachment_removes_record_and_file(): void
    {
        $path = 'user/docs/' . Str::uuid() . '.pdf';
        Storage::disk('local')->put($path, 'content');

        $attachment = Attachment::create([
            'id'                => (string) Str::uuid(),
            'entity_type'       => 'user',
            'entity_id'         => (string) Str::uuid(),
            'collection'        => 'docs',
            'disk'              => 'local',
            'path'              => $path,
            'original_filename' => 'doc.pdf',
            'mime_type'         => 'application/pdf',
            'size'              => 1024,
            'visibility'        => 'private',
        ]);

        $this->service->deleteAttachment($attachment->id);

        $this->assertDatabaseMissing('attachments', ['id' => $attachment->id]);
        Storage::disk('local')->assertMissing($path);
    }

    // ──────────────────────────────────────────────────────────
    // findAttachmentById()
    // ──────────────────────────────────────────────────────────

    public function test_find_attachment_by_id_returns_array(): void
    {
        $attachment = Attachment::create([
            'id'                => (string) Str::uuid(),
            'entity_type'       => 'user',
            'entity_id'         => (string) Str::uuid(),
            'collection'        => 'default',
            'disk'              => 'local',
            'path'              => 'user/default/file.txt',
            'original_filename' => 'file.txt',
            'mime_type'         => 'text/plain',
            'size'              => 100,
            'visibility'        => 'private',
        ]);

        $result = $this->service->findAttachmentById($attachment->id);

        $this->assertNotNull($result);
        $this->assertSame($attachment->id, $result['id']);
        $this->assertArrayHasKey('url', $result);
    }

    public function test_find_attachment_by_id_returns_null_when_not_found(): void
    {
        $result = $this->service->findAttachmentById((string) Str::uuid());

        $this->assertNull($result);
    }

    // ──────────────────────────────────────────────────────────
    // generateSignedUrl()
    // ──────────────────────────────────────────────────────────

    public function test_generate_signed_url_returns_url_string(): void
    {
        $path = 'public/test-file.jpg';
        Storage::disk('public')->put($path, 'content');

        $url = $this->service->generateSignedUrl($path, 3600, 'public');

        $this->assertNotEmpty($url);
        $this->assertIsString($url);
    }
}
