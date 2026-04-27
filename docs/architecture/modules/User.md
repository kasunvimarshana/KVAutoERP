# Module Contract: User

## 1. Bounded Context
- Purpose: Authentication-adjacent user identity, roles, permissions, and user assets.
- Core business capabilities: User domain workflow and supporting services.
- In-scope: Module-owned domain/application/infrastructure behavior.
- Out-of-scope: Cross-module behavior not exposed via contracts/events/routes.

## 2. Domain Model
- Primary entities: Permission, Role, User, UserAttachment, UserDevice
- Value objects: Address, Email, PhoneNumber, UserPreferences, ValidatedEmail
- Aggregates and roots: Derived from entity/repository boundaries in module namespace.
- Invariants and constraints: Enforced via DTO/request validation, service workflows, and DB constraints.

## 3. Data Ownership
- Owned tables: permission_role, permission_user, permissions, role_user, roles, user_attachments, user_devices, users
- Referenced external tables: Derived from migration FKs to cross-module tables.
- Tenant scoping strategy: tenant_id-based row isolation on tenant-owned tables.
- Soft-delete and archival policy: Table-specific; many transactional tables include softDeletes().

## 4. Application Layer
- Commands/use-cases: Service-driven mutation flows.
- Queries/read-models: Repository/Eloquent read flows and API resources.
- Transaction boundaries: Write paths expected to be wrapped by service-layer transaction handling.
- Idempotency strategy: Document/status-based workflow progression and unique business keys where defined.

## 5. Integration Model
- Published events: RoleAssigned, UserAvatarUpdated, UserCreated, UserPasswordChanged, UserProfileUpdated, UserUpdated
- Consumed events: Listener-driven integration where module listeners are registered.
- External module dependencies (contracts only): Cross-module references should remain contract/event based.
- Failure and retry behavior: Queue/listener behavior and transactional safeguards per use-case.

## 6. API Surface
- Route prefix: n/a
- Resource endpoints: users
- Action endpoints: permissions, permissions/{permission}, profile, profile/avatar, profile/change-password, profile/devices, profile/devices/{device}, profile/preferences, roles, roles/{role}, roles/{role}/permissions, storage/user-attachments/{uuid}, users/{user}/assign-role, users/{user}/attachments, users/{user}/attachments/{attachment}, users/{user}/devices, users/{user}/devices/{device}, users/{user}/preferences
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
- Current module-aligned tests: OrganizationUnitUserRoutesTest.php, WarehouseRoutesTest.php

## 10. Open Risks and Refactor Backlog
- Current risks: Role/permission assignment drift can occur without stronger least-privilege and revocation verification paths.
- Technical debt: User profile/device/security workflows are broad, with some policy rules encoded only at service level.
- Planned refactors: Expand authorization regression tests and formalize role-permission invariants with contract-level checks.
## 11. Concrete Source Map
- Module root: [app/Modules/User](app/Modules/User)
- Route source: [app/Modules/User/routes/api.php](app/Modules/User/routes/api.php)
- Provider files:
  - [app/Modules/User/Infrastructure/Providers/UserServiceProvider.php](app/Modules/User/Infrastructure/Providers/UserServiceProvider.php)
- Domain entities (representative):
  - [app/Modules/User/Domain/Entities/Permission.php](app/Modules/User/Domain/Entities/Permission.php)
  - [app/Modules/User/Domain/Entities/Role.php](app/Modules/User/Domain/Entities/Role.php)
  - [app/Modules/User/Domain/Entities/User.php](app/Modules/User/Domain/Entities/User.php)
  - [app/Modules/User/Domain/Entities/UserAttachment.php](app/Modules/User/Domain/Entities/UserAttachment.php)
  - [app/Modules/User/Domain/Entities/UserDevice.php](app/Modules/User/Domain/Entities/UserDevice.php)
