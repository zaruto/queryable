<?php

declare(strict_types=1);

namespace Zaruto\Queryable\Filtering;

use Zaruto\Queryable\Contracts\FilterParserContract;
use Zaruto\Queryable\Enums\FilterOperator;
use Zaruto\Queryable\Enums\LogicalOperator;
use Zaruto\Queryable\Exceptions\InvalidFilterException;
use Zaruto\Queryable\Filtering\Ast\ConditionNode;
use Zaruto\Queryable\Filtering\Ast\GroupNode;
use Zaruto\Queryable\Filtering\Ast\Node;

final class FilterParser implements FilterParserContract
{
    private int $position = 0;

    /** @var array<int, string> */
    private array $tokens = [];

    public function parse(string $input): Node
    {
        $this->position = 0;
        $this->tokens = $this->tokenize($input);

        if ($this->tokens === []) {
            throw new InvalidFilterException('Empty filter expression.');
        }

        $node = $this->parseExpression();

        if ($this->peek() !== null) {
            throw new InvalidFilterException('Unexpected token: '.$this->peek());
        }

        return $node;
    }

    /** @return array<int, string> */
    private function tokenize(string $input): array
    {
        $pattern = '/\s*(\(|\)|and|or|not\s+in|gte|lte|starts_with|contains|like|eq|ne|gt|lt|in|[a-zA-Z_\.]+|"(?:[^"\\\\]|\\\\.)*"|[^,\s()]+)\s*/i';
        preg_match_all($pattern, $input, $matches);

        $rawTokens = $matches[1];

        return array_values(array_filter(array_map('trim', $rawTokens), static fn (string $token): bool => $token !== ''));
    }

    private function parseExpression(): Node
    {
        $node = $this->parseTerm();

        while (($token = $this->peek()) !== null) {
            $logical = LogicalOperator::fromString($token);
            if ($logical === null) {
                break;
            }

            $this->next();
            $right = $this->parseTerm();
            $node = new GroupNode($logical, $node, $right);
        }

        return $node;
    }

    private function parseTerm(): Node
    {
        if ($this->peek() === '(') {
            $this->next();
            $node = $this->parseExpression();
            if ($this->next() !== ')') {
                throw new InvalidFilterException('Expected closing parenthesis.');
            }

            return $node;
        }

        return $this->parseCondition();
    }

    private function parseCondition(): ConditionNode
    {
        $field = $this->next();
        $operatorToken = $this->next();
        $value = $this->next();

        if ($field === null || $operatorToken === null || $value === null) {
            throw new InvalidFilterException('Incomplete filter condition.');
        }

        $operator = FilterOperator::fromString($operatorToken);

        if ($operator === null) {
            throw new InvalidFilterException('Invalid filter operator: '.$operatorToken);
        }

        if (preg_match('/^("|\')(.*)\1$/', $value, $matches) === 1) {
            $value = $matches[2];
        }

        return new ConditionNode($field, $operator, $value);
    }

    private function peek(): ?string
    {
        return $this->tokens[$this->position] ?? null;
    }

    private function next(): ?string
    {
        return $this->tokens[$this->position++] ?? null;
    }
}
