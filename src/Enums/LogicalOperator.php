<?php

declare(strict_types=1);

namespace Zaruto\Queryable\Enums;

enum LogicalOperator: string
{
    case And = 'and';
    case Or = 'or';

    public static function fromString(string $value): ?self
    {
        return self::tryFrom(strtolower(trim($value)));
    }
}
