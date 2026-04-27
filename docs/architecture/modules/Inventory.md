# Module Contract: Inventory

## 1. Bounded Context
- Purpose: Stock state, movements, reservations, cost layers, and valuation configuration.
- Core business capabilities: Inventory domain workflow and supporting services.
- In-scope: Module-owned domain/application/infrastructure behavior.
- Out-of-scope: Cross-module behavior not exposed via contracts/events/routes.

## 2. Domain Model
- Primary entities: AllocationLine, AllocationResult, CycleCountHeader, CycleCountLine, InventoryCostLayer, StockMovement, StockReservation, TransferOrder, TransferOrderLine, ValuationConfig
- Value objects: No module-specific value objects detected.
- Aggregates and roots: Derived from entity/repository boundaries in module namespace.
- Invariants and constraints: Enforced via DTO/request validation, service workflows, and DB constraints.

## 3. Data Ownership
- Owned tables: batches, cycle_count_headers, cycle_count_lines, inventory_cost_layers, serials, stock_adjustment_lines, stock_adjustments, stock_levels, stock_movements, stock_reservations, stock_transfer_lines, stock_transfers, trace_logs, transfer_order_lines, transfer_orders, valuation_configs
- Referenced external tables: Derived from migration FKs to cross-module tables.
- Tenant scoping strategy: tenant_id-based row isolation on tenant-owned tables.
- Soft-delete and archival policy: Table-specific; many transactional tables include softDeletes().

## 4. Application Layer
- Commands/use-cases: Service-driven mutation flows.
- Queries/read-models: Repository/Eloquent read flows and API resources.
- Transaction boundaries: Write paths expected to be wrapped by service-layer transaction handling.
- Idempotency strategy: Document/status-based workflow progression and unique business keys where defined.

## 5. Integration Model
- Published events: ExpiredStockReservationsReleased
- Consumed events: Listener-driven integration where module listeners are registered.
- External module dependencies (contracts only): Cross-module references should remain contract/event based.
- Failure and retry behavior: Queue/listener behavior and transactional safeguards per use-case.

## 6. API Surface
- Route prefix: inventory
- Resource endpoints: No apiResource routes declared.
- Action endpoints: cycle-counts, cycle-counts/{cycleCount}, cycle-counts/{cycleCount}/complete, cycle-counts/{cycleCount}/start, stock-reservations, stock-reservations/{reservation}, stock-reservations/release-expired, transfer-orders, transfer-orders/{transferOrder}, transfer-orders/{transferOrder}/approve, transfer-orders/{transferOrder}/receive, valuation-configs, valuation-configs/{config}, valuation-configs/resolve, warehouses/{warehouse}/movements, warehouses/{warehouse}/stock-levels
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
- Current module-aligned tests: InventoryAllocationStrategyIntegrationTest.php, InventoryCycleCountIntegrationTest.php, InventoryCycleCountRoutesTest.php, InventoryReleaseExpiredReservationsCommandTest.php, InventoryRoutesTest.php, InventoryStockMovementIntegrationTest.php, InventoryStockReservationEndpointsAuthenticatedTest.php, InventoryStockReservationIntegrationTest.php, InventoryStockReservationRoutesTest.php, InventoryTransferOrderIntegrationTest.php, InventoryTransferOrderRoutesTest.php, InventoryValuationConfigRoutesTest.php, InventoryValuationStrategyIntegrationTest.php

## 10. Open Risks and Refactor Backlog
- Current risks: Concurrency on reservations, transfers, and adjustments can still create contention and replay/idempotency edge cases.
- Technical debt: Cost-layer and movement traceability rules are complex and dispersed across repositories/services.
- Planned refactors: Expand lock/idempotency test coverage and formalize valuation + traceability invariants in architecture docs/tests.
## 11. Concrete Source Map
- Module root: [app/Modules/Inventory](app/Modules/Inventory)
- Route source: [app/Modules/Inventory/routes/api.php](app/Modules/Inventory/routes/api.php)
- Provider files:
  - [app/Modules/Inventory/Infrastructure/Providers/InventoryServiceProvider.php](app/Modules/Inventory/Infrastructure/Providers/InventoryServiceProvider.php)
- Domain entities (representative):
  - [app/Modules/Inventory/Domain/Entities/AllocationLine.php](app/Modules/Inventory/Domain/Entities/AllocationLine.php)
  - [app/Modules/Inventory/Domain/Entities/AllocationResult.php](app/Modules/Inventory/Domain/Entities/AllocationResult.php)
  - [app/Modules/Inventory/Domain/Entities/CycleCountHeader.php](app/Modules/Inventory/Domain/Entities/CycleCountHeader.php)
  - [app/Modules/Inventory/Domain/Entities/CycleCountLine.php](app/Modules/Inventory/Domain/Entities/CycleCountLine.php)
  - [app/Modules/Inventory/Domain/Entities/InventoryCostLayer.php](app/Modules/Inventory/Domain/Entities/InventoryCostLayer.php)
