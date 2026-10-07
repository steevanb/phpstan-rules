<?php

declare(strict_types=1);

namespace Steevanb\PhpStanRules\Tests\Fixture\Rule\CoverageTarget;

use PHPUnit\Framework\Attributes\CoversTrait;

#[CoversTrait(FixtureTrait::class)]
#[CoversTrait(OtherFixtureTrait::class)]
final class TwoCoversTraitFixture
{
}
