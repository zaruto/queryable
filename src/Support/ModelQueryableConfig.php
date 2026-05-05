<?php

declare(strict_types=1);

namespace Zaruto\Queryable\Support;

final readonly class ModelQueryableConfig
{
    /**
     * @param  array<string, string>  $searchable
     * @param  array<string, array<int, string>>  $filterable
     * @param  array<int, string>  $sortable
     */
    public function __construct(
        public array $searchable,
        public array $filterable,
        public array $sortable,
    ) {}
}