- Application services (representative):
  - [app/Modules/Inventory/Application/Services/AllocationEngineService.php](app/Modules/Inventory/Application/Services/AllocationEngineService.php)
  - [app/Modules/Inventory/Application/Services/ApproveTransferOrderService.php](app/Modules/Inventory/Application/Services/ApproveTransferOrderService.php)
  - [app/Modules/Inventory/Application/Services/CompleteCycleCountService.php](app/Modules/Inventory/Application/Services/CompleteCycleCountService.php)
  - [app/Modules/Inventory/Application/Services/CreateCycleCountService.php](app/Modules/Inventory/Application/Services/CreateCycleCountService.php)
  - [app/Modules/Inventory/Application/Services/CreateStockReservationService.php](app/Modules/Inventory/Application/Services/CreateStockReservationService.php)
- Repository implementations (representative):
  - [app/Modules/Inventory/Infrastructure/Persistence/Eloquent/Repositories/EloquentCostLayerRepository.php](app/Modules/Inventory/Infrastructure/Persistence/Eloquent/Repositories/EloquentCostLayerRepository.php)
  - [app/Modules/Inventory/Infrastructure/Persistence/Eloquent/Repositories/EloquentCycleCountRepository.php](app/Modules/Inventory/Infrastructure/Persistence/Eloquent/Repositories/EloquentCycleCountRepository.php)
  - [app/Modules/Inventory/Infrastructure/Persistence/Eloquent/Repositories/EloquentInventoryStockRepository.php](app/Modules/Inventory/Infrastructure/Persistence/Eloquent/Repositories/EloquentInventoryStockRepository.php)
  - [app/Modules/Inventory/Infrastructure/Persistence/Eloquent/Repositories/EloquentStockReservationRepository.php](app/Modules/Inventory/Infrastructure/Persistence/Eloquent/Repositories/EloquentStockReservationRepository.php)
  - [app/Modules/Inventory/Infrastructure/Persistence/Eloquent/Repositories/EloquentTraceLogRepository.php](app/Modules/Inventory/Infrastructure/Persistence/Eloquent/Repositories/EloquentTraceLogRepository.php)
- Migration files (representative):
  - [app/Modules/Inventory/database/migrations/2024_01_01_900001_create_batches_table.php](app/Modules/Inventory/database/migrations/2024_01_01_900001_create_batches_table.php)
  - [app/Modules/Inventory/database/migrations/2024_01_01_900002_create_serials_table.php](app/Modules/Inventory/database/migrations/2024_01_01_900002_create_serials_table.php)
  - [app/Modules/Inventory/database/migrations/2024_01_01_900002a_create_valuation_configs_table.php](app/Modules/Inventory/database/migrations/2024_01_01_900002a_create_valuation_configs_table.php)
  - [app/Modules/Inventory/database/migrations/2024_01_01_900003_create_stock_levels_table.php](app/Modules/Inventory/database/migrations/2024_01_01_900003_create_stock_levels_table.php)
  - [app/Modules/Inventory/database/migrations/2024_01_01_900004_create_stock_movements_table.php](app/Modules/Inventory/database/migrations/2024_01_01_900004_create_stock_movements_table.php)
- Test references:
  - [tests/Feature/InventoryAllocationStrategyIntegrationTest.php](tests/Feature/InventoryAllocationStrategyIntegrationTest.php)
  - [tests/Feature/InventoryCycleCountIntegrationTest.php](tests/Feature/InventoryCycleCountIntegrationTest.php)
  - [tests/Feature/InventoryCycleCountRoutesTest.php](tests/Feature/InventoryCycleCountRoutesTest.php)
  - [tests/Feature/InventoryReleaseExpiredReservationsCommandTest.php](tests/Feature/InventoryReleaseExpiredReservationsCommandTest.php)
  - [tests/Feature/InventoryRoutesTest.php](tests/Feature/InventoryRoutesTest.php)
  - [tests/Feature/InventoryStockMovementIntegrationTest.php](tests/Feature/InventoryStockMovementIntegrationTest.php)
  - [tests/Feature/InventoryStockReservationEndpointsAuthenticatedTest.php](tests/Feature/InventoryStockReservationEndpointsAuthenticatedTest.php)
  - [tests/Feature/InventoryStockReservationIntegrationTest.php](tests/Feature/InventoryStockReservationIntegrationTest.php)

## 12. Real-Time Sequence References
- Entry path: HTTP routes dispatch to thin controllers in module infrastructure.
- Orchestration path: Controllers delegate mutations/queries to application services and contracts.
- Persistence path: Services persist through module repositories implementing domain interfaces.
- Event publication sources:
  - [app/Modules/Inventory/Domain/Events/ExpiredStockReservationsReleased.php](app/Modules/Inventory/Domain/Events/ExpiredStockReservationsReleased.php)
- Event consumption/listener sources:
  - [app/Modules/Inventory/Infrastructure/Listeners/HandleGoodsReceiptPosted.php](app/Modules/Inventory/Infrastructure/Listeners/HandleGoodsReceiptPosted.php)
  - [app/Modules/Inventory/Infrastructure/Listeners/HandlePurchaseReturnPosted.php](app/Modules/Inventory/Infrastructure/Listeners/HandlePurchaseReturnPosted.php)
  - [app/Modules/Inventory/Infrastructure/Listeners/HandleSalesReturnReceived.php](app/Modules/Inventory/Infrastructure/Listeners/HandleSalesReturnReceived.php)
  - [app/Modules/Inventory/Infrastructure/Listeners/HandleShipmentProcessed.php](app/Modules/Inventory/Infrastructure/Listeners/HandleShipmentProcessed.php)



