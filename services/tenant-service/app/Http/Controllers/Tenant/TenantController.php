<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Application\Shared\DTOs\PaginationDTO;
use App\Application\Tenant\Commands\CreateTenantCommand;
use App\Application\Tenant\Commands\UpdateTenantCommand;
use App\Application\Tenant\Services\TenantServiceInterface;
use App\Http\Requests\Tenant\CreateTenantRequest;
use App\Http\Requests\Tenant\UpdateConfigRequest;
use App\Http\Requests\Tenant\UpdateTenantRequest;
use App\Http\Resources\TenantResource;
use App\Support\Api\ApiResponse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class TenantController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly TenantServiceInterface $tenantService,
    ) {}

    /**
     * GET /api/tenants
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $pagination = PaginationDTO::fromArray($request->all());
            $filters    = [];

            if ($request->filled('status')) {
                $filters['where']['status'] = $request->input('status');
            }

            if ($request->filled('plan')) {
                $filters['where']['plan'] = $request->input('plan');
            }

            if ($request->filled('search')) {
                $filters['search'] = [
                    'term'    => $request->input('search'),
                    'columns' => ['name', 'slug', 'domain'],
                ];
            }

            $result = $this->tenantService->getTenants($pagination, $filters);

            if ($result instanceof LengthAwarePaginator) {
                return $this->paginated($result);
            }

            return $this->success(TenantResource::collection($result));
        } catch (Throwable $e) {
            Log::error('Failed to list tenants', ['error' => $e->getMessage()]);

            return $this->serverError('Failed to retrieve tenants.');
        }
    }

    /**
     * POST /api/tenants
     */
    public function store(CreateTenantRequest $request): JsonResponse
    {
        try {
            $command = new CreateTenantCommand(
                name:             $request->string('name')->trim()->toString(),
                slug:             $request->string('slug', '')->trim()->toString(),
                plan:             $request->input('plan'),
                domain:           $request->input('domain'),
                status:           $request->input('status', 'pending'),
                maxUsers:         (int) $request->input('max_users', 100),
                maxOrganizations: (int) $request->input('max_organizations', 10),
                trialEndsAt:      $request->input('trial_ends_at'),
                settings:         $request->input('settings', []),
                config:           $request->input('config', []),
                databaseConfig:   $request->input('database_config', []),
                mailConfig:       $request->input('mail_config', []),
                cacheConfig:      $request->input('cache_config', []),
                brokerConfig:     $request->input('broker_config', []),
                metadata:         $request->input('metadata', []),
            );

            $dto = $this->tenantService->createTenant($command);

            return $this->created(new TenantResource((object) (array) $dto), 'Tenant created successfully.');
        } catch (InvalidArgumentException $e) {
            return $this->unprocessable(null, $e->getMessage());
        } catch (Throwable $e) {
            Log::error('Failed to create tenant', ['error' => $e->getMessage()]);

            return $this->serverError('Failed to create tenant.');
        }
    }

    /**
     * GET /api/tenants/{id}
     */
    public function show(string $id): JsonResponse
    {
        try {
            $dto = $this->tenantService->getTenant($id);

            return $this->success(new TenantResource((object) (array) $dto));
        } catch (RuntimeException $e) {
            return $e->getCode() === 404
                ? $this->notFound($e->getMessage())
                : $this->serverError($e->getMessage());
        } catch (Throwable $e) {
            return $this->serverError('Failed to retrieve tenant.');
        }
    }

    /**
     * PUT /api/tenants/{id}
     */
    public function update(UpdateTenantRequest $request, string $id): JsonResponse
    {
        try {
            $command = new UpdateTenantCommand(
                name:               $request->input('name'),
                slug:               $request->input('slug'),
                domain:             $request->input('domain'),
                status:             $request->input('status'),
                plan:               $request->input('plan'),
                maxUsers:           $request->input('max_users') !== null ? (int) $request->input('max_users') : null,
                maxOrganizations:   $request->input('max_organizations') !== null ? (int) $request->input('max_organizations') : null,
                trialEndsAt:        $request->input('trial_ends_at'),
                subscriptionEndsAt: $request->input('subscription_ends_at'),
                settings:           $request->input('settings'),
                config:             $request->input('config'),
                metadata:           $request->input('metadata'),
            );

            $dto = $this->tenantService->updateTenant($id, $command);

            return $this->success(new TenantResource((object) (array) $dto), 'Tenant updated successfully.');
        } catch (RuntimeException $e) {
            return $e->getCode() === 404
                ? $this->notFound($e->getMessage())
                : $this->serverError($e->getMessage());
        } catch (InvalidArgumentException $e) {
            return $this->unprocessable(null, $e->getMessage());
        } catch (Throwable $e) {
            Log::error('Failed to update tenant', ['id' => $id, 'error' => $e->getMessage()]);

            return $this->serverError('Failed to update tenant.');
        }
    }

    /**
     * DELETE /api/tenants/{id}
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $this->tenantService->deleteTenant($id);

            return $this->noContent();
        } catch (RuntimeException $e) {
            return $e->getCode() === 404
                ? $this->notFound($e->getMessage())
                : $this->serverError($e->getMessage());
        } catch (Throwable $e) {
            Log::error('Failed to delete tenant', ['id' => $id, 'error' => $e->getMessage()]);

            return $this->serverError('Failed to delete tenant.');
        }
    }

    /**
     * PATCH /api/tenants/{id}/config
     */
    public function updateConfig(UpdateConfigRequest $request, string $id): JsonResponse
    {
        try {
            $dto = $this->tenantService->updateConfig($id, $request->input('config', []));

            return $this->success(new TenantResource((object) (array) $dto), 'Tenant config updated.');
        } catch (RuntimeException $e) {
            return $e->getCode() === 404
                ? $this->notFound($e->getMessage())
                : $this->serverError($e->getMessage());
        } catch (Throwable $e) {
            Log::error('Failed to update tenant config', ['id' => $id, 'error' => $e->getMessage()]);

            return $this->serverError('Failed to update config.');
        }
    }

    /**
     * GET /api/tenants/{id}/health
     */
    public function getHealth(string $id): JsonResponse
    {
        try {
            $dto = $this->tenantService->getTenant($id);

            $dbConnected = false;

            try {
                $this->tenantService->applyRuntimeConfig($id);
                DB::connection()->getPdo();
                $dbConnected = true;
            } catch (Throwable) {
                // DB unreachable
            }

            return $this->success([
                'tenant_id'    => $dto->id,
                'status'       => $dto->status,
                'plan'         => $dto->plan,
                'is_active'    => $dto->isActive,
                'is_on_trial'  => $dto->isOnTrial,
                'plan_active'  => $dto->isPlanActive,
                'db_connected' => $dbConnected,
                'checked_at'   => now()->toIso8601String(),
            ]);
        } catch (RuntimeException $e) {
            return $e->getCode() === 404
                ? $this->notFound($e->getMessage())
                : $this->serverError($e->getMessage());
        } catch (Throwable $e) {
            return $this->serverError('Health check failed.');
        }
    }
}
