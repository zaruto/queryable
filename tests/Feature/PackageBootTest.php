<?php

declare(strict_types=1);

it('boots package config', function (): void {
    expect(config('queryable.strict_mode'))->toBeTrue();
});
