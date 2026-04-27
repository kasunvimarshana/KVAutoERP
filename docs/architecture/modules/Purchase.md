# Module Contract: Purchase

## 1. Bounded Context
- Purpose: Procure-to-pay commercial documents from order through invoice/returns.
- Core business capabilities: Purchase domain workflow and supporting services.
- In-scope: Module-owned domain/application/infrastructure behavior.
- Out-of-scope: Cross-module behavior not exposed via contracts/events/routes.

## 2. Domain Model
- Primary entities: GrnHeader, GrnLine, PurchaseInvoice, PurchaseInvoiceLine, PurchaseOrder, PurchaseOrderLine, PurchaseReturn, PurchaseReturnLine
- Value objects: No module-specific value objects detected.
- Aggregates and roots: Derived from entity/repository boundaries in module namespace.
- Invariants and constraints: Enforced via DTO/request validation, service workflows, and DB constraints.

## 3. Data Ownership
- Owned tables: grn_headers, grn_lines, purchase_invoice_lines, purchase_invoices, purchase_order_lines, purchase_orders, purchase_return_lines, purchase_returns
- Referenced external tables: Derived from migration FKs to cross-module tables.
- Tenant scoping strategy: tenant_id-based row isolation on tenant-owned tables.
- Soft-delete and archival policy: Table-specific; many transactional tables include softDeletes().

## 4. Application Layer
- Commands/use-cases: Service-driven mutation flows.
- Queries/read-models: Repository/Eloquent read flows and API resources.
- Transaction boundaries: Write paths expected to be wrapped by service-layer transaction handling.
- Idempotency strategy: Document/status-based workflow progression and unique business keys where defined.

## 5. Integration Model
- Published events: GoodsReceiptPosted, PurchaseInvoiceApproved, PurchaseOrderConfirmed, PurchasePaymentRecorded, PurchaseReturnPosted
- Consumed events: Listener-driven integration where module listeners are registered.
- External module dependencies (contracts only): Cross-module references should remain contract/event based.
- Failure and retry behavior: Queue/listener behavior and transactional safeguards per use-case.

## 6. API Surface
- Route prefix: n/a
- Resource endpoints: grns, purchase-invoices, purchase-orders, purchase-returns
- Action endpoints: grns/{grn}/post, purchase-invoices/{invoice}/approve, purchase-invoices/{invoice}/payment, purchase-orders/{purchaseOrder}/cancel, purchase-orders/{purchaseOrder}/confirm, purchase-orders/{purchaseOrder}/send, purchase-returns/{purchaseReturn}/post
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
- Current module-aligned tests: PurchaseOrderRepositoryIntegrationTest.php, PurchaseRoutesTest.php

## 10. Open Risks and Refactor Backlog
- Current risks: Procure-to-pay document state transitions remain vulnerable to out-of-order operations and partial posting failures.
- Technical debt: Cross-module touchpoints (inventory receipts, finance postings) rely on event timing assumptions.
- Planned refactors: Add sequence-aware integration tests for PO->GRN->Invoice->Payment paths including retry/failure branches.
## 11. Concrete Source Map
- Module root: [app/Modules/Purchase](app/Modules/Purchase)
- Route source: [app/Modules/Purchase/routes/api.php](app/Modules/Purchase/routes/api.php)
- Provider files:
  - [app/Modules/Purchase/Infrastructure/Providers/PurchaseServiceProvider.php](app/Modules/Purchase/Infrastructure/Providers/PurchaseServiceProvider.php)
- Domain entities (representative):
  - [app/Modules/Purchase/Domain/Entities/GrnHeader.php](app/Modules/Purchase/Domain/Entities/GrnHeader.php)
  - [app/Modules/Purchase/Domain/Entities/GrnLine.php](app/Modules/Purchase/Domain/Entities/GrnLine.php)
  - [app/Modules/Purchase/Domain/Entities/PurchaseInvoice.php](app/Modules/Purchase/Domain/Entities/PurchaseInvoice.php)
  - [app/Modules/Purchase/Domain/Entities/PurchaseInvoiceLine.php](app/Modules/Purchase/Domain/Entities/PurchaseInvoiceLine.php)
  - [app/Modules/Purchase/Domain/Entities/PurchaseOrder.php](app/Modules/Purchase/Domain/Entities/PurchaseOrder.php)
