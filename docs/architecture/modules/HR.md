# Module Contract: HR

## 1. Bounded Context
- Purpose: HR operations including attendance, leave, payroll, performance, and documents.
- Core business capabilities: HR domain workflow and supporting services.
- In-scope: Module-owned domain/application/infrastructure behavior.
- Out-of-scope: Cross-module behavior not exposed via contracts/events/routes.

## 2. Domain Model
- Primary entities: AttendanceLog, AttendanceRecord, BiometricDevice, EmployeeDocument, LeaveBalance, LeavePolicy, LeaveRequest, LeaveType, PayrollItem, PayrollRun, Payslip, PayslipLine, PerformanceCycle, PerformanceReview, Shift, ShiftAssignment
- Value objects: AttendanceStatus, BiometricDeviceStatus, LeaveRequestStatus, PayrollRunStatus, PerformanceRating, ShiftType
- Aggregates and roots: Derived from entity/repository boundaries in module namespace.
- Invariants and constraints: Enforced via DTO/request validation, service workflows, and DB constraints.

## 3. Data Ownership
- Owned tables: hr_attendance_logs, hr_attendance_records, hr_biometric_devices, hr_employee_documents, hr_leave_balances, hr_leave_policies, hr_leave_requests, hr_leave_types, hr_payroll_items, hr_payroll_runs, hr_payslip_lines, hr_payslips, hr_performance_cycles, hr_performance_reviews, hr_shift_assignments, hr_shifts
- Referenced external tables: Derived from migration FKs to cross-module tables.
- Tenant scoping strategy: tenant_id-based row isolation on tenant-owned tables.
- Soft-delete and archival policy: Table-specific; many transactional tables include softDeletes().

## 4. Application Layer
- Commands/use-cases: Service-driven mutation flows.
- Queries/read-models: Repository/Eloquent read flows and API resources.
- Transaction boundaries: Write paths expected to be wrapped by service-layer transaction handling.
- Idempotency strategy: Document/status-based workflow progression and unique business keys where defined.

## 5. Integration Model
- Published events: AttendanceLogCreated, AttendanceRecordProcessed, LeaveRequestApproved, LeaveRequestRejected, LeaveRequestSubmitted, PayrollRunApproved, PayslipGenerated
- Consumed events: Listener-driven integration where module listeners are registered.
- External module dependencies (contracts only): Cross-module references should remain contract/event based.
- Failure and retry behavior: Queue/listener behavior and transactional safeguards per use-case.

## 6. API Surface
- Route prefix: hr
- Resource endpoints: biometric-devices, employee-documents, leave-policies, leave-requests, leave-types, payroll-items, payroll-runs, performance-cycles, performance-reviews, shifts
- Action endpoints: attendance-logs, attendance-logs/{attendanceLog}, attendance-records, attendance-records/{attendanceRecord}, attendance-records/process, biometric-devices/{biometric_device}/sync, leave-balances, leave-balances/{leaveBalance}, leave-requests/{leave_request}/approve, leave-requests/{leave_request}/cancel, leave-requests/{leave_request}/reject, payroll-runs/{payroll_run}/approve, payroll-runs/{payroll_run}/process, payslips, payslips/{payslip}, performance-reviews/{performance_review}/submit, shifts/{shift}/assign
- Auth and middleware requirements: auth:api, resolve.tenant

## 7. Operational Profile
- High-volume query paths: Status/date/party scoped document retrieval and tenant-scoped listings.
- Required indexes: Composite tenant + business key/status/date indexes in module migrations.
- Expected concurrency hotspots: Approval/posting/stock-allocation style state transitions.
- Observability signals (logs/metrics/audit): Audit logs, domain events, and endpoint/test traces.

## 8. Security and Compliance
- Sensitive data classes: PII/financial/operational data based on module context.
- Access-control model: API middleware plus role/permission controls where applicable.
- Audit obligations: Changes should be traceable through audit/logging/event records.
- Data retention requirements: Domain and regulatory policy dependent.

## 9. Test Coverage Expectations
- Architecture guardrails: Boundary/provider/route/migration guardrail tests.
- Feature tests: Endpoint and integration flows for module routes/services.
- Integration tests: Repository and cross-module posting/allocation consistency checks.
- Regression scenarios: Status transitions, FK integrity, tenant isolation, and rounding/precision behavior.
- Current module-aligned tests: HREndpointsAuthenticatedTest.php, HRModuleGuardrailsTest.php, HRRoutesTest.php

## 10. Open Risks and Refactor Backlog
- Current risks: Status normalization remains a top risk across leave, payroll, and performance workflows.
- Technical debt: HR-to-Finance posting dependencies are operationally coupled with limited contract-level guarantees.
- Planned refactors: Standardize status strategy and add contract tests for payroll-to-journal posting flows.
## 11. Concrete Source Map
- Module root: [app/Modules/HR](app/Modules/HR)
- Route source: [app/Modules/HR/routes/api.php](app/Modules/HR/routes/api.php)
- Provider files:
  - [app/Modules/HR/Infrastructure/Providers/HRServiceProvider.php](app/Modules/HR/Infrastructure/Providers/HRServiceProvider.php)
