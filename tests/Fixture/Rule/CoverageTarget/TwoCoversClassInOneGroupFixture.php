<?php

declare(strict_types=1);

namespace Steevanb\PhpStanRules\Tests\Fixture\Rule\CoverageTarget;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TargetFixture::class), CoversClass(OtherTargetFixture::class)]
final class TwoCoversClassInOneGroupFixture
{
}
