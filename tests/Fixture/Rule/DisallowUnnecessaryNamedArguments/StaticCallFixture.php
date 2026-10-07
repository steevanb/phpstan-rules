<?php

declare(strict_types=1);

namespace Steevanb\PhpStanRules\Tests\Fixture\Rule\DisallowUnnecessaryNamedArguments;

final class StaticCallFixture
{
    public static function create(int $a = 0, int $b = 0): TargetFixture
    {
        return TargetFixture::create($a, $b);
    }

    public function run(): int
    {
        return TargetFixture::create(1, 2, 3)->target()
            + TargetFixture::create(a: 1)->target()
            + TargetFixture::create(b: 2, a: 1)->target()
            + TargetFixture::create(1, c: 3)->target()
            + TargetFixture::create(c: 3)->target()
            + static::create(a: 1)->target()
            + static::create(1, b: 2)->target()
            + static::create(b: 2)->target();
    }
}
