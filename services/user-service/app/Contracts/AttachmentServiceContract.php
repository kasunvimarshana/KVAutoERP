<?php

declare(strict_types=1);

namespace App\Contracts;

use Illuminate\Http\UploadedFile;

interface AttachmentServiceContract
{
    /** Upload avatar, persist it, and return the public/signed URL. */
    public function uploadAvatar(string $userId, UploadedFile $file): string;

    public function deleteAvatar(string $userId): void;

    /**
     * Upload one or more files attached to any entity.
     *
     * @param  UploadedFile[]  $files
     * @return array<int, array<string, mixed>>  Serialised Attachment data arrays
     */
    public function uploadFiles(
        string $entityType,
        string $entityId,
        array  $files,
        string $collection  = 'default',
        string $visibility  = 'private',
        string $tenantId    = '',
        string $uploadedBy  = '',
    ): array;

    /** Return a signed (time-limited) or public URL for an attachment path. */
    public function generateSignedUrl(string $path, int $ttl = 3600, string $disk = 'public'): string;

    /** Delete an attachment record and its underlying file. */
    public function deleteAttachment(string $attachmentId): void;

    /** Find a single attachment by its ID. */
    public function findAttachmentById(string $attachmentId): ?array;

    /**
     * List all attachments for an entity (optionally filtered by collection).
     *
     * @return array<int, array<string, mixed>>
     */
    public function listAttachments(
        string  $entityType,
        string  $entityId,
        ?string $collection = null,
    ): array;
}
