# Queryable Roadmap

This file mirrors GitHub Project roadmap items so planning context stays visible in-repo.

## Project Metadata

- Project: `Queryable Roadmap`
- Visibility: `Public`
- Canonical board: https://github.com/users/zaruto/projects
- Fields:
  - `Status`: Backlog, Planned, In Progress, Done
  - `Wave`: Wave 1 DX, Wave 2 Power, Wave 3 Performance
  - `Priority`: P0, P1, P2
  - `Target Release`: v1.1, v1.2, v1.3+

## Backlog

### Wave 1 DX

#### Pipeline helper (`applyQueryable(...)`)

- Priority: `P0`
- Status: `Backlog`
- Target release: `v1.1`

Acceptance criteria:

- Add one helper entrypoint to apply search/filter/sort in one call.
- Keep existing scope API unchanged and chainable.
- Support configured parameter names from `config/queryable.php`.
- Add usage docs and integration tests.

#### Standardized API error response helper/docs

- Priority: `P1`
- Status: `Backlog`
- Target release: `v1.1`

Acceptance criteria:

- Provide documented error payload shape for invalid filter/search/sort usage.
- Include helper pattern for `InvalidFilterException` to 422 responses.
- Add request/response examples in README.

#### Integration recipes + docs examples

- Priority: `P1`
- Status: `Backlog`
- Target release: `v1.1`

Acceptance criteria:

- Add cookbook-style examples for common model/controller patterns.
- Include customer/admin style examples, pagination, and relation fields.
- Keep examples aligned with current package behavior.

#### Context-aware field allowlists (`customer` vs `admin`)

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

### Wave 2 Power

#### Custom operator registration

- Priority: `P1`
- Status: `Backlog`
- Target release: `v1.2`

Acceptance criteria:

- Allow registering additional operators without editing core package code.
- Keep built-in operators as default behavior.
- Add tests for registration and operator execution paths.

#### Multi-column sort expression support

- Priority: `P1`
- Status: `Backlog`
- Target release: `v1.2`

Acceptance criteria:

- Support sort expressions like `sort=name,-created_at`.
- Enforce sortable allowlist for every requested field.
- Preserve current single-field `scopeSort` behavior for backward compatibility.

#### Relation-aware sorting

- Priority: `P2`
- Status: `Backlog`
- Target release: `v1.2`

Acceptance criteria:

- Support safe sorting by relation fields where configured.
- Document query strategy/limits and expected SQL behavior.
- Add integration tests for relation sort correctness.

### Wave 3 Performance

#### Query safety limits

- Priority: `P1`
- Status: `Backlog`
- Target release: `v1.3+`

Acceptance criteria:

- Add configurable caps for filter complexity (e.g., conditions/list size/depth).
- Return predictable validation errors when limits are exceeded.

#### Typed value casting

- Priority: `P1`
- Status: `Backlog`
- Target release: `v1.3+`

Acceptance criteria:

- Allow per-field value casting before operator application.
- Cover common scalar/date casting scenarios with tests.

#### Query debug metadata

- Priority: `P2`
- Status: `Backlog`
- Target release: `v1.3+`

Acceptance criteria:

- Expose optional debug metadata about resolved fields/operators/sorts.
- Keep disabled by default and non-breaking for existing consumers.
