# Product Search Performance Runbook

## Purpose

This runbook standardizes how to measure, compare, and respond to Product catalog search latency across local, CI, staging, and production-like environments.

## Tools

1. Runtime benchmark command:

```bash
php artisan product:benchmark-search --tenant_id=<TENANT_ID> --warehouse_id=<WAREHOUSE_ID> --iterations=5 --per_page=25 --term="SKU-001" --term="BARCODE-001"
```

Machine-readable variant:

```bash
php artisan product:benchmark-search --tenant_id=<TENANT_ID> --warehouse_id=<WAREHOUSE_ID> --iterations=5 --per_page=25 --include_pricing=0 --format=json --term="SKU-001" --term="BARCODE-001"
```

1. Deterministic seeded benchmark test:

```bash
php ./vendor/bin/phpunit --group performance
```

1. Command behavior test (CI-safe):

```bash
php ./vendor/bin/phpunit tests/Feature/ProductSearchBenchmarkCommandTest.php
```

## Baseline Capture Process

1. Choose a stable tenant and warehouse with representative catalog size.
2. Use a fixed term set (for example: SKU term, identifier term, product-name term).
3. Use fixed command options per environment:

- `--iterations=5`
- `--per_page=25`
- `--include_pricing=0` for base search latency

1. Record command output table values: `min_ms`, `avg_ms`, `p95_ms`, `max_ms`.
1. Save baseline results as a release artifact (or in deployment notes).

## Suggested Guard Rails

Use these as starting operational thresholds for command output; tune after collecting 3-5 release baselines.

1. Healthy:

- `p95_ms <= 250`
- `max_ms <= 500`

1. Warning:

- `250 < p95_ms <= 500`
- `500 < max_ms <= 1000`

1. Critical:

- `p95_ms > 500`
- `max_ms > 1000`

For large tenants (very high SKU counts), set tenant-specific thresholds and track trend direction over absolute values.

## Release-Over-Release Comparison

For each release candidate:

1. Run the command using the same term set and options as the previous release baseline.
2. Compare `p95_ms` and `max_ms` term-by-term.
3. Flag regression if either is true:

- `p95_ms` increased by more than 20%
- `max_ms` increased by more than 30%

1. If regression is flagged, block promotion until root cause is reviewed.

## Incident Response Playbook

When search latency crosses the critical threshold:

1. Confirm reproducibility:

- Re-run benchmark command 2-3 times.
- Verify DB health and host saturation.

1. Fast checks:

- Confirm expected index migrations are applied.
- Verify tenant scope and warehouse filters are present in query input.
- Check recent changes affecting Product search repository, stock summary joins, or identifier search.

1. Deep checks:

- Use query plan tools (`EXPLAIN`) for slow terms (identifier, lot/serial, attribute-value).
- Confirm index usage on:

- `product_identifiers` (tenant/activity/value path)
- `batches` (tenant/batch_number, tenant/lot_number)
- `serials` (tenant/product/variant and tenant/batch)
- `attribute_values` and `variant_attribute_values` lookup path

1. Mitigation options:

- Roll back the offending release.
- Temporarily reduce expensive payload options for heavy screens.
- Run benchmark again after mitigation and document impact.

## Environment Notes

1. Local development:

- Command requires a configured and reachable DB.
- If DB is unavailable, command exits with clear diagnostics.

1. CI:

- Prefer deterministic test harness (`--group performance`) and command test.
- Do not gate CI on strict runtime millisecond thresholds from shared runners.
- Use `--format=json` and parse output for dashboard ingestion and automated release checks.
- Use `.github/workflows/product-search-benchmark-gate.yml` as a ready-to-run GitHub Actions gate.
- Use `.github/workflows/product-search-benchmark-pr-check.yml` for lightweight PR contract checks (test + JSON schema contract) without environment-sensitive latency gates.
- Keep baseline terms and values in `docs/benchmarks/product-search-baseline.example.json` (or a copied environment-specific file).

1. Staging/production-like:

- Use runtime command with stable tenant data.
- Track trend lines over releases, not one-off samples.

Release branch automation notes:

- The benchmark gate workflow now runs automatically on `release/**` pushes.
- Configure repository variables for automated runs:

- `BENCHMARK_TENANT_ID`
- `BENCHMARK_WAREHOUSE_ID`
- Optional: `BENCHMARK_TERMS`, `BENCHMARK_ITERATIONS`, `BENCHMARK_PER_PAGE`, `BENCHMARK_BASELINE_FILE`, `BENCHMARK_P95_THRESHOLD`, `BENCHMARK_MAX_THRESHOLD`
- Default release baseline path is `docs/benchmarks/product-search-baseline.staging.example.json` unless overridden.
- For production-like gating, point `BENCHMARK_BASELINE_FILE` to `docs/benchmarks/product-search-baseline.prod-like.example.json` (or your environment-specific baseline).

Main branch reporting notes:

- `.github/workflows/product-search-benchmark-main-report.yml` runs on `main` pushes for non-blocking trend visibility.
- It publishes delta results to workflow summary and emits warnings for threshold breaches without failing the build.
- Use this for continuous monitoring; use the release gate workflow for blocking decisions.

## Benchmark Variables Setup

Set these as GitHub repository variables for automated benchmark workflows.

GitHub CLI quick setup example:

