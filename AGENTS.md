# AGENTS.md

Guidance for AI coding assistants working in `zaruto/queryable`.

## Primary Goals

- Preserve the public query API and model integration behavior.
- Keep strict typing and Laravel-conventional code style.
- Prevent unsafe query exposure (allowlists must remain authoritative).
- Keep parser grammar behavior stable unless an explicit breaking change is requested.

## Required Conventions

- Use `declare(strict_types=1);` in all PHP files.
- Prefer explicit types for params, returns, and properties.
- Follow Laravel conventions for traits/scopes and naming.
- Avoid silent failures for invalid operators/syntax in strict-mode paths.

## Compatibility Targets

- PHP: `8.3` to `8.5`
- Laravel: `12.x` and `13.x`

Do not introduce dependencies or syntax that violate these ranges.

## Critical Behavior Guardrails

When editing parser/builder/traits:

- `scopeSearch(Builder $query, ?string $search): Builder` must stay chainable and non-breaking.
- `scopeFilter(Builder $query): Builder` and `applyFilters(Builder $query, Request $request): Builder` must remain the filtering entry points.
- `scopeSort(Builder $query, ?string $sortBy = null, string $direction = 'asc'): Builder` must continue allowlist-only sorting.
- Attribute-first config resolution with method fallback must stay intact.
- `strict_mode=true` must continue to enforce field/operator validation.

## Query Grammar and Operator Rules

- Supported operators: `eq`, `ne`, `gt`, `gte`, `lt`, `lte`, `like`, `contains`, `starts_with`, `in`, `not in`.
- Grouping via `and` / `or` and parentheses must remain supported.
- Changes to operator behavior or grammar require:
  - README updates
  - parser/builder tests
  - changelog entry

## Editing Safety Checklist

Before finalizing changes:

- Confirm README examples still match runtime behavior.
- Confirm namespaces and package name stay `Zaruto\\Queryable` and `zaruto/queryable`.
- Confirm no undocumented public API signature changes.
- Confirm strict-mode validation paths are covered.

## Test Expectations

Minimum expectations for behavior or docs updates:

- Parser unit coverage for grammar changes.
- Integration-style coverage for search/filter/sort chaining when behavior changes.
- `composer test` passes.
- If dependencies are unavailable, state what was not run.

## PR Checklist (Queryable Specific)

- [ ] Public API unchanged or intentionally versioned.
- [ ] Strict typing preserved.
- [ ] Operator/grammar docs updated if behavior changed.
- [ ] Added/updated tests for modified behavior.
- [ ] Backward compatibility explicitly noted for traits/config resolution.
