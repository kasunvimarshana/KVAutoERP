<?php

declare(strict_types=1);

namespace Modules\User\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Modules\User\Application\Contracts\FindUserServiceInterface;
use Modules\Core\Application\Contracts\FileStorageServiceInterface;
use Modules\Core\Infrastructure\Http\Controllers\AuthorizedController;
use Modules\User\Application\Contracts\DeleteUserAttachmentServiceInterface;
use Modules\User\Application\Contracts\FindUserAttachmentsServiceInterface;
use Modules\User\Application\Contracts\UploadUserAttachmentServiceInterface;
use Modules\User\Domain\Entities\User;
use Modules\User\Domain\Entities\UserAttachment;
use Modules\User\Infrastructure\Http\Requests\ListUserAttachmentRequest;
use Modules\User\Infrastructure\Http\Requests\UploadUserAttachmentRequest;
use Modules\User\Infrastructure\Http\Resources\UserAttachmentCollection;
use Modules\User\Infrastructure\Http\Resources\UserAttachmentResource;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserAttachmentController extends AuthorizedController
{
    public function __construct(
        protected FindUserServiceInterface $findUserService,
        protected UploadUserAttachmentServiceInterface $uploadService,
        protected DeleteUserAttachmentServiceInterface $deleteService,
        protected FindUserAttachmentsServiceInterface $findService,
        protected FileStorageServiceInterface $storage
    ) {}

    public function index(int $userId, ListUserAttachmentRequest $request): UserAttachmentCollection
    {
        $user = $this->findUserOrFail($userId);
        $this->authorize('viewAttachments', $user);

        $validated = $request->validated();
        $type = $validated['type'] ?? null;
        $perPage = (int) ($validated['per_page'] ?? 15);
        $page = (int) ($validated['page'] ?? 1);
        $attachments = $this->findService->paginateByUser(
            $userId,
            is_string($type) ? $type : null,
            $perPage,
            $page
        );

        return new UserAttachmentCollection($attachments);
    }

    public function store(UploadUserAttachmentRequest $request, int $userId): UserAttachmentResource
    {
        $user = $this->findUserOrFail($userId);
        $this->authorize('uploadAttachment', $user);
        $file = $request->file('file');
        $fileInfo = [
            'tmp_path' => $file->getPathname(),
            'name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ];
        $attachment = $this->uploadService->execute([
            'user_id' => $userId,
            'file' => $fileInfo,
            'type' => $request->input('type'),
            'metadata' => $request->input('metadata'),
        ]);

        return new UserAttachmentResource($attachment);
    }


    public function destroy(int $userId, int $attachmentId): JsonResponse
    {
        $user = $this->findUserOrFail($userId);
        $this->authorize('deleteAttachment', $user);

        $attachment = $this->findAttachmentOrFail($attachmentId);
        if ($attachment->getUserId() !== $userId) {
            throw new NotFoundHttpException('Attachment not found.');
        }

        $this->deleteService->execute(['attachment_id' => $attachmentId]);

        return Response::json(['message' => 'Attachment deleted successfully']);
    }


    public function serve(string $uuid): StreamedResponse
    {
        $attachment = $this->findService->findByUuid($uuid);
        if (! $attachment) {
            throw new NotFoundHttpException('Attachment not found.');
        }
        $this->authorize('view', $attachment);

        return $this->storage->stream($attachment->getFilePath());
    }

    private function findUserOrFail(int $userId): User
    {
        $user = $this->findUserService->find($userId);
        if (! $user) {
            throw new NotFoundHttpException('User not found.');
        }

        return $user;
    }

    private function findAttachmentOrFail(int $attachmentId): UserAttachment
    {
        $attachment = $this->findService->find($attachmentId);
        if (! $attachment) {
            throw new NotFoundHttpException('Attachment not found.');
        }

        return $attachment;
    }
}
