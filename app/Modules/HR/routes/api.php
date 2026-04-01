<?php

use Illuminate\Support\Facades\Route;
use Modules\HR\Infrastructure\Http\Controllers\DepartmentController;
use Modules\HR\Infrastructure\Http\Controllers\EmployeeController;
use Modules\HR\Infrastructure\Http\Controllers\LeaveRequestController;
use Modules\HR\Infrastructure\Http\Controllers\PositionController;

Route::middleware(['auth:api', 'resolve.tenant'])->prefix('hr')->group(function () {
    // Static routes must be declared BEFORE resource wildcard routes
    Route::get('employees/by-department/{departmentId}', [EmployeeController::class, 'byDepartment']);
    Route::apiResource('employees', EmployeeController::class);

    Route::get('departments/tree', [DepartmentController::class, 'tree']);
    Route::apiResource('departments', DepartmentController::class);

    Route::get('positions/by-department/{departmentId}', [PositionController::class, 'byDepartment']);
    Route::apiResource('positions', PositionController::class);

    Route::get('leave-requests/by-employee/{employeeId}', [LeaveRequestController::class, 'byEmployee']);
    Route::post('leave-requests/{id}/approve', [LeaveRequestController::class, 'approve']);
    Route::post('leave-requests/{id}/reject',  [LeaveRequestController::class, 'reject']);
    Route::apiResource('leave-requests', LeaveRequestController::class);
});
