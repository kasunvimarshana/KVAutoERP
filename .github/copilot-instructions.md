# Copilot Coding Agent Instructions

## Project Overview

This repository is an **enterprise-grade, production-ready ERP/CRM SaaS platform** composed entirely of independent microservices. Every domain capability runs as a loosely coupled microservice that is fully dynamic, customizable, reusable, extendable, vertically and horizontally scalable, and configurable at runtime without redeployment.

---

## Architecture Principles

### Microservices Design
- **Strict service isolation**: no direct database access between services.
- All inter-service communication must use **versioned REST APIs** (`/api/v1`), **gRPC**, or **asynchronous messaging** via Kafka or RabbitMQ.
- Each service independently owns its technology stack, database, infrastructure, and deployment lifecycle.
- Core microservices: **Auth, User, Product, Inventory, Warehouse, Order, Finance, CRM, Procurement, Workflow, Reporting, Configuration**.

### Engineering Principles
- **Domain-Driven Design (DDD)**, Clean Architecture, API-first, SOLID, DRY, KISS.
- **Controller → Service → Repository pipeline** with thin controllers.
- Use **Request classes** for input validation, **Resource/DTO classes** for API responses.
- All abstractions must be backed by **interfaces/contracts** to enforce dependency inversion.
- Enforce **meaningful naming conventions**, strict modular boundaries, and **zero circular dependencies**.
- Use a **modular plugin-style architecture**; modules must be independently deployable and runtime-enable/disable-able per tenant.

---

## Multi-Tenant SaaS Model

- Hierarchical tenant isolation: **Tenant → Organisation → Branch → Location → Department**.
- Support multi-organization, multi-vendor, multi-branch, multi-currency, multi-language, multi-device, and multi-unit-of-measure (UOM) with conversion matrices.
- Strict **tenant-scoped queries, caches, queues, storage, configurations**, and tenant-specific feature flags.
- Runtime configuration for databases, caches, queues, message brokers, feature flags, API keys, email services, storage, workflow definitions, pricing rules, tax rules, and environment parameters — **without redeployment or restart**.

---

## Authentication & Authorization System

### Centralized Auth Microservice
- Issues **stateless JWT access tokens** signed with **asymmetric public/private keys**; support cryptographic agility across algorithms (RS256, RS384, RS512) with RS256 as the default, so the algorithm can be upgraded without architectural changes.
- All other microservices perform **local token verification using the public key** — no Auth service call per request.
- Token claims must include: `user_id`, `tenant_id`, `organization_id`, `branch_id`, `roles`, `permissions`, `device_id`, `token_version`, `issuer`, `expiration`.

### Authorization Model
- **RBAC + ABAC**: roles, permissions, attributes, and contextual policies enforced through middleware, policies, and gates across all microservices.
- Support **multi-guard authentication** per user/device/organization.

### Session & Token Management
- Short-lived access tokens + revocable refresh tokens stored securely.
- **Distributed revocation lists** via Redis for global logout and device-level logout.
- Token refresh, rotation, and revocation flows must be fully implemented.
- Multi-device session management, SSO, API authentication, and service-to-service authentication.

### Security Requirements
- Password hashing: **Argon2 or bcrypt**.
- Protect against CSRF, XSS, SQL injection, rate limiting, token replay, and suspicious activity.
- Use **signed URLs** where appropriate.
- Maintain **immutable append-only audit logs** for all authentication events.
- Propagate identity securely between services via JWT headers or event metadata.

---

## Product Domain