- Domain entities (representative):
  - [app/Modules/HR/Domain/Entities/AttendanceLog.php](app/Modules/HR/Domain/Entities/AttendanceLog.php)
  - [app/Modules/HR/Domain/Entities/AttendanceRecord.php](app/Modules/HR/Domain/Entities/AttendanceRecord.php)
  - [app/Modules/HR/Domain/Entities/BiometricDevice.php](app/Modules/HR/Domain/Entities/BiometricDevice.php)
  - [app/Modules/HR/Domain/Entities/EmployeeDocument.php](app/Modules/HR/Domain/Entities/EmployeeDocument.php)
  - [app/Modules/HR/Domain/Entities/LeaveBalance.php](app/Modules/HR/Domain/Entities/LeaveBalance.php)
- Application services (representative):
  - [app/Modules/HR/Application/Services/AllocateLeaveBalanceService.php](app/Modules/HR/Application/Services/AllocateLeaveBalanceService.php)
  - [app/Modules/HR/Application/Services/ApproveLeaveRequestService.php](app/Modules/HR/Application/Services/ApproveLeaveRequestService.php)
  - [app/Modules/HR/Application/Services/ApprovePayrollRunService.php](app/Modules/HR/Application/Services/ApprovePayrollRunService.php)
  - [app/Modules/HR/Application/Services/AssignShiftService.php](app/Modules/HR/Application/Services/AssignShiftService.php)
  - [app/Modules/HR/Application/Services/CancelLeaveRequestService.php](app/Modules/HR/Application/Services/CancelLeaveRequestService.php)
- Repository implementations (representative):
  - [app/Modules/HR/Infrastructure/Persistence/Eloquent/Repositories/EloquentAttendanceLogRepository.php](app/Modules/HR/Infrastructure/Persistence/Eloquent/Repositories/EloquentAttendanceLogRepository.php)
  - [app/Modules/HR/Infrastructure/Persistence/Eloquent/Repositories/EloquentAttendanceRecordRepository.php](app/Modules/HR/Infrastructure/Persistence/Eloquent/Repositories/EloquentAttendanceRecordRepository.php)
  - [app/Modules/HR/Infrastructure/Persistence/Eloquent/Repositories/EloquentBiometricDeviceRepository.php](app/Modules/HR/Infrastructure/Persistence/Eloquent/Repositories/EloquentBiometricDeviceRepository.php)
  - [app/Modules/HR/Infrastructure/Persistence/Eloquent/Repositories/EloquentEmployeeDocumentRepository.php](app/Modules/HR/Infrastructure/Persistence/Eloquent/Repositories/EloquentEmployeeDocumentRepository.php)
  - [app/Modules/HR/Infrastructure/Persistence/Eloquent/Repositories/EloquentLeaveBalanceRepository.php](app/Modules/HR/Infrastructure/Persistence/Eloquent/Repositories/EloquentLeaveBalanceRepository.php)
- Migration files (representative):
  - [app/Modules/HR/database/migrations/2024_01_01_900001_create_hr_shifts_table.php](app/Modules/HR/database/migrations/2024_01_01_900001_create_hr_shifts_table.php)
  - [app/Modules/HR/database/migrations/2024_01_01_900002_create_hr_shift_assignments_table.php](app/Modules/HR/database/migrations/2024_01_01_900002_create_hr_shift_assignments_table.php)
  - [app/Modules/HR/database/migrations/2024_01_01_900003_create_hr_leave_types_table.php](app/Modules/HR/database/migrations/2024_01_01_900003_create_hr_leave_types_table.php)
  - [app/Modules/HR/database/migrations/2024_01_01_900004_create_hr_leave_policies_table.php](app/Modules/HR/database/migrations/2024_01_01_900004_create_hr_leave_policies_table.php)
  - [app/Modules/HR/database/migrations/2024_01_01_900005_create_hr_leave_balances_table.php](app/Modules/HR/database/migrations/2024_01_01_900005_create_hr_leave_balances_table.php)
- Test references:
  - [tests/Feature/HREndpointsAuthenticatedTest.php](tests/Feature/HREndpointsAuthenticatedTest.php)
  - [tests/Unit/Architecture/HRModuleGuardrailsTest.php](tests/Unit/Architecture/HRModuleGuardrailsTest.php)
  - [tests/Feature/HRRoutesTest.php](tests/Feature/HRRoutesTest.php)

## 12. Real-Time Sequence References
- Entry path: HTTP routes dispatch to thin controllers in module infrastructure.
- Orchestration path: Controllers delegate mutations/queries to application services and contracts.
- Persistence path: Services persist through module repositories implementing domain interfaces.
- Event publication sources:
  - [app/Modules/HR/Domain/Events/AttendanceLogCreated.php](app/Modules/HR/Domain/Events/AttendanceLogCreated.php)
  - [app/Modules/HR/Domain/Events/AttendanceRecordProcessed.php](app/Modules/HR/Domain/Events/AttendanceRecordProcessed.php)
  - [app/Modules/HR/Domain/Events/LeaveRequestApproved.php](app/Modules/HR/Domain/Events/LeaveRequestApproved.php)
  - [app/Modules/HR/Domain/Events/LeaveRequestRejected.php](app/Modules/HR/Domain/Events/LeaveRequestRejected.php)



