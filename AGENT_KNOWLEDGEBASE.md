# Agent Knowledge Base

> **This file has been consolidated.** All authoritative knowledge is maintained in these documents:

| Document | Purpose |
|----------|---------|
| [SKILL.md](SKILL.md) | Canonical system specification — architecture, module schemas, business rules, database standards |
| [AGENT.md](AGENT.md) | Agent operational guide — workflows, decision trees, event catalog, checklists, testing/security standards |
| [README.md](README.md) | Project overview, setup instructions, module status |
| [CLAUDE.md](CLAUDE.md) | Concise AI agent instructions |
| [.github/copilot-instructions.md](.github/copilot-instructions.md) | GitHub Copilot-specific instructions |

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
- **Monetary values**: `DECIMAL(20,6)` — never `float`
- **12 registered ServiceProviders** in `bootstrap/providers.php`
- **66 module migrations** + 3 framework migrations; cross-module FKs deferred to `add_remaining_foreign_keys.php`
