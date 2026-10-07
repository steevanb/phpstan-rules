<?php

declare(strict_types=1);

namespace Steevanb\PhpStanRules\Tests\Fixture\Rule\DisallowUnnecessaryNamedArguments;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class AttributeFixture
{
    public function __construct(public string $name = '', public string $label = '')
    {
    }
}
