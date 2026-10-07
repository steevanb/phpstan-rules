<?php

declare(strict_types=1);

namespace Steevanb\PhpStanRules\Tests\Unit\Extension;

use PHPStan\Rules\{
    RestrictedUsage\RestrictedClassConstantUsageRule,
    Rule,
};
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Steevanb\PhpStanRules\Extension\DisallowConstantsInTestsExtension;

/** @extends RuleTestCase<RestrictedClassConstantUsageRule> */
#[CoversClass(DisallowConstantsInTestsExtension::class)]
final class DisallowConstantsInTestsExtensionTest extends RuleTestCase
{
    /** @return list<string> */
    #[\Override]
    public static function getAdditionalConfigFiles(): array
    {
        return [self::getFixture('config.neon')];
    }

    private static function getFixture(string $fixture): string
    {
        return dirname(__DIR__, 2) . '/Fixture/Extension/DisallowConstantsInTests/' . $fixture;
    }

    public function testConstantInTestCase(): void
    {
        $this->analyse(
            [self::getFixture('Tests/AbstractConstantInTestCaseFixture.php')],
            [
                [
                    'Using Steevanb\PhpStanRules\Tests\Fixture\Extension\DisallowConstantsInTests\Source\\'
                        . 'SourceConstantFixture::TYPE in tests is disallowed, use the literal value instead.',
                    17,
                ],
                ['Using DateTimeInterface::ATOM in tests is disallowed, use the literal value instead.', 19],
            ]
        );
    }

    public function testConstantOutsideTestCase(): void
    {
        $this->analyse([self::getFixture('Tests/ConstantOutsideTestCaseFixture.php')], []);
    }

    #[\Override]
    protected function getRule(): Rule
    {
        return static::getContainer()->getByType(RestrictedClassConstantUsageRule::class);
    }
}
