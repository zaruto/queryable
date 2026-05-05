<?php

declare(strict_types=1);

namespace Zaruto\Queryable\Contracts;

use Zaruto\Queryable\Filtering\Ast\Node;

interface FilterParserContract
{
    public function parse(string $input): Node;
}
