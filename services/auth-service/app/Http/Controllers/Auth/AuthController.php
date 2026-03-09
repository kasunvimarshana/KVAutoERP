<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Application\Auth\Commands\LoginCommand;
use App\Application\Auth\Commands\LogoutCommand;
use App\Application\Auth\Commands\RegisterUserCommand;
use App\Application\Auth\DTOs\UserDTO;
use App\Application\Auth\Handlers\LoginHandler;
use App\Application\Auth\Handlers\LogoutHandler;
use App\Application\Auth\Handlers\RegisterUserHandler;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Domain\Tenant\Entities\Tenant;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\TokenResource;
use App\Http\Resources\UserResource;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Throwable;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly RegisterUserHandler $registerHandler,
        private readonly LoginHandler $loginHandler,
        private readonly LogoutHandler $logoutHandler,
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    /**
     * Register a new user within the current tenant.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $tenant = app(Tenant::class);

            $command = new RegisterUserCommand(
                tenantId: $tenant->id,
                name: $request->string('name')->trim()->toString(),
                email: $request->string('email')->lower()->toString(),
                password: $request->input('password'),
                organizationId: $request->input('organization_id'),
                roles: $request->input('roles', ['user']),
                metadata: $request->input('metadata', []),
            );

            $userDTO = $this->registerHandler->handle($command);

            return $this->created(
                new UserResource($userDTO),
                'Registration successful. Please verify your email address.',
            );
        } catch (\InvalidArgumentException $e) {
            return $this->unprocessable(null, $e->getMessage());
        } catch (\RuntimeException $e) {
            $code = $e->getCode();

            return match ((int) $code) {
                404     => $this->notFound($e->getMessage()),
                403     => $this->forbidden($e->getMessage()),
                422     => $this->unprocessable(null, $e->getMessage()),
                default => $this->serverError($e->getMessage()),
            };
        } catch (Throwable $e) {
            Log::error('Registration failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            return $this->serverError('Registration failed. Please try again.');
        }
    }

    /**
     * Authenticate a user and issue an access token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $tenant = app(Tenant::class);

            $command = new LoginCommand(
                tenantId: $tenant->id,
                email: $request->string('email')->lower()->toString(),
                password: $request->input('password'),
                deviceInfo: $request->input('device_info', []),
                ipAddress: $request->ip() ?? '',
                remember: (bool) $request->input('remember', false),
            );

            $tokenDTO = $this->loginHandler->handle($command);

            return $this->success(new TokenResource($tokenDTO), 'Login successful.');
        } catch (\RuntimeException $e) {
            $code = (int) $e->getCode();

            return match ($code) {
                401     => $this->unauthorized($e->getMessage()),
                403     => $this->forbidden($e->getMessage()),
                404     => $this->notFound($e->getMessage()),
                default => $this->serverError($e->getMessage()),
            };
        } catch (Throwable $e) {
            Log::error('Login failed', ['error' => $e->getMessage()]);

            return $this->serverError('Authentication failed. Please try again.');
        }
    }

    /**
     * Revoke the authenticated user's current access token.
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            /** @var \App\Domain\User\Entities\User $user */
            $user   = $request->user();
            $tenant = app(Tenant::class);

            $command = new LogoutCommand(
                userId: $user->id,
                tenantId: $tenant->id,
                revokeAll: (bool) $request->input('revoke_all', false),
            );

            $this->logoutHandler->handle($command);

            return $this->success(null, 'Successfully logged out.');
        } catch (Throwable $e) {
            Log::error('Logout failed', ['error' => $e->getMessage()]);

            return $this->serverError('Logout failed. Please try again.');
        }
    }

    /**
     * Return the authenticated user's profile.
     */
    public function me(Request $request): JsonResponse
    {
        /** @var \App\Domain\User\Entities\User $user */
        $user = $request->user();

        return $this->success(
            new UserResource(UserDTO::fromEntity($user)),
            'User profile retrieved.',
        );
    }

    /**
     * Refresh an access token using the current bearer token.
     * (Passport handles the actual refresh via /oauth/token; this endpoint
     *  is a convenience wrapper for clients that need user context.)
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            /** @var \App\Domain\User\Entities\User $user */
            $user   = $request->user();
            $tenant = app(Tenant::class);

            // Revoke old token and issue a fresh one
            $user->token()?->revoke();

            $tokenResult = $user->createToken(
                "tenant:{$tenant->id}",
                ['read', 'write'],
                now()->addMinutes(config('passport.token_lifetime', 1440)),
            );

            return $this->success([
                'access_token' => $tokenResult->accessToken,
                'token_type'   => 'Bearer',
                'expires_in'   => config('passport.token_lifetime', 1440) * 60,
            ], 'Token refreshed.');
        } catch (Throwable $e) {
            Log::error('Token refresh failed', ['error' => $e->getMessage()]);

            return $this->serverError('Token refresh failed.');
        }
    }

    /**
     * Verify a user's email address using the token sent by email.
     */
    public function verifyEmail(Request $request, string $token): JsonResponse
    {
        try {
            $user = $this->userRepository->findByEmailVerificationToken($token);

            if ($user === null) {
                return $this->notFound('Invalid or expired verification token.');
            }

            if ($user->isEmailVerified()) {
                return $this->success(null, 'Email address already verified.');
            }

            $user->email_verified_at = now();
            $metadata                = $user->metadata ?? [];
            unset($metadata['email_verification_token']);
            $user->metadata = $metadata;
            $user->save();

            return $this->success(null, 'Email address verified successfully.');
        } catch (Throwable $e) {
            Log::error('Email verification failed', ['error' => $e->getMessage()]);

            return $this->serverError('Email verification failed.');
        }
    }
}
