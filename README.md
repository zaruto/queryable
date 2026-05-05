# Laravel Queryable

Attribute-first, type-safe query composition for Laravel Eloquent models.

`zaruto/queryable` helps API teams safely expose `search`, `filter`, and `sort` query params without allowing arbitrary field/operator access.

## Compatibility

- PHP: `8.3`, `8.4`, `8.5`
- Laravel: `12.x`, `13.x`

## Installation

```bash
composer require zaruto/queryable
```

Publish config (optional):

```bash
php artisan vendor:publish --tag="queryable-config"
```

## Quickstart (Under 5 Minutes)

### 1. Add traits + attributes to a model

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Zaruto\Queryable\Attributes\QueryableFilterable;
use Zaruto\Queryable\Attributes\QueryableSearchable;
use Zaruto\Queryable\Attributes\QueryableSortable;
use Zaruto\Queryable\Concerns\Filterable;
use Zaruto\Queryable\Concerns\Searchable;
use Zaruto\Queryable\Concerns\Sortable;

#[QueryableSearchable([
    'name' => 'like',
    'email' => 'starts_with',
    'team.name' => 'like',
])]
#[QueryableFilterable([
    'status' => ['eq', 'ne', 'in', 'not in'],
    'score' => ['gt', 'gte', 'lt', 'lte'],
    'name' => ['like', 'contains', 'starts_with'],
    'team.name' => ['eq', 'like'],
])]
#[QueryableSortable(['id', 'name', 'created_at'])]
class Customer extends Model
{
    use Searchable;
    use Filterable;
    use Sortable;
}
```

### 2. Chain query scopes in controller/repository

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

final class CustomerIndexController
{
    public function __invoke(Request $request)
    {
        $query = Customer::query()
            ->search((string) $request->query('search', ''))
            ->filter()
            ->sort(
                $request->query('sort_by'),
                (string) $request->query('direction', 'asc')
            );

        return $query->paginate(25);
    }
}
```

### 3. Call endpoint with query params

```text
GET /api/customers?search=ali
GET /api/customers?filter=status%20eq%20active
GET /api/customers?sort_by=created_at&direction=desc
GET /api/customers?search=ali&filter=score%20gte%2050&sort_by=name
```

## Public API (Stable Surface)

- `scopeSearch(Builder $query, ?string $search): Builder`
- `scopeFilter(Builder $query): Builder`
- `applyFilters(Builder $query, Request $request): Builder`
- `scopeSort(Builder $query, ?string $sortBy = null, string $direction = 'asc'): Builder`

## Configuration

`config/queryable.php`:

```php
return [
    'strict_mode' => true,

    'parameters' => [
        'search' => 'search',
        'filter' => 'filter',
        'sort_by' => 'sort_by',
        'direction' => 'direction',
    ],
];
```

### Notes

- `strict_mode=true` validates filter fields/operators against your allowlist.
- If you rename parameter keys, use the same keys in your clients.

## Attribute-First With Method Fallback

The package resolves model config in this order:

1. Attributes (`#[QueryableSearchable]`, `#[QueryableFilterable]`, `#[QueryableSortable]`)
2. Static methods (`searchable()`, `filters()`, `sortable()`)

Use method fallback when you need dynamic configuration or gradual migration.

### Method fallback example

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Zaruto\Queryable\Concerns\Filterable;
use Zaruto\Queryable\Concerns\Searchable;
use Zaruto\Queryable\Concerns\Sortable;

class Order extends Model
{
    use Searchable;
    use Filterable;
    use Sortable;

    public static function searchable(): array
    {
        return [
            'number' => 'starts_with',
            'customer.name' => 'like',
        ];
    }

    public static function filters(): array
    {
        return [
            'status' => ['eq', 'ne', 'in'],
            'total' => ['gt', 'gte', 'lt', 'lte'],
            'customer.name' => ['like'],
        ];
    }

