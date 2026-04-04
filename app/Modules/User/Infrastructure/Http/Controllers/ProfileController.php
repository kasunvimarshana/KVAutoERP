<?php

declare(strict_types=1);

namespace Modules\User\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Infrastructure\Http\Controllers\AuthorizedController;
use Modules\User\Application\Contracts\ChangePasswordServiceInterface;
use Modules\User\Application\Contracts\GetUserServiceInterface;
use Modules\User\Application\Contracts\UpdateProfileServiceInterface;
use Modules\User\Application\Contracts\UploadAvatarServiceInterface;
use Modules\User\Application\DTOs\ChangePasswordData;
use Modules\User\Application\DTOs\UpdateProfileData;
use Modules\User\Infrastructure\Http\Resources\UserResource;

class ProfileController extends AuthorizedController
{
    public function __construct(
        private readonly GetUserServiceInterface $getService,
        private readonly UpdateProfileServiceInterface $updateProfileService,
        private readonly ChangePasswordServiceInterface $changePasswordService,
        private readonly UploadAvatarServiceInterface $uploadAvatarService,
    ) {}

    public function show(): JsonResponse
    {
        $userId = (int) auth()->id();
        $user = $this->getService->execute($userId);

        return (new UserResource($user))->response();
    }

    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:20'],
            'locale' => ['sometimes', 'string', 'max:10'],
            'timezone' => ['sometimes', 'string', 'max:50'],
        ]);

        $userId = (int) auth()->id();
        $data = new UpdateProfileData(
            name: $request->input('name'),
            phone: $request->input('phone'),
            locale: $request->input('locale'),
            timezone: $request->input('timezone'),
        );

        $user = $this->updateProfileService->execute($userId, $data);

        return (new UserResource($user))->response();
    }

    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $userId = (int) auth()->id();
        $data = new ChangePasswordData(
            userId: $userId,
            currentPassword: $request->input('current_password'),
            newPassword: $request->input('new_password'),
        );

        $this->changePasswordService->execute($data);

        return response()->json(['message' => 'Password changed successfully.']);
    }

    public function updatePreferences(Request $request): JsonResponse
    {
        $userId = (int) auth()->id();
        $data = new UpdateProfileData(
            preferences: $request->input('preferences', []),
        );

        $user = $this->updateProfileService->execute($userId, $data);

        return (new UserResource($user))->response();
    }

    public function uploadAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => ['required', 'file', 'image', 'max:2048'],
        ]);

        $userId = (int) auth()->id();
        $path = $request->file('avatar')->store('avatars', 'public');

        $user = $this->uploadAvatarService->execute($userId, $path);

        return (new UserResource($user))->response();
    }
}
