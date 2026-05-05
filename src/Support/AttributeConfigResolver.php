<?php

declare(strict_types=1);

namespace Zaruto\Queryable\Support;

use ReflectionClass;
use Zaruto\Queryable\Attributes\QueryableFilterable;
use Zaruto\Queryable\Attributes\QueryableSearchable;
use Zaruto\Queryable\Attributes\QueryableSortable;

final class AttributeConfigResolver
{
    /** @var array<class-string, ModelQueryableConfig> */
    private static array $cache = [];

    /** @param class-string $modelClass */
    public static function resolve(string $modelClass): ModelQueryableConfig
    {
        if (isset(self::$cache[$modelClass])) {
            return self::$cache[$modelClass];
        }

        $reflection = new ReflectionClass($modelClass);

        $searchable = $reflection->getAttributes(QueryableSearchable::class)[0] ?? null;
        $filterable = $reflection->getAttributes(QueryableFilterable::class)[0] ?? null;
        $sortable = $reflection->getAttributes(QueryableSortable::class)[0] ?? null;

        $config = new ModelQueryableConfig(
            searchable: $searchable?->newInstance()->fields ?? [],
            filterable: $filterable?->newInstance()->fields ?? [],
            sortable: $sortable?->newInstance()->fields ?? [],
        );

        self::$cache[$modelClass] = $config;

        return $config;
    }
}