- Application services (representative):
  - [app/Modules/Purchase/Application/Services/ApprovePurchaseInvoiceService.php](app/Modules/Purchase/Application/Services/ApprovePurchaseInvoiceService.php)
  - [app/Modules/Purchase/Application/Services/CancelPurchaseOrderService.php](app/Modules/Purchase/Application/Services/CancelPurchaseOrderService.php)
  - [app/Modules/Purchase/Application/Services/ConfirmPurchaseOrderService.php](app/Modules/Purchase/Application/Services/ConfirmPurchaseOrderService.php)
  - [app/Modules/Purchase/Application/Services/CreateGrnService.php](app/Modules/Purchase/Application/Services/CreateGrnService.php)
  - [app/Modules/Purchase/Application/Services/CreatePurchaseInvoiceService.php](app/Modules/Purchase/Application/Services/CreatePurchaseInvoiceService.php)
- Repository implementations (representative):
  - [app/Modules/Purchase/Infrastructure/Persistence/Eloquent/Repositories/EloquentGrnHeaderRepository.php](app/Modules/Purchase/Infrastructure/Persistence/Eloquent/Repositories/EloquentGrnHeaderRepository.php)
  - [app/Modules/Purchase/Infrastructure/Persistence/Eloquent/Repositories/EloquentGrnLineRepository.php](app/Modules/Purchase/Infrastructure/Persistence/Eloquent/Repositories/EloquentGrnLineRepository.php)
  - [app/Modules/Purchase/Infrastructure/Persistence/Eloquent/Repositories/EloquentPurchaseInvoiceLineRepository.php](app/Modules/Purchase/Infrastructure/Persistence/Eloquent/Repositories/EloquentPurchaseInvoiceLineRepository.php)
  - [app/Modules/Purchase/Infrastructure/Persistence/Eloquent/Repositories/EloquentPurchaseInvoiceRepository.php](app/Modules/Purchase/Infrastructure/Persistence/Eloquent/Repositories/EloquentPurchaseInvoiceRepository.php)
  - [app/Modules/Purchase/Infrastructure/Persistence/Eloquent/Repositories/EloquentPurchaseOrderLineRepository.php](app/Modules/Purchase/Infrastructure/Persistence/Eloquent/Repositories/EloquentPurchaseOrderLineRepository.php)
- Migration files (representative):
  - [app/Modules/Purchase/database/migrations/2024_01_01_100001_create_purchase_orders_table.php](app/Modules/Purchase/database/migrations/2024_01_01_100001_create_purchase_orders_table.php)
  - [app/Modules/Purchase/database/migrations/2024_01_01_100002_create_purchase_order_lines_table.php](app/Modules/Purchase/database/migrations/2024_01_01_100002_create_purchase_order_lines_table.php)
  - [app/Modules/Purchase/database/migrations/2024_01_01_100003a_create_grn_headers_table.php](app/Modules/Purchase/database/migrations/2024_01_01_100003a_create_grn_headers_table.php)
  - [app/Modules/Purchase/database/migrations/2024_01_01_100003b_create_grn_lines_table.php](app/Modules/Purchase/database/migrations/2024_01_01_100003b_create_grn_lines_table.php)
  - [app/Modules/Purchase/database/migrations/2024_01_01_100004a_create_purchase_invoices_table.php](app/Modules/Purchase/database/migrations/2024_01_01_100004a_create_purchase_invoices_table.php)
- Test references:
  - [tests/Feature/PurchaseOrderRepositoryIntegrationTest.php](tests/Feature/PurchaseOrderRepositoryIntegrationTest.php)
  - [tests/Feature/PurchaseRoutesTest.php](tests/Feature/PurchaseRoutesTest.php)

## 12. Real-Time Sequence References
- Entry path: HTTP routes dispatch to thin controllers in module infrastructure.
- Orchestration path: Controllers delegate mutations/queries to application services and contracts.
- Persistence path: Services persist through module repositories implementing domain interfaces.
- Event publication sources:
  - [app/Modules/Purchase/Domain/Events/GoodsReceiptPosted.php](app/Modules/Purchase/Domain/Events/GoodsReceiptPosted.php)
  - [app/Modules/Purchase/Domain/Events/PurchaseInvoiceApproved.php](app/Modules/Purchase/Domain/Events/PurchaseInvoiceApproved.php)
  - [app/Modules/Purchase/Domain/Events/PurchaseOrderConfirmed.php](app/Modules/Purchase/Domain/Events/PurchaseOrderConfirmed.php)
  - [app/Modules/Purchase/Domain/Events/PurchasePaymentRecorded.php](app/Modules/Purchase/Domain/Events/PurchasePaymentRecorded.php)



