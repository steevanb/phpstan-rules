<?php

declare(strict_types=1);

namespace Steevanb\PhpStanRules\Tests\Fixture\Rule\DisallowUnnecessaryNamedArguments;

final class InterfaceMethodCallFixture
{
    public function run(TargetFixtureInterface $target): int
    {
        return $target->target(a: 1)
            + $target->target(b: 2)
            + $target->target(1, c: 3, b: 2);
    }
}
