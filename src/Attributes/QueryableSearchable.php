<?php

declare(strict_types=1);

namespace Zaruto\Queryable\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class QueryableSearchable
{
    /** @param array<string, string> $fields */
    public function __construct(public array $fields) {}
}
