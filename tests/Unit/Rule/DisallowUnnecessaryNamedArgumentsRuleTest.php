<?php

declare(strict_types=1);

namespace Steevanb\PhpStanRules\Tests\Unit\Rule;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Steevanb\PhpStanRules\{
    Rule\DisallowUnnecessaryNamedArgumentsRule,
    Tests\Fixture\Rule\DisallowUnnecessaryNamedArguments\AttributeFixture,
    Tests\Fixture\Rule\DisallowUnnecessaryNamedArguments\InterfaceMethodCallFixture,
    Tests\Fixture\Rule\DisallowUnnecessaryNamedArguments\MethodCallFixture,
    Tests\Fixture\Rule\DisallowUnnecessaryNamedArguments\NullsafeMethodCallFixture,
    Tests\Fixture\Rule\DisallowUnnecessaryNamedArguments\StaticCallFixture,
    Tests\Fixture\Rule\DisallowUnnecessaryNamedArguments\TargetFixture,
    Tests\Fixture\Rule\DisallowUnnecessaryNamedArguments\TargetFixtureInterface,
    Tests\Fixture\Rule\DisallowUnnecessaryNamedArguments\VariadicFixture,
};

/** @extends RuleTestCase<DisallowUnnecessaryNamedArgumentsRule> */
#[CoversClass(DisallowUnnecessaryNamedArgumentsRule::class)]
final class DisallowUnnecessaryNamedArgumentsRuleTest extends RuleTestCase
{
    private const string SINGULAR = ' is unnecessary: pass it positionally, in the order of the parameters.';

    private const string PLURAL = ' are unnecessary: pass them positionally, in the order of the parameters.';

    public function testMethodCall(): void
    {
        $this->analyse(
            [$this->getFixture(MethodCallFixture::class)],
            [
                ['Named argument a of ' . TargetFixture::class . '::target()' . self::SINGULAR, 12],
                ['Named arguments a, b of ' . TargetFixture::class . '::target()' . self::PLURAL, 13],
                ['Named arguments a, b, c of ' . TargetFixture::class . '::target()' . self::PLURAL, 14],
                ['Named arguments a, b of ' . TargetFixture::class . '::target()' . self::PLURAL, 15],
                ['Named arguments a, b, c of ' . TargetFixture::class . '::target()' . self::PLURAL, 16],
                ['Named argument b of ' . TargetFixture::class . '::target()' . self::SINGULAR, 17],
                ['Named argument c of ' . TargetFixture::class . '::target()' . self::SINGULAR, 18],
                ['Named arguments b, c of ' . TargetFixture::class . '::target()' . self::PLURAL, 19],
                ['Named argument a of ' . TargetFixture::class . '::target()' . self::SINGULAR, 25],
                ['Named argument a of ' . TargetFixture::class . '::target()' . self::SINGULAR, 26],
                ['Named arguments a, b of ' . TargetFixture::class . '::target()' . self::PLURAL, 27],
            ]
        );
    }

    public function testNullsafeMethodCall(): void
    {
        $this->analyse(
            [$this->getFixture(NullsafeMethodCallFixture::class)],
            [
                ['Named argument a of ' . TargetFixture::class . '::target()' . self::SINGULAR, 11],
                ['Named argument b of ' . TargetFixture::class . '::target()' . self::SINGULAR, 12],
            ]
        );
    }

    public function testInterfaceMethodCall(): void
    {
        $this->analyse(
            [$this->getFixture(InterfaceMethodCallFixture::class)],
            [
                ['Named argument a of ' . TargetFixtureInterface::class . '::target()' . self::SINGULAR, 11],
                ['Named arguments b, c of ' . TargetFixtureInterface::class . '::target()' . self::PLURAL, 13],
            ]
        );
    }

    public function testConstructor(): void
    {
        $this->analyse(
            [$this->getFixture('ConstructorFixture')],
            [
                ['Named argument a of ' . TargetFixture::class . '::__construct()' . self::SINGULAR, 12],
                ['Named arguments a, b of ' . TargetFixture::class . '::__construct()' . self::PLURAL, 13],
                ['Named argument datetime of DateTimeImmutable::__construct()' . self::SINGULAR, 16],
                ['Named argument timezone of DateTimeImmutable::__construct()' . self::SINGULAR, 17],
            ]
        );
    }

    public function testStaticCall(): void
    {
        $this->analyse(
            [$this->getFixture(StaticCallFixture::class)],
            [
                ['Named argument a of ' . TargetFixture::class . '::create()' . self::SINGULAR, 17],
                ['Named arguments a, b of ' . TargetFixture::class . '::create()' . self::PLURAL, 18],
                ['Named argument a of ' . StaticCallFixture::class . '::create()' . self::SINGULAR, 21],
                ['Named argument b of ' . StaticCallFixture::class . '::create()' . self::SINGULAR, 22],
            ]
        );
    }

    public function testFunctionCall(): void
    {
        $this->analyse(
            [$this->getFixture('FunctionCallFixture')],
            [
                ['Named arguments string, length of str_pad()' . self::PLURAL, 12],
                ['Named arguments string, length of str_pad()' . self::PLURAL, 13],
                ['Named argument pad_type of str_pad()' . self::SINGULAR, 15],
                ['Named argument format of sprintf()' . self::SINGULAR, 16],
            ]
        );
    }

    public function testVariadic(): void
    {
        $this->analyse(
            [$this->getFixture(VariadicFixture::class)],
            [
                ['Named argument first of ' . TargetFixture::class . '::collect()' . self::SINGULAR, 12],
                ['Named argument first of ' . TargetFixture::class . '::collect()' . self::SINGULAR, 14],
            ]
        );
    }

    public function testUnpack(): void
    {
        $this->analyse([$this->getFixture('UnpackFixture')], []);
    }

    public function testFirstClassCallable(): void
    {
        $this->analyse([$this->getFixture('FirstClassCallableFixture')], []);
    }

    public function testUnresolvableCallee(): void
    {
        $this->analyse([$this->getFixture('UnresolvableCalleeFixture')], []);
    }

    public function testAttribute(): void
    {
        $this->analyse(
            [$this->getFixture('AttributeUsageFixture')],
            [
                ['Named argument name of ' . AttributeFixture::class . '::__construct()' . self::SINGULAR, 7],
                ['Named argument label of ' . AttributeFixture::class . '::__construct()' . self::SINGULAR, 10],
                ['Named arguments name, label of ' . AttributeFixture::class . '::__construct()' . self::PLURAL, 11],
            ]
        );
    }

    #[\Override]
    protected function getRule(): Rule
    {
        return new DisallowUnnecessaryNamedArgumentsRule(static::createReflectionProvider());
    }

    private function getFixture(string $fixture): string
    {
        return dirname(__DIR__, 2)
            . '/Fixture/Rule/DisallowUnnecessaryNamedArguments/'
            . basename(str_replace('\\', '/', $fixture))
            . '.php';
    }
}
