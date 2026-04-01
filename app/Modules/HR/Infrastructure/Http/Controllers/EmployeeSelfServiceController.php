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

class EmployeeSelfServiceController extends Controller
{
    public function __construct(
        private readonly FindEmployeeServiceInterface $findEmployeeService,
        private readonly FindLeaveRequestServiceInterface $findLeaveRequestService,
    ) {}

    public function profile(Request $request): JsonResponse
    {
        $userId   = $request->user()->id;
        $employee = $this->findEmployeeService->findByUserId($userId);
        if (! $employee) {
            abort(404, 'No employee record linked to your account.');
        }

        return (new EmployeeResource($employee))->response();
    }

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
