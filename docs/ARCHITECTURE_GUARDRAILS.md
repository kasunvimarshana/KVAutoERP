# Architecture Guardrails

This file documents enforceable module-boundary rules for the modular ERP codebase.

## Purpose

- Keep Core limited to foundational abstractions.
- Prevent cross-module infrastructure coupling.
- Preserve clean architecture layering over time.

## Enforced Rules

1. Core value objects are minimal:
   - `app/Modules/Core/Domain/ValueObjects` must contain only `ValueObject.php` (plus `.gitkeep`).
   - Domain-specific value objects belong to their owning module.

2. Domain layer is framework and infrastructure agnostic:
   - Files under any `Domain` directory must not import `...\Infrastructure\...` namespaces.

3. Application layer stays independent from infrastructure:
   - Files under `Application` must not import `...\\Infrastructure\\...` namespaces.
   - Domain and Application layers integrate through contracts, DTOs, and events.

## Automated Validation

The following test enforces these rules:

- `tests/Unit/Architecture/ModuleBoundaryGuardrailsTest.php`

Run all tests:

```bash
php artisan test
```

Run only guardrail tests:

```bash
php artisan test tests/Unit/Architecture/ModuleBoundaryGuardrailsTest.php
```

## Refactor Guidance

- If guardrail tests fail, move classes to the correct module/layer instead of adding exceptions.
- Keep Core generic and reusable. Do not place module-specific business semantics in Core.
- Prefer interface-driven dependencies in `Domain` and `Application` layers.
