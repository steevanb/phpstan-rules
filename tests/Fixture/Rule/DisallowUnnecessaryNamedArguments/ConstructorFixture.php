<?php

declare(strict_types=1);

namespace Steevanb\PhpStanRules\Tests\Fixture\Rule\DisallowUnnecessaryNamedArguments;

final class ConstructorFixture
{
    public function run(): int
    {
        return new TargetFixture(1, 2, 3)->target()
            + new TargetFixture(a: 1)->target()
            + new TargetFixture(b: 2, a: 1)->target()
            + new TargetFixture(1, c: 3)->target()
            + new TargetFixture(c: 3)->target()
            + new \DateTimeImmutable(datetime: 'now')->getTimestamp()
            + new \DateTimeImmutable('now', timezone: new \DateTimeZone('UTC'))->getTimestamp();
    }
}
