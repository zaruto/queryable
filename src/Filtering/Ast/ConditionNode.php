<?php

declare(strict_types=1);

namespace Zaruto\Queryable\Filtering\Ast;

use Zaruto\Queryable\Enums\FilterOperator;

final readonly class ConditionNode implements Node
{
    public function __construct(
        public string $field,
        public FilterOperator $operator,
        public string $value,
    ) {}
}
