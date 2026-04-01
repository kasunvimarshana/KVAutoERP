<?php

declare(strict_types=1);

namespace Modules\HR\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Infrastructure\Http\Controllers\AuthorizedController;
use Modules\HR\Application\Contracts\CreateAttendanceServiceInterface;
use Modules\HR\Application\Contracts\DeleteAttendanceServiceInterface;
use Modules\HR\Application\Contracts\FindAttendanceServiceInterface;
use Modules\HR\Application\Contracts\UpdateAttendanceServiceInterface;
use Modules\HR\Application\DTOs\AttendanceData;
use Modules\HR\Application\DTOs\UpdateAttendanceData;
use Modules\HR\Domain\Entities\Attendance;
use Modules\HR\Infrastructure\Http\Requests\StoreAttendanceRequest;
use Modules\HR\Infrastructure\Http\Requests\UpdateAttendanceRequest;
use Modules\HR\Infrastructure\Http\Resources\AttendanceCollection;
use Modules\HR\Infrastructure\Http\Resources\AttendanceResource;

class AttendanceController extends AuthorizedController
{
    public function __construct(
        protected FindAttendanceServiceInterface $findService,
        protected CreateAttendanceServiceInterface $createService,
        protected UpdateAttendanceServiceInterface $updateService,
        protected DeleteAttendanceServiceInterface $deleteService,
    ) {}

    public function index(Request $request): AttendanceCollection
    {
        $this->authorize('viewAny', Attendance::class);
        $filters = $request->only(['employee_id', 'status', 'date']);
        $perPage = $request->integer('per_page', 15);
        $page    = $request->integer('page', 1);
        $sort    = $request->input('sort');
        $include = $request->input('include');
        $records = $this->findService->list($filters, $perPage, $page, $sort, $include);

        return new AttendanceCollection($records);
    }

    public function store(StoreAttendanceRequest $request): JsonResponse
    {
        $this->authorize('create', Attendance::class);
        $dto        = AttendanceData::fromArray($request->validated());
        $attendance = $this->createService->execute($dto->toArray());

        return (new AttendanceResource($attendance))->response()->setStatusCode(201);
    }

    public function show(int $id): AttendanceResource
    {
        $attendance = $this->findService->find($id);
        if (! $attendance) {
            abort(404);
        }
        $this->authorize('view', $attendance);

        return new AttendanceResource($attendance);
    }

    public function update(UpdateAttendanceRequest $request, int $id): AttendanceResource
    {
        $attendance = $this->findService->find($id);
        if (! $attendance) {
            abort(404);
        }
        $this->authorize('update', $attendance);
        $validated       = $request->validated();
        $validated['id'] = $id;
        $dto             = UpdateAttendanceData::fromArray($validated);
        $updated         = $this->updateService->execute($dto->toArray());

        return new AttendanceResource($updated);
    }

    public function destroy(int $id): JsonResponse
    {
        $attendance = $this->findService->find($id);
        if (! $attendance) {
            abort(404);
        }
        $this->authorize('delete', $attendance);
        $this->deleteService->execute(['id' => $id]);

        return response()->json(['message' => 'Attendance record deleted successfully']);
    }

    public function byEmployee(int $employeeId): JsonResponse
    {
        $this->authorize('viewAny', Attendance::class);
        $items = $this->findService->getByEmployee($employeeId);

        return response()->json(['data' => AttendanceResource::collection(collect($items))]);
    }
}
