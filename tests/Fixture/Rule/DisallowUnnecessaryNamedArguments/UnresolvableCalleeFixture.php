<?php

declare(strict_types=1);

namespace Steevanb\PhpStanRules\Tests\Fixture\Rule\DisallowUnnecessaryNamedArguments;

final class UnresolvableCalleeFixture
{
    /**
     * @param class-string<TargetFixture> $className
     * @return array<mixed>
     */
    public function run(mixed $unknown, string $method, string $function, string $className): array
    {
        return [
            $unknown->target(a: 1),
            $unknown::create(a: 1),
            $this->{$method}(a: 1),
            $function(a: 1),
            new $className(a: 1),
            $className::create(a: 1),
        ];
    }
}