- Application services (representative):
  - [app/Modules/User/Application/Services/AssignRoleService.php](app/Modules/User/Application/Services/AssignRoleService.php)
  - [app/Modules/User/Application/Services/ChangePasswordService.php](app/Modules/User/Application/Services/ChangePasswordService.php)
  - [app/Modules/User/Application/Services/CreatePermissionService.php](app/Modules/User/Application/Services/CreatePermissionService.php)
  - [app/Modules/User/Application/Services/CreateRoleService.php](app/Modules/User/Application/Services/CreateRoleService.php)
  - [app/Modules/User/Application/Services/CreateUserService.php](app/Modules/User/Application/Services/CreateUserService.php)
- Repository implementations (representative):
  - [app/Modules/User/Infrastructure/Persistence/Eloquent/Repositories/EloquentPermissionRepository.php](app/Modules/User/Infrastructure/Persistence/Eloquent/Repositories/EloquentPermissionRepository.php)
  - [app/Modules/User/Infrastructure/Persistence/Eloquent/Repositories/EloquentRoleRepository.php](app/Modules/User/Infrastructure/Persistence/Eloquent/Repositories/EloquentRoleRepository.php)
  - [app/Modules/User/Infrastructure/Persistence/Eloquent/Repositories/EloquentUserAttachmentRepository.php](app/Modules/User/Infrastructure/Persistence/Eloquent/Repositories/EloquentUserAttachmentRepository.php)
  - [app/Modules/User/Infrastructure/Persistence/Eloquent/Repositories/EloquentUserDeviceRepository.php](app/Modules/User/Infrastructure/Persistence/Eloquent/Repositories/EloquentUserDeviceRepository.php)
  - [app/Modules/User/Infrastructure/Persistence/Eloquent/Repositories/EloquentUserRepository.php](app/Modules/User/Infrastructure/Persistence/Eloquent/Repositories/EloquentUserRepository.php)
- Migration files (representative):
  - [app/Modules/User/database/migrations/2024_01_01_300001_create_users_table.php](app/Modules/User/database/migrations/2024_01_01_300001_create_users_table.php)
  - [app/Modules/User/database/migrations/2024_01_01_300002a_create_roles_table.php](app/Modules/User/database/migrations/2024_01_01_300002a_create_roles_table.php)
  - [app/Modules/User/database/migrations/2024_01_01_300002b_create_permissions_table.php](app/Modules/User/database/migrations/2024_01_01_300002b_create_permissions_table.php)
  - [app/Modules/User/database/migrations/2024_01_01_300002c_create_role_user_table.php](app/Modules/User/database/migrations/2024_01_01_300002c_create_role_user_table.php)
  - [app/Modules/User/database/migrations/2024_01_01_300002d_create_permission_role_table.php](app/Modules/User/database/migrations/2024_01_01_300002d_create_permission_role_table.php)
- Test references:
  - [tests/Feature/OrganizationUnitUserRoutesTest.php](tests/Feature/OrganizationUnitUserRoutesTest.php)
  - [tests/Feature/WarehouseRoutesTest.php](tests/Feature/WarehouseRoutesTest.php)

## 12. Real-Time Sequence References
- Entry path: HTTP routes dispatch to thin controllers in module infrastructure.
- Orchestration path: Controllers delegate mutations/queries to application services and contracts.
- Persistence path: Services persist through module repositories implementing domain interfaces.
- Event publication sources:
  - [app/Modules/User/Domain/Events/RoleAssigned.php](app/Modules/User/Domain/Events/RoleAssigned.php)
  - [app/Modules/User/Domain/Events/UserAvatarUpdated.php](app/Modules/User/Domain/Events/UserAvatarUpdated.php)
  - [app/Modules/User/Domain/Events/UserCreated.php](app/Modules/User/Domain/Events/UserCreated.php)
  - [app/Modules/User/Domain/Events/UserPasswordChanged.php](app/Modules/User/Domain/Events/UserPasswordChanged.php)



