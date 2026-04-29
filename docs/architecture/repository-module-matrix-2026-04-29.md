# Repository Module Matrix (2026-04-29)

## Purpose

This matrix provides a repository-focused reference for implemented modules so future changes can quickly identify:

- which modules own persistence logic,
- which repositories are aggregate roots versus line/detail accessors,
- where tenant-sensitive writes are concentrated,
- and which modules participate in high-value ERP runtime flows.

## Repository Pattern Summary

| Module | Repo Count | Dominant Repository Style | High-Risk Write Paths | Runtime Integration Role |
| --- | ---: | --- | --- | --- |
| Audit | 1 | append/list projection repository | audit log recording | passive audit sink |
| Configuration | 4 | reference-data lookup/save repositories | low-risk master data writes | shared configuration source |
| Customer | 3 | header + detail repositories | primary/default contact/address toggles | master data source for Sales/Finance |
| Employee | 1 | single aggregate repository | employee identity/profile writes | HR dependency |
| Finance | 19 | document/config repositories + posting-support lookups | numbering, posting support, balances, approval state | central accounting sink |
| HR | 15 | aggregate repositories + operational lookup repositories | payroll, payslips, leave, attendance | operational source; Finance posting source |
| Inventory | 7 | workflow repositories + valuation/state repositories | transfer receipt, cycle count completion, stock reservations, cost layers | stock-state sink and Finance source |
| OrganizationUnit | 4 | hierarchy + attachment repositories | hierarchy membership and attachment updates | shared org structure source |
| Pricing | 4 | pricing lookup/match repositories | default price list switching, best-match logic | pricing source for Sales/Purchase |
| Product | 12 | catalog and supporting master-data repositories | variant/default resolution, conversion rules | master data source for Inventory/Sales/Purchase |
| Purchase | 8 | document header/detail repositories | PO/GRN/invoice/return lifecycle persistence | source for Inventory and Finance |
| Sales | 4 | aggregate document repositories with line synchronization | order/invoice/shipment/return aggregate writes | source for Inventory and Finance |
| Supplier | 4 | header + detail repositories | preferred supplier/default address-contact toggles | master data source for Purchase/Finance |
| Tax | 4 | tax rule/rate/group + transaction tax repositories | tax line replacement and rule matching | shared taxation source |
| Tenant | 5 | tenant configuration repositories | tenant settings/domains/attachments | platform tenancy source |
| User | 5 | identity/authorization/supporting repositories | password, avatar, role/permission sync, device lifecycle | cross-cutting identity source |
| Warehouse | 2 | warehouse + location hierarchy repositories | default warehouse/location path updates | location source for Inventory/Sales/Purchase |

## Aggregate Repository Categories

### Transactional aggregate-sync repositories

These repositories persist a header/root entity together with mutable child collections and therefore require the strongest write guardrails:

- Sales: sales orders, sales invoices, shipments, sales returns.
- HR: payslips.
- Inventory: transfer orders, cycle counts.

Required invariants:

- writes occur inside a database transaction,
- existing root updates delegate through the shared scoped update helper,
- child updates are tenant-scoped,
- removed child rows are pruned explicitly,
- refreshed aggregates are loaded before mapping back to domain entities.

### Header-only repositories delegating to shared base update paths

These repositories do not persist mutable child collections directly and should continue routing ID-based writes through the shared scoped base repository:

- Purchase order, purchase invoice, purchase return, GRN header.
- Many Finance, Customer, Supplier, Product, Tax, Tenant, and User header repositories.

Required invariants:

- update-by-id uses the shared base repository path,
- tenant-owned models inherit centralized tenant scoping,
- domain mapping occurs only after persistence completes.

### Specialized repositories with explicit scope bypass

These repositories intentionally bypass global tenant scope and therefore must keep explicit tenant guards in query chains:

- Inventory valuation config.
- Inventory cost layers.
- Finance numbering sequence.
- Finance payment term and cost center lookups that intentionally bypass global scope for controlled query behavior.

Required invariants:

- any `withoutGlobalScope('tenant')` path must re-apply `where('tenant_id', ...)`,
- locking or transactional behavior must remain explicit on contention-sensitive sequences or workflow mutations.

## Module-to-Flow Mapping

| ERP Flow | Source Modules | Sink Modules | Repository Hotspots |
| --- | --- | --- | --- |
| Order to Cash | Sales | Inventory, Finance | Sales aggregate repositories, Inventory stock/transfer repos, Finance AR/journal repos |
| Procure to Pay | Purchase | Inventory, Finance | Purchase header/detail repos, Inventory receiving repos, Finance AP/journal repos |
| Payroll to Ledger | HR | Finance | Payslip/payroll repositories, Finance journal/AP/AR support repos |
| Stock Adjustment and Valuation | Inventory | Finance | stock reservation, cost layer, valuation config, cycle count repos |
| Tenant and Access Control | Tenant, User | all modules indirectly | tenant settings/domain repos, user/role/permission/device repos |

## Recommended Ongoing Guardrails

1. Keep repository-layer transport independence: no Auth or Request facade access in Eloquent repositories.
2. Keep tenant-owned ID-based writes on the shared `EloquentRepository` scoped update/delete path.
3. Keep aggregate child synchronization tenant-scoped and transactional.
4. Keep explicit tenant guards whenever global scope is intentionally bypassed.
5. Keep integration tests for cross-tenant mutation prevention on the highest-value Sales, Purchase, Inventory, and HR mutation paths, and continue extending the same pattern to adjacent cancel/delete flows that still lack direct behavioral coverage.
