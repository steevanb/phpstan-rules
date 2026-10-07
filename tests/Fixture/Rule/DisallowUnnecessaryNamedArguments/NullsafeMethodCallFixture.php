<?php

declare(strict_types=1);

namespace Steevanb\PhpStanRules\Tests\Fixture\Rule\DisallowUnnecessaryNamedArguments;

final class NullsafeMethodCallFixture
{
    public function run(?TargetFixture $target): ?int
    {
        return $target?->target(a: 1)
            ?? $target?->target(1, b: 2)
            ?? $target?->target(c: 3);
    }
}
