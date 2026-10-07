<?php

declare(strict_types=1);

namespace Steevanb\PhpStanRules\Tests\Fixture\Rule\DisallowUnnecessaryNamedArguments;

final class MethodCallFixture
{
    public function run(TargetFixture $target): int
    {
        return $target->target(1, 2, 3)
            + $target->target(a: 1)
            + $target->target(a: 1, b: 2)
            + $target->target(a: 1, b: 2, c: 3)
            + $target->target(b: 2, a: 1)
            + $target->target(c: 3, b: 2, a: 1)
            + $target->target(1, b: 2)
            + $target->target(1, 2, c: 3)
            + $target->target(1, c: 3, b: 2)
            + $target->target(1, c: 3)
            + $target->target(b: 2)
            + $target->target(c: 3)
            + $target->target(b: 2, c: 3)
            + $target->target(c: 3, b: 2)
            + $target->target(a: 1, c: 3)
            + $target->target(c: 3, a: 1)
            + $target->target(
                a: 1,
                b: 2,
            )
            + $target->target(
                1,
                c: 3,
            );
    }
}
