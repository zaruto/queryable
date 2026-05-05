<?php

declare(strict_types=1);

namespace Zaruto\Queryable\Enums;

enum FilterOperator: string
{
    case Eq = 'eq';
    case Ne = 'ne';
    case Gt = 'gt';
    case Lt = 'lt';
    case Gte = 'gte';
    case Lte = 'lte';
    case Like = 'like';
    case Contains = 'contains';
    case StartsWith = 'starts_with';
    case In = 'in';
    case NotIn = 'not in';

    public static function fromString(string $value): ?self
    {
        return self::tryFrom(strtolower(trim($value)));
    }
}
