# Migration Naming Refactor Report

Date: 2026-04-18
Workspace: KVAutoERP
Scope: app/Modules/*/database/migrations/*.php

## Objective
Standardize migration index and constraint naming for readability, reusability, and maintainability without changing functional behavior.

## Naming Convention Applied
- Pattern: {table}_{column(s)}_{type}
- Suffixes:
  - `_pk` primary key constraints
  - `_uk` unique constraints
  - `_idx` non-unique indexes
  - `_fk` foreign key constraints

## What Was Standardized
- Converted legacy prefix-style names (`uq_*`, `idx_*`, `pk_*`) to suffix-style names.
- Converted remaining `_uq` names to `_uk`.
- Added explicit names for previously unnamed:
  - composite `unique`, `index`, and `primary` declarations
  - explicit `foreign(...)` declarations
  - implicit `foreignId(...)->constrained(...)` declarations
- Normalized abbreviated identifiers to clearer table/column-based names where practical.
- Repaired malformed migration lines introduced during automation (multi-statements merged on a single line).
- Removed accidental duplicate `tenant_id` declaration in `app/Modules/Product/database/migrations/2024_01_01_600008_create_product_identifiers_table.php`.

## Scope Summary
- Changed migration files: 67
- Named explicit constraints/indexes currently present: 70
- Named `constrained(..., ..., '<name>_fk')` foreign keys: 258

## Validation Performed
- Pint formatting over all changed migration files: PASS
- PHP syntax check (`php -l`) over all changed migration files: PASS
- Compliance checks:
  - No remaining `uq_`, `idx_`, `pk_`, `_uq` legacy naming patterns: PASS
  - No unnamed composite unique/index/primary declarations: PASS
  - No unnamed explicit `foreign(...)` declarations: PASS
  - No `constrained(...)` declarations missing explicit `_fk` name in module migrations: PASS
  - No explicit names over 64 characters: PASS

## Runtime Validation
- Executed `php artisan migrate:fresh --force`: PASS
- Executed `php artisan migrate:status`: PASS (for currently loaded migration set)

Note: Laravel migration execution scope depends on loaded migration paths/providers at runtime. The naming refactor was applied across all module migration files regardless of provider loading state.

## Functional Safety
- No business logic changes were introduced.
- Changes are limited to naming, formatting, and syntax/structure integrity of migration definitions.
