<?php

declare(strict_types=1);

namespace Steevanb\PhpStanRules\Tests\Fixture\Rule\DisallowUnnecessaryNamedArguments;

final class FunctionCallFixture
{
    public function run(): string
    {
        return str_pad('x', 3, ' ', STR_PAD_LEFT)
            . str_pad(string: 'x', length: 3)
            . str_pad(length: 3, string: 'x')
            . str_pad('x', 3, pad_type: STR_PAD_LEFT)
            . str_pad('x', 3, ' ', pad_type: STR_PAD_LEFT)
            . sprintf(format: '%d', values: 1)
            . sprintf('%d', values: 1);
    }
}
