<?php

declare(strict_types=1);

namespace Modules\User\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\User\Application\Contracts\ChangePasswordServiceInterface;
use Modules\User\Application\Contracts\GetUserServiceInterface;
use Modules\User\Application\Contracts\UpdateProfileServiceInterface;
use Modules\User\Application\Contracts\UploadAvatarServiceInterface;
use Modules\User\Application\DTOs\ChangePasswordData;
use Modules\User\Application\DTOs\UpdateProfileData;
use Modules\User\Infrastructure\Http\Requests\ChangePasswordRequest;
use Modules\User\Infrastructure\Http\Requests\UpdateProfileRequest;
use Modules\User\Infrastructure\Http\Resources\UserResource;

class ProfileController extends Controller
{
    public function __construct(
        private readonly GetUserServiceInterface $getUserService,
        private readonly UpdateProfileServiceInterface $updateProfileService,
        private readonly ChangePasswordServiceInterface $changePasswordService,
        private readonly UploadAvatarServiceInterface $uploadAvatarService,
    ) {}

    public function show(Request $request): JsonResponse
    {
        $user = $this->getUserService->execute((int) $request->user()->getAuthIdentifier());

        return response()->json(new UserResource($user));
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $data = UpdateProfileData::fromArray($request->validated());
        $user = $this->updateProfileService->execute(
            (int) $request->user()->getAuthIdentifier(),
            $data,
        );

        return response()->json(new UserResource($user));
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $data = ChangePasswordData::fromArray([
            'currentPassword'         => $validated['current_password'],
            'newPassword'             => $validated['new_password'],
            'newPasswordConfirmation' => $validated['new_password_confirmation'],
        ]);

        $this->changePasswordService->execute(
            (int) $request->user()->getAuthIdentifier(),
            $data,
        );

        return response()->json(['message' => 'Password changed successfully.']);
    }

    public function avatar(Request $request): JsonResponse
    {
        $request->validate(['avatar' => ['required', 'file', 'image', 'max:2048']]);

        $path = $request->file('avatar')->store('avatars', 'public');

        $user = $this->uploadAvatarService->execute(
            (int) $request->user()->getAuthIdentifier(),
            $path,
        );

        return response()->json(new UserResource($user));
    }
}
