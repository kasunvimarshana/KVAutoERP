<?php

declare(strict_types=1);

namespace Modules\User\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Modules\Auth\Application\UseCases\GetAuthenticatedUser;
use Modules\Core\Infrastructure\Http\Controllers\AuthorizedController;
use Modules\Core\Domain\Exceptions\DomainException;
use Modules\User\Application\Contracts\ChangePasswordServiceInterface;
use Modules\User\Application\Contracts\FindUserServiceInterface;
use Modules\User\Application\Contracts\UpdatePreferencesServiceInterface;
use Modules\User\Application\Contracts\UpdateProfileServiceInterface;
use Modules\User\Application\Contracts\UploadAvatarServiceInterface;
use Modules\User\Application\DTOs\UserPreferencesData;
use Modules\User\Infrastructure\Http\Requests\ChangePasswordRequest;
use Modules\User\Infrastructure\Http\Requests\UpdatePreferencesRequest;
use Modules\User\Infrastructure\Http\Requests\UpdateProfileRequest;
use Modules\User\Infrastructure\Http\Requests\UploadAvatarRequest;
use Modules\User\Infrastructure\Http\Resources\UserResource;

class ProfileController extends AuthorizedController
{
    public function __construct(
        protected GetAuthenticatedUser $getAuthenticatedUser,
        protected FindUserServiceInterface $findService,
        protected UpdateProfileServiceInterface $updateProfileService,
        protected ChangePasswordServiceInterface $changePasswordService,
        protected UpdatePreferencesServiceInterface $updatePreferencesService,
        protected UploadAvatarServiceInterface $uploadAvatarService
    ) {}

    public function show(): JsonResponse
    {
        $userId = $this->authenticatedUserId();
        if ($userId === null) {
            return Response::json(['message' => 'Unauthenticated'], 401);
        }

        $user = $this->findService->find($userId);

        if (! $user) {
            return Response::json(['message' => 'User profile unavailable'], 404);
        }

        return Response::json(new UserResource($user));
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $userId = $this->authenticatedUserId();
        if ($userId === null) {
            return Response::json(['message' => 'Unauthenticated'], 401);
        }

        $data = array_merge($request->validated(), ['user_id' => $userId]);
        $user = $this->updateProfileService->execute($data);

        return Response::json(new UserResource($user));
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $userId = $this->authenticatedUserId();
        if ($userId === null) {
            return Response::json(['message' => 'Unauthenticated'], 401);
        }

        $data = array_merge($request->validated(), ['user_id' => $userId]);

        try {
            $this->changePasswordService->execute($data);
        } catch (DomainException $e) {
            return Response::json(['message' => $e->getMessage()], 422);
        }

        return Response::json(['message' => 'Password changed successfully.']);
    }

    public function updatePreferences(UpdatePreferencesRequest $request): JsonResponse
    {
        $userId = $this->authenticatedUserId();
        if ($userId === null) {
            return Response::json(['message' => 'Unauthenticated'], 401);
        }

        $dto = UserPreferencesData::fromArray($request->validated());
        $user = $this->updatePreferencesService->execute(['user_id' => $userId] + $dto->toArray());

        return Response::json(new UserResource($user));
    }

    public function uploadAvatar(UploadAvatarRequest $request): JsonResponse
    {
        $userId = $this->authenticatedUserId();
        if ($userId === null) {
            return Response::json(['message' => 'Unauthenticated'], 401);
        }

        $file = $request->file('avatar');

        $user = $this->uploadAvatarService->execute([
            'user_id' => $userId,
            'file'    => [
                'tmp_path'  => $file->getRealPath(),
                'name'      => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size'      => $file->getSize(),
            ],
        ]);

        return Response::json(new UserResource($user));
    }

    private function authenticatedUserId(): ?int
    {
        $authenticatable = $this->getAuthenticatedUser->execute();
        if (! $authenticatable) {
            return null;
        }

        return (int) $authenticatable->getAuthIdentifier();
    }
}
