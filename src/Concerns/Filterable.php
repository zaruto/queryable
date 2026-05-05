<?php

declare(strict_types=1);

namespace Zaruto\Queryable\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Zaruto\Queryable\Enums\FilterOperator;
use Zaruto\Queryable\Exceptions\InvalidFilterException;
use Zaruto\Queryable\Filtering\Ast\ConditionNode;
use Zaruto\Queryable\Filtering\Ast\GroupNode;
use Zaruto\Queryable\Filtering\Ast\Node;
use Zaruto\Queryable\Filtering\FilterBuilder;
use Zaruto\Queryable\Filtering\FilterParser;
use Zaruto\Queryable\Support\AttributeConfigResolver;

trait Filterable
{
    public function scopeFilter(Builder $query): Builder
    {
        $request = request();

        return static::applyFilters($query, $request instanceof Request ? $request : new Request);
    }

    public static function applyFilters(Builder $query, Request $request): Builder
    {
        $filterKey = (string) config('queryable.parameters.filter', 'filter');
        $filterString = (string) $request->query($filterKey, '');

        if ($filterString === '') {
            return $query;
        }

        $allowedFilters = static::resolveFilterableFields();

        if ($allowedFilters === []) {
            return $query;
        }

        $parser = new FilterParser;
        $ast = $parser->parse($filterString);

        if ((bool) config('queryable.strict_mode', true)) {
            static::validateAppliedFilters($ast, $allowedFilters);
        }

        (new FilterBuilder)->apply($query, $ast);

        return $query;
    }

    /** @return array<string, array<int, string>> */
    private static function resolveFilterableFields(): array
    {
        $attributeFields = AttributeConfigResolver::resolve(static::class)->filterable;

        if ($attributeFields !== []) {
            return $attributeFields;
        }

        if (method_exists(static::class, 'filters')) {
            /** @var array<string, array<int, string>> $fields */
            $fields = static::filters();

            return $fields;
        }

        return [];
    }

    /** @param array<string, array<int, string>> $allowedFilters */
    private static function validateAppliedFilters(Node $node, array $allowedFilters): void
    {
        if ($node instanceof ConditionNode) {
            if (! array_key_exists($node->field, $allowedFilters)) {
                throw new InvalidFilterException("Invalid filter field [{$node->field}].");
            }

            $allowedOps = array_map(
                static fn (string $operator): string => strtolower(trim($operator)),
                $allowedFilters[$node->field]
            );

            if (! in_array($node->operator->value, $allowedOps, true)) {
                throw new InvalidFilterException("Invalid operator [{$node->operator->value}] for field [{$node->field}].");
            }

            if (FilterOperator::fromString($node->operator->value) === null) {
                throw new InvalidFilterException("Unsupported operator [{$node->operator->value}].");
            }

            return;
        }

        if ($node instanceof GroupNode) {
            static::validateAppliedFilters($node->left, $allowedFilters);
            static::validateAppliedFilters($node->right, $allowedFilters);
        }
    }
}
