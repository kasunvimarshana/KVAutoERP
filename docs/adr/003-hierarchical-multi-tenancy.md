# ADR 003: Hierarchical Multi-Tenancy via Closure Tables

## Status
Accepted

## Context
Standard multi-tenancy often only supports flat isolation. However, enterprise ERP/CRM systems require hierarchical organization support (e.g., Parent Org -> Regions -> Branches). Performance for hierarchical queries (finding all records for an organization and its sub-organizations) must scale to 10k+ concurrent organizations.

## Decision
We will implement hierarchical isolation using **Closure Tables**.
- **Schema**: A dedicated `organisation_tree` table will store all ancestor-descendant relationships.
- **Performance**: This allows for $O(1)$ lookup of all descendants compared to recursive CTEs or path-based approaches.
- **Inheritance**: Permissions and configurations will cascade down the tree but can be overridden at any node.
- **Implementation**: Managed via [HierarchicalIsolation.php](file:///c:/projects/KV/KV/packages/shared-core/src/Hierarchies/HierarchicalIsolation.php).

## Consequences
- **Storage**: Increased storage for the closure table.
- **Complexity**: Organization moves (re-parenting) require more complex tree maintenance logic.
- **Reliability**: Ensures strict data isolation across complex enterprise structures.
