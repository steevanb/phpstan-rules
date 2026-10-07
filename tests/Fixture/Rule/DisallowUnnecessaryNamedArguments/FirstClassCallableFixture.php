<?php

declare(strict_types=1);

namespace Steevanb\PhpStanRules\Tests\Fixture\Rule\DisallowUnnecessaryNamedArguments;

final class FirstClassCallableFixture
{
    public function run(TargetFixture $target): int
    {
        return $target->target(...)(a: 1)
            + TargetFixture::create(...)(b: 2)->target()
            + strlen(str_pad(...)(string: 'x', length: 3));
    }
}
