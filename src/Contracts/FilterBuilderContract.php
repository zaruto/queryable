<?php

declare(strict_types=1);

namespace Zaruto\Queryable\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Zaruto\Queryable\Filtering\Ast\Node;

interface FilterBuilderContract
{
    public function apply(Builder $query, Node $node, string $boolean = 'and'): void;
}
