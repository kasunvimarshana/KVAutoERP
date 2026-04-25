# Product Search Benchmark Report Template

Use this template to record Product search performance before and after each release.

## Metadata

- Release: <RELEASE_TAG_OR_COMMIT>
- Environment: <LOCAL|CI|STAGING|PROD-LIKE>
- Date: <YYYY-MM-DD>
- Operator: <NAME>
- Tenant ID: <TENANT_ID>
- Warehouse ID: <WAREHOUSE_ID>
- Command:

```bash
php artisan product:benchmark-search --tenant_id=<TENANT_ID> --warehouse_id=<WAREHOUSE_ID> --iterations=5 --per_page=25 --include_pricing=0 --term="<TERM1>" --term="<TERM2>" --term="<TERM3>"
```

- JSON command (optional, for CI parsing):

```bash
php artisan product:benchmark-search --tenant_id=<TENANT_ID> --warehouse_id=<WAREHOUSE_ID> --iterations=5 --per_page=25 --include_pricing=0 --format=json --term="<TERM1>" --term="<TERM2>" --term="<TERM3>"
```

## Current Release Measurements

| Term | Matches | Min (ms) | Avg (ms) | P95 (ms) | Max (ms) | Status |
|------|---------|----------|----------|----------|----------|--------|
| <TERM1> | <N> | <MIN> | <AVG> | <P95> | <MAX> | <HEALTHY|WARNING|CRITICAL> |
| <TERM2> | <N> | <MIN> | <AVG> | <P95> | <MAX> | <HEALTHY|WARNING|CRITICAL> |
| <TERM3> | <N> | <MIN> | <AVG> | <P95> | <MAX> | <HEALTHY|WARNING|CRITICAL> |

## Previous Release Baseline

| Term | P95 (ms) | Max (ms) |
|------|----------|----------|
| <TERM1> | <P95_OLD> | <MAX_OLD> |
| <TERM2> | <P95_OLD> | <MAX_OLD> |
| <TERM3> | <P95_OLD> | <MAX_OLD> |

## Delta Analysis

| Term | P95 Delta % | Max Delta % | Regression Flag |
|------|-------------|-------------|-----------------|
| <TERM1> | <DELTA_P95> | <DELTA_MAX> | <YES|NO> |
| <TERM2> | <DELTA_P95> | <DELTA_MAX> | <YES|NO> |
| <TERM3> | <DELTA_P95> | <DELTA_MAX> | <YES|NO> |

Regression guidance from runbook:
- Flag if P95 increases by more than 20%.
- Flag if Max increases by more than 30%.

## Gate Decision

- Decision: <PASS|WARN|BLOCK>
- Reason: <SHORT_SUMMARY>

## Follow-up Actions

1. <ACTION_ITEM_1>
2. <ACTION_ITEM_2>
3. <ACTION_ITEM_3>

## Appendix (Raw Command Output)

Paste raw command table output here for auditability.
