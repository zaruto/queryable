<?php

declare(strict_types=1);

namespace Zaruto\Queryable\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Zaruto\Queryable\QueryableServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            QueryableServiceProvider::class,
        ];
    }
}
