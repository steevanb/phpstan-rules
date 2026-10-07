<?php

declare(strict_types=1);

namespace Steevanb\PhpStanRules\Tests\Fixture\Rule\DisallowUnnecessaryNamedArguments;

final class TargetFixture implements TargetFixtureInterface
{
    public static function create(int $a = 0, int $b = 0, int $c = 0): self
    {
        return new self($a, $b, $c);
    }

    public function __construct(private int $a = 0, private int $b = 0, private int $c = 0)
    {
    }

    #[\Override]
    public function target(int $a = 0, int $b = 0, int $c = 0): int
    {
        return $this->a + $this->b + $this->c + $a + $b + $c;
    }

    public function collect(int $first = 0, int ...$rest): int
    {
        return $first + array_sum($rest);
    }
}
