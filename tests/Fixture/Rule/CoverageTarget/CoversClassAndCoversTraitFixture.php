<?php

declare(strict_types=1);

namespace Steevanb\PhpStanRules\Tests\Fixture\Rule\CoverageTarget;

use PHPUnit\Framework\Attributes\{
    CoversClass,
    CoversTrait,
};

#[CoversClass(TargetFixture::class)]
#[CoversTrait(FixtureTrait::class)]
final class CoversClassAndCoversTraitFixture
{
}
