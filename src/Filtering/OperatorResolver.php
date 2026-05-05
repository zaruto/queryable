<?php

declare(strict_types=1);

namespace Zaruto\Queryable\Filtering;

use Illuminate\Database\Eloquent\Builder;
use Zaruto\Queryable\Contracts\OperatorResolverContract;
use Zaruto\Queryable\Enums\FilterOperator;
use Zaruto\Queryable\Exceptions\UnsupportedOperatorException;

final class OperatorResolver implements OperatorResolverContract
{
    public function apply(Builder $query, string $field, FilterOperator $operator, string $value): void
    {
        match ($operator) {
            FilterOperator::Eq => $query->where($field, '=', $value),
            FilterOperator::Ne => $query->where($field, '!=', $value),
            FilterOperator::Gt => $query->where($field, '>', $value),
            FilterOperator::Lt => $query->where($field, '<', $value),
            FilterOperator::Gte => $query->where($field, '>=', $value),
            FilterOperator::Lte => $query->where($field, '<=', $value),
            FilterOperator::Like => $query->where($field, 'like', $value),
            FilterOperator::Contains => $query->where($field, 'like', "%{$value}%"),
            FilterOperator::StartsWith => $query->where($field, 'like', "{$value}%"),
            FilterOperator::In => $query->whereIn($field, $this->explodeList($value)),
            FilterOperator::NotIn => $query->whereNotIn($field, $this->explodeList($value)),
            default => throw new UnsupportedOperatorException("Unsupported filter operator [{$operator->value}]."),
        };
    }

    /** @return array<int, string> */
    private function explodeList(string $value): array
    {
        $items = array_map(static fn (string $item): string => trim($item), explode(',', $value));

        return array_values(array_filter($items, static fn (string $item): bool => $item !== ''));
    }
}
