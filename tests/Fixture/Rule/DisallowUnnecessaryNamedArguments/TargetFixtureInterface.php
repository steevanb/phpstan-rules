<?php

declare(strict_types=1);

namespace Steevanb\PhpStanRules\Tests\Fixture\Rule\DisallowUnnecessaryNamedArguments;

interface TargetFixtureInterface
{
    public function target(int $a = 0, int $b = 0, int $c = 0): int;
}
