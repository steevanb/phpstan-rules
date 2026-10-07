<?php

declare(strict_types=1);

namespace Steevanb\PhpStanRules\Tests\Unit\Rule;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\{
    CoversClass,
    DataProvider,
};
use Steevanb\PhpStanRules\Rule\CoverageTargetRule;

/** @extends RuleTestCase<CoverageTargetRule> */
#[CoversClass(CoverageTargetRule::class)]
final class CoverageTargetRuleTest extends RuleTestCase
{
    private const string FIXTURES_NAMESPACE = 'Steevanb\PhpStanRules\Tests\Fixture\Rule\CoverageTarget\\';

    /** @return array<string, array{string, int}> */
    public static function severalCoverageTargetsDataProvider(): array
    {
        return [
            'two CoversClass' => ['TwoCoversClassFixture', 9],
            'two CoversClass in one group' => ['TwoCoversClassInOneGroupFixture', 9],
            'CoversClass and CoversTrait' => ['CoversClassAndCoversTraitFixture', 12],
            'two CoversTrait' => ['TwoCoversTraitFixture', 9],
        ];
    }

    /** @return array<string, array{string}> */
    public static function validCoverageTargetDataProvider(): array
    {
        return [
            'single CoversClass' => ['SingleCoversClassFixture'],
            'single CoversTrait' => ['SingleCoversTraitFixture'],
            'no coverage target' => ['NoCoverageTargetFixture'],
            'mirrored class of tests' => ['MirroredFixtureTest'],
            'mirrored class of src' => ['MirroredInAppFixtureTest'],
            'mirrored trait' => ['MirroredTraitFixtureTest'],
            'mirrored class given as a string' => ['MirroredStringFixtureTest'],
            'class without the Test suffix' => ['NoTestSuffixFixture'],
        ];
    }

    private bool $mirroring = true;

    #[DataProvider('severalCoverageTargetsDataProvider')]
    public function testSeveralCoverageTargets(string $fixture, int $line): void
    {
        $this->analyse(
            [$this->getFixture($fixture)],
            [
                [
                    self::FIXTURES_NAMESPACE . $fixture . ' declares 2 #[CoversClass] / #[CoversTrait] attributes, '
                        . 'a test class covers a single class or a single trait.',
                    $line,
                ],
            ]
        );
    }

    #[DataProvider('validCoverageTargetDataProvider')]
    public function testValidCoverageTarget(string $fixture): void
    {
        $this->analyse([$this->getFixture($fixture)], []);
    }

    public function testNotMirroredCoverageTarget(): void
    {
        $this->analyse(
            [$this->getFixture('NotMirroredFixtureTest')],
            [
                [
                    self::FIXTURES_NAMESPACE . 'NotMirroredFixtureTest covers ' . self::FIXTURES_NAMESPACE
                        . 'TargetFixture, a test class covers the class or trait it mirrors: '
                        . 'Steevanb\PhpStanRules\Tests\Rule\CoverageTarget\NotMirroredFixture or '
                        . 'Steevanb\PhpStanRules\Rule\CoverageTarget\NotMirroredFixture.',
                    9,
                ],
            ]
        );
    }

    public function testForeignCoverageTarget(): void
    {
        $this->analyse(
            [$this->getFixture('ForeignTargetFixtureTest')],
            [
                [
                    self::FIXTURES_NAMESPACE . 'ForeignTargetFixtureTest covers DateTimeImmutable, '
                        . 'a test class covers the class or trait it mirrors: '
                        . 'Steevanb\PhpStanRules\Tests\Rule\CoverageTarget\ForeignTargetFixture or '
                        . 'Steevanb\PhpStanRules\Rule\CoverageTarget\ForeignTargetFixture.',
                    9,
                ],
            ]
        );
    }

    public function testMirroringWithoutNamespacePrefixes(): void
    {
        $this->mirroring = false;

        $this->analyse([$this->getFixture('NotMirroredFixtureTest')], []);
    }

    #[\Override]
    protected function getRule(): Rule
    {
        return $this->mirroring
            ? new CoverageTargetRule(
                ['Steevanb\PhpStanRules\Tests\Fixture\\'],
                ['Steevanb\PhpStanRules\Tests\\', 'Steevanb\PhpStanRules\\'],
            )
            : new CoverageTargetRule([], []);
    }

    private function getFixture(string $fixture): string
    {
        return dirname(__DIR__, 2) . '/Fixture/Rule/CoverageTarget/' . $fixture . '.php';
    }
}
