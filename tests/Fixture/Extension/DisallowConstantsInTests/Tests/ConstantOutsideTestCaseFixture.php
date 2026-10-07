<?php

declare(strict_types=1);

namespace Steevanb\PhpStanRules\Tests\Fixture\Extension\DisallowConstantsInTests\Tests;

use Steevanb\PhpStanRules\Tests\Fixture\Extension\DisallowConstantsInTests\Source\SourceConstantFixture;

final class ConstantOutsideTestCaseFixture
{
    public function run(): string
    {
        return SourceConstantFixture::TYPE;
    }
}
