# Copilot Instructions for KV-SAAS-APP

## Project Overview

This is a **production-ready, enterprise-grade ERP/CRM SaaS platform** built on a fully microservices-driven architecture. The backend ecosystem is primarily built with **Laravel (LTS)** while each microservice can independently use its own technology stack.

---

## Core Architectural Principles

Every microservice must be:
- **Loosely coupled** — no direct database access between services
- **Independently deployable** — each service has its own lifecycle
- **Easily replaceable** — swap any service without breaking the system
- **Fully dynamic** — configurable at runtime without redeployment
- **Customizable and reusable** — plug-in style, interface-driven design

All inter-service communication uses **versioned REST APIs**, **gRPC**, or **asynchronous messaging** (Kafka / RabbitMQ) only.

---

## Engineering Standards

Apply the following principles in every microservice:

- **Domain-Driven Design (DDD)** with bounded contexts
- **Clean Architecture** — Controller → Service → Repository pipeline
- **SOLID, DRY, KISS** — thin controllers, meaningful naming, zero circular dependencies
- **API-first design** — OpenAPI 3.1, versioned endpoints (`/api/v1`)
- **Interface / Contract driven** — use `interfaces` and `contracts` for all abstractions to enforce dependency inversion
- **Request classes** for validation; **Resource / DTO classes** for API responses

---

## Microservices

Core services and their responsibilities:

| Service | Responsibility |
|---|---|
| **Auth** | Login, logout, JWT issuance (asymmetric keys), token refresh/rotation/revocation, SSO, service-to-service auth |
| **User** | User profiles, credentials metadata, roles, permissions, tenant hierarchy, IAM provider mappings |
| **Product** | SKU management, variants, pricing engines, UOM matrices, cost valuation (FIFO/LIFO/Weighted Average) |
| **Inventory** | Ledger-driven immutable stock transactions, serial/lot/batch tracking, reservations, adjustments |
| **Warehouse** | Multi-warehouse/bin storage, stock transfers, cycle counting, FEFO/FIFO enforcement |
| **Order** | Sales/POS flows: Quotation → Sales Order → Delivery → Invoice → Payment |
| **Finance** | Double-entry bookkeeping, chart of accounts, journal entries, tax engine, P&L, balance sheets |
| **CRM** | Pipeline management: Lead → Opportunity → Proposal → Closed Won/Lost |
| **Procurement** | Purchase Request → RFQ → Vendor Selection → PO → Goods Receipt → Vendor Bill → Payment |
| **Workflow** | Metadata-driven state machines: State → Event → Transition → Guard → Action |
| **Reporting** | Cross-service analytics, aggregated reporting, financial reconciliation |
| **Configuration** | Runtime tenant configuration, feature flags, IAM provider settings, policy management |

---

## Authentication & Authorization

### Auth Microservice
- Issues **stateless JWT access tokens** signed with **asymmetric RS256 keys**
- Tokens contain tenant-aware claims: `user_id`, `tenant_id`, `organization_id`, `branch_id`, `roles`, `permissions`, `device_id`, `token_version`, `provider`, `issuer`, `exp`
- All other microservices **verify tokens locally using the public key** — no calls to Auth per request
- **Distributed revocation** via Redis (global logout, device-level logout)
- Supports **multi-device session management**, token rotation, and replay prevention

### User Microservice
- Manages user profiles, credentials metadata, hierarchical roles and permissions
- Supports external **IAM provider mappings**: Okta, Keycloak, Active Directory, OAuth2/OpenID Connect, SAML
- Auth ↔ User interaction is **exclusively via versioned APIs or events** — no shared database

### IAM Integration (Interface-Driven)
All identity providers must implement a common `IdentityProviderInterface`:
```
interface IdentityProviderInterface {
    authenticate(credentials): AuthResult
    exchangeToken(code): TokenPair
    getUserInfo(token): UserInfo
    logout(token): void
    refreshToken(refreshToken): TokenPair
}
```
Use **Strategy + Factory + Adapter** patterns to resolve providers dynamically per tenant at runtime.

### Authorization
- **RBAC + ABAC** enforced via middleware, policies, and gates across all services
- **Hierarchical multi-tenant**: Tenant → Organisation → Branch → Location → Department
- Tenant-scoped queries, caches, queues, storage, and configurations

---

## Multi-Tenant SaaS Model

- **Tenant isolation**: separate data scoping for queries, caches, queues, and storage per tenant
- **Runtime configurability**: tenants can define IAM providers, roles, permissions, policies, token lifetimes, feature flags, and workflow rules without redeployment
- **Metadata-driven**: forms, fields, workflows, approval chains, pricing engines, tax engines, and UI layouts are all configurable at runtime

---

## Inventory & Warehouse System

- **Ledger-driven, immutable** — all stock movements occur through transactions, never direct edits
- Support **serial/lot/batch tracking**, expiry tracking, FEFO/FIFO/LIFO valuation
- **Pessimistic locking** for stock deductions; **optimistic locking** for updates
- **Idempotent APIs**, atomic transactions, versioning, and complete audit trails
- Industry-agnostic: suitable for pharmacy, manufacturing, ecommerce, retail, wholesale, etc.

---

## Distributed Transactions

- Use the **Saga Pattern** for distributed workflows (e.g., Order → Inventory → Payment → Confirmation) with compensating rollback actions
- Use the **Outbox Pattern** for guaranteed event delivery and eventual consistency

---

## Financial Precision

- All monetary and financial values use **arbitrary-precision decimal calculations** (BCMath with ≥4 decimal places)

---

## Security Requirements

Enforce in every service:

- **Password hashing**: Argon2 (preferred) or bcrypt
- **CSRF/XSS/SQL injection protection** on all endpoints
- **Rate limiting** per tenant and per endpoint
- **Token replay prevention** using `jti` claim + Redis blacklist
- **Signed URLs** for secure attachment access
- **Immutable audit logs** — append-only, tamper-evident
- **Suspicious activity detection** with alerting
- **Secure identity propagation** between services via JWT headers or event metadata
- **HTTPS only**; secrets managed via environment variables or a secrets manager

---

## Infrastructure & DevOps

- **Docker** containers; **Kubernetes** with Helm charts for orchestration
- **CI/CD pipelines** with blue-green deployments and auto-scaling
- **Health-check endpoints** on every service
- **Centralized logging** and **observability** (Prometheus + OpenTelemetry)
- **Laravel Horizon** for queue monitoring; **Laravel Telescope** for debug/inspection in dev
- **Static analysis**: PHPStan at level 9 for PHP services
- **API versioning**: `/api/v1` prefix with standardized response envelopes and pagination

---

## Code Quality & Testing

- Automated tests: **unit**, **feature**, **tenant isolation**, **authorization**, **concurrency**, and **financial precision**
- Performance target: **≤200 ms p95** for standard CRUD operations
- Mutation testing for critical business logic
- OpenAPI 3.1 documentation required for every service

---

## Response Envelope Standard

All API responses must follow this envelope:

```json
{
  "success": true,
  "data": { },
  "meta": { "pagination": { } },
  "errors": null,
  "message": "Operation successful"
}
```

---

## Key Patterns to Follow

1. **Never access another service's database directly** — use APIs or events
2. **Never hardcode tenant, role, or permission values** — always resolve dynamically
3. **All new IAM providers** must implement `IdentityProviderInterface` — no core code changes required
4. **All stock movements** must go through ledger transactions — never update stock fields directly
5. **Financial calculations** must always use BCMath (or equivalent arbitrary-precision library)
6. **Secrets** (keys, credentials) must never be committed to source control