- Support product types: **physical, consumable, service, digital, bundle, composite, variant-based**.
- SKU management, barcode/QR support, optional GS1 compatibility, multiple images/assets.
- Cost and valuation methods: **FIFO, LIFO, Weighted Average**.
- Serial/lot/batch traceability, multi-location and multi-currency pricing.
- Rule-based pricing engines, tier pricing, base UOM / buying UOM / selling UOM with unit conversion matrices.
- All financial values must use **arbitrary-precision decimal calculations** (BCMath or equivalent):
  - Monetary amounts: minimum **4 decimal places** of storage and calculation precision.
  - Exchange rates and unit conversion factors: minimum **6 decimal places**.
  - Rounding must use **ROUND_HALF_UP** (banker's rounding is not acceptable for financial ledgers).
  - Display precision may differ from storage precision; truncation must never occur silently.

---

## Inventory & Warehouse Management

- **Ledger-driven, immutable, transactional**: all stock movements occur only through transactions, never direct edits.
- Multi-warehouse and multi-bin storage, serial/lot/batch tracking, expiry tracking.
- Valuation strategies: **FEFO, FIFO, LIFO**.
- Features: reservations, adjustments, transfers, returns, cycle counting, reorder rules, procurement suggestions, backorders, drop-shipping, damage handling, stock history reconstruction.
- Enforce **pessimistic locking** for stock deductions, **optimistic locking** for updates.
- **Idempotent APIs**, atomic database transactions, versioning, and complete audit trails.
- Industry-agnostic: suitable for pharmacy, manufacturing, ecommerce, retail, wholesale, logistics, hospitals, service centers, supermarkets.
- Optional **pharmaceutical compliance mode**: mandatory lot tracking, expiry management, FEFO enforcement, quarantine workflows, regulatory audit logs.

---

## ERP Domain Flows

- **Sales/POS**: Quotation → Sales Order → Delivery → Invoice → Payment.
- **CRM Pipeline**: Lead → Opportunity → Proposal → Closed Won/Lost.
- **Procurement**: Purchase Request → RFQ → Vendor Selection → Purchase Order → Goods Receipt → Vendor Bill → Payment.
- **Accounting**: Double-entry bookkeeping, chart of accounts, journal entries, trial balance, P&L, balance sheets, tax engines.
- Financial reconciliation with inventory valuation must be maintained.

---

## Distributed Transactions & Messaging

- Implement the **Saga Pattern** with a reusable orchestrator for distributed workflows (e.g., Order → Inventory → Payment → Confirmation) with compensating rollback actions.
- Ensure eventual consistency using **event-driven communication** and the **Outbox Pattern**.
- Cross-service filtering (e.g., orders by product attributes) must use APIs or event-driven sync — never direct DB queries.

---

## Runtime & Metadata-Driven Configuration

- Forms, fields, workflows, approval chains, validation rules, conditional logic, computed fields, pricing engines, tax engines, commission engines, discount engines, notification templates, UI layouts, rule engines (IF condition THEN action), and workflow state machines (State → Event → Transition → Guard → Action) must all be **metadata-driven and editable at runtime without code changes**.
- Administrators must be able to dynamically manage tenants, roles, permissions, feature flags, policies, token lifetimes, and service integrations without redeployment.

---

## API Standards

- All APIs follow **versioned REST** standards: `/api/v1/...`.
- Standardized **response envelopes** with consistent structure (data, meta, errors).
- Support pagination, filtering, and sorting on all list endpoints.
- **OpenAPI 3.1** documentation for all services.
- Include webhook integrations, event publishing, and third-party connectors.
- Health-check endpoints on every service.

---

## Infrastructure & DevOps

- **Docker** containerization, **Kubernetes** orchestration with Helm charts.
- Auto-scaling, service discovery, CI/CD pipelines, blue-green deployments.
- Centralized logging, observability metrics (**Prometheus**, OpenTelemetry).
- **Laravel Horizon / Telescope** monitoring for Laravel-based services.
- Static analysis: **PHPStan level 9** for PHP services.
- Micro-frontend-ready **React (LTS)** UI architecture.

---

## Testing & Quality

- Automated tests: unit, feature, tenant isolation, authorization, concurrency, financial precision.
- Mutation testing.
- Performance targets (p95, measured at the service boundary, excluding network transit):
  - Simple reads (single record, no joins): **≤100 ms**.
  - Complex reads (filtered lists, aggregations, cross-service lookups): **≤200 ms**.
  - Write operations (create/update with validation and event publishing): **≤300 ms**.
- No hardcoded values; all configuration must be externalised.

---

## Security & Compliance

- CSRF protection, XSS prevention, SQL injection protection, rate limiting.
- Token rotation and replay prevention.
- Immutable, append-only audit logs with long-term regulatory retention.
- Data integrity: transactions, foreign keys, unique constraints, idempotency keys, optimistic/pessimistic locking, version tracking.

---

## Code Style & Conventions

- Follow the **Controller → Service → Repository** pattern consistently.
- Name classes and methods clearly and descriptively; avoid abbreviations.
- All service interfaces must live in a dedicated `Contracts` or `Interfaces` namespace.
- Event names: past tense (e.g., `OrderPlaced`, `StockReserved`).
- Use dependency injection throughout; no `new ClassName()` outside of factories/providers.
- Write PHPDoc blocks for all public methods on service and repository classes.
