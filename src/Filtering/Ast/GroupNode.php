<?php

declare(strict_types=1);

namespace Zaruto\Queryable\Filtering\Ast;

use Zaruto\Queryable\Enums\LogicalOperator;

final readonly class GroupNode implements Node
{
    public function __construct(
        public LogicalOperator $operator,
        public Node $left,
        public Node $right,
    ) {}
}
