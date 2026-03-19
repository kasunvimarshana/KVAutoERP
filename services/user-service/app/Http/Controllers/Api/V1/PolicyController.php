<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Contracts\PolicyServiceContract;
use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * ABAC Policy management controller.
 *
 * Policies define fine-grained attribute-based access-control rules
 * beyond simple RBAC.  Each policy specifies:
 *   - action         : the operation being controlled (e.g. "users:delete")
 *   - effect         : allow | deny
 *   - subject_conditions  : attributes the requesting principal must have
 *   - resource_conditions : attributes the target resource must have
 *   - environment_conditions : contextual constraints (IP range, time, etc.)
 */
class PolicyController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly PolicyServiceContract $policyService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = (string) $request->attributes->get('tenant_id', '');
        $filters  = $request->only(['is_active', 'effect', 'search']);
        $perPage  = (int) $request->get('per_page', 20);

        $result = $this->policyService->list($tenantId, $filters, $perPage);

        return $this->paginatedResponse($result['data'], $result['pagination']);
    }

    public function show(string $id): JsonResponse
    {
        $policy = $this->policyService->findById($id);

        if (! $policy) {
            return $this->errorResponse('Policy not found', [], 404);
        }

        return $this->successResponse($policy);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'                   => ['required', 'string', 'max:255'],
            'slug'                   => ['sometimes', 'string', 'max:255'],
            'description'            => ['sometimes', 'nullable', 'string'],
            'effect'                 => ['required', 'in:allow,deny'],
            'action'                 => ['required', 'string', 'max:255'],
            'subject_conditions'     => ['sometimes', 'nullable', 'array'],
            'resource_conditions'    => ['sometimes', 'nullable', 'array'],
            'environment_conditions' => ['sometimes', 'nullable', 'array'],
            'is_active'              => ['sometimes', 'boolean'],
            'priority'               => ['sometimes', 'integer'],
        ]);

        $data['tenant_id']  = (string) $request->attributes->get('tenant_id', '');
        $data['created_by'] = (string) $request->attributes->get('user_id', '');

        $policy = $this->policyService->create($data);

        return $this->successResponse($policy, 'Policy created successfully', 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'name'                   => ['sometimes', 'string', 'max:255'],
            'slug'                   => ['sometimes', 'string', 'max:255'],
            'description'            => ['sometimes', 'nullable', 'string'],
            'effect'                 => ['sometimes', 'in:allow,deny'],
            'action'                 => ['sometimes', 'string', 'max:255'],
            'subject_conditions'     => ['sometimes', 'nullable', 'array'],
            'resource_conditions'    => ['sometimes', 'nullable', 'array'],
            'environment_conditions' => ['sometimes', 'nullable', 'array'],
            'is_active'              => ['sometimes', 'boolean'],
            'priority'               => ['sometimes', 'integer'],
        ]);

        $policy = $this->policyService->update($id, $data);

        return $this->successResponse($policy, 'Policy updated successfully');
    }

    public function destroy(string $id): JsonResponse
    {
        $this->policyService->delete($id);

        return $this->successResponse(null, 'Policy deleted successfully');
    }

    /**
     * Evaluate a policy check for the authenticated subject.
     *
     * POST /api/v1/policies/evaluate
     * Body: { action, resource: {...}, environment: {...} }
     */
    public function evaluate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'action'      => ['required', 'string', 'max:255'],
            'resource'    => ['sometimes', 'array'],
            'environment' => ['sometimes', 'array'],
        ]);

        $subject = (array) $request->attributes->get('jwt_claims', []);

        $allowed = $this->policyService->evaluate(
            subject:     $subject,
            action:      $data['action'],
            resource:    $data['resource'] ?? [],
            environment: $data['environment'] ?? [],
        );

        return $this->successResponse(['allowed' => $allowed]);
    }
}
