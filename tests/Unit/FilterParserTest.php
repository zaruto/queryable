<?php

declare(strict_types=1);

use Zaruto\Queryable\Enums\LogicalOperator;
use Zaruto\Queryable\Filtering\Ast\GroupNode;
use Zaruto\Queryable\Filtering\FilterParser;

it('parses grouped expressions', function (): void {
    $parser = new FilterParser;
    $ast = $parser->parse('(status eq active and score gte 50) or team.name like "Ops"');

    expect($ast)->toBeInstanceOf(GroupNode::class)
        ->and($ast->operator)->toBe(LogicalOperator::Or);
});
