<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Contracts\AttachmentServiceContract;
use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * General-purpose multi-file attachment controller.
 *
 * Handles file uploads for any entity type (user, product, document, etc.)
 * and serves signed URLs for private attachments.
 */
class AttachmentController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly AttachmentServiceContract $attachmentService,
    ) {}

    /**
     * Upload one or more files for an entity.
     *
     * POST /api/v1/attachments
     * Body (multipart):
     *   - files[]        : one or many files (required)
     *   - entity_type    : string  (required, e.g. "user", "product")
     *   - entity_id      : string  (required, UUID)
     *   - collection     : string  (optional, default "default")
     *   - visibility     : "public"|"private" (optional, default "private")
     */
    public function upload(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'files'       => ['required', 'array', 'min:1', 'max:10'],
            'files.*'     => ['required', 'file', 'max:20480'],          // 20 MB per file
            'entity_type' => ['required', 'string', 'max:100'],
            'entity_id'   => ['required', 'string', 'max:36'],
            'collection'  => ['sometimes', 'string', 'max:100'],
            'visibility'  => ['sometimes', 'in:public,private'],
        ]);

        $tenantId  = (string) $request->attributes->get('tenant_id', '');
        $uploadedBy = (string) $request->attributes->get('user_id', '');

        $attachments = $this->attachmentService->uploadFiles(
            entityType: $validated['entity_type'],
            entityId:   $validated['entity_id'],
            files:      $request->file('files', []),
            collection: $validated['collection'] ?? 'default',
            visibility: $validated['visibility'] ?? 'private',
            tenantId:   $tenantId,
            uploadedBy: $uploadedBy,
        );

        return $this->successResponse($attachments, 'Files uploaded successfully', 201);
    }

    /**
     * List attachments for an entity.
     *
     * GET /api/v1/attachments?entity_type=user&entity_id=xxx&collection=documents
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'entity_type' => ['required', 'string', 'max:100'],
            'entity_id'   => ['required', 'string', 'max:36'],
            'collection'  => ['sometimes', 'string', 'max:100'],
        ]);

        $attachments = $this->attachmentService->listAttachments(
            entityType:  $request->string('entity_type')->toString(),
            entityId:    $request->string('entity_id')->toString(),
            collection:  $request->get('collection'),
        );

        return $this->successResponse($attachments);
    }

    /**
     * Delete an attachment by ID.
     *
     * DELETE /api/v1/attachments/{id}
     */
    public function destroy(string $id): JsonResponse
    {
        $this->attachmentService->deleteAttachment($id);

        return $this->successResponse(null, 'Attachment deleted successfully');
    }

    /**
     * Generate a fresh signed URL for a private attachment.
     *
     * GET /api/v1/attachments/{id}/signed-url?ttl=3600
     */
    public function signedUrl(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'ttl' => ['sometimes', 'integer', 'min:60', 'max:86400'],
        ]);

        $attachment = $this->attachmentService->findAttachmentById($id);

        if (! $attachment) {
            return $this->errorResponse('Attachment not found', [], 404);
        }

        $ttl = (int) $request->get('ttl', 3600);
        $url = $this->attachmentService->generateSignedUrl($attachment['path'], $ttl, $attachment['disk']);

        return $this->successResponse(['url' => $url, 'expires_in' => $ttl]);
    }
}
