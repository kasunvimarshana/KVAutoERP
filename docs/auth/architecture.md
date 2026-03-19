# Distributed Authentication & Authorization System

## Architecture Overview

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                        KV Enterprise SaaS Platform                               │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                   │
│   ┌─────────────┐     ┌──────────────────────────────────────────────────────┐   │
│   │   Clients   │     │                  API Gateway                          │   │
│   │  (Web/Mobile│────▶│  Rate Limiting │ TLS Termination │ Request Routing   │   │
│   │  /IoT/SaaS) │     └──────────────────────────────────────────────────────┘   │
│   └─────────────┘                         │                                       │
│                                           ▼                                       │
│         ┌─────────────────────────────────────────────────────────────┐          │
│         │                     Auth Service (Laravel)                   │          │
│         │                                                               │          │
│         │   /api/v1/auth/login     → AuthController.login()           │          │
│         │   /api/v1/auth/logout    → AuthController.logout()          │          │
│         │   /api/v1/auth/refresh   → AuthController.refresh()         │          │
│         │   /api/v1/auth/register  → AuthController.register()        │          │
│         │   /api/v1/auth/sessions  → SessionController               │          │
│         │   /api/v1/auth/roles     → RolePermissionController        │          │
│         │                                                               │          │
│         │   ┌─────────────────────────────────────────────────────┐   │          │
│         │   │  AuthService  →  TokenService  →  RS256-signed JWT  │   │          │
│         │   │  SessionService  →  DeviceSession  →  Redis/MySQL   │   │          │
│         │   │  PermissionService  →  RBAC + ABAC  →  Cache        │   │          │
│         │   │  AuditService  →  Immutable AuditLog  →  DB         │   │          │
│         │   │  TenantConfigService  →  Runtime Config  →  Redis   │   │          │
│         │   └─────────────────────────────────────────────────────┘   │          │
│         │                                                               │          │
│         │   ┌─────────────┐   ┌───────────────┐   ┌───────────────┐  │          │
│         │   │  MySQL DB   │   │ Redis (Cache  │   │  RabbitMQ/    │  │          │
│         │   │  (auth db)  │   │  + Revocation │   │  Kafka        │  │          │
│         │   └─────────────┘   │  list)        │   │  (Outbox)     │  │          │
│         │                     └───────────────┘   └───────────────┘  │          │
│         └─────────────────────────────────────────────────────────────┘          │
│                          │                                                         │
│            Public Key Distribution (at deploy time)                               │
│                          │                                                         │
│    ┌─────────────────────┼──────────────────────────────────────────────┐        │
│    │                     ▼                                               │        │
│    │  ┌────────────────────────────────────────────────────────────┐    │        │
│    │  │           Other Microservices (Laravel / any stack)         │    │        │
│    │  │                                                              │    │        │
│    │  │  Product  │  Inventory  │  Warehouse  │  Order  │  Finance  │    │        │
│    │  │                                                              │    │        │
│    │  │  Each service uses packages/shared-auth:                    │    │        │
│    │  │  ┌────────────────────────────────────────────────────┐    │    │        │
│    │  │  │  VerifyMicroserviceToken middleware                  │    │    │        │
│    │  │  │    1. Extract Bearer token from request             │    │    │        │
│    │  │  │    2. Decode JWT using Auth service's public key    │    │    │        │
│    │  │  │       (LOCAL — no Auth service roundtrip)           │    │    │        │
│    │  │  │    3. Check Redis revocation list (O(1))            │    │    │        │
│    │  │  │    4. Populate TenantContext                        │    │    │        │
│    │  │  │    5. Continue request if valid                     │    │    │        │
│    │  │  └────────────────────────────────────────────────────┘    │    │        │
│    │  └────────────────────────────────────────────────────────────┘    │        │
│    └─────────────────────────────────────────────────────────────────────┘        │
└─────────────────────────────────────────────────────────────────────────────────┘
```

## JWT Token Structure

```json
{
  "iss": "https://auth.kv-enterprise.com",
  "sub": "usr_01HX9K2M3N4P5Q6R7S8T9U0V1",
  "user_id": "usr_01HX9K2M3N4P5Q6R7S8T9U0V1",
  "tenant_id": "tnt_ACME_CORP_01",
  "organization_id": "org_HQ_01",
  "branch_id": "brnch_MAIN_01",
  "location_id": "loc_WH1_01",
  "department_id": null,
  "roles": ["admin", "inventory-manager"],
  "permissions": ["inventory.*", "products.view", "orders.approve"],
  "device_id": "browser-chrome-laptop-001",
  "token_version": 3,
  "jti": "550e8400-e29b-41d4-a716-446655440123",
  "iat": 1710000000,
  "nbf": 1710000000,
  "exp": 1710000900
}
```

## Authentication Flow

```
Client                      Auth Service                  Other Microservice
  │                               │                               │
  │  POST /api/v1/auth/login      │                               │
  │  {email, password, tenant_id, │                               │
  │   device_id}                  │                               │
  │──────────────────────────────▶│                               │
  │                               │ 1. Validate credentials       │
  │                               │ 2. Check account status       │
  │                               │ 3. Load RBAC roles/perms      │
  │                               │ 4. Sign JWT (RS256)           │
  │                               │ 5. Create device session      │
  │                               │ 6. Publish audit event        │
  │◀──────────────────────────────│                               │
  │  {access_token, refresh_token}│                               │
  │                               │                               │
  │                               │                               │
  │  GET /api/v1/inventory/stock  │                               │
  │  Authorization: Bearer {jwt}  │                               │
  │───────────────────────────────────────────────────────────────▶
  │                               │  LOCAL verification:          │
  │                               │  1. Decode JWT (public key)   │
  │                               │  2. Check Redis revocation    │
  │                               │  3. Populate TenantContext    │
  │                               │  4. Check permission          │
  │◀───────────────────────────────────────────────────────────────
  │  {stock data}                 │                               │
  │                               │                               │
  │  POST /api/v1/auth/refresh    │                               │
  │  {refresh_token, device_id}   │                               │
  │──────────────────────────────▶│                               │
  │                               │ 1. Verify refresh token hash  │
  │                               │ 2. Check device_id matches    │
  │                               │ 3. Issue new token pair       │
  │                               │ 4. Rotate refresh token       │
  │◀──────────────────────────────│                               │
  │  {new_access, new_refresh}    │                               │
