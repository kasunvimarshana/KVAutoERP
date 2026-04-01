<?php

declare(strict_types=1);

namespace Modules\HR\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\HR\Application\Contracts\FindEmployeeServiceInterface;
use Modules\HR\Application\Contracts\FindLeaveRequestServiceInterface;
use Modules\HR\Infrastructure\Http\Resources\EmployeeResource;
use Modules\HR\Infrastructure\Http\Resources\LeaveRequestCollection;
use OpenApi\Attributes as OA;

class EmployeeSelfServiceController extends Controller
{
    public function __construct(
        private readonly FindEmployeeServiceInterface $findEmployeeService,
        private readonly FindLeaveRequestServiceInterface $findLeaveRequestService,
    ) {}

    #[OA\Get(
        path: '/api/hr/me/profile',
        summary: 'Get authenticated employee profile',
        tags: ['HR - Self Service'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Employee profile for the authenticated user'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'No employee record linked to your account'),
        ],
    )]
    public function profile(Request $request): JsonResponse
    {
        $userId   = $request->user()->id;
        $employee = $this->findEmployeeService->findByUserId($userId);
        if (! $employee) {
            abort(404, 'No employee record linked to your account.');
        }

        return (new EmployeeResource($employee))->response();
    }

    #[OA\Get(
        path: '/api/hr/me/leave-requests',
        summary: 'Get authenticated employee leave requests',
        tags: ['HR - Self Service'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Leave requests for the authenticated employee'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'No employee record linked to your account'),
        ],
    )]
    public function leaveRequests(Request $request): JsonResponse
    {
        $userId   = $request->user()->id;
        $employee = $this->findEmployeeService->findByUserId($userId);
        if (! $employee) {
            abort(404, 'No employee record linked to your account.');
        }

        $leaves = $this->findLeaveRequestService->getByEmployee($employee->getId());

        return (new LeaveRequestCollection($leaves))->response();
    }
}
