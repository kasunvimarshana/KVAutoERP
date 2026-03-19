# Copilot Instructions for KV Enterprise SaaS

## Project Overview

This repository contains the architecture and implementation blueprint for a **fully functional, production-ready, enterprise-grade microservices-driven ERP/CRM SaaS platform** with a complete **Inventory and Warehouse Management System**.

The backend is primarily built with **Laravel (LTS)**, while each microservice may independently use its own technology stack (languages, databases, operating systems, infrastructure). The frontend uses **React (LTS)** in a micro-frontend-ready architecture.

---

## Architecture & Engineering Principles

- **Architecture Pattern**: Microservices with strict service isolation
- **Design Principles**: Domain-Driven Design (DDD), Clean Architecture, API-first, SOLID, DRY, KISS
- **Pipeline Pattern**: Controller → Service → Repository (thin controllers)
- **Validation**: Laravel Request classes
- **API Responses**: Laravel Resource classes
- **Abstractions**: Interfaces/Contracts for all dependencies (dependency inversion)
- **Modularity**: Plugin-style architecture with strict module boundaries — no circular dependencies

### Core Microservices

`Auth`, `User`, `Product`, `Inventory`, `Warehouse`, `Order`, `Finance`, `CRM`, `Procurement`, `Workflow`, `Reporting`, `Configuration`

Each service:
- Communicates only via **REST APIs**, **gRPC**, or **async messaging** (Kafka or RabbitMQ)
- Has its **own database** — no direct cross-service database access
- Is independently deployable and horizontally/vertically scalable

---

## Multi-Tenancy

- Hierarchical tenant model: **Tenant → Organisation → Branch → Location → Department**
- All queries, caches, queues, configurations, and storage must be **tenant-scoped**
- Supports: multi-organization, multi-vendor, multi-branch, multi-currency, multi-language, multi-device, multi-unit-of-measure (UOM) with conversion matrices

---

## Authentication & Authorization

- **Auth Service**: Centralized, issues stateless JWT tokens (Laravel Passport, public/private key signing)
- **Token Verification**: Each microservice verifies tokens **locally** — no round-trip to Auth per request
- **Authorization**: RBAC + ABAC enforced via Laravel Policies, Gates, and Middleware
- **Token Claims**: `user_id`, `tenant_id`, `organization_id`, `branch_id`, `roles`, `permissions`, `device_id`, `token_version`, `iss`, `exp`
- **Sessions**: Multi-device session management, token refresh & rotation, distributed revocation (Redis)
- **Logout**: Support global logout and device-level logout via revocation lists
- **SSO**: Supported; service-to-service auth via JWT propagation in headers or event metadata

---

## Security Standards

- Password hashing: **Argon2** (preferred) or **bcrypt**
- Protect against: CSRF, XSS, SQL injection
- Rate limiting on all sensitive endpoints
- Token replay prevention
- Signed URLs for file access
- Immutable, append-only audit logs
- Suspicious activity detection

---

## Laravel Coding Conventions

- Use **thin controllers** — delegate all business logic to Services
- Use **Request classes** for all input validation
- Use **Resource classes** for all API responses
- Use **Interfaces/Contracts** for all Service and Repository dependencies
- Register bindings in **Service Providers**
- Use **Eloquent scopes** for tenant-scoped queries
- Use **BCMath** for all financial calculations (≥ 4 decimal places)
- Use **database transactions** (atomic) for all stock and financial operations
- Use **pessimistic locking** (`lockForUpdate()`) for stock deductions
- Use **optimistic locking** (version column) for concurrent updates
- Follow **Outbox Pattern** for reliable event publishing

### Directory Structure (per Laravel microservice)

```
app/
  Http/
    Controllers/       # Thin controllers only
    Requests/          # Form Request validation
    Resources/         # API response transformers
  Services/            # Business logic (implement interfaces)
  Repositories/        # Data access (implement interfaces)
  Contracts/           # Interfaces for Services and Repositories
  Models/              # Eloquent models
  Events/              # Domain events
  Listeners/           # Event handlers
  Policies/            # Authorization policies
  Jobs/                # Queued jobs
  Observers/           # Model observers (audit logs)
routes/
  api.php              # Versioned: /api/v1/...
```

---

## Inventory & Warehouse

- **Ledger-driven**: All stock changes are recorded as immutable transaction entries
- **Never** modify stock quantities directly — always use transactions
- Supports: FEFO / FIFO / LIFO valuation, serial/lot/batch tracking, expiry tracking
- Multi-warehouse and multi-bin storage
- Reorder rules, cycle counting, stock reservations, adjustments, transfers, returns
- Industry-agnostic — suitable for pharmacy, manufacturing, retail, eCommerce, hospitals, etc.

---

## API Standards

- **Versioned REST APIs**: `/api/v1/...`
- **Standardized response envelope**:
  ```json
  {
    "success": true,
    "data": { ... },
    "message": "...",
    "meta": { "page": 1, "per_page": 15, "total": 100 }
  }
  ```
- **OpenAPI 3.1** documentation for all endpoints
- **Idempotency keys** required on mutating requests where applicable
- Use HTTP status codes correctly (200, 201, 204, 400, 401, 403, 404, 422, 429, 500)

---

## Dynamic Configuration

- Platform is **metadata-driven**: forms, fields, workflows, rules, pricing engines, tax engines, approval chains, and UI layouts are configurable at runtime via the **Configuration Service** — no code changes or redeployment required
- Workflow state machines: State → Event → Transition → Guard → Action
- Rule engine: IF condition THEN action

---

## Distributed Transactions

- Use the **Saga Pattern** (orchestrator-based) for distributed workflows
  - Example: Order → Inventory → Payment → Confirmation
  - Include compensating transactions for rollback
- Use the **Outbox Pattern** to guarantee at-least-once event delivery

---

## Infrastructure & DevOps

- **Containerization**: Docker (one container per microservice)
- **Orchestration**: Kubernetes with Helm charts, HPA for auto-scaling
- **CI/CD**: Automated pipelines with blue-green deployments
- **Observability**: Prometheus metrics, OpenTelemetry tracing, centralized logging (ELK/Loki)
- **Laravel-specific**: Horizon (queue monitoring), Telescope (dev debugging)
- **Health checks**: Every service must expose `/health` or `/api/v1/health`

---

## Testing Standards

- **Unit tests**: Services, Repositories (mock dependencies)
- **Feature tests**: Full HTTP request/response cycle
- **Tenant isolation tests**: Ensure no data leaks across tenants
- **Authorization tests**: Validate RBAC/ABAC policies
- **Financial precision tests**: Validate BCMath calculations
- **Concurrency tests**: Validate pessimistic/optimistic locking

Run tests:
```bash
php artisan test
# or
./vendor/bin/phpunit
```

Run static analysis:
```bash
./vendor/bin/phpstan analyse --level=9
```

---

## Key Workflow Examples

### Sales Flow
`Quotation → Sales Order → Delivery → Invoice → Payment`

### CRM Pipeline
`Lead → Opportunity → Proposal → Closed Won / Closed Lost`

### Procurement Flow
`Purchase Request → RFQ → Vendor Selection → Purchase Order → Goods Receipt → Vendor Bill → Payment`

### Order Saga (Distributed Transaction)
`Order Created → Reserve Inventory → Process Payment → Confirm Order`
*(with compensating rollbacks at each step)*

---

## Performance Targets

- p95 response time for CRUD operations: **≤ 200 ms**
- Financial calculations: **≥ 4 decimal places** precision using BCMath
- All APIs must be paginated for list endpoints