```

## Multi-Tenant Hierarchy

```
Tenant (ACME Corp)
  └── Organisation (Headquarters)
        └── Branch (Main Branch)
              └── Location (Warehouse 1)
                    └── Department (Receiving)
                          └── Users
```

All queries, caches, queues, and configurations are scoped to the tenant context embedded in the JWT.

## Security Architecture

| Concern | Implementation |
|---------|---------------|
| Password hashing | Argon2id (PHP default) |
| Token signing | RS256 (asymmetric) — private key never leaves Auth service |
| Token revocation | Redis revocation list (JTI-based) |
| Token replay prevention | JTI uniqueness + revocation list |
| Refresh token security | Stored as SHA-256 hash only |
| Rotation | Every refresh = new refresh token (old invalidated) |
| Device theft detection | Device ID mismatch on refresh → all sessions revoked |
| Account locking | After N failed attempts → locked for M minutes |
| Rate limiting | Tenant + IP scoped, configurable per endpoint |
| Suspicious activity | Threshold-based detection → audit + notification |
| Audit logs | Immutable (no UPDATE/DELETE), append-only |
| CSRF protection | SameSite cookies or Bearer tokens (no cookie auth) |
| Signed URLs | JWT with `type: signed_url` + URL hash |

## Runtime Configuration (No Redeployment)

The following can be changed at runtime via the Configuration API:

```json
{
  "tenant_id": "tnt_ACME_CORP_01",
  "configurations": {
    "token_lifetimes": { "access": 30, "refresh": 86400 },
    "max_devices_per_user": 5
  },
  "feature_flags": {
    "sso_enabled": true,
    "suspicious_activity_alerts": true,
    "multi_device_sessions": false
  }
}
```

## Shared Auth Package Usage

Any microservice can add JWT verification in 3 steps:

```bash
composer require kv-enterprise/shared-auth
```

```php
// In config/app.php providers:
KvEnterprise\SharedAuth\Providers\SharedAuthServiceProvider::class,
```

```php
// In .env:
SHARED_AUTH_JWT_PUBLIC_KEY_PATH=storage/keys/auth-public.pem
SHARED_AUTH_REVOCATION_PREFIX=kv_auth_revoked:
```

```php
// In routes/api.php:
Route::middleware('auth.jwt')->group(function () {
    Route::middleware('require.permission:inventory.view')->group(function () {
        Route::get('/inventory/stock', [StockController::class, 'index']);
    });
});
```

```php
// In any controller:
use KvEnterprise\SharedAuth\Contracts\TenantContextInterface;

class StockController extends Controller
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
    ) {}

    public function index(): JsonResponse
    {
        $tenantId = $this->tenantContext->getTenantId();
        $userId   = $this->tenantContext->getUserId();
        // ... tenant-scoped query
    }
}
```