    public static function sortable(): array
    {
        return ['id', 'number', 'created_at'];
    }
}
```

## Filter Grammar Reference

Queryable uses a token parser and supports grouped boolean expressions.

### Grammar shape

```text
condition := field operator value
group     := (expression)
expression := condition (and|or condition|group)*
```

### Supported operators

| Operator | Meaning | Example filter snippet |
|---|---|---|
| `eq` | equals | `status eq active` |
| `ne` | not equals | `status ne archived` |
| `gt` | greater than | `score gt 80` |
| `gte` | greater or equal | `score gte 80` |
| `lt` | lower than | `score lt 80` |
| `lte` | lower or equal | `score lte 80` |
| `like` | raw SQL `like` value | `name like ali%` |
| `contains` | `%value%` | `name contains ali` |
| `starts_with` | `value%` | `email starts_with admin` |
| `in` | in comma list | `status in active,pending` |
| `not in` | not in comma list | `status not in blocked,deleted` |

### Boolean and grouping

- `and`
- `or`
- Parentheses `(...)`

Example:

```text
filter=(status eq active and score gte 50) or team.name like "Ops%"
```

## Query URL Examples

- `GET /api/customers?filter=status%20eq%20active`
- `GET /api/customers?filter=score%20gte%2050%20and%20score%20lt%2090`
- `GET /api/customers?filter=team.name%20like%20Ops%25`
- `GET /api/customers?filter=status%20in%20active,pending`
- `GET /api/customers?filter=(status%20eq%20active%20or%20status%20eq%20pending)%20and%20score%20gt%2060`

## Relation Fields

Use dot notation for relation fields:

- Search: `team.name`
- Filter: `team.name`

Current behavior:

- Filter relation handling splits on first dot (`relation.field`).
- Search supports nested relation path style via dot notation keys.

## Sorting Behavior

- Only allowlisted fields are sortable.
- Direction normalization:
  - `desc` stays `desc`
  - any other value becomes `asc`

Examples:

- `GET /api/customers?sort_by=name&direction=asc`
- `GET /api/customers?sort_by=created_at&direction=desc`
- `GET /api/customers?sort_by=id&direction=INVALID` -> uses `asc`

## Strict Mode and Errors

When `strict_mode=true`:

- Unknown filter field throws `InvalidFilterException`.
- Disallowed operator for an allowed field throws `InvalidFilterException`.
- Invalid/incomplete syntax throws `InvalidFilterException` from parser.

Example invalid requests:

- `filter=unknown eq 1`
- `filter=status between active,pending` (unsupported operator)
- `filter=(status eq active` (missing closing `)`)

## End-to-End Example

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CustomerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $customers = Customer::query()
            ->search((string) $request->query('search', ''))
            ->filter()
            ->sort(
                $request->query('sort_by'),
                (string) $request->query('direction', 'asc')
            )
            ->paginate((int) $request->query('per_page', 25));

        return response()->json($customers);
    }
}
```

## Testing and Development

```bash
composer test
composer analyse
composer format
```

CI workflow coverage:

- tests (`.github/workflows/tests.yml`)
- lint (`.github/workflows/lint.yml`)
- static analysis (`.github/workflows/static-analysis.yml`)

## Pre-Tag Release Gate

Run this before creating any new release tag:

```bash
composer run release:gate
```

This runs, in order:

1. `composer install`
2. `./vendor/bin/pint --test`
3. `./vendor/bin/phpstan analyse --error-format=table`
4. `./vendor/bin/pest --ci`

For additional Laravel matrix spot-checks (recommended for release candidates/finals):

```bash
composer run release:gate:matrix
```

This additionally runs sequential checks for:

- Laravel `12.*` + Testbench `^10.0`
- Laravel `13.*` + Testbench `^11.0`

Tagging rule: only create/push a tag if the gate passes and the working tree is clean.

## Roadmap (Concise)

- Custom operator registration.
- Relation-aware sorting.
- Multi-column sort expressions.
- Request helper/pipeline utilities.

## License

MIT
