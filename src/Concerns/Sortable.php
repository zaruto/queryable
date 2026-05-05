<?php

declare(strict_types=1);

namespace Zaruto\Queryable\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Zaruto\Queryable\Support\AttributeConfigResolver;

trait Sortable
{
    public function scopeSort(Builder $query, ?string $sortBy = null, string $direction = 'asc'): Builder
    {
        if ($sortBy === null || $sortBy === '') {
            return $query;
        }

        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';
        $sortableFields = $this->resolveSortableFields();

        if (in_array($sortBy, $sortableFields, true)) {
            return $query->orderBy($sortBy, $direction);
        }

        return $query;
    }

    /** @return array<int, string> */
    private function resolveSortableFields(): array
    {
        $attributeFields = AttributeConfigResolver::resolve(static::class)->sortable;

        if ($attributeFields !== []) {
            return $attributeFields;
        }

        if (method_exists(static::class, 'sortable')) {
            /** @var array<int, string> $fields */
            $fields = static::sortable();

            return $fields;
        }

        return [];
    }
}
