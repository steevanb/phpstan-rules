<?php

declare(strict_types=1);

namespace Steevanb\PhpStanRules\Tests\Fixture\Rule\DisallowUnnecessaryNamedArguments;

final class VariadicFixture
{
    public function run(TargetFixture $target): int
    {
        return $target->collect(1, 2, 3)
            + $target->collect(first: 1)
            + $target->collect(1, rest: 2)
            + $target->collect(first: 1, rest: 2);
    }
}
