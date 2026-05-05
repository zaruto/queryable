<?php

declare(strict_types=1);

namespace Zaruto\Queryable\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Zaruto\Queryable\Enums\FilterOperator;

interface OperatorResolverContract
{
    public function apply(Builder $query, string $field, FilterOperator $operator, string $value): void;
}
