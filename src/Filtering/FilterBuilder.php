<?php

declare(strict_types=1);

namespace Zaruto\Queryable\Filtering;

use Illuminate\Database\Eloquent\Builder;
use Zaruto\Queryable\Contracts\FilterBuilderContract;
use Zaruto\Queryable\Contracts\OperatorResolverContract;
use Zaruto\Queryable\Enums\LogicalOperator;
use Zaruto\Queryable\Filtering\Ast\ConditionNode;
use Zaruto\Queryable\Filtering\Ast\GroupNode;
use Zaruto\Queryable\Filtering\Ast\Node;

final class FilterBuilder implements FilterBuilderContract
{
    public function __construct(private readonly OperatorResolverContract $operatorResolver = new OperatorResolver) {}

    public function apply(Builder $query, Node $node, string $boolean = 'and'): void
    {
        if ($node instanceof ConditionNode) {
            $this->applyCondition($query, $node, $boolean);

            return;
        }

        if ($node instanceof GroupNode) {
            $method = $boolean === LogicalOperator::Or->value ? 'orWhere' : 'where';

            $query->{$method}(function (Builder $inner) use ($node): void {
                $this->apply($inner, $node->left);
                $this->apply($inner, $node->right, $node->operator->value);
            });
        }
    }

    private function applyCondition(Builder $query, ConditionNode $node, string $boolean): void
    {
        $method = $boolean === LogicalOperator::Or->value ? 'orWhere' : 'where';

        if (str_contains($node->field, '.')) {
            [$relation, $column] = explode('.', $node->field, 2);

            $query->{$method}(function (Builder $inner) use ($relation, $column, $node): void {
                $inner->whereHas($relation, function (Builder $relationQuery) use ($column, $node): void {
                    $this->operatorResolver->apply($relationQuery, $column, $node->operator, $node->value);
                });
            });

            return;
        }

        $query->{$method}(function (Builder $inner) use ($node): void {
            $this->operatorResolver->apply($inner, $node->field, $node->operator, $node->value);
        });
    }
}
