# ADR 001: Use of High-Precision Numeric Types for Financial Data

## Status
Accepted

## Context
In an ERP system, financial and inventory quantity calculations must be absolutely precise. Using floating-point types (float, double) or even standard decimals with low precision can lead to rounding errors over millions of transactions, causing financial reconciliation failures.

## Decision
We will use PostgreSQL `numeric(24,8)` for all financial amounts (prices, costs, taxes, commissions) and inventory quantities.
- `24` total digits allow for extremely large values suitable for global enterprises.
- `8` decimal places ensure precision for unit costs and fractional quantities (e.g., in pharmaceutical or chemical domains).

## Consequences
- **Storage**: Slightly higher storage requirement compared to floats.
- **Performance**: Mathematical operations on `numeric` types are slightly slower than CPU-native floats but essential for accuracy.
- **Code**: All Laravel models must use `decimal:8` casting or custom high-precision math libraries (BCMath) for calculations.
