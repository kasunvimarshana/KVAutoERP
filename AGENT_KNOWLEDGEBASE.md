# Agent Knowledge Base

> **This file has been consolidated.** All authoritative knowledge is maintained in these documents:

| Document | Purpose |
| -------- | ------- |
| [SKILL.md](SKILL.md) | Canonical system specification — architecture, module schemas, business rules, database standards |
| [AGENT.md](AGENT.md) | Agent operational guide — workflows, decision trees, event catalog, checklists, testing/security standards |
| [README.md](README.md) | Project overview, setup instructions, module status |
| [CLAUDE.md](CLAUDE.md) | Concise AI agent instructions |
| [.github/copilot-instructions.md](.github/copilot-instructions.md) | GitHub Copilot-specific instructions |
| [docs/PRODUCT_PRICING_INVENTORY_ARCHITECTURE_AUDIT_2026_04_25.md](docs/PRODUCT_PRICING_INVENTORY_ARCHITECTURE_AUDIT_2026_04_25.md) | Product/Pricing/Inventory architecture findings and search-system design |
| [docs/PRODUCT_SEARCH_PERFORMANCE_RUNBOOK.md](docs/PRODUCT_SEARCH_PERFORMANCE_RUNBOOK.md) | Search latency benchmark thresholds, release comparison workflow, and incident response guidance |
| [docs/PRODUCT_SEARCH_BENCHMARK_REPORT_TEMPLATE.md](docs/PRODUCT_SEARCH_BENCHMARK_REPORT_TEMPLATE.md) | Standardized benchmark report format for release comparisons |
| [.github/workflows/product-search-benchmark-gate.yml](.github/workflows/product-search-benchmark-gate.yml) | CI workflow example that gates releases based on benchmark p95/max regression thresholds |
| [.github/workflows/product-search-benchmark-pr-check.yml](.github/workflows/product-search-benchmark-pr-check.yml) | PR workflow for benchmark contract verification (tests + JSON shape checks) |
| [.github/workflows/product-search-benchmark-main-report.yml](.github/workflows/product-search-benchmark-main-report.yml) | Non-blocking main-branch benchmark trend report with workflow summary deltas |
| [docs/benchmarks/product-search-baseline.example.json](docs/benchmarks/product-search-baseline.example.json) | Example baseline JSON payload for benchmark delta comparisons |
| [docs/benchmarks/product-search-baseline.staging.example.json](docs/benchmarks/product-search-baseline.staging.example.json) | Example staging baseline used by default in release branch benchmark automation |
| [docs/benchmarks/product-search-baseline.prod-like.example.json](docs/benchmarks/product-search-baseline.prod-like.example.json) | Example production-like baseline for stricter release benchmark comparisons |

## Why This File Was Consolidated

The previous version of this file (3,538 lines) contained:

- ~60% duplicated raw prompt text with no structured value
- ~40% content that was an exact duplicate of SKILL.md sections 1–14

All unique, structured knowledge has been preserved in **SKILL.md** (system specification) and **AGENT.md** (operational guide). Refer to those documents as the single source of truth.

## Quick Reference — Key Facts

- **19 modules** under `app/Modules/` — 8 fully implemented, 2 infrastructure-only, 9 migration-only stubs
- **Namespaces**: `Modules\<Module>\...` (PSR-4: `"Modules\\" => "app/Modules/"`)
- **Multi-tenancy**: `ResolveTenant` middleware with `X-Tenant-ID` header; repositories filter `tenant_id` explicitly
- **Models**: All extend `Illuminate\Database\Eloquent\Model` directly (BaseModel exists but is unused)
- **PKs**: Integer auto-increment (`BIGINT UNSIGNED`); HasUuid trait exists but is unused
- **Monetary values**: `DECIMAL(20,6)`
- **12 registered ServiceProviders** in `bootstrap/providers.php`
- **66 module migrations** + 3 framework migrations; cross-module FKs deferred to `add_remaining_foreign_keys.php`

## Benchmark Workflow Trio

Use these three workflows together for benchmark governance:

- `.github/workflows/product-search-benchmark-pr-check.yml`: validates benchmark tests and output contract in pull requests.
- `.github/workflows/product-search-benchmark-main-report.yml`: reports non-blocking latency trend deltas on `main`.
- `.github/workflows/product-search-benchmark-gate.yml`: enforces blocking regression thresholds for release promotion.
