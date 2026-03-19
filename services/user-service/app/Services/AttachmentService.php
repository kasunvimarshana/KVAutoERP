<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\AttachmentServiceContract;
use App\Models\Attachment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttachmentService implements AttachmentServiceContract
{
    // ──────────────────────────────────────────────────────────
    // Avatar helpers (backwards-compatible)
    // ──────────────────────────────────────────────────────────

    public function uploadAvatar(string $userId, UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $path      = "avatars/{$userId}/avatar.{$extension}";

        Storage::disk('public')->put($path, file_get_contents($file->getRealPath()));

        User::where('id', $userId)->update(['avatar' => $path]);

        // Upsert a canonical Attachment record for the avatar
        $existing = Attachment::where('entity_type', 'user')
            ->where('entity_id', $userId)
            ->where('collection', 'avatar')
            ->first();

        $attachmentData = [
            'disk'              => 'public',
            'path'              => $path,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type'         => $file->getMimeType() ?? 'image/jpeg',
            'size'              => $file->getSize(),
            'visibility'        => 'public',
            'uploaded_by'       => $userId,
        ];

        if ($existing) {
            $existing->update($attachmentData);
        } else {
            Attachment::create(array_merge($attachmentData, [
                'id'          => (string) Str::uuid(),
                'entity_type' => 'user',
                'entity_id'   => $userId,
                'collection'  => 'avatar',
            ]));
        }

        return $this->generateSignedUrl($path);
    }

    public function deleteAvatar(string $userId): void
    {
        $user = User::findOrFail($userId);

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $user->update(['avatar' => null]);
        }

        Attachment::where('entity_type', 'user')
            ->where('entity_id', $userId)
            ->where('collection', 'avatar')
            ->delete();
    }

    // ──────────────────────────────────────────────────────────
    // General multi-file upload
    // ──────────────────────────────────────────────────────────

    /**
     * @param  UploadedFile[]  $files
     * @return array<int, array<string, mixed>>
     */
    public function uploadFiles(
        string $entityType,
        string $entityId,
        array  $files,
        string $collection  = 'default',
        string $visibility  = 'private',
        string $tenantId    = '',
        string $uploadedBy  = '',
    ): array {
        $disk    = $visibility === 'public' ? 'public' : 'local';
        $results = [];

        foreach ($files as $file) {
            $extension = $file->getClientOriginalExtension();
            $filename  = Str::uuid() . '.' . $extension;
            $path      = "{$entityType}/{$entityId}/{$collection}/{$filename}";

            Storage::disk($disk)->put($path, file_get_contents($file->getRealPath()));

            $attachment = Attachment::create([
                'id'                => (string) Str::uuid(),
                'tenant_id'         => $tenantId ?: null,
                'entity_type'       => $entityType,
                'entity_id'         => $entityId,
                'collection'        => $collection,
                'disk'              => $disk,
                'path'              => $path,
                'original_filename' => $file->getClientOriginalName(),
                'mime_type'         => $file->getMimeType() ?? 'application/octet-stream',
                'size'              => $file->getSize(),
                'visibility'        => $visibility,
                'uploaded_by'       => $uploadedBy ?: null,
            ]);

            $results[] = $this->toArray($attachment);
        }

        return $results;
    }

    // ──────────────────────────────────────────────────────────
    // URL generation
    // ──────────────────────────────────────────────────────────

    public function generateSignedUrl(string $path, int $ttl = 3600, string $disk = 'public'): string
    {
        $storage = Storage::disk($disk);

        // Use temporaryUrl for S3-compatible drivers; fall back to asset URL for local
        if (method_exists($storage, 'temporaryUrl')) {
            try {
                return $storage->temporaryUrl($path, now()->addSeconds($ttl));
            } catch (\RuntimeException) {
                // Local driver does not support temporaryUrl
            }
        }

        return $storage->url($path);
    }

    // ──────────────────────────────────────────────────────────
    // Attachment management
    // ──────────────────────────────────────────────────────────

    public function deleteAttachment(string $attachmentId): void
    {
        $attachment = Attachment::findOrFail($attachmentId);

        Storage::disk($attachment->disk)->delete($attachment->path);

        $attachment->delete();
    }

    public function findAttachmentById(string $attachmentId): ?array
    {
        $attachment = Attachment::find($attachmentId);

        return $attachment ? $this->toArray($attachment) : null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listAttachments(
        string  $entityType,
        string  $entityId,
        ?string $collection = null,
    ): array {
        $query = Attachment::where('entity_type', $entityType)
            ->where('entity_id', $entityId);

        if ($collection !== null) {
            $query->where('collection', $collection);
        }

        return $query
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn (Attachment $a) => $this->toArray($a))
            ->all();
    }

    // ──────────────────────────────────────────────────────────
    // Internal helpers
    // ──────────────────────────────────────────────────────────

    private function toArray(Attachment $attachment): array
    {
        return [
            'id'                => $attachment->id,
            'tenant_id'         => $attachment->tenant_id,
            'entity_type'       => $attachment->entity_type,
            'entity_id'         => $attachment->entity_id,
            'collection'        => $attachment->collection,
            'disk'              => $attachment->disk,
            'path'              => $attachment->path,
            'original_filename' => $attachment->original_filename,
            'mime_type'         => $attachment->mime_type,
            'size'              => $attachment->size,
            'visibility'        => $attachment->visibility,
            'uploaded_by'       => $attachment->uploaded_by,
            'url'               => $attachment->url(),
            'metadata'          => $attachment->metadata,
            'created_at'        => $attachment->created_at?->toIso8601String(),
        ];
    }
}
