<?php

declare(strict_types=1);

namespace Steevanb\PhpStanRules\Tests\Fixture\Rule\DisallowUnnecessaryNamedArguments;

final class UnpackFixture
{
    /** @param array<int> $values */
    public function run(TargetFixture $target, array $values): int
    {
        return $target->target(...$values)
            + $target->target(...$values, c: 3)
            + $target->target(...['a' => 1, 'b' => 2]);
    }
}
