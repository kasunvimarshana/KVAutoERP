# Migration Refactoring — SUPERSEDED

> **This document is historical.** It was written prematurely on 2026-04-16 when only 24 of 66 migrations had been refactored using prefix-style naming (`uq_*`, `idx_*`).
>
> A subsequent complete refactoring pass on 2026-04-18 converted all 67 migration files to suffix-style naming (`_pk`, `_uk`, `_idx`, `_fk`), superseding the work described here.
>
> **Current authoritative report:** [`MIGRATION_NAMING_REFACTOR_REPORT.md`](MIGRATION_NAMING_REFACTOR_REPORT.md)

## Summary

- **Date:** April 16, 2026
- **Claimed scope:** All 66 migrations (100%)
- **Actual scope at time of writing:** 24/66 migrations (36%) using prefix-style naming
- **Status:** Fully superseded by the suffix-style refactor (2026-04-18) covering all 67 files

The prefix-style convention (`uq_{table}_{fields}`, `idx_{table}_{fields}`) used in this first pass was replaced by the suffix-style convention (`{table}_{column(s)}_{type}` with `_pk`, `_uk`, `_idx`, `_fk` suffixes) documented in [`MIGRATION_NAMING_REFACTOR_REPORT.md`](MIGRATION_NAMING_REFACTOR_REPORT.md).
