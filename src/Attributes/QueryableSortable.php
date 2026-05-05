<?php

declare(strict_types=1);

namespace Zaruto\Queryable\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class QueryableSortable
{
    /** @param array<int, string> $fields */
    public function __construct(public array $fields) {}
}
