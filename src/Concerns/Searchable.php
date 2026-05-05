<?php

declare(strict_types=1);

namespace Zaruto\Queryable\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Zaruto\Queryable\Support\AttributeConfigResolver;

trait Searchable
{
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if ($search === null || $search === '') {
            return $query;
        }

        $fields = $this->resolveSearchableFields();

        if ($fields === []) {
            return $query;
        }

        return $query->where(function (Builder $builder) use ($search, $fields): void {
            foreach ($fields as $field => $searchType) {
                $this->applySearchOnField($builder, $field, $search, (string) $searchType);
            }
        });
    }

    /** @return array<string, string> */
    private function resolveSearchableFields(): array
    {
        $attributeFields = AttributeConfigResolver::resolve(static::class)->searchable;

        if ($attributeFields !== []) {
            return $attributeFields;
        }

        if (method_exists(static::class, 'searchable')) {
            /** @var array<string, string> $fields */
            $fields = static::searchable();

            return $fields;
        }

        return [];
    }

    private function applySearchOnField(Builder $query, string $field, string $search, string $searchType): void
    {
        if (str_contains($field, '.')) {
            $segments = explode('.', $field);
            $relation = implode('.', array_slice($segments, 0, -1));
            $relationField = end($segments);

            $query->orWhereHas($relation, function (Builder $builder) use ($relationField, $search, $searchType): void {
                $this->applySearchMode($builder, (string) $relationField, $search, $searchType);
            });

            return;
        }

        $this->applySearchMode($query, $field, $search, $searchType, true);
    }

    private function applySearchMode(Builder $query, string $field, string $search, string $searchType, bool $or = false): void
    {
        $method = $or ? 'orWhere' : 'where';

        match ($searchType) {
            'starts_with' => $query->{$method}($field, 'like', "{$search}%"),
            'ends_with' => $query->{$method}($field, 'like', "%{$search}"),
            'exact' => $query->{$method}($field, '=', $search),
            default => $query->{$method}($field, 'like', "%{$search}%"),
        };
    }
}
