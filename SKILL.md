# SKILL.md

Reusable AI skill for integrating `zaruto/queryable` into Laravel APIs.

## When To Use This Skill

Use this skill when a user wants to add safe `search`, `filter`, and/or `sort` query capabilities to Eloquent models or API endpoints.

## Inputs To Collect

Gather these before generating code:

- Target model class (for example `Customer`).
- Searchable fields and search mode per field.
- Filterable fields and allowed operators per field.
- Relation-dot fields needed (for example `team.name`).
- Sortable fields.
- Query parameter names (default or custom).

## Core Workflow

1. Identify model + endpoint query params.
2. Add `Searchable`, `Filterable`, `Sortable` traits.
3. Configure model using attributes first:
   - `#[QueryableSearchable([...])]`
   - `#[QueryableFilterable([...])]`
   - `#[QueryableSortable([...])]`
4. If attributes are not desired, provide static method fallback:
   - `searchable(): array`
   - `filters(): array`
   - `sortable(): array`
5. Chain scopes in endpoint/repository:
   - `->search(...)`
   - `->filter()`
   - `->sort(...)`
6. Validate strict-mode compatibility:
   - every filter field is allowlisted
   - every used operator is allowed for that field
7. Provide concrete URL examples and expected behavior.

## Output Template

Return results in this shape:

1. Model snippet (traits + attributes or fallback methods).
2. Controller/repository snippet with chained query scopes.
3. 3-6 sample query URLs (`search`, `filter`, `sort`, combined).
4. Edge-case notes:
   - invalid field/operator behavior
   - parenthesis/syntax errors
   - direction normalization for sorting

## Safety Rules

- Never propose unrestricted free-form filtering over arbitrary DB fields.
- Never bypass allowlists in examples.
- Always call out strict-mode error behavior for invalid filter clauses.
- Keep package identity accurate: `zaruto/queryable`, namespace `Zaruto\\Queryable`.

## Operator Reference

Supported operators:

- `eq`, `ne`, `gt`, `gte`, `lt`, `lte`
- `like`, `contains`, `starts_with`
- `in`, `not in`

## Quality Checks Before Final Answer

- Namespaces/imports use `Zaruto\\Queryable\\...`.
- Examples match current API signatures:
  - `scopeSearch(Builder $query, ?string $search): Builder`
  - `scopeFilter(Builder $query): Builder`
  - `applyFilters(Builder $query, Request $request): Builder`
  - `scopeSort(Builder $query, ?string $sortBy = null, string $direction = 'asc'): Builder`
- Filter grammar examples use supported syntax only.
