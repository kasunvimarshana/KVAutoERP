# Module Contract: Sales

## 1. Bounded Context
- Purpose: Order-to-cash commercial documents from order through shipment/invoice/returns.
- Core business capabilities: Sales domain workflow and supporting services.
- In-scope: Module-owned domain/application/infrastructure behavior.
- Out-of-scope: Cross-module behavior not exposed via contracts/events/routes.

## 2. Domain Model
- Primary entities: SalesInvoice, SalesInvoiceLine, SalesOrder, SalesOrderLine, SalesReturn, SalesReturnLine, Shipment, ShipmentLine
- Value objects: No module-specific value objects detected.
- Aggregates and roots: Derived from entity/repository boundaries in module namespace.
- Invariants and constraints: Enforced via DTO/request validation, service workflows, and DB constraints.

## 3. Data Ownership
- Owned tables: sales_invoice_lines, sales_invoices, sales_order_lines, sales_orders, sales_return_lines, sales_returns, shipment_lines, shipments
- Referenced external tables: Derived from migration FKs to cross-module tables.
- Tenant scoping strategy: tenant_id-based row isolation on tenant-owned tables.
- Soft-delete and archival policy: Table-specific; many transactional tables include softDeletes().

## 4. Application Layer
- Commands/use-cases: Service-driven mutation flows.
- Queries/read-models: Repository/Eloquent read flows and API resources.
- Transaction boundaries: Write paths expected to be wrapped by service-layer transaction handling.
- Idempotency strategy: Document/status-based workflow progression and unique business keys where defined.

## 5. Integration Model
- Published events: SalesInvoicePosted, SalesPaymentRecorded, SalesReturnReceived, ShipmentProcessed
- Consumed events: Listener-driven integration where module listeners are registered.
- External module dependencies (contracts only): Cross-module references should remain contract/event based.
- Failure and retry behavior: Queue/listener behavior and transactional safeguards per use-case.

## 6. API Surface
- Route prefix: n/a
- Resource endpoints: sales-invoices, sales-orders, sales-returns, shipments
- Action endpoints: sales-invoices/{salesInvoice}/post, sales-invoices/{salesInvoice}/record-payment, sales-orders/{salesOrder}/cancel, sales-orders/{salesOrder}/confirm, sales-returns/{salesReturn}/approve, sales-returns/{salesReturn}/receive, shipments/{shipment}/process
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
- Current module-aligned tests: SalesOrderRepositoryIntegrationTest.php, SalesRoutesTest.php

## 10. Open Risks and Refactor Backlog
- Current risks: Order-to-cash transitions can diverge when shipment/invoice/payment events arrive out of sequence.
- Technical debt: Return and credit interactions with inventory/finance are distributed across handlers without a unified flow contract.
- Planned refactors: Define O2C sequence contracts and add resilience tests for partial shipment, return, and payment reconciliation.
## 11. Concrete Source Map
- Module root: [app/Modules/Sales](app/Modules/Sales)
- Route source: [app/Modules/Sales/routes/api.php](app/Modules/Sales/routes/api.php)
- Provider files:
  - [app/Modules/Sales/Infrastructure/Providers/SalesServiceProvider.php](app/Modules/Sales/Infrastructure/Providers/SalesServiceProvider.php)
- Domain entities (representative):
  - [app/Modules/Sales/Domain/Entities/SalesInvoice.php](app/Modules/Sales/Domain/Entities/SalesInvoice.php)
  - [app/Modules/Sales/Domain/Entities/SalesInvoiceLine.php](app/Modules/Sales/Domain/Entities/SalesInvoiceLine.php)
  - [app/Modules/Sales/Domain/Entities/SalesOrder.php](app/Modules/Sales/Domain/Entities/SalesOrder.php)
  - [app/Modules/Sales/Domain/Entities/SalesOrderLine.php](app/Modules/Sales/Domain/Entities/SalesOrderLine.php)
  - [app/Modules/Sales/Domain/Entities/SalesReturn.php](app/Modules/Sales/Domain/Entities/SalesReturn.php)
