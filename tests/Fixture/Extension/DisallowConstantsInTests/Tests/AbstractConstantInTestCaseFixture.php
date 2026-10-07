<?php

declare(strict_types=1);

namespace Steevanb\PhpStanRules\Tests\Fixture\Extension\DisallowConstantsInTests\Tests;

use PHPUnit\Framework\TestCase;
use Steevanb\PhpStanRules\Tests\Fixture\Extension\DisallowConstantsInTests\Source\{
    SourceConstantFixture,
    SourceEnumFixture,
};

abstract class AbstractConstantInTestCaseFixture extends TestCase
{
    public function run(): string
    {
        return SourceConstantFixture::TYPE
            . SourceEnumFixture::FR->value
            . \DateTimeInterface::ATOM
            . ConstantFixture::VALUE;
    }
}