```bash
gh variable set BENCHMARK_TENANT_ID --body "777"
gh variable set BENCHMARK_WAREHOUSE_ID --body "701"
gh variable set BENCHMARK_TERMS --body "SKU-001,BARCODE-001,PRODUCT-NAME"
gh variable set BENCHMARK_ITERATIONS --body "5"
gh variable set BENCHMARK_PER_PAGE --body "25"
gh variable set BENCHMARK_BASELINE_FILE --body "docs/benchmarks/product-search-baseline.staging.example.json"
gh variable set BENCHMARK_P95_THRESHOLD --body "20"
gh variable set BENCHMARK_MAX_THRESHOLD --body "30"
```

Required:

- `BENCHMARK_TENANT_ID`
- `BENCHMARK_WAREHOUSE_ID`

Optional:

- `BENCHMARK_TERMS` (comma-separated)
- `BENCHMARK_ITERATIONS`
- `BENCHMARK_PER_PAGE`
- `BENCHMARK_BASELINE_FILE`
- `BENCHMARK_P95_THRESHOLD`
- `BENCHMARK_MAX_THRESHOLD`
- `BENCHMARK_ARTIFACT_RETENTION_DAYS`

### Staging Example Values

- `BENCHMARK_TENANT_ID=777`
- `BENCHMARK_WAREHOUSE_ID=701`
- `BENCHMARK_TERMS=SKU-001,BARCODE-001,PRODUCT-NAME`
- `BENCHMARK_ITERATIONS=5`
- `BENCHMARK_PER_PAGE=25`
- `BENCHMARK_BASELINE_FILE=docs/benchmarks/product-search-baseline.staging.example.json`
- `BENCHMARK_P95_THRESHOLD=20`
- `BENCHMARK_MAX_THRESHOLD=30`
- `BENCHMARK_ARTIFACT_RETENTION_DAYS=30`

### Prod-like Example Values

- `BENCHMARK_TENANT_ID=777`
- `BENCHMARK_WAREHOUSE_ID=701`
- `BENCHMARK_TERMS=SKU-001,BARCODE-001,PRODUCT-NAME`
- `BENCHMARK_ITERATIONS=7`
- `BENCHMARK_PER_PAGE=25`
- `BENCHMARK_BASELINE_FILE=docs/benchmarks/product-search-baseline.prod-like.example.json`
- `BENCHMARK_P95_THRESHOLD=15`
- `BENCHMARK_MAX_THRESHOLD=25`
- `BENCHMARK_ARTIFACT_RETENTION_DAYS=30`

## Artifact Retention Policy

Benchmark workflows upload run artifacts for traceability:

- PR checks: `benchmark-output.json` and baseline example file.
- Main report: `current-benchmark.json`, baseline file, and `benchmark-comparison-summary.txt`.
- Release gate: `current-benchmark.json`, baseline file, and `benchmark-comparison-summary.txt`.

Retention defaults:

- PR workflow default retention: 14 days.
- Main and release workflows default retention: 30 days.

To override retention for all benchmark workflows, set repository variable:

- `BENCHMARK_ARTIFACT_RETENTION_DAYS=<days>`

## Baseline JSON Field Guide

Baseline files are used by benchmark workflows to calculate release-over-release deltas.

Expected shape:

```json
{
  "status": "success",
  "results": [
    {
      "term": "SKU-001",
      "p95_ms": 142.5,
      "max_ms": 201.2
    }
  ]
}
```

Field meaning:

- `status`: should be `success`.
- `results`: array of per-term benchmark rows.
- `term`: search token used in benchmark execution.
- `p95_ms`: p95 latency baseline in milliseconds.
- `max_ms`: max latency baseline in milliseconds.

Safety checks before commit:

1. Ensure each benchmark term configured in `BENCHMARK_TERMS` has a matching `results[].term` entry.
1. Keep numeric values as numbers (not quoted strings).
1. Use the same term set and benchmark options that release workflows will execute.
1. Prefer environment-specific files for staging and prod-like thresholds.

## Benchmark Workflow Troubleshooting

1. Benchmark step is skipped unexpectedly:

- Confirm `BENCHMARK_TENANT_ID` and `BENCHMARK_WAREHOUSE_ID` are set as repository variables.
- Confirm workflow is running on expected branches and matching path filters.

1. Baseline comparison is skipped:

- Check `BENCHMARK_BASELINE_FILE` points to a committed file path.
- Validate the baseline JSON contains a `results` array with `term`, `p95_ms`, and `max_ms`.

1. Benchmark command fails with DB connectivity notice:

- Verify `DB_*` secrets are configured for the target environment.
- Validate network reachability and credentials for the configured host.

1. Regressions are reported on main but build remains green:

- This is expected for `.github/workflows/product-search-benchmark-main-report.yml` (non-blocking by design).
- For blocking behavior, use `.github/workflows/product-search-benchmark-gate.yml` on release branches.

## Operational Checklist

1. Before release:

- Run performance test group.
- Run benchmark command in staging with baseline term set.
- Compare `p95_ms` and `max_ms` to previous baseline.

1. After release:

- Re-run benchmark command.
- Log measurements in release notes.
- Open follow-up task if warning or critical thresholds are exceeded.

## Reporting Template

Use `docs/PRODUCT_SEARCH_BENCHMARK_REPORT_TEMPLATE.md` to capture release-over-release measurements in a consistent format.