- Application services (representative):
  - [app/Modules/Sales/Application/Services/ApproveSalesReturnService.php](app/Modules/Sales/Application/Services/ApproveSalesReturnService.php)
  - [app/Modules/Sales/Application/Services/CancelSalesOrderService.php](app/Modules/Sales/Application/Services/CancelSalesOrderService.php)
  - [app/Modules/Sales/Application/Services/ConfirmSalesOrderService.php](app/Modules/Sales/Application/Services/ConfirmSalesOrderService.php)
  - [app/Modules/Sales/Application/Services/CreateSalesInvoiceService.php](app/Modules/Sales/Application/Services/CreateSalesInvoiceService.php)
  - [app/Modules/Sales/Application/Services/CreateSalesOrderService.php](app/Modules/Sales/Application/Services/CreateSalesOrderService.php)
- Repository implementations (representative):
  - [app/Modules/Sales/Infrastructure/Persistence/Eloquent/Repositories/EloquentSalesInvoiceRepository.php](app/Modules/Sales/Infrastructure/Persistence/Eloquent/Repositories/EloquentSalesInvoiceRepository.php)
  - [app/Modules/Sales/Infrastructure/Persistence/Eloquent/Repositories/EloquentSalesOrderRepository.php](app/Modules/Sales/Infrastructure/Persistence/Eloquent/Repositories/EloquentSalesOrderRepository.php)
  - [app/Modules/Sales/Infrastructure/Persistence/Eloquent/Repositories/EloquentSalesReturnRepository.php](app/Modules/Sales/Infrastructure/Persistence/Eloquent/Repositories/EloquentSalesReturnRepository.php)
  - [app/Modules/Sales/Infrastructure/Persistence/Eloquent/Repositories/EloquentShipmentRepository.php](app/Modules/Sales/Infrastructure/Persistence/Eloquent/Repositories/EloquentShipmentRepository.php)
- Migration files (representative):
  - [app/Modules/Sales/database/migrations/2024_01_01_110001_create_sales_orders_table.php](app/Modules/Sales/database/migrations/2024_01_01_110001_create_sales_orders_table.php)
  - [app/Modules/Sales/database/migrations/2024_01_01_110002_create_sales_order_lines_table.php](app/Modules/Sales/database/migrations/2024_01_01_110002_create_sales_order_lines_table.php)
  - [app/Modules/Sales/database/migrations/2024_01_01_110003a_create_shipments_table.php](app/Modules/Sales/database/migrations/2024_01_01_110003a_create_shipments_table.php)
  - [app/Modules/Sales/database/migrations/2024_01_01_110003b_create_shipment_lines_table.php](app/Modules/Sales/database/migrations/2024_01_01_110003b_create_shipment_lines_table.php)
  - [app/Modules/Sales/database/migrations/2024_01_01_110004a_create_sales_invoices_table.php](app/Modules/Sales/database/migrations/2024_01_01_110004a_create_sales_invoices_table.php)
- Test references:
  - [tests/Feature/SalesOrderRepositoryIntegrationTest.php](tests/Feature/SalesOrderRepositoryIntegrationTest.php)
  - [tests/Feature/SalesRoutesTest.php](tests/Feature/SalesRoutesTest.php)

## 12. Real-Time Sequence References
- Entry path: HTTP routes dispatch to thin controllers in module infrastructure.
- Orchestration path: Controllers delegate mutations/queries to application services and contracts.
- Persistence path: Services persist through module repositories implementing domain interfaces.
- Event publication sources:
  - [app/Modules/Sales/Domain/Events/SalesInvoicePosted.php](app/Modules/Sales/Domain/Events/SalesInvoicePosted.php)
  - [app/Modules/Sales/Domain/Events/SalesPaymentRecorded.php](app/Modules/Sales/Domain/Events/SalesPaymentRecorded.php)
  - [app/Modules/Sales/Domain/Events/SalesReturnReceived.php](app/Modules/Sales/Domain/Events/SalesReturnReceived.php)
  - [app/Modules/Sales/Domain/Events/ShipmentProcessed.php](app/Modules/Sales/Domain/Events/ShipmentProcessed.php)



