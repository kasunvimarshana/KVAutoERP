<?php

namespace App\Modules\User\Webhooks;

use App\Modules\User\DTOs\UserWebhookDTO;
use App\Modules\User\Repositories\UserRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

class UserWebhookHandler extends Controller
{
    public function __construct(private UserRepositoryInterface $userRepository) {}

    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();
        $dto     = UserWebhookDTO::fromPayload($payload);

        Log::info('User webhook received', ['event' => $dto->event, 'user_id' => $dto->userId]);

        match ($dto->event) {
            'user.registered' => $this->handleRegistered($dto),
            'user.deactivated' => $this->handleDeactivated($dto),
            default => Log::warning('Unknown webhook event', ['event' => $dto->event]),
        };

        return response()->json(['status' => 'processed']);
    }

    private function handleRegistered(UserWebhookDTO $dto): void
    {
        $user = $this->userRepository->findByKeycloakId($dto->keycloakId ?? '');

        if ($user && !$user->keycloak_id) {
            $this->userRepository->update($user, ['keycloak_id' => $dto->keycloakId]);
        }
    }

    private function handleDeactivated(UserWebhookDTO $dto): void
    {
        $user = $this->userRepository->findByKeycloakId($dto->keycloakId ?? '');

        if ($user) {
            $this->userRepository->update($user, ['is_active' => false]);
        }
    }
}
