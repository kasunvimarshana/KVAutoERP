<?php

declare(strict_types=1);

namespace Modules\HR\Infrastructure\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\HR\Application\Biometric\BiometricAttendanceServiceInterface;
use Modules\HR\Application\Biometric\BiometricDeviceRegistryInterface;
use Modules\HR\Application\Biometric\BiometricEnrollmentServiceInterface;
use Modules\HR\Application\Contracts\ApproveLeaveRequestServiceInterface;
use Modules\HR\Application\Contracts\CreateAttendanceServiceInterface;
use Modules\HR\Application\Contracts\CreateDepartmentServiceInterface;
use Modules\HR\Application\Contracts\CreateEmployeeServiceInterface;
use Modules\HR\Application\Contracts\CreateLeaveRequestServiceInterface;
use Modules\HR\Application\Contracts\CreatePositionServiceInterface;
use Modules\HR\Application\Contracts\DeleteAttendanceServiceInterface;
use Modules\HR\Application\Contracts\DeleteDepartmentServiceInterface;
use Modules\HR\Application\Contracts\DeleteEmployeeServiceInterface;
use Modules\HR\Application\Contracts\DeleteLeaveRequestServiceInterface;
use Modules\HR\Application\Contracts\DeletePositionServiceInterface;
use Modules\HR\Application\Contracts\FindAttendanceServiceInterface;
use Modules\HR\Application\Contracts\FindDepartmentServiceInterface;
use Modules\HR\Application\Contracts\FindEmployeeServiceInterface;
use Modules\HR\Application\Contracts\FindLeaveRequestServiceInterface;
use Modules\HR\Application\Contracts\FindPositionServiceInterface;
use Modules\HR\Application\Contracts\LinkEmployeeToUserServiceInterface;
use Modules\HR\Application\Contracts\RejectLeaveRequestServiceInterface;
use Modules\HR\Application\Contracts\UpdateAttendanceServiceInterface;
use Modules\HR\Application\Contracts\UpdateDepartmentServiceInterface;
use Modules\HR\Application\Contracts\UpdateEmployeeServiceInterface;
use Modules\HR\Application\Contracts\UpdateLeaveRequestServiceInterface;
use Modules\HR\Application\Contracts\UpdatePositionServiceInterface;
use Modules\HR\Application\Services\ApproveLeaveRequestService;
use Modules\HR\Application\Services\CreateAttendanceService;
use Modules\HR\Application\Services\CreateDepartmentService;
use Modules\HR\Application\Services\CreateEmployeeService;
use Modules\HR\Application\Services\CreateLeaveRequestService;
use Modules\HR\Application\Services\CreatePositionService;
use Modules\HR\Application\Services\DeleteAttendanceService;
use Modules\HR\Application\Services\DeleteDepartmentService;
use Modules\HR\Application\Services\DeleteEmployeeService;
use Modules\HR\Application\Services\DeleteLeaveRequestService;
use Modules\HR\Application\Services\DeletePositionService;
use Modules\HR\Application\Services\FindAttendanceService;
use Modules\HR\Application\Services\FindDepartmentService;
use Modules\HR\Application\Services\FindEmployeeService;
use Modules\HR\Application\Services\FindLeaveRequestService;
use Modules\HR\Application\Services\FindPositionService;
use Modules\HR\Application\Services\LinkEmployeeToUserService;
use Modules\HR\Application\Services\RejectLeaveRequestService;
use Modules\HR\Application\Services\UpdateAttendanceService;
use Modules\HR\Application\Services\UpdateDepartmentService;
use Modules\HR\Application\Services\UpdateEmployeeService;
use Modules\HR\Application\Services\UpdateLeaveRequestService;
use Modules\HR\Application\Services\UpdatePositionService;
use Modules\HR\Domain\RepositoryInterfaces\DepartmentRepositoryInterface;
use Modules\HR\Domain\RepositoryInterfaces\AttendanceRepositoryInterface;
use Modules\HR\Domain\RepositoryInterfaces\EmployeeRepositoryInterface;
use Modules\HR\Domain\RepositoryInterfaces\LeaveRequestRepositoryInterface;
use Modules\HR\Domain\RepositoryInterfaces\PositionRepositoryInterface;
use Modules\HR\Infrastructure\Biometric\BiometricAttendanceService;
use Modules\HR\Infrastructure\Biometric\BiometricDeviceRegistry;
use Modules\HR\Infrastructure\Biometric\BiometricEnrollmentService;
use Modules\HR\Infrastructure\Biometric\FingerprintDeviceAdapter;
use Modules\HR\Infrastructure\Persistence\Eloquent\Models\AttendanceModel;
use Modules\HR\Infrastructure\Persistence\Eloquent\Models\DepartmentModel;
use Modules\HR\Infrastructure\Persistence\Eloquent\Models\EmployeeModel;
use Modules\HR\Infrastructure\Persistence\Eloquent\Models\LeaveRequestModel;
use Modules\HR\Infrastructure\Persistence\Eloquent\Models\PositionModel;
use Modules\HR\Infrastructure\Persistence\Eloquent\Repositories\EloquentAttendanceRepository;
use Modules\HR\Infrastructure\Persistence\Eloquent\Repositories\EloquentDepartmentRepository;
use Modules\HR\Infrastructure\Persistence\Eloquent\Repositories\EloquentEmployeeRepository;
use Modules\HR\Infrastructure\Persistence\Eloquent\Repositories\EloquentLeaveRequestRepository;
use Modules\HR\Infrastructure\Persistence\Eloquent\Repositories\EloquentPositionRepository;

class HRServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Repositories
        $this->app->bind(EmployeeRepositoryInterface::class, function ($app) {
            return new EloquentEmployeeRepository($app->make(EmployeeModel::class));
        });
        $this->app->bind(DepartmentRepositoryInterface::class, function ($app) {
            return new EloquentDepartmentRepository($app->make(DepartmentModel::class));
        });
        $this->app->bind(PositionRepositoryInterface::class, function ($app) {
            return new EloquentPositionRepository($app->make(PositionModel::class));
        });
        $this->app->bind(LeaveRequestRepositoryInterface::class, function ($app) {
            return new EloquentLeaveRequestRepository($app->make(LeaveRequestModel::class));
        });
        $this->app->bind(AttendanceRepositoryInterface::class, function ($app) {
            return new EloquentAttendanceRepository($app->make(AttendanceModel::class));
        });

        // Employee Services
        $this->app->bind(FindEmployeeServiceInterface::class, function ($app) {
            return new FindEmployeeService($app->make(EmployeeRepositoryInterface::class));
        });
        $this->app->bind(CreateEmployeeServiceInterface::class, function ($app) {
            return new CreateEmployeeService($app->make(EmployeeRepositoryInterface::class));
        });
        $this->app->bind(UpdateEmployeeServiceInterface::class, function ($app) {
            return new UpdateEmployeeService($app->make(EmployeeRepositoryInterface::class));
        });
        $this->app->bind(DeleteEmployeeServiceInterface::class, function ($app) {
            return new DeleteEmployeeService($app->make(EmployeeRepositoryInterface::class));
        });
        $this->app->bind(LinkEmployeeToUserServiceInterface::class, function ($app) {
            return new LinkEmployeeToUserService($app->make(EmployeeRepositoryInterface::class));
        });

        // Department Services
        $this->app->bind(FindDepartmentServiceInterface::class, function ($app) {
            return new FindDepartmentService($app->make(DepartmentRepositoryInterface::class));
        });
        $this->app->bind(CreateDepartmentServiceInterface::class, function ($app) {
            return new CreateDepartmentService($app->make(DepartmentRepositoryInterface::class));
        });
        $this->app->bind(UpdateDepartmentServiceInterface::class, function ($app) {
            return new UpdateDepartmentService($app->make(DepartmentRepositoryInterface::class));
        });
        $this->app->bind(DeleteDepartmentServiceInterface::class, function ($app) {
            return new DeleteDepartmentService($app->make(DepartmentRepositoryInterface::class));
        });

        // Position Services
        $this->app->bind(FindPositionServiceInterface::class, function ($app) {
            return new FindPositionService($app->make(PositionRepositoryInterface::class));
        });
        $this->app->bind(CreatePositionServiceInterface::class, function ($app) {
            return new CreatePositionService($app->make(PositionRepositoryInterface::class));
        });
        $this->app->bind(UpdatePositionServiceInterface::class, function ($app) {
            return new UpdatePositionService($app->make(PositionRepositoryInterface::class));
        });
        $this->app->bind(DeletePositionServiceInterface::class, function ($app) {
            return new DeletePositionService($app->make(PositionRepositoryInterface::class));
        });

        // Leave Request Services
        $this->app->bind(FindLeaveRequestServiceInterface::class, function ($app) {
            return new FindLeaveRequestService($app->make(LeaveRequestRepositoryInterface::class));
        });
        $this->app->bind(CreateLeaveRequestServiceInterface::class, function ($app) {
            return new CreateLeaveRequestService($app->make(LeaveRequestRepositoryInterface::class));
        });
        $this->app->bind(UpdateLeaveRequestServiceInterface::class, function ($app) {
            return new UpdateLeaveRequestService($app->make(LeaveRequestRepositoryInterface::class));
        });
        $this->app->bind(DeleteLeaveRequestServiceInterface::class, function ($app) {
            return new DeleteLeaveRequestService($app->make(LeaveRequestRepositoryInterface::class));
        });
        $this->app->bind(ApproveLeaveRequestServiceInterface::class, function ($app) {
            return new ApproveLeaveRequestService($app->make(LeaveRequestRepositoryInterface::class));
        });
        $this->app->bind(RejectLeaveRequestServiceInterface::class, function ($app) {
            return new RejectLeaveRequestService($app->make(LeaveRequestRepositoryInterface::class));
        });

        // Attendance Services
        $this->app->bind(FindAttendanceServiceInterface::class, function ($app) {
            return new FindAttendanceService($app->make(AttendanceRepositoryInterface::class));
        });
        $this->app->bind(CreateAttendanceServiceInterface::class, function ($app) {
            return new CreateAttendanceService($app->make(AttendanceRepositoryInterface::class));
        });
        $this->app->bind(UpdateAttendanceServiceInterface::class, function ($app) {
            return new UpdateAttendanceService($app->make(AttendanceRepositoryInterface::class));
        });
        $this->app->bind(DeleteAttendanceServiceInterface::class, function ($app) {
            return new DeleteAttendanceService($app->make(AttendanceRepositoryInterface::class));
        });

        // Biometric Device Registry (singleton so all services share the same registry)
        $this->app->singleton(BiometricDeviceRegistryInterface::class, function () {
            return new BiometricDeviceRegistry;
        });

        // Biometric Attendance Service
        $this->app->bind(BiometricAttendanceServiceInterface::class, function ($app) {
            return new BiometricAttendanceService(
                $app->make(BiometricDeviceRegistryInterface::class),
                $app->make(AttendanceRepositoryInterface::class),
            );
        });

        // Biometric Enrollment Service
        $this->app->bind(BiometricEnrollmentServiceInterface::class, function ($app) {
            return new BiometricEnrollmentService(
                $app->make(BiometricDeviceRegistryInterface::class),
            );
        });
    }

    public function boot(): void
    {
        Route::middleware('api')
             ->prefix('api')
             ->group(function () {
                 $this->loadRoutesFrom(__DIR__ . '/../../routes/api.php');
             });

        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        // Register the default fingerprint device so it is available out of the box.
        // Production deployments should override this in their own service provider
        // or config file, pointing to real hardware device IDs.
        /** @var BiometricDeviceRegistry $registry */
        $registry = $this->app->make(BiometricDeviceRegistryInterface::class);
        $registry->register(new FingerprintDeviceAdapter('default-fingerprint'));
    }
}
