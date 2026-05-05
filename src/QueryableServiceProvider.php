<?php

declare(strict_types=1);

namespace Zaruto\Queryable;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class QueryableServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('queryable')
            ->hasConfigFile('queryable');
    }
}
