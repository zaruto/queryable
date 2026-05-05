# Queryable Roadmap

This file mirrors roadmap backlog items so planning context stays visible in-repo.

## Backlog

### Context-aware field allowlists (`customer` vs `admin`)

- Wave: `Wave 1 DX`
- Priority: `P1`
- Status: `Backlog`
- Target release: `v1.1`

Description:
Enable `search`, `filter`, and `sort` to resolve field sets by runtime context (default: authenticated user role), so different roles can use different allowlisted fields.
If context-specific config is missing, fallback to model default field sets.

Acceptance criteria:

- Add optional model methods:
  - `searchableByContext()`
  - `filtersByContext()`
  - `sortableByContext()`
- Default context resolver uses request user role.
- Existing API signatures remain unchanged.
- Fallback to default `searchable/filters/sortable` when context mapping is missing.
- Strict-mode validation applies to the resolved context field set.
- Add tests for admin/customer field separation and fallback behavior.
- Add README docs with usage examples.

Assumptions:

- Context source is role-based by default.
- Change is additive and backward-compatible.
- No parser/operator grammar changes are required.
