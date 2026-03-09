<?php

declare(strict_types=1);

namespace App\Shared\Base;

use App\Shared\Contracts\RepositoryInterface;
use App\Shared\Contracts\WebhookInterface;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Abstract Base Service.
 *
 * All domain services in KV_SAAS extend this class to gain:
 *  - Repository injection
 *  - Request-processing pipeline
 *  - Structured logging
 *  - Validation helpers
 *  - Authorization helpers
 *  - Event / webhook emission
 *
 * @template TRepository of RepositoryInterface
 */
abstract class BaseService
{
    protected LoggerInterface $logger;

    /**
     * @param  TRepository               $repository  Domain repository.
     * @param  WebhookInterface|null     $webhook     Webhook dispatcher (optional).
     * @param  LoggerInterface|null      $logger      PSR-3 logger.
     */
    public function __construct(
        protected readonly RepositoryInterface $repository,
        protected readonly ?WebhookInterface $webhook = null,
        ?LoggerInterface $logger = null,
    ) {
        $this->logger = $logger ?? new NullLogger();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Pipeline
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Process a payload through an ordered list of pipe callables / classes.
     *
     * Each pipe receives the payload and a $next closure:
     *   fn(mixed $payload, callable $next): mixed
     *
     * @param  mixed          $payload  Data to process.
     * @param  array<callable|string>  $pipes   Pipeline stages.
     * @return mixed                    Final processed result.
     */
    protected function pipeline(mixed $payload, array $pipes): mixed
    {
        return app(Pipeline::class)
            ->send($payload)
            ->through($pipes)
            ->thenReturn();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Authorization
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Assert the currently authenticated user has the given ability.
     *
     * @param  string  $ability  Gate ability name.
     * @param  mixed   $subject  Subject model (optional).
     * @return void
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    protected function authorize(string $ability, mixed $subject = null): void
    {
        /** @var Guard $guard */
        $user = Auth::user();

        if ($subject !== null) {
            \Illuminate\Support\Facades\Gate::authorize($ability, $subject);
        } else {
            \Illuminate\Support\Facades\Gate::authorize($ability);
        }
    }

    /**
     * Check without throwing.
     *
     * @param  string  $ability
     * @param  mixed   $subject
     * @return bool
     */
    protected function can(string $ability, mixed $subject = null): bool
    {
        return $subject !== null
            ? \Illuminate\Support\Facades\Gate::check($ability, $subject)
            : \Illuminate\Support\Facades\Gate::check($ability);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Validation
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Validate data against the given rules.
     *
     * @param  array<string,mixed>             $data
     * @param  array<string,string|array>      $rules
     * @param  array<string,string>            $messages  Custom messages.
     * @return array<string,mixed>                        Validated data.
     *
     * @throws ValidationException
     */
    protected function validate(
        array $data,
        array $rules,
        array $messages = [],
    ): array {
        $validator = \Illuminate\Support\Facades\Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Event emission
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Fire a Laravel event.
     *
     * @param  object|string  $event    Event object or class name.
     * @param  array          $payload  Payload for string events.
     * @return void
     */
    protected function emit(object|string $event, array $payload = []): void
    {
        if (is_string($event)) {
            \Illuminate\Support\Facades\Event::dispatch($event, $payload);
        } else {
            \Illuminate\Support\Facades\Event::dispatch($event);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Webhook dispatch
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Dispatch a webhook event for the current tenant.
     *
     * Silently no-ops when no WebhookInterface was injected.
     *
     * @param  string              $event     E.g. "order.created".
     * @param  array<string,mixed> $payload   Event data.
     * @param  string|null         $tenantId  Falls back to current tenant.
     * @return void
     */
    protected function dispatchWebhook(
        string $event,
        array $payload,
        ?string $tenantId = null,
    ): void {
        if ($this->webhook === null) {
            return;
        }

        $tenantId ??= $this->resolveTenantId();

        if ($tenantId === null) {
            $this->logger->warning('[BaseService] Cannot dispatch webhook: no tenant ID', [
                'event' => $event,
            ]);
            return;
        }

        try {
            $this->webhook->dispatch($event, $payload, $tenantId);
        } catch (\Throwable $e) {
            // Never let webhook failures bubble up into the main request flow.
            $this->logger->error('[BaseService] Webhook dispatch failed', [
                'event' => $event,
                'error' => $e->getMessage(),
            ]);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Protected helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Attempt to resolve the current tenant ID from the service container.
     *
     * @return string|null
     */
    protected function resolveTenantId(): ?string
    {
        try {
            return app(\App\Shared\Contracts\TenantInterface::class)->getTenantId();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Return the currently authenticated user's ID or null.
     *
     * @return string|int|null
     */
    protected function currentUserId(): string|int|null
    {
        return Auth::id();
    }

    /**
     * Return the currently authenticated user or null.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    protected function currentUser(): ?\Illuminate\Contracts\Auth\Authenticatable
    {
        return Auth::user();
    }
}
