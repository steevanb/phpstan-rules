<?php

declare(strict_types=1);

namespace Steevanb\PhpStanRules\Tests\Fixture\Rule\DisallowUnnecessaryNamedArguments;

#[AttributeFixture(name: 'attribute')]
#[AttributeFixture(label: 'attribute')]
#[AttributeFixture('attribute', 'attribute')]
#[AttributeFixture('attribute', label: 'attribute')]
#[AttributeFixture(label: 'attribute', name: 'attribute')]
final class AttributeUsageFixture
{
}
